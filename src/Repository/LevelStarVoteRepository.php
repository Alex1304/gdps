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
        return $this->createQueryBuilder('lsv')
            ->join('lsv.player', 'p')
            ->join('lsv.level', 'l')
            ->where('p.id = :pid AND l.id = :lid')
            ->setParameter('pid', $playerID)
            ->setParameter('lid', $levelID)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function averageVotesForLevel($levelID)
    {
        return $this->createQueryBuilder('lsv')
            ->select('AVG(lsv.starValue) AS avgVotes, COUNT(lsv.id) AS HIDDEN totalVotes')
            ->join('lsv.level', 'l')
			->join('lsv.player', 'p')
			->join('p.account', 'a')
            ->where('l.id = :id')
            ->setParameter('id', $levelID)
			->andWhere('a.isVerified = 1')
            ->groupBy('l.id')
            ->having('totalVotes >= ' . self::MIN_VOTES_REQUIRED)
            ->getQuery()
            ->getScalarResult();
    }
}
