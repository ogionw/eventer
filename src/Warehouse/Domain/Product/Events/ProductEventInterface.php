<?php

namespace App\Warehouse\Domain\Product\Events;

interface ProductEventInterface
{
    public function getType(): string;
}
