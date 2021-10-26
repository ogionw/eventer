<?php

namespace App\Tests;

use App\Domain\Product\Events\AdjustedProductEvent;
use App\Domain\Product\Events\ReceivedProductEvent;
use App\Domain\Product\Events\ShippedProductEvent;
use DateTimeImmutable;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProductFactoryTest extends KernelTestCase
{
    /**
     * @return array<string,mixed>
     */
    public function getData(){
        return [
            'shipped' => [
                    'type'=>ShippedProductEvent::TYPE,
                    'sku' => 'ABC111',
                    'quantity' => 1,
                    'datetime' => new DateTimeImmutable(),
            ],
            'received' => [
                    'type'=>ReceivedProductEvent::TYPE,
                    'sku' => 'ABC222',
                    'quantity' => 2,
                    'datetime' => new DateTimeImmutable(),
            ],
            'adjusted' => [
                    'type'=>AdjustedProductEvent::TYPE,
                    'sku' => 'ABC333',
                    'quantity' => 3,
                    'datetime' => new DateTimeImmutable(),
                    'description'=>'magic'
            ],
        ];
    }

    /**
     * @dataProvider getData
     */
    public function testCreateEvent(string $type, string $sku, int $quantity, DateTimeImmutable $datetime, string $description = null): void
    {
        $event = $this->factory->createEvent($sku, $type, $quantity, $datetime, $description);
        $this->assertEquals($type, $event->getType());
    }

    /**
     * @throws DomainException
     */
    public function testException(): void
    {
        $type = 'BRIBE';

        $this->expectException(DomainException::class);

        $this->factory->createEvent('AAA', $type, 50, new DateTimeImmutable(), 'test');
    }

    private $factory;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->factory = $kernel->getContainer()->get('product_factory');
        //$this->factory = new ProductFactory([new ReceivedProductEvent(), new ShippedProductEvent(), new AdjustedProductEvent()]);
        parent::setUp();
    }
}
