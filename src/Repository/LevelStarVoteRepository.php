<?php

namespace App\Repository;

use App\Entity\LevelStarVote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method LevelStarVote|null find($id, $lockMode = null, $lockVersion = null)
 * @method LevelStarVote|null findOneBy(array $criteria, array $orderBy = null)
 * @method LevelStarVote[]    findAll()
 * @method LevelStarVote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LevelStarVoteRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, LevelStarVote::class);
    }

//    /**
//     * @return LevelStarVote[] Returns an array of LevelStarVote objects
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
    public function findOneBySomeField($value): ?LevelStarVote
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
