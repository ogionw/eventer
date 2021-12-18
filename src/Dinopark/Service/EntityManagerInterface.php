<?php

namespace App\Dinopark\Service;

interface EntityManagerInterface
{
    public function persist($object);
    public function flush();
}