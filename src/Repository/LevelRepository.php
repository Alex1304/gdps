<?php

namespace App\Repository;

use App\Entity\Level;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Level|null find($id, $lockMode = null, $lockVersion = null)
 * @method Level|null findOneBy(array $criteria, array $orderBy = null)
 * @method Level[]    findAll()
 * @method Level[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LevelRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Level::class);
    }

    public function searchLevels($keywords, $difficulties, $lengths, int $page, bool $uncompleted, bool $onlyCompleted, bool $featured, bool $original, bool $twoPlayer, bool $coins, bool $epic, ?int $demonFilter, ?bool $star)
    {
        $qb = $this->createQueryBuilder('l');
        $qb->where('l.name LIKE \'' . $keywords . '%\'');

        if (preg_match('#^([1-5],)*[1-5]$#', $difficulties))
            $qb->andWhere($qb->expr()->in('l.difficulty', explode(',', $difficulties)));
        else if ($difficulties == '-2')
            $qb->andWhere('l.isDemon = 1');
        else if ($difficulties == '-3')
            $qb->andWhere('l.isAuto = 1');
        else if ($difficulties == '-1')
            $qb->andWhere('l.difficulty = 0');

        if (preg_match('#^([0-4],)*[0-4]$#', $lengths))
            $qb->andWhere($qb->expr()->in('l.length', explode(',', $lengths)));

        if ($featured)
            $qb->andWhere('l.featureScore > 0');

        if ($original)
            $qb->andWhere('l.original IS NULL');

        if ($twoPlayer)
            $qb->andWhere('l.twoPlayer = 1');

        if ($coins)
            $qb->andWhere('l.coins > 0');

        if ($epic)
            $qb->andWhere('l.epic = 1');

        if (!empty($demonFilter))
            $qb->andWhere('l.demonDifficulty = :demondiff')
                ->setParameter('demondiff', $demonFilter);

        if ($star)
            $qb->andWhere('l.stars > 0');

        $total = count($qb->getQuery()->getResult());

        $qb->setFirstResult($page * 10);
        $qb->setMaxResults(10);

        return [
            'result' => $qb->getQuery()->getResult(),
            'total' => $total,
        ];
    }

}
