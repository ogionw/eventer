<?php

namespace App\Azure\Entity;

use App\Repository\AuthSessionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AuthSessionRepository::class)
 */
class AuthSession
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $sessionKey;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $expires;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $redir;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $refreshToken;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $codeVerifier;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $token;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $idToken;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSessionKey(): ?string
    {
        return $this->sessionKey;
    }

    public function setSessionKey(?string $sessionKey): self
    {
        $this->sessionKey = $sessionKey;

        return $this;
    }

    public function getExpires(): ?\DateTimeInterface
    {
        return $this->expires;
    }

    public function setExpires(?\DateTimeInterface $expires): self
    {
        $this->expires = $expires;

        return $this;
    }

    public function getRedir(): ?string
    {
        return $this->redir;
    }

    public function setRedir(?string $redir): self
    {
        $this->redir = $redir;

        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(?string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    public function getCodeVerifier(): ?string
    {
        return $this->codeVerifier;
    }

    public function setCodeVerifier(?string $codeVerifier): self
    {
        $this->codeVerifier = $codeVerifier;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getIdToken(): ?string
    {
        return $this->idToken;
    }

    public function setIdToken(?string $idToken): self
    {
        $this->idToken = $idToken;

        return $this;
    }
}
