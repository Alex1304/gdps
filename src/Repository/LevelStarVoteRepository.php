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

    public function findPlayerVoteForLevel($playerID, $levelID): ?LevelStarVote
    {
        $results = $this->createQueryBuilder('lsv')
            ->join('lsv.player', 'p')
            ->join('lsv.level', 'l')
            ->where('p.id = :pid AND l.id = :lid')
            ->setParameter('pid', $playerID)
            ->setParameter('lid', $levelID)
            ->getQuery()
            ->getResult();

        return !count($results) ? null : $results[0];
    }
}
