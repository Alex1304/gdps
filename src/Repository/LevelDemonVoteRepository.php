<?php

namespace App\Repository;

use App\Entity\LevelDemonVote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method LevelDemonVote|null find($id, $lockMode = null, $lockVersion = null)
 * @method LevelDemonVote|null findOneBy(array $criteria, array $orderBy = null)
 * @method LevelDemonVote[]    findAll()
 * @method LevelDemonVote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LevelDemonVoteRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, LevelDemonVote::class);
    }

//    /**
//     * @return LevelDemonVote[] Returns an array of LevelDemonVote objects
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
    public function findOneBySomeField($value): ?LevelDemonVote
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
