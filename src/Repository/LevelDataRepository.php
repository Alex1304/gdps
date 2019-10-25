<?php

namespace App\Repository;

use App\Entity\LevelData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method LevelData|null find($id, $lockMode = null, $lockVersion = null)
 * @method LevelData|null findOneBy(array $criteria, array $orderBy = null)
 * @method LevelData[]    findAll()
 * @method LevelData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LevelDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LevelData::class);
    }

    // /**
    //  * @return LevelData[] Returns an array of LevelData objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LevelData
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
