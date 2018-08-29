<?php

namespace App\Repository;

use App\Entity\LevelComment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method LevelComment|null find($id, $lockMode = null, $lockVersion = null)
 * @method LevelComment|null findOneBy(array $criteria, array $orderBy = null)
 * @method LevelComment[]    findAll()
 * @method LevelComment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LevelCommentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, LevelComment::class);
    }

//    /**
//     * @return LevelComment[] Returns an array of LevelComment objects
//     */
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
    public function findOneBySomeField($value): ?LevelComment
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
