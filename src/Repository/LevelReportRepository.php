<?php

namespace App\Repository;

use App\Entity\LevelReport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method LevelReport|null find($id, $lockMode = null, $lockVersion = null)
 * @method LevelReport|null findOneBy(array $criteria, array $orderBy = null)
 * @method LevelReport[]    findAll()
 * @method LevelReport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LevelReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LevelReport::class);
    }

    public function numberOfReportsForLevelLast10Mins($levelID)
    {
        return $this->createQueryBuilder('r')
			->select('COUNT(r.id)')
            ->join('r.level', 'l')
			->where('l.id = :id')
			->setParameter('id', $levelID)
			->andWhere('r.reportedAt > :interval')
			->setParameter('interval', new \DateTime('10 minutes ago'))
			->getQuery()
			->getSingleScalarResult();
        ;
    }

    public function findReportsGroupedByLevel($sortMode)
	{
		$qb = $this->createQueryBuilder('r')
			->addSelect('COUNT(r.id) AS count')
			->join('r.level', 'l');
		
		switch (abs($sortMode)) {
			case LevelReport::SORT_MOST_REPORTS:
				$qb->addOrderBy('count', $sortMode < 0 ? 'ASC' : 'DESC');
				break;
			case LevelReport::SORT_MOST_RECENT_REPORTS:
				$qb->addOrderBy('r.reportedAt', $sortMode < 0 ? 'ASC' : 'DESC');
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
			return [
				'level' => $r[0]->getLevel(),
				'report_count' => $r['count'],
			];
		}, $results);
		
		return $results;
	}
	
	public function findReportsForLevel($levelID)
    {
        return $this->createQueryBuilder('r')
            ->join('r.level', 'l')
			->where('l.id = :id')
			->setParameter('id', $levelID)
			->getQuery()
			->getResult();
        ;
    }
}
