<?php
declare(strict_types=1);
namespace App\Warehouse\Application\Message\Cqrs;

use App\Warehouse\Presentation\Message\Query\Query;

interface QueryBus
{
    /** @return mixed */
    public function handle(Query $query);
}
