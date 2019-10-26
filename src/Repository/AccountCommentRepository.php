<?php

namespace App\Repository;

use App\Entity\AccountComment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AccountComment|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccountComment|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccountComment[]    findAll()
 * @method AccountComment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountCommentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AccountComment::class);
    }

    /**
     * Limits results of the query to show the desired page
     */
    private function getPaginatedResult(&$qb, $page, $count = 10)
    {
        $result = $qb
			->setFirstResult($page * $count)
			->setMaxResults($count)
			->getQuery()
			->getResult();
		$totalCount = $qb
			->setFirstResult(null)
			->setMaxResults(null)
			->select('COUNT(c.id)')
			->getQuery()
			->getSingleScalarResult();

        return [
            'result' => $result,
            'total' => $totalCount,
        ];
    }

    /**
     * Returns the comments on the given account
     */
    public function commentsForAccount(int $authorID, int $page)
    {
        $qb = $this->createQueryBuilder('c')
            ->join('c.author', 'a')
            ->where('a.id = :id')
            ->setParameter('id', $authorID)
            ->orderBy('c.postedAt', 'DESC');

        return $this->getPaginatedResult($qb, $page);
    }
}
