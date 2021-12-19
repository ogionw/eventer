<?php
declare(strict_types=1);

namespace App\Warehouse\Domain\Product;
use DateTimeImmutable;
use DomainException;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

final class ProductFactory implements ProductFactoryInterface
{
    private iterable $productEvents;

    public function __construct(
        #[TaggedIterator('app.product.event')] iterable $productEvents
    ) {
        $this->productEvents = $productEvents;
    }

    public function createEvent(
        string $sku,
        string $type,
        int $quantity,
        DateTimeImmutable $createdAt,
        string $description = null,
        int $id = null)
    {
        foreach ($this->productEvents as $eventPrototype){
            if($eventPrototype->getType() === $type){
                $event = clone $eventPrototype;
                $event->init($sku, $quantity, $createdAt, $description, $id);
                return $event;
            }
        }
        throw new DomainException('Unknown event: '.$type);
    }

    public function create(string $sku, array $dbEvents) : Product
    {
        $product = new Product();
        $product->setSku($sku);
        $product->setCurrentState(new ProductStateDto());
        $product->setFactory($this);
        foreach ($dbEvents as $i=>$pe){
            if($i === 0){
                $product->setFirstAddedAt($pe->getCreatedAt());
            }
            $event = $this->createEvent(
                $sku,
                $pe->getType(),
                $pe->getQuantity(),
                $pe->getCreatedAt(),
                $pe->getDescription(),
                $pe->getId()
            );
            $product->addEvent($event);
        }
        return $product;
    }
}
