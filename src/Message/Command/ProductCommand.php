<?php
declare(strict_types=1);

namespace App\Message\Command;

use App\Message\Cqrs\Command;

abstract class ProductCommand  implements Command
{
    public function __construct(private string $sku, private int $quantity){}

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @return string
     */
    public abstract function getType(): string;

    /**
     * @return string
     */
    public function getSku()
    {
        return $this->sku;
    }
}
