<?php

namespace App\Repository;

use App\Entity\LevelSuggestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method LevelSuggestion|null find($id, $lockMode = null, $lockVersion = null)
 * @method LevelSuggestion|null findOneBy(array $criteria, array $orderBy = null)
 * @method LevelSuggestion[]    findAll()
 * @method LevelSuggestion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LevelSuggestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LevelSuggestion::class);
    }
    
    public function findExisting($playerID, $levelID)
    {
        return $this->createQueryBuilder('s')
			->join('s.level', 'l')
			->join('s.moderator', 'p')
            ->where('l.id = :lid')
            ->setParameter('lid', $levelID)
            ->andWhere('p.id = :pid')
            ->setParameter('pid', $playerID)
            ->getQuery()
            ->getOneOrNullResult();
    }
	
	public function findSuggestionsByCriteria($minStars, $maxStars, $maxSongUses, $sortMode)
	{
		$qb = $this->createQueryBuilder('s')
			->addSelect('GROUP_CONCAT(CONCAT(p.id, \':\', p.name, \':\', s.stars, \':\', s.isFeatured) SEPARATOR \',\') AS sentBy')
			->join('s.level', 'l')
			->join('s.moderator', 'p')
			->where('s.stars BETWEEN :min AND :max')
			->setParameter('min', $minStars)
			->setParameter('max', $maxStars)
			->orderBy('s.isFeatured', 'DESC');
			
		if ($maxSongUses > 0) {
			$qb->andWhere('(SELECT COUNT(ls.id) FROM App\Entity\Level ls WHERE ls.featureScore > 0 AND l.customSongID > 0 AND ls.customSongID = l.customSongID) <= :maxsonguses')
				->setParameter('maxsonguses', $maxSongUses);
		}
		
		switch (abs($sortMode)) {
			case LevelSuggestion::SORT_MOST_SENDS:
				$qb->addSelect('COUNT(p.id) AS HIDDEN sendCount');
				$qb->addOrderBy('sendCount', $sortMode < 0 ? 'ASC' : 'DESC');
				break;
			case LevelSuggestion::SORT_MOST_RECENT_SENDS:
				$qb->addOrderBy('s.sentAt', $sortMode < 0 ? 'ASC' : 'DESC');
				break;
			default:
				$qb->addOrderBy('l.uploadedAt', $sortMode < 0 ? 'ASC' : 'DESC');
				break;
		}
		
		$results = $qb->groupBy('l.id')
			->setMaxResults(10)
			->getQuery()
			->getResult();
		
		$results = array_map(function($r) {
			$sentBy = $r['sentBy'];
			$sentBy = explode(',', $sentBy);
			$sentBy = array_map(function ($s) {
				$tokens = explode(':', $s);
				return [
					'player_id' => $tokens[0],
					'name' => $tokens[1],
					'stars' => $tokens[2],
					'featured' => !!$tokens[3],
				];
			}, $sentBy);
			return [
				'level' => $r[0]->getLevel(),
				'send_details' => $sentBy,
			];
		}, $results);
		
		return $results;
	}
	
	public function findSuggestionsForLevel($levelID)
    {
        return $this->createQueryBuilder('s')
			->join('s.level', 'l')
            ->where('l.id = :lid')
            ->setParameter('lid', $levelID)
            ->getQuery()
            ->getResult();
    }
}
