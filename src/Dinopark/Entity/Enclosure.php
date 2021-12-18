<?php

namespace App\Dinopark\Entity;

use App\Dinopark\Exception\DinosaursAreRunningRampantException;
use App\Dinopark\Exception\NotABuffetException;

class Enclosure
{
    /** @var Security[] */
    private array $securities = [];
    /** @var Dinosaur[] */
    private array $dinosaurs = [];

    /**
     * @throws DinosaursAreRunningRampantException
     * @throws NotABuffetException
     */
    public function __construct(bool $withBasicSecurity = false, array $initialDinosaurs = [])
    {
        if ($withBasicSecurity) {
            $this->addSecurity(new Security('Fence', true, $this));
        }

        foreach ($initialDinosaurs as $dinosaur) {
            $this->addDinosaur($dinosaur);
        }
    }

    public function getDinosaurs(): array
    {
        return $this->dinosaurs;
    }

    public function addDinosaur(Dinosaur $dinosaur)
    {
        if (!$this->canAddDinosaur($dinosaur)) {
            throw new NotABuffetException();
        }
        if (!$this->isSecurityActive()) {
            throw new DinosaursAreRunningRampantException('Are you craaazy?!?');
        }
        $this->dinosaurs[] = $dinosaur;
    }

    private function canAddDinosaur(Dinosaur $dinosaur) : bool
    {
        return count($this->dinosaurs) === 0 || $this->dinosaurs[0]->hasSameDietAs($dinosaur);
    }

    public function isSecurityActive(): bool
    {
        foreach ($this->securities as $security) {
            if ($security->getIsActive()) {
                return true;
            }
        }
        return false;
    }

    public function addSecurity(Security $security)
    {
        $this->securities[] = $security;
    }


}
