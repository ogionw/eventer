<?php

namespace spec\Service;

interface EntityManagerInterface
{
    public function persist($object);
    public function flush();
}