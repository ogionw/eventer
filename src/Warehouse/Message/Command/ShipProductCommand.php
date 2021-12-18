<?php
declare(strict_types=1);

namespace App\Warehouse\Message\Command;

use App\Warehouse\Domain\Product\Events\ShippedProductEvent;

final class ShipProductCommand extends ProductCommand
{
    public function getType(): string
    {
        return ShippedProductEvent::TYPE;
    }
}
