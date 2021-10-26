<?php

namespace App\Domain\Product\Events;

interface ProductEventInterface
{
    public function getType(): string;
}
