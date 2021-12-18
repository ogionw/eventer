<?php
declare(strict_types=1);

namespace App\Warehouse\Domain\Product\Events;
use DateTimeImmutable;

final class AdjustedProductEvent implements ProductEventInterface
{
    const TYPE = "ADJUSTED";

    private ?int $id;
    private string $sku;
    private int $quantity;
    private DateTimeImmutable $createdAt;
    private string $description;
    public function init(string $sku, int $quantity, DateTimeImmutable $createdAt, string $description, int $id = null)
    {
        $this->id = $id;
        $this->sku = $sku;
        $this->description = $description;
        $this->createdAt = $createdAt;
        $this->quantity = $quantity;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    public function getType(): string
    {
        return self::TYPE;
    }

}
