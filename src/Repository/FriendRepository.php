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
}
