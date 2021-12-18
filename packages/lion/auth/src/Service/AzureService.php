<?php

namespace Lion\Auth\Service;

use Lion\Auth\Dto\UserData;
use Lion\Auth\Exception\AzureException;

class AzureService
{
    private $oauth;
    private $graph;
    private $session;
    private $userData;

    public function __construct(OAuth $oauth, Graph $graph, Session $session, UserData $userData)
    {
        $this->oauth = $oauth;
        $this->graph = $graph;
        $this->session = $session;
        $this->userData = $userData;
        $this->setSessionKey();
        $this->setOAuthVerifier();
        $this->setOAuthChallenge();
    }

    public function getLoginUrl() : string
    {
        return $this->oauth->getLoginUrl($this->session->oAuthChallenge);
    }

    public function getLogoutUrl() : string
    {
        return $this->oauth->getLogoutUrl();
    }

    public function isAdAuthenticated() : bool
    {
        return $this->session->isAdAuthenticated;
    }

    public function getAccessToken() : string
    {
        return $this->session->accessToken;
    }

    public function logout() : string
    {
        if ($this->isAdAuthenticated()) {
            $this->session->reset();
            return $this->oauth->getLogoutUrl();
        }
        return '';
    }

    public function createGroups(array $groups) : array
    {
        $result = [];
        foreach ($groups as $userGroup) {
            $result[] = $this->graph->createUserGroup($this->session, $userGroup);
        }
        return $result;
    }

    public function createRoles(array $roles) : array
    {
        $result = [];
        foreach ($roles as $role) {
            $result[] = $this->graph->createRole($this->session, $role);
        }
        return $result;
    }

    /**
     * @throws AzureException
     */
    public function getUserData(string $code = '') : UserData
    {
        if (! $code) {
            throw new AzureException('you must provide a valid code');
        }
        $this->session->code = $code;
        $this->oauth->fillWithProfileData($this->session, $this->userData);
        $this->session->isAdAuthenticated = true;
        $this->graph->fillWithProfileData($this->session, $this->userData);
        return $this->userData;
    }

    private function setOAuthVerifier()
    {
        if (! $this->session->oAuthVerifier) {
            $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-._~';
            $charLen = strlen($chars) - 1;
            $this->session->oAuthVerifier = '';
            for ($i = 0; $i < 128; $i++) {
                $this->session->oAuthVerifier .= $chars[mt_rand(0, $charLen)];
            }
        }
    }

    private function setOAuthChallenge()
    {
        if (! $this->session->oAuthChallenge) {
            $encode = strtr(base64_encode(pack('H*', hash('sha256', $this->session->oAuthVerifier))), '+/', '-_');
            $this->session->oAuthChallenge = str_replace('=', '', $encode);
        }
    }

    private function setSessionKey()
    {
        if (! $this->session->sessionKey) {
            $this->session->sessionKey = sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff)
            );
        }
    }



    public function testPhoto()
    {
        $this->graph->fillWithPhotoData($this->session, $this->userData);
    }

    public function assignRolesToGroup($roles, $group){
        $result = [];
        foreach ($roles as $role) {
            $result[] = $this->graph->assignRoleToGroup($this->session, $group, $role);
        }
        return $result;
    }
}
