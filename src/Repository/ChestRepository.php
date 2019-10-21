<?php

namespace App\Repository;

use App\Entity\Chest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Chest|null find($id, $lockMode = null, $lockVersion = null)
 * @method Chest|null findOneBy(array $criteria, array $orderBy = null)
 * @method Chest[]    findAll()
 * @method Chest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chest::class);
    }

    // /**
    //  * @return Chest[] Returns an array of Chest objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Chest
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
