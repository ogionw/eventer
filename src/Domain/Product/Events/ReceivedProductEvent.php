<?php
declare(strict_types=1);

namespace App\Domain\Product\Events;
use App\Entity\ProductEvent;
use DateTimeImmutable;
use Doctrine\DBAL\Types\DateImmutableType;

final class ReceivedProductEvent implements ProductEventInterface
{
    const TYPE = "RECEIVED";



    private ?int $id;
    private string $sku;
    private int $quantity;
    private DateTimeImmutable $createdAt;

    public function init(string $sku, int $quantity, DateTimeImmutable $createdAt, string $description = null, int $id = null)
    {
        $this->id = $id;
        $this->sku = $sku;
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

    public function getType(): string
    {
        return self::TYPE;
    }
}
