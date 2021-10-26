<?php
declare(strict_types=1);

namespace App\Domain\Product;

use DateTimeImmutable;

final class ProductStateDto
{
    public DateTimeImmutable $firstAddedAt;
    public DateTimeImmutable $lastUpdatedAt;
    public int $quantity = 0;
}
