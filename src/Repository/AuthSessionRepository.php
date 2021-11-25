<?php

namespace App\Repository;

use App\Entity\AuthSession;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AuthSession|null find($id, $lockMode = null, $lockVersion = null)
 * @method AuthSession|null findOneBy(array $criteria, array $orderBy = null)
 * @method AuthSession[]    findAll()
 * @method AuthSession[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuthSessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuthSession::class);
    }

    // /**
    //  * @return AuthSession[] Returns an array of AuthSession objects
    //  */
    public function findByAllByDate(DateTime $dateTime)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.expires < :val')
            ->setParameter('val', $dateTime)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneBySessionKey($value): ?AuthSession
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.sessionKey = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function delete(AuthSession $session)
    {
        $this->_em->remove($session);
        $this->_em->flush();
    }

    public function save(AuthSession $session) : void
    {
        $this->_em->persist($session);
        $this->_em->flush();
        //var_dump($this->findBySku($product->getSku())); die();
    }

    public function deleteExpired(DateTime $datetime)
    {
        foreach ($this->findByAllByDate($datetime) as $session){
            $this->_em->remove($session);
        }
        $this->_em->flush();
    }
}
