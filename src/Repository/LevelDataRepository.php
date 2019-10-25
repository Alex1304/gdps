<?php

namespace App\Repository;

use App\Entity\LevelData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method LevelData|null find($id, $lockMode = null, $lockVersion = null)
 * @method LevelData|null findOneBy(array $criteria, array $orderBy = null)
 * @method LevelData[]    findAll()
 * @method LevelData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LevelDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LevelData::class);
    }

    public function forLevelOfId($id)
	{
		return $this->createQueryBuilder('d')
			->join('d.level', 'l')
			->where('l.id = :id')
			->setParameter('id', $id)
			->getQuery()
			->getOneOrNullResult();
	}
}
