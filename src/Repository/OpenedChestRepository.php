<?php

namespace App\Repository;

use App\Entity\OpenedChest;
use App\Entity\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method OpenedChest|null find($id, $lockMode = null, $lockVersion = null)
 * @method OpenedChest|null findOneBy(array $criteria, array $orderBy = null)
 * @method OpenedChest[]    findAll()
 * @method OpenedChest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OpenedChestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OpenedChest::class);
    }

    public function totalOrbs(Player $player)
	{
		return $this->createQueryBuilder('o')
			->select('SUM(o.orbs)')
			->join('o.player', 'p')
			->where('p.id = :id')
			->setParameter('id', $player->getId())
			->getQuery()
			->getSingleScalarResult();
	}
	
	public function findMostRecentChest(Player $player, $type)
	{
		return $this->createQueryBuilder('o')
			->join('o.player', 'p')
			->where('p.id = :id')
			->setParameter('id', $player->getId())
			->andWhere('o.type = :type')
			->setParameter('type', $type)
			->orderBy('o.openedAt', 'DESC')
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();
	}
}
