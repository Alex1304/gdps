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
			->andWhere('p.periodEnd > :now')
            ->setParameter('now', new \DateTime("now"))
            ->orderBy('p.periodStart', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findCurrentOfType($type)
    {
        return $this->createQueryBuilder('p')
            ->where(':now BETWEEN p.periodStart AND p.periodEnd')
            ->setParameter('now', new \DateTime("now"))
            ->andWhere('p.type = :type')
            ->setParameter('type', $type)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findQueuedOfType($type)
    {
        return $this->findFromDateOfType($type, new \DateTime("now"));
    }

    public function findFromDateOfType($type, \DateTimeInterface $start)
    {
        return $this->createQueryBuilder('p')
            ->where('p.periodStart >= :start')
            ->setParameter('start', $start)
            ->andWhere('p.type = :type')
            ->setParameter('type', $type)
            ->orderBy('p.periodStart', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findIfNotPast($index)
    {
        return $this->createQueryBuilder('p')
            ->where('p.id = :id')
            ->setParameter('id', $index)
            ->andWhere('p.periodEnd > :now')
            ->setParameter('now', new \DateTime("now"))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function nextId($type)
    {
        $result = $this->createQueryBuilder('p')
            ->where('p.type = :type')
            ->setParameter('type', $type)
            ->orderBy('p.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$result)
            return 1 + PeriodicLevel::offsetForType($type);

        return $result->getId() + 1;
    }
}
