<?php
declare(strict_types=1);

namespace App\Warehouse\Application\Message\Command;
use App\Warehouse\Domain\Repository\ProductEventRepositoryInterface;
use App\Warehouse\Presentation\Message\Command\AdjustProductCommand;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class AdjustProductCommandReaction implements MessageHandlerInterface
{
    public function __construct(private ProductEventRepositoryInterface $peRepo){}

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function __invoke(AdjustProductCommand $command)
    {
        $product = $this->peRepo->getProduct($command->getSku());
        $product->adjust($command->getQuantity(), $command->getDescription());
        $this->peRepo->save($product);
    }
}
