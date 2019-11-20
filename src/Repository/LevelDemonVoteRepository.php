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

    public function findPlayerVoteForLevel($playerID, $levelID): ?LevelDemonVote
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
	
	private function averageVotesForLevel0($levelID, $isModVote)
	{
		$result = $this->createQueryBuilder('ldv')
            ->select('AVG(ldv.demonValue) AS avgVotes')
            ->join('ldv.level', 'l')
            ->where('l.id = :id')
            ->setParameter('id', $levelID)
			->andWhere('ldv.isModVote = ' . $isModVote)
            ->groupBy('l.id')
            ->getQuery()
            ->getScalarResult();
			
		return !count($result) ? null : $result[0]['avgVotes'];
	}

    public function averageVotesForLevel($levelID)
    {
        $playerAvg = $this->averageVotesForLevel0($levelID, 0);
		$modAvg = $this->averageVotesForLevel0($levelID, 1);
		
		if ($playerAvg === null) {
			return $modAvg;
		} elseif ($modAvg === null) {
			return $playerAvg;
		} elseif ($playerAvg === null && $modAvg === null) {
			return 0;
		}
		return (2 * $playerAvg + $modAvg) / 3;
    }
}
