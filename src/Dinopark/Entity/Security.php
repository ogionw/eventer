<?php

namespace App\Dinopark\Entity;

class Security
{
    private string $name;
    private bool $isActive;
    private Enclosure $enclosure;
    public function __construct(string $name, bool $isActive, Enclosure $enclosure)
    {
        $this->name = $name;
        $this->isActive = $isActive;
        $this->enclosure = $enclosure;
    }
    public function getIsActive(): bool
    {
        return $this->isActive;
    }
}