<?php

namespace App\Repository;

use App\Entity\CommentBan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method CommentBan|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommentBan|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommentBan[]    findAll()
 * @method CommentBan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentBanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommentBan::class);
    }

    public function findCurrentBan($targetID): ?CommentBan
    {
        return $this->createQueryBuilder('b')
			->join('b.target', 't')
            ->where('t.id = :id')
            ->setParameter('id', $targetID)
			->andWhere('b.expiresAt > :now')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
