<?php

namespace App\Repository;

use App\Entity\PeriodicLevel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class PeriodicLevelRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PeriodicLevel::class);
    }

    public function findLatestOfType($type)
    {
        return $this->createQueryBuilder('p')
            ->where('p.type = :type')
            ->setParameter('type', $type)
            ->orderBy('p.periodStart', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findCurrentOfType($type)
    {
        return $this->createQueryBuilder('p')
            ->where(':now BETWEEN p.periodStart AND p.periodEnd')
            ->setParameter('now', new \DateTime('now'))
            ->andWhere('p.type = :type')
            ->setParameter('type', $type)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findQueuedOfType($type)
    {
        return $this->createQueryBuilder('p')
            ->where('p.periodStart > :now')
            ->setParameter('now', new \DateTime('now'))
            ->andWhere('p.type = :type')
            ->setParameter('type', $type)
            ->getQuery()
            ->getResult();
    }

}
