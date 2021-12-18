<?php

declare(strict_types=1);

namespace App\Warehouse\Application\Message\Cqrs;
use App\Warehouse\Presentation\Message\Command\Command;

interface CommandBus
{
    public function dispatch(Command $command): void;
}
