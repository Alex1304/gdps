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
    const MIN_VOTES_REQUIRED = 20;

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

    public function averageVotesForLevel($levelID)
    {
        return $this->createQueryBuilder('lsv')
            ->select('AVG(lsv.starValue) AS avgVotes, COUNT(lsv.id) AS HIDDEN totalVotes')
            ->join('lsv.level', 'l')
            ->where('l.id = :id')
            ->setParameter('id', $levelID)
            ->groupBy('l.id')
            ->having('totalVotes >= ' . self::MIN_VOTES_REQUIRED) // No need to use query parameters because it isn't user input
            ->getQuery()
            ->getScalarResult();
    }
}
