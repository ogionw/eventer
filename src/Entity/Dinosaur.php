<?php

namespace App\Entity;

class Dinosaur
{
    private int $length = 0;

    public function __construct(private string $genus = 'Unknown', private bool $isCarnivorous = false){}

    public static function growVelociraptor(int $length): self
    {
        $dinosaur = new static('Velociraptor', true);
        $dinosaur->setLength($length);
        return $dinosaur;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function setLength(int $length)
    {
        $this->length = $length;
    }

    public function getDescription(): string
    {
        return sprintf(
            'The %s %scarnivorous dinosaur is %d meters long',
            $this->genus,
            $this->isCarnivorous ? '' : 'non-',
            $this->length
        );
    }

    public function getGenus()
    {
        return $this->genus;
    }

    public function isCarnivorous() : bool
    {
        return $this->isCarnivorous;
    }

    public function hasSameDietAs(Dinosaur $dinosaur) : bool
    {
        return $this->isCarnivorous === $dinosaur->isCarnivorous();
    }
}
