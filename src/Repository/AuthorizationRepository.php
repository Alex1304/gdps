<?php

namespace App\Repository;

use App\Entity\Authorization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Authorization|null find($id, $lockMode = null, $lockVersion = null)
 * @method Authorization|null findOneBy(array $criteria, array $orderBy = null)
 * @method Authorization[]    findAll()
 * @method Authorization[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuthorizationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Authorization::class);
    }

    public function forUser($userID)
    {
        return $this->createQueryBuilder('a')
            ->join('a.user', 'u')
            ->where('u.id = :id')
            ->setParameter('id', $userID)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
