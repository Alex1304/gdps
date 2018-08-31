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
    const MIN_VOTES_REQUIRED = 50;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, LevelDemonVote::class);
    }

    public function findPlayerVoteForLevel($playerID, $levelID): ?LevelDemonVote
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
        return $this->createQueryBuilder('ldv')
            ->select('AVG(ldv.demonValue) AS avgVotes, COUNT(ldv.id) AS HIDDEN totalVotes')
            ->join('ldv.level', 'l')
            ->where('l.id = :id')
            ->setParameter('id', $levelID)
            ->groupBy('l.id')
            ->having('totalVotes >= ' . self::MIN_VOTES_REQUIRED) // No need to use query parameters because it isn't user input
            ->getQuery()
            ->getScalarResult();
    }
}
