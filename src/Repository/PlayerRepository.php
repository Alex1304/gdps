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
            ->orderBy('p.creatorPoints DESC, p.statsLastUpdatedAt')
            ->setMaxResults($count)
            ->getQuery()
            ->getResult();
    }
}
