<?php

namespace spec\App\Dinopark\Service;

use App\Dinopark\Entity\Dinosaur;
use App\Dinopark\Entity\Enclosure;
use App\Dinopark\Factory\DinosaurFactory;
use App\Dinopark\Service\EnclosureBuilderService;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use App\Dinopark\Service\EntityManagerInterface;

class EnclosureBuilderServiceSpec extends ObjectBehavior
{
    function let(DinosaurFactory $dinosaurFactory, EntityManagerInterface $entityManager)
    {
        $this->beConstructedWith($dinosaurFactory, $entityManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EnclosureBuilderService::class);
    }

    function it_builds_enclosure_with_dinosaurs(DinosaurFactory $dinosaurFactory, EntityManagerInterface $entityManager)
    {
        $dino1 = new Dinosaur('Stegosaurus', false);
        $dino1->setLength(6);
        $dino2 = new Dinosaur('Baby Stegosaurus', false);
        $dino2->setLength(2);
        $dinosaurFactory->growVelociraptor(Argument::type('integer'))->willReturn(
            $dino1,
            $dino2
        )->shouldBeCalledTimes(2);;
        $enclosure = $this->buildEnclosure(1, 2);
        $enclosure->shouldBeAnInstanceOf(Enclosure::class);
        $enclosure->isSecurityActive()->shouldReturn(true);
        $enclosure->getDinosaurs()[0]->shouldBe($dino1);
        $enclosure->getDinosaurs()[1]->shouldBe($dino2);
        $entityManager->persist(Argument::type(Enclosure::class))
            ->shouldHaveBeenCalled();
        $entityManager->flush()->shouldHaveBeenCalled();
    }
}
