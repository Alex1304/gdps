<?php

namespace App\Repository;

use App\Entity\LevelScore;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

use App\Entity\Friend;

/**
 * @method LevelScore|null find($id, $lockMode = null, $lockVersion = null)
 * @method LevelScore|null findOneBy(array $criteria, array $orderBy = null)
 * @method LevelScore[]    findAll()
 * @method LevelScore[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LevelScoreRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, LevelScore::class);
    }

    public function findExistingScore($accountID, $levelID): ?LevelScore
    {
        return $this->createQueryBuilder('s')
            ->join('s.account', 'a')
            ->join('s.level', 'l')
            ->where('a.id = :aid AND l.id = :lid')
            ->setParameter('aid', $accountID)
            ->setParameter('lid', $levelID)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function friendsLeaderboard($accountID, $levelID)
    {
        $friends = $this->getEntityManager()->getRepository(Friend::class)->friendsFor($accountID);
        $friendIDs = [];
        foreach ($friends as $f) {
            $theFriend = $f->getA()->getId() === $accountID ? $f->getB()->getId() : $f->getA()->getId();
            $friendIDs[] = $theFriend;
        }

        $friendIDs[] = $accountID; // Include the player himself in the leaderboard

        $qb = $this->createQueryBuilder('s');

        return $qb
            ->join('s.account', 'a')
            ->join('s.level', 'l')
            ->where('l.id = :id')
            ->setParameter('id', $levelID)
            ->andWhere($qb->expr()->in('a.id', $friendIDs))
            ->orderBy('s.percent DESC, s.coins DESC, s.updatedAt')
            ->getQuery()
            ->getResult();
    }

    public function topLeaderboard($levelID)
    {
        return $this->createQueryBuilder('s')
            ->join('s.level', 'l')
            ->where('l.id = :id')
            ->setParameter('id', $levelID)
            ->orderBy('s.percent DESC, s.coins DESC, s.updatedAt')
            ->setMaxResults(200)
            ->getQuery()
            ->getResult();
    }

    public function weekLeaderboard($levelID)
    {
        return $this->createQueryBuilder('s')
            ->join('s.level', 'l')
            ->where('l.id = :id')
            ->setParameter('id', $levelID)
            ->andWhere('s.updatedAt > :week')
            ->setParameter('week', new \DateTime("1 week ago"))
            ->orderBy('s.percent DESC, s.coins DESC, s.updatedAt')
            ->setMaxResults(200)
            ->getQuery()
            ->getResult();
    }
}
