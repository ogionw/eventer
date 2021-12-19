<?php
declare(strict_types=1);

namespace App\Warehouse\Application\Message\Command;
use App\Warehouse\Application\Message\Cqrs\CommandHandler;
use App\Warehouse\Domain\Repository\ProductEventRepositoryInterface;
use App\Warehouse\Presentation\Message\Command\ReceiveProductCommand;

final class ReceiveProductCommandReaction implements  CommandHandler
{
    public function __construct(private ProductEventRepositoryInterface $peRepo){}

    public function __invoke(ReceiveProductCommand $command)
    {
        $product = $this->peRepo->getProduct($command->getSku());
        $product->receive($command->getQuantity());
        $this->peRepo->save($product);
    }
}
