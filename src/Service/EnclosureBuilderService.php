<?php

namespace App\Service;

use App\Entity\Enclosure;
use App\Entity\Security;
use App\Factory\DinosaurFactory;
use spec\Service\EntityManagerInterface;

class EnclosureBuilderService
{
    public function __construct(private DinosaurFactory $dinosaurFactory, private EntityManagerInterface $entityManager)
    {
    }

    public function buildEnclosure(int $numberOfSecuritySystems = 1, int $numberOfDinosaurs = 3): Enclosure
    {
        $enclosure = new Enclosure();
        $this->addSecuritySystems($numberOfSecuritySystems, $enclosure);
        $this->addDinosaurs($numberOfDinosaurs, $enclosure);
        $this->entityManager->persist($enclosure);
        $this->entityManager->flush();
        return $enclosure;
    }

    private function addSecuritySystems(int $numberOfSecuritySystems, Enclosure $enclosure)
    {
        $securityNames = ['Fence', 'Electric fence', 'Guard tower'];
        for ($i = 0; $i < $numberOfSecuritySystems; $i++) {
            $securityName = $securityNames[array_rand($securityNames)];
            $security = new Security($securityName, true, $enclosure);
            $enclosure->addSecurity($security);
        }
    }

    private function addDinosaurs(int $numberOfDinosaurs, Enclosure $enclosure)
    {
        for ($i = 0; $i < $numberOfDinosaurs; $i++) {
            $enclosure->addDinosaur(
                $this->dinosaurFactory->growVelociraptor(5+$i)
            );
        }
    }
}
