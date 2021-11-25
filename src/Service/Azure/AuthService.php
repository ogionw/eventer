<?php

namespace App\Service\Azure;

use App\Entity\AuthSession;
use App\Repository\AuthSessionRepository;
use DateTime;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AuthService
{
    const SKEY = 'sessionkey';
   public $token;
    public $userName;
    public $oAuthVerifier;
    public $oAuthChallenge;
    public $oAuthChallengeMethod;
    public $userRoles;
    public $isLoggedIn;

    function __construct(private AuthSessionRepository $repo, private OAuth $oAuth) {}

    public function deleteSession(AuthSession $session){
        $this->repo->delete($session);
        unset($_SESSION[self::SKEY]);
        session_destroy();
    }

    public function login() : ?RedirectResponse
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $session = isset($_SESSION[self::SKEY]) ? $this->repo->findOneBySessionKey($_SESSION[self::SKEY]) : false;
        if($session && ! $session->getIdToken()){
            $this->deleteSession($session);
            session_start();
            $session = false;
        }
        if(! $session){
            return $this->createNewSession();
        }
        return $this->checkSession($session);
    }

    public function logout() : RedirectResponse
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $session = isset($_SESSION[self::SKEY]) ? $this->repo->findOneBySessionKey($_SESSION[self::SKEY]) : false;
        if(! $session){
            die("no session, cannot logout");
        }
        // Logout action selected, clear from database and browser cookie, redirect to logout URL
        $this->deleteSession($session);
        $logoutUrl = OAuth::OAUTH_LOGOUT.'&post_logout_redirect_uri=' . urlencode(OAuth::REDIRECT_URL);
        $logoutUrl = 'https://login.microsoftonline.com/'.OAuth::OAUTH_TENANTID.'/oauth2/logout?post_logout_redirect_uri='.urlencode(OAuth::REDIRECT_URL);

        return new RedirectResponse($logoutUrl);
    }

    public function refreshToken(AuthSession $session)
    {
        $oauthRequest = $this->oAuth->generateRequest('grant_type=refresh_token&refresh_token=' . $session->getRefreshToken() . '&client_id=' . OAuth::OAUTH_CLIENTID . '&scope=' . OAuth::OAUTH_SCOPE);
        $response = $this->oAuth->postRequest('token', $oauthRequest);
        $reply = json_decode($response);
        if ($reply->error) {
            if(substr($reply->error_description, 0, 12) == 'AADSTS70008:') {
                //refresh token expired
                $session->setRedir(OAuth::REDIRECT_URL);
                $session->setRefreshToken('');
                $session->setExpires((new DateTime())->modify("+5 minutes"));
                $this->repo->save($session);
                $oAuthURL = 'https://login.microsoftonline.com/' . OAuth::OAUTH_TENANTID . '/oauth2/v2.0/' . 'authorize?'.
                    'response_type=code&client_id=' . OAuth::OAUTH_CLIENTID .
                    '&redirect_uri=' . urlencode(OAuth::REDIRECT_URL . '/oauth') .
                    '&scope=' . OAuth::OAUTH_SCOPE .
                    '&code_challenge=' . $this->oAuthChallenge .
                    '&code_challenge_method=' . $this->oAuthChallengeMethod;
                header('Location: ' . $oAuthURL);
                exit;
            }
            echo $this->oAuth->errorMessage($reply->error_description);
            exit;
        }
        $idToken = base64_decode(explode('.', $reply->id_token)[1]);
        $session->setToken($reply->access_token);
        $session->setIdToken($idToken);
        $session->setRefreshToken($reply->refresh_token);
        $session->setRedir('');
        $session->setExpires((new DateTime())->modify("+5 minutes"));
        $session->setExpires((new DateTime())->modify('+' . $reply->expires_in . ' seconds'));
        $this->repo->save($session);
    }

    public function checkSession(AuthSession $session)
    {
        // see if it's still valid. Expiry date doesn't mean that we can't just use the refresh token, so don't test this here
        $this->oAuthVerifier = $session->getCodeVerifier();
        $this->oAuthChallenge();
        if ($session->getExpires() < (new DateTime())->modify("+10 minutes") && $session->getRefreshToken()) {
            $this->refreshToken($session);
        }
        //Populate userData and userName from the JWT stored in the database.
        $this->token = $session->getToken();
        $idToken = json_decode($session->getIdToken());
        $this->userName = $idToken->preferred_username;
        $this->userRoles = property_exists($idToken, 'roles') ? $idToken->roles : ['Default Access'];
        $this->isLoggedIn = true;
        return null;
    }

    public function createNewSession()
    {
        $session = new AuthSession();
        // Generate the code verifier and challenge
        $this->oAuthChallenge();
        // Generate a session key and store in cookie, then populate database
        $_SESSION[self::SKEY] = $this->uuid();
        $session->setSessionKey($_SESSION[self::SKEY]);
        $session->setRedir(OAuth::REDIRECT_URL);
        $session->setCodeVerifier($this->oAuthVerifier);
        $session->setExpires((new DateTime())->modify("+5 minutes"));
        $this->repo->save($session);
        // Redirect to Azure AD login page
        return new RedirectResponse('https://login.microsoftonline.com/' . OAuth::OAUTH_TENANTID . '/oauth2/v2.0/' . 'authorize?response_type=code&client_id=' . OAuth::OAUTH_CLIENTID . '&redirect_uri=' . urlencode(OAuth::REDIRECT_URL . '/oauth') . '&scope=' . OAuth::OAUTH_SCOPE . '&code_challenge=' . $this->oAuthChallenge . '&code_challenge_method=' . $this->oAuthChallengeMethod);
    }

    function checkUserRole($role) {
        // Check that the requested role has been assigned to the user
        if (in_array($role, $this->userRoles)) {
            return 1;
        }
        return;
    }

    function uuid() {
        //uuid function is not my code, but unsure who the original author is. KN
        //uuid version 4
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,
            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }

    function oAuthChallenge() {
        // Function to generate code verifier and code challenge for oAuth login. See RFC7636 for details.
        $verifier = $this->oAuthVerifier;
        if (!$this->oAuthVerifier) {
            $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-._~';
            $charLen = strlen($chars) - 1;
            $verifier = '';
            for ($i = 0; $i < 128; $i++) {
                $verifier .= $chars[mt_rand(0, $charLen)];
            }
            $this->oAuthVerifier = $verifier;
        }
        // Challenge = Base64 Url Encode ( SHA256 ( Verifier ) )
        // Pack (H) to convert 64 char hash into 32 byte hex
        // As there is no B64UrlEncode we use strtr to swap +/ for -_ and then strip off the =
        $this->oAuthChallenge = str_replace('=', '', strtr(base64_encode(pack('H*', hash('sha256', $verifier))), '+/', '-_'));
        $this->oAuthChallengeMethod = 'S256'; //change to S256
    }

}