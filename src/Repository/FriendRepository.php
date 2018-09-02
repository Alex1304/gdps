<?php

namespace App\Repository;

use App\Entity\Friend;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Friend|null find($id, $lockMode = null, $lockVersion = null)
 * @method Friend|null findOneBy(array $criteria, array $orderBy = null)
 * @method Friend[]    findAll()
 * @method Friend[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FriendRepository extends ServiceEntityRepository
{
    const MAX_FRIENDS = 1000;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Friend::class);
    }

    public function friendAB($aID, $bID): ?Friend
    {
        return $this->createQueryBuilder('f')
            ->join('f.a', 'a')
            ->join('f.b', 'b')
            ->where('(a.id = :aid AND b.id = :bid) OR (a.id = :bid AND b.id = :aid)')
            ->setParameter('aid', $aID)
            ->setParameter('bid', $bID)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function friendsFor($id)
    {
        return $this->createQueryBuilder('f')
            ->join('f.a', 'a')
            ->join('f.b', 'b')
            ->where('a.id = :id OR b.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }

    public function countNewFriends($id)
    {
        return $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->join('f.a', 'a')
            ->join('f.b', 'b')
            ->where('(a.id = :id AND f.isNewForA = 1) OR (b.id = :id AND f.isNewForB = 1)')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function hasReachedFriendsLimit($id): bool
    {
        return $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->join('f.a', 'a')
            ->join('f.b', 'b')
            ->where('a.id = :id OR b.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleScalarResult() >= self::MAX_FRIENDS;
    }
}
