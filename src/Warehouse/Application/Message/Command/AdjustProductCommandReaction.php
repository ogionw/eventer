<?php
declare(strict_types=1);

namespace App\Warehouse\Application\Message\Command;
use App\Warehouse\Presentation\Message\Command\AdjustProductCommand;
use App\Warehouse\Infrastructure\Repository\ProductEventRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class AdjustProductCommandReaction implements MessageHandlerInterface
{
    public function __construct(private ProductEventRepository $peRepo){}

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function __invoke(AdjustProductCommand $command)
    {
        $product = $this->peRepo->getProduct($command->getSku());
        $product->adjust($command->getQuantity(), $command->getDescription);
        $this->peRepo->save($product);
    }
}