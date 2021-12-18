<?php

namespace Lion\Auth\Service;

use Lion\Auth\Dto\UserData;
use Lion\Auth\Exception\AzureException;
use stdClass;

class OAuth
{
    private const OAUTH_SCOPE = 'openid%20offline_access%20profile%20user.read';
    private const OAUTH_BASE_URL = 'https://login.microsoftonline.com/';
    public $config = [];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @throws AzureException
     */
    public function fillWithProfileData(Session $session, UserData $userData): void
    {
        $data = 'grant_type=authorization_code' .
            '&client_id=' .$this->config['params']['application_id'] .
            '&redirect_uri=' . urlencode($this->config['urls']['base_url'].$this->config['urls']['process_url_part']) .
            '&code=' . $session->code .
            '&code_verifier=' . $session->oAuthVerifier .
            '&client_secret=' . urlencode($this->config['params']['secret_value']);
        $url = self::OAUTH_BASE_URL . $this->config['params']['tenant_id'] . '/oauth2/v2.0/token';

        $response = $this->curl($url, $data);
        $reply = $this->extractReply($response);

        $session->accessToken = $reply->access_token;
        $session->idToken = $reply->id_token;
        $session->refreshToken = $reply->refresh_token;
        //$this->scope = $reply->scope;
        $session->expirationSeconds = $reply->expires_in;
        $base64DecodedToken = base64_decode(explode('.', $reply->id_token)[1]);
        $userData->authData = json_decode($base64DecodedToken);
        $userData->email = $userData->authData->preferred_username;
        $userData->roles = $userData->authData->roles;
    }

    /**
     * @throws AzureException
     */
    public function curl(string $url, string $data) : string
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);
        if ($cError = curl_error($curl)) {
            throw new AzureException('oauth request error', $cError);
        }
        curl_close($curl);
        return $response;
    }

    private function extractReply(string $response) : stdClass
    {
        if (!$response) {
            throw new AzureException('Unknown error acquiring token', $response);
        }
        $reply = json_decode($response);
        if (!$reply) {
            throw new AzureException('cannot decode oauth response', $response);
        }
        if (property_exists($reply, 'error') && $reply->error) {
            throw new AzureException('error exists in oauth reply', $reply->error_description);
        }
        return $reply;
    }

    public function getLoginUrl(string $oAuthChallenge): string
    {
        return self::OAUTH_BASE_URL . $this->config['params']['tenant_id'] . '/oauth2/v2.0/authorize?'.
            'response_type=code&client_id=' . $this->config['params']['application_id'] .
            '&redirect_uri=' . urlencode($this->config['urls']['base_url'].$this->config['urls']['process_url_part']) .
            '&scope=' . self::OAUTH_SCOPE .
            '&code_challenge=' . $oAuthChallenge .
            '&code_challenge_method=S256';
    }

    public function getLogoutUrl(): string
    {
        return self::OAUTH_BASE_URL.$this->config['params']['tenant_id'].'/oauth2/logout?'.
            'post_logout_redirect_uri='.
            urlencode($this->config['urls']['base_url'].$this->config['urls']['redirect_url_part']);
    }
}
