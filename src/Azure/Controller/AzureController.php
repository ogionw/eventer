<?php

namespace App\Azure\Controller;

use Lion\Auth\Exception\AzureException;
use Lion\Auth\Factory\AzureServiceFactory;
use Lion\Auth\Service\AzureService;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\Application;
use Microsoft\Graph\Model\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Yaml\Yaml;

class AzureController extends AbstractController
{
    private AzureService $azureService;

    #[Route('/azure', name: 'azure')]
    /** @throws AzureException */
    public function index(AzureServiceFactory $azureServiceFactory, Request $request): Response
    {
        $azureService = $azureServiceFactory->create(
            $request->getSchemeAndHttpHost(),
            $this->getParameter('kernel.project_dir'),
            '/config/'
        );
        $msg = $azureService->isAdAuthenticated()
            ? 'you are authed <a href="/azure/logout">Log out</a>'
            : 'You are not logged in, please <a href="'.$azureService->getLoginUrl().'">login</a>';
        return new Response($msg);
    }

    /**
     * @throws AzureException
     */
    #[Route('/azure/oauth', name: 'oauth')]
    public function oauth(AzureServiceFactory $azureServiceFactory, Request $request): Response
    {
        $this->azureService = $azureServiceFactory->create(
            $request->getSchemeAndHttpHost(),
            $this->getParameter('kernel.project_dir'),
            '/config/'
        );
        if ($request->query->has('error')) {
            throw new AzureException('unknown authentication error', $request->query->get('error_description'));
        }
        if(! $request->query->has('code')){
            throw new AzureException('no code');
        }
        $userData = $this->azureService->getUserData($request->query->get('code'));
        return $this->render('azure/index.html.twig', [
            'photoSrc' => '',
            'userName' => $userData->email ?? 'default_username',
            'displayName' => 'default_display_name',
            'profile'=>print_r($userData->profile, true),
            'userRoles' => $userData->roles
        ]);
    }

    #[Route('/azure/logout', name: 'logout')]
    public function logout(AzureServiceFactory $azureServiceFactory, Request $request): Response
    {
        $azureService = $azureServiceFactory->create(
            $request->getSchemeAndHttpHost(),
            $this->getParameter('kernel.project_dir'),
            '/config/'
        );
        $url = $azureService->logout();
        return new RedirectResponse($url ?: '/azure');
    }

    #[Route('/azure/populate', name: 'test')]
    public function populate(AzureServiceFactory $azureServiceFactory, Request $request): Response
    {
        $azureService = $azureServiceFactory->create(
            $request->getSchemeAndHttpHost(),
            $this->getParameter('kernel.project_dir'),
            '/config/'
        );
        $data[] = [
            'name' => 'MyFirstGroup',
            'description' => 'desc desc desc',
            'key'=>'aaa_bbb',
        ];
        //var_dump($azureService->getAccessToken());
        //$result = $azureService->createGroups($data);
        //$result = $azureService->createRoles([[]]);
        $result = $azureService->assignRolesToGroup(['role'], 'group');

        return new JsonResponse($result);
    }

    #[Route('/azure/sdk', name: 'sdk')]
    public function sdk(AzureServiceFactory $azureServiceFactory, Request $request): Response
    {
        $config = Yaml::parseFile($this->getParameter('kernel.project_dir').
            '/config/'.'lion-auth-config.yaml');
//        $guzzle = new Client(['verify' => false]);
//        $url = 'https://login.microsoftonline.com/' . $config['params']['tenant_id'] . '/oauth2/token?api-version=1.0';
//        $token = json_decode($guzzle->post($url, [
//            'form_params' => [
//                'client_id' => $config['params']['application_id'],
//                'client_secret' => $config['params']['secret_value'],
//                'resource' => 'https://graph.microsoft.com/',
//                'grant_type' => 'client_credentials',
//            ],
//        ])->getBody()->getContents());
//        $accessToken = $token->access_token;
        $azureService = $azureServiceFactory->create(
            $request->getSchemeAndHttpHost(),
            $this->getParameter('kernel.project_dir'),
            '/config/'
        );
        $graph = new Graph();
        $graph->setAccessToken($azureService->getAccessToken());

        $user = $graph->createRequest("GET", "/me")
            ->setReturnType(User::class)
            ->execute();

        $data = [
            "appRoles" => [
                [
                    'allowedMemberTypes' => [0 => 'User'],
                    'description' => 'this is task writers index 0',
                    'displayName' => 'TaskWriters',
                    'id' => '10c31468-50e0-4fb1-9359-caae6dd18ab4',
                    'isEnabled' => true,
                    'origin' => 'Application',
                    'value' => 'Task.Write'
                ],
                [
                    'allowedMemberTypes' => [0 => 'User'],
                    'description' => 'this is admins index 1',
                    'displayName' => 'Admins',
                    'id' => 'b52bd783-789b-452b-8aad-ff2ab52b33ca',
                    'isEnabled' => true,
                    'origin' => 'Application',
                    'value' => 'Eventer.Admin'
                ]
            ]
        ];
        $graph->createRequest("PATCH", "/applications")
            ->attachBody($data)
            ->setReturnType(Application::class)
            ->execute();
        $application = $graph->createRequest("GET", "/applications/".$config['params']['object_id'])
            ->setReturnType(Application::class)
            ->execute();
        var_export($application[0]->getAppRoles()[0]);
        return new Response("Hello, I am {$user->getGivenName()}.");
    }
//
//    #[Route('/azure/good', name: 'good')]
//    public function good(): Response
//    {
//        return new Response('good');
//    }
}
