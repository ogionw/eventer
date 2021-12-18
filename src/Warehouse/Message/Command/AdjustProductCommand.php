<?php
declare(strict_types=1);

namespace App\Warehouse\Message\Command;

use App\Warehouse\Domain\Product\Events\AdjustedProductEvent;

final class AdjustProductCommand extends ProductCommand{
    private string $description;

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    public function __construct(string $sku, int $quantity, string $description)
    {
        $this->description = $description;
        parent::__construct($sku, $quantity);
    }

    public function getType(): string
    {
        return AdjustedProductEvent::TYPE;
    }
}
