<?php

namespace Lion\Auth\Service;

use Lion\Auth\Dto\UserData;
use Lion\Auth\Exception\AzureException;
use stdClass;

class Graph
{
    private const PROFILE_URL = 'https://graph.microsoft.com/v1.0/me/';
    private const PHOTO_TYPE_URL = 'https://graph.microsoft.com/v1.0/me/photo/';
    private const PHOTO_VALUE_URL = 'https://graph.microsoft.com/v1.0/me/photo/%24value';
    private const USER_BY_ID = 'https://graph.microsoft.com/v1.0/users/';
    private const GROUP_URL = 'https://graph.microsoft.com/v1.0/groups';
    private const APP_URL = 'https://graph.microsoft.com/v1.0/applications/';
    public $config = [];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @throws AzureException
     */
    public function fillWithProfileData(Session $session, UserData $userData) : void
    {
        if (!$session->accessToken) {
            throw new AzureException('Empty access token send to graph', []);
        }
        $response = $this->curl(self::PROFILE_URL, $session->accessToken);
        $reply = $this->extractReply($response);
        if (! $userData->email) {
            $userData->email = $reply->mail;
        }
        if (! $userData->roles && property_exists($reply, 'userRoles')) {
            $userData->roles = $reply->userRoles;
        }
        $userData->profile = $reply;
    }

    public function fillWithPhotoData(Session $session, UserData $userData)
    {
        $response = $this->curl(self::PHOTO_TYPE_URL, $session->accessToken);
        $photoType = json_decode($response);
        $photo = $this->curl(self::PHOTO_VALUE_URL, $session->accessToken);
        $userData->photo = $photoType && ! $photoType->error
            ? 'data:' . $photoType->{'@odata.mediaContentType'} . ';base64,' . base64_encode($photo)
            : 'https://localhost:8000/img/default-avatar.png';
    }




    /**
     * @throws AzureException
     */
    public function curl(string $url, string $accessToken, array $post = [], string $method = 'GET')
    {
        $headers = ['Authorization: Bearer ' . $accessToken, 'Content-Type: application/json'];
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post));
        }
        if ($method === 'PATCH') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
            //curl_setopt($curl, CURLOPT_PATCH, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post));
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $response = curl_exec($curl);
        if ($cError = curl_error($curl)) {
            throw new AzureException('graph request error', $cError);
        }
        curl_close($curl);
        return $response;
    }

    /**
     * @throws AzureException
     */
    public function extractReply(string $response) : stdClass
    {
        if (!$response) {
            throw new AzureException('Unknown error while getting graph data', $response);
        }
        $reply = json_decode($response);
        if (!$reply) {
            throw new AzureException('cannot decode graph response', $response);
        }
        return $reply;
    }

    public function createUserGroup(Session $session, array $userGroup) : array
    {
        $data = [
            "description" => $userGroup['description'],
            "displayName" => $userGroup['name'],
            "groupTypes" => [],
            "mailEnabled" => false,
            "mailNickname" => $userGroup['key'],
            "securityEnabled" => true,
            "owners@odata.bind" => [self::USER_BY_ID.$this->config['params']['owner_id']]//,
            //"members@odata.bind" => []
        ];
        //var_dump($data); die();
        $response = $this->curl(self::GROUP_URL, $session->accessToken, $data, 'POST');
        var_dump($response); die();
        return $data;
    }

    public function addUserToGroup(){
        //https://graph.microsoft.com/v1.0/groups/{group-id}/members/$ref
        $groupId = "41c7df4d-a29a-4c27-a353-0031f51757ab";
//body:        {
//            "@odata.id": "https://graph.microsoft.com/v1.0/directoryObjects/{id}"
//}
    }

    public function createRole(Session $session, array $role)
    {
        //https://jeevanbmanoj.medium.com/programmatic-ways-to-create-app-roles-in-azure-ad-a21fc93c531b
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
                ],
                [
                    'allowedMemberTypes' => [0 => 'User'],
                    'description' => 'this bfg users index 2',
                    'displayName' => 'BFG users',
                    'id' => 'b7ae1e56-2e22-4e03-bbf8-40992228164e',
                    'isEnabled' => true,
                    'origin' => 'Application',
                    'value' => 'BFG.User'
                ],
            ]
        ];
        $response = $this->curl(self::APP_URL.$this->config['params']['object_id'],
            $session->accessToken,
            $data,
            'PATCH'
        );

        var_dump($response); die();
        return $data;
    }

    /**
     * @throws AzureException
     */
    public function assignRoleToGroup(Session $session, $group, $role)
    {
        $data = [
            "principalId" => "41c7df4d-a29a-4c27-a353-0031f51757ab",//group id
            "resourceId" => $this->config['params']['enterprise_object_id'],//enterprise_object_id
            "appRoleId" => "b7ae1e56-2e22-4e03-bbf8-40992228164e"
        ];
        $response = $this->curl(
            "https://graph.microsoft.com/v1.0/groups/41c7df4d-a29a-4c27-a353-0031f51757ab/appRoleAssignments",
            $session->accessToken,
            $data,
            'POST'
        );
        return $response;
    }
}
