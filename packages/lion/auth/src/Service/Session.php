<?php

namespace Lion\Auth\Service;

class Session
{
    /**
     * @var bool
     */
    private $isAdAuthenticated = false;
    /**
     * @var string
     */
    private $sessionKey = '';
    /**
     * @var string
     */
    private $oAuthVerifier = '';
    /**
     * @var string
     */
    private $oAuthChallenge = '';
    /**
     * @var string
     */
    private $code = '';
    /**
     * @var string
     */
    private $accessToken = '';
    /**
     * @var string
     */
    private $idToken = '';
    /**
     * @var string
     */
    private $refreshToken = '';
    /**
     * @var string
     */
    private $scope = '';
    /**
     * @var ?int
     */
    private $expirationSeconds = null;
    /**
     * @var array
     */
    private $defaults = [];

    public function __construct()
    {
        $this->defaults = get_object_vars($this);
        $this->setFromSession();
    }

    public function __set($property, $value) : void
    {
        if (property_exists($this, $property)) {
            $_SESSION['azureAd'][$property] = $value;
            $this->$property = $value;
        }
    }

    public function __get($property)
    {
        return $this->$property;
    }

    public function reset() : void
    {
        foreach ($this->defaults as $property => $value) {
            $this->$property = $_SESSION['azureAd'][$property] = $value;
        }
        session_destroy();
        session_start();
    }

    private function setFromSession()
    {
        session_start();
        foreach (array_keys($this->defaults) as $property) {
            if (isset($_SESSION['azureAd'][$property])) {
                $this->$property = $_SESSION['azureAd'][$property];
            }
        }
    }
}
