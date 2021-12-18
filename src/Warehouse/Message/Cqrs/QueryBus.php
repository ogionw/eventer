<?php
declare(strict_types=1);
namespace App\Warehouse\Message\Cqrs;

interface QueryBus
{
    /** @return mixed */
    public function handle(Query $query);
}
