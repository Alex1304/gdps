<?php

namespace App\Repository;

use App\Entity\PrivateMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PrivateMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method PrivateMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method PrivateMessage[]    findAll()
 * @method PrivateMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrivateMessageRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PrivateMessage::class);
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
			->select('COUNT(m.id)')
			->getQuery()
			->getSingleScalarResult();

        return [
            'result' => $result,
            'total' => $totalCount,
        ];
    }

    public function privateMessagesFor($accountID, int $page, bool $outgoing)
    {
        $qb = $this->createQueryBuilder('m');

        if ($outgoing) {
            $qb->join('m.author', 'a')
                ->where('a.id = :id AND m.authorHasDeleted = 0');
        } else {
            $qb->join('m.recipient', 'r')
                ->where('r.id = :id AND m.recipientHasDeleted = 0');
        }
        
        $qb->setParameter('id', $accountID)
            ->orderBy('m.postedAt', 'DESC');

        return $this->getPaginatedResult($qb, $page, 50);
    }

    public function countNewPrivateMessages($id): int
    {
        return $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->join('m.recipient', 'r')
            ->where('r.id = :id')
            ->setParameter('id', $id)
            ->andWhere('m.isUnread = 1 AND m.recipientHasDeleted = 0')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
