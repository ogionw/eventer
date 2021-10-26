<?php
declare(strict_types=1);

namespace App\Message\Command;
use App\Message\Cqrs\CommandHandler;
use App\Repository\ProductEventRepository;

final class ReceiveProductCommandReaction implements  CommandHandler
{
    public function __construct(private ProductEventRepository $peRepo){}

    public function __invoke(ReceiveProductCommand $command)
    {
        $product = $this->peRepo->getProduct($command->getSku());
        $product->receive($command->getQuantity());
        $this->peRepo->save($product);
    }
}
