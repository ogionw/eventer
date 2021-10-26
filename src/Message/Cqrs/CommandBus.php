<?php

declare(strict_types=1);

namespace App\Message\Cqrs;

interface CommandBus
{
    public function dispatch(Command $command): void;
}
