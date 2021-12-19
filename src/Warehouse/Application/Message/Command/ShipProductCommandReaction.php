<?php
declare(strict_types=1);

namespace App\Warehouse\Application\Message\Command;
use App\Warehouse\Domain\Repository\ProductEventRepositoryInterface;
use App\Warehouse\Presentation\Message\Command\ShipProductCommand;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class ShipProductCommandReaction implements MessageHandlerInterface
{
    public function __construct(private ProductEventRepositoryInterface $peRepo){}

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function __invoke(ShipProductCommand $command)
    {
        $product = $this->peRepo->getProduct($command->getSku());
        $product->ship($command->getQuantity());
        $this->peRepo->save($product);
    }
}
