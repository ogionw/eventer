<?php
declare(strict_types=1);

namespace App\Message\Command;

use App\Domain\Product\Events\ShippedProductEvent;

final class ShipProductCommand extends ProductCommand
{
    public function getType(): string
    {
        return ShippedProductEvent::TYPE;
    }
}
