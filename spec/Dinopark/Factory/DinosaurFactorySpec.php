<?php

namespace spec\App\Dinopark\Factory;

use App\Dinopark\Entity\Dinosaur;
use App\Dinopark\Factory\DinosaurFactory;
use PhpSpec\Exception\Example\SkippingException;
use PhpSpec\ObjectBehavior;

class DinosaurFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(DinosaurFactory::class);
    }

    function it_grows_a_large_velociraptor()
    {
        $dinosaur = $this->growVelociraptor(5);
        $dinosaur->shouldBeAnInstanceOf(Dinosaur::class);
        $dinosaur->getGenus()->shouldBeString();
        $dinosaur->getGenus()->shouldBe('Velociraptor');
        $dinosaur->getLength()->shouldBe(5);
    }

    function it_grows_a_small_velociraptor()
    {
        if (!class_exists('Nanny')) {
            throw new SkippingException('Skipping cause I dont have nanny class for dino puppies');
        }
        $this->growVelociraptor(1)->shouldBeAnInstanceOf(Dinosaur::class);
    }
}
