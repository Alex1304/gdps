<?php

namespace App\Repository;

use App\Entity\FriendRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method FriendRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method FriendRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method FriendRequest[]    findAll()
 * @method FriendRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FriendRequestRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, FriendRequest::class);
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
			->select('COUNT(f.id)')
			->getQuery()
			->getSingleScalarResult();

        return [
            'result' => $result,
            'total' => $totalCount,
        ];
    }

    public function friendRequestBySenderAndRecipient($senderID, $recipientID): ?FriendRequest
    {
        return $this->createQueryBuilder('f')
            ->join('f.sender', 's')
            ->join('f.recipient', 'r')
            ->where('s.id = :sid')
            ->setParameter('sid', $senderID)
            ->andWhere('r.id = :rid')
            ->setParameter('rid', $recipientID)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function incomingFriendRequestsForAccount($accountID, $page)
    {
        $qb = $this->createQueryBuilder('f')
            ->join('f.recipient', 'r')
            ->where('r.id = :rid')
            ->setParameter('rid', $accountID)
            ->orderBy('f.madeAt', 'DESC');

        return $this->getPaginatedResult($qb, $page, 20);
    }

    public function outgoingFriendRequestsForAccount($accountID, $page)
    {
        $qb = $this->createQueryBuilder('f')
            ->join('f.sender', 's')
            ->where('s.id = :sid')
            ->setParameter('sid', $accountID)
            ->orderBy('f.madeAt', 'DESC');

        return $this->getPaginatedResult($qb, $page, 20);
    }

    public function countUnreadIncomingFriendRequests($id)
    {
        return $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->join('f.recipient', 'r')
            ->where('r.id = :id')
            ->setParameter('id', $id)
            ->andWhere('f.isUnread = 1')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
