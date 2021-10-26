<?php
declare(strict_types=1);

namespace App\Domain\Product;
use App\Domain\Product\Events\AdjustedProductEvent;
use App\Domain\Product\Events\ProductEventInterface;
use App\Domain\Product\Events\ReceivedProductEvent;
use App\Domain\Product\Events\ShippedProductEvent;
use DateTimeImmutable;
use DomainException;

final class Product
{
    private string $sku;
    private array $events = [];
    private ProductFactoryInterface $factory;
    private ProductStateDto $currentState;

    public function setCurrentState(ProductStateDto $currentState){
        $this->currentState = $currentState;
    }

    public function getCurrentState(){
        return $this->currentState;
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function setSku(string $sku){
        $this->sku = $sku;
    }

    public function getSku(){
        return $this->sku;
    }

    public function setFactory(ProductFactoryInterface $factory){
        $this->factory = $factory;
    }

    public function setFirstAddedAt(DateTimeImmutable $dateImmutableType){
        $this->currentState->firstAddedAt = $dateImmutableType;
    }

    public function addEvent(ProductEventInterface $event)
    {
        $this->events[] = $event;
        match ($event->getType()) {
            ReceivedProductEvent::TYPE => $this->applyReceived($event),
            ShippedProductEvent::TYPE => $this->applyShipped($event),
            AdjustedProductEvent::TYPE => $this->applyAdjusted($event),
        };
        $this->currentState->lastUpdatedAt = $event->getCreatedAt();
    }

    public function receive(int $quantity) : void
    {
        $event = $this->factory->createEvent($this->sku, ReceivedProductEvent::TYPE, $quantity, new DateTimeImmutable(), null, null);
        $this->addEvent($event);
    }

    public function ship(int $quantity) : void
    {
        if($this->currentState->quantity < $quantity){
            throw new DomainException("insufficient quantity");
        }
        $event = $this->factory->createEvent($this->sku, ShippedProductEvent::TYPE, $quantity, new DateTimeImmutable(), null, null);
        $this->addEvent($event);
    }

    public function adjust(int $quantity, string $description) : void
    {
        if($quantity < 0){
            throw new DomainException("incorrect value");
        }
        $event = $this->factory->createEvent($this->sku, AdjustedProductEvent::TYPE, $quantity, new DateTimeImmutable(), $description, null);
        $this->addEvent($event);
    }

    public function applyReceived(ReceivedProductEvent $event)
    {
        $this->currentState->quantity += $event->getQuantity();
    }
    public function applyShipped(ShippedProductEvent $event)
    {
        $this->currentState->quantity -= $event->getQuantity();
    }
    public function applyAdjusted(AdjustedProductEvent $event)
    {
        $this->currentState->quantity = $event->getQuantity();
    }
}
