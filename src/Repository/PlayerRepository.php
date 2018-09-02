<?php

namespace App\Repository;

use App\Entity\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method Player|null find($id, $lockMode = null, $lockVersion = null)
 * @method Player|null findOneBy(array $criteria, array $orderBy = null)
 * @method Player[]    findAll()
 * @method Player[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayerRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Player::class);
    }

    /**
     * Limits results of the query to show the desired page
     */
    private function getPaginatedResult(&$qb, $page, $count = 10)
    {
        $result = $qb->getQuery()->getResult();

        return [
            'result' => array_slice($result, $page * $count, $count),
            'total' => count($result),
        ];
    }

    public function search($str, int $page)
    {
        $qb = $this->createQueryBuilder('p');

        if (is_numeric($str)) {
            $qb->where('p.id = :strID')
                ->setParameter('strID', $str);
        } else {
            $qb->join('p.account', 'a')
                ->where('a.username LIKE :str')
                ->setParameter('str', $str . '%');
        }
        
        $qb->orderBy('p.stars DESC, p.statsLastUpdatedAt');

        return $this->getPaginatedResult($qb, $page);
    }

    /**
     * Finds a player that meets the following requirements:
     * - the deviceID matches exactly the one given in argument
     * - the player doesn't have any associated account
     *
     * null is returned if not found.
     */
    public function findUnregisteredByDeviceID(string $deviceID): ?Player
    {
        $players = $this->findBy([
            'deviceID' => $deviceID,
        ]);

        $player = null;

        foreach($players as $e) {
            if ($e->getAccount() == null) {
                $player = $e;
                break;
            }
        }

        return $player;
    }

    public function globalRank(Player $player)
    {
        $qb = $this->createQueryBuilder('p');
        return $qb
            ->select('COUNT(p.id)')
            ->where($qb->expr()->orX(
                $qb->expr()->gt('p.stars', ':pstars'),
                $qb->expr()->andX(
                    $qb->expr()->eq('p.stars', ':pstars'),
                    $qb->expr()->lt('p.statsLastUpdatedAt', ':ptime')
                )
            ))
            ->setParameter('pstars', $player->getStars())
            ->setParameter('ptime', $player->getStatsLastUpdatedAt())
            ->getQuery()
            ->getSingleScalarResult() + 1;
    }

    public function topLeaderboard(int $count)
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.stars DESC, p.statsLastUpdatedAt')
            ->setMaxResults($count)
            ->getQuery()
            ->getResult();
    }

    public function relativeLeaderboard(Player $player, int $count)
    {
        $rank = $this->globalRank($player);

        return [
            'result' => $this->createQueryBuilder('p')
                ->orderBy('p.stars DESC, p.statsLastUpdatedAt')
                ->setFirstResult(max(0, $rank - $count / 2))
                ->setMaxResults($count)
                ->getQuery()
                ->getResult(),
            'rank' => $rank,
        ];
    }

    public function creatorLeaderboard(int $count)
    {
        return $this->createQueryBuilder('p')
            ->where('p.creatorPoints > 0')
            ->orderBy('p.creatorPoints DESC, p.stars DESC, p.statsLastUpdatedAt')
            ->setMaxResults($count)
            ->getQuery()
            ->getResult();
    }
}
