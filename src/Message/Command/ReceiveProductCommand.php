<?php
declare(strict_types=1);

namespace App\Message\Command;

use App\Domain\Product\Events\ReceivedProductEvent;

final class ReceiveProductCommand extends ProductCommand
{
    public function getType(): string
    {
        return ReceivedProductEvent::TYPE;
    }
}
