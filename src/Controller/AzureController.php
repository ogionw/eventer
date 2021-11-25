<?php

namespace App\Controller;

use App\Repository\AuthSessionRepository;
use App\Service\Azure\AuthService;
use App\Service\Azure\Graph;
use App\Service\Azure\OAuth;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AzureController extends AbstractController
{
    #[Route('/azure', name: 'azure')]
    public function index(AuthService $authService, Graph $graph): Response
    {
        $resp = $authService->login();
        if($resp){
            return $resp;
        }
        $profile = $graph->getProfile();

        return $this->render('azure/index.html.twig', [
            'photoSrc' => $graph->getPhotoSrc(),
            'userName' => $authService->userName ?? 'default_username',
            'displayName' => !is_null($profile) && $profile->displayName ? $profile->displayName : 'default_display_name',
            'profile'=>print_r($profile, true),
            'userRoles' => $authService->userRoles
        ]);
    }

    #[Route('/azure/oauth', name: 'oauth')]
    public function oauth(Request $request, AuthSessionRepository $repo, OAuth $oAuth): Response
    {
        session_start();
        if ($request->query->get('error')) {
            return $this->render('azure/index.html.twig', [
                'message' => $request->query->get('error_description'),
            ]);
        }
        //retrieve session data from database
        $session = $repo->findOneBySessionKey($_SESSION['sessionkey']);
        if ($session) {
            // Request token from Azure AD

            $oauthRequest = $oAuth->generateRequest($request->query->get('code'),$session->getCodeVerifier());

            $response = $oAuth->postRequest('token', $oauthRequest);

            // Decode response from Azure AD. Extract JWT data from supplied access_token and id_token and update database.
            if (!$response) {
                return $this->render('azure/index.html.twig', [
                    'message' => 'Unknown error acquiring token',
                ]);
            }
            $reply = json_decode($response);
            if (property_exists($reply, 'error') && $reply->error) {
                return $this->render('azure/index.html.twig', [
                    'message' => $reply->error_description,
                ]);
            }
            $session->setToken($reply->access_token);
            $session->setRefreshToken($reply->refresh_token);
            $session->setIdToken(base64_decode(explode('.', $reply->id_token)[1]));
            $session->setRedir('');
            $session->setExpires((new DateTime())->modify('+' . $reply->expires_in . ' seconds'));
            $repo->save($session);
            // Redirect user back to where they came from.
            return new RedirectResponse('/azure');
        } else {
            return new RedirectResponse('/azure/bad');
        }
    }

    #[Route('/azure/logout', name: 'logout')]
    public function logout(AuthService $authService): Response
    {
        return $authService->logout();
    }

    #[Route('/azure/test', name: 'test')]
    public function test(): Response
    {
        return new Response('hello');
    }

    #[Route('/azure/bad', name: 'bad')]
    public function bad(): Response
    {
        return new Response('Oauth error, session was not found in DB');
    }

    #[Route('/azure/good', name: 'good')]
    public function good(): Response
    {
        return new Response('good');
    }
}
