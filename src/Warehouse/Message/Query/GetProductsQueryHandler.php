<?php
declare(strict_types=1);

namespace App\Warehouse\Message\Query;
use App\Warehouse\Domain\Product\ProductFactoryInterface;
use App\Warehouse\Message\Cqrs\QueryHandler;
use App\Warehouse\Repository\ProductEventRepository;

final class GetProductsQueryHandler implements QueryHandler
{
    public function __construct(
        private ProductEventRepository $peRepo,
        private ProductFactoryInterface $factory
    ){}

    public function __invoke(GetProductsQuery $command)
    {
        $productStates = [];
        $eArr = [];
        foreach ($this->peRepo->findAllOrdered() as $event){
            $eArr[$event->getSku()][] = $event;
        }
        foreach ($eArr as $sku=>$events){
            $product = $this->factory->create($sku, $events);
            $productStates[$product->getSku()] = $product->getCurrentState();
        }
        return $productStates;
    }
}
