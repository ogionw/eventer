<?php

namespace App\Warehouse\Infrastructure\Repository;


use App\Warehouse\Domain\Product\Events\AdjustedProductEvent;
use App\Warehouse\Domain\Product\Events\ProductEventInterface;
use App\Warehouse\Domain\Product\Product;
use App\Warehouse\Domain\Product\ProductFactoryInterface;
use App\Warehouse\Domain\Repository\ProductEventRepositoryInterface;
use App\Warehouse\Infrastructure\Entity\ProductEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProductEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductEvent[]    findAll()
 * @method ProductEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductEventRepository extends ServiceEntityRepository implements ProductEventRepositoryInterface
{
    private ProductFactoryInterface $factory;

    public function __construct(
        ManagerRegistry $registry,
        ProductFactoryInterface $factory,
    )
    {
        $this->factory = $factory;
        parent::__construct($registry, ProductEvent::class);
    }

    public function getProduct(string $sku): Product
    {
        $productEvents = $this->findBySku($sku);
        return $this->factory->create($sku, $productEvents);
    }

    /**
    * @return ProductEvent[] Returns an array of ProductEvent objects
    */
    public function findBySku(string $sku): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.sku = :val')
            ->setParameter('val', $sku)
            ->orderBy('p.createdAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }


    /*
    public function findOneBySomeField($value): ?ProductEvent
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function save(Product $product) : void
    {
        foreach ($product->getEvents() as $event){
            if(! $event->getId()){
                $entity = $this->eventToEntity($event);
                $this->_em->persist($entity);
            }
        }
        $this->_em->flush();
    }

    private function eventToEntity(ProductEventInterface $event): ProductEvent
    {
        $entity = new ProductEvent();
        $entity->setType($event->getType());
        $entity->setSku($event->getSku());
        $entity->setCreatedAt($event->getCreatedAt());
        $entity->setQuantity($event->getQuantity());
        if(get_class($event) === AdjustedProductEvent::class){
            $entity->setDescription($event->getDescription());
        }
        return $entity;
    }

    public function findAllOrdered() : array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.sku, p.createdAt', 'ASC')
            //->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }
}
