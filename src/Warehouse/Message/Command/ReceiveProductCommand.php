<?php
declare(strict_types=1);

namespace App\Warehouse\Message\Command;

use App\Warehouse\Domain\Product\Events\ReceivedProductEvent;

final class ReceiveProductCommand extends ProductCommand
{
    public function getType(): string
    {
        return ReceivedProductEvent::TYPE;
    }
}
