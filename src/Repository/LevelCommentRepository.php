<?php

namespace App\Repository;

use App\Entity\LevelComment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method LevelComment|null find($id, $lockMode = null, $lockVersion = null)
 * @method LevelComment|null findOneBy(array $criteria, array $orderBy = null)
 * @method LevelComment[]    findAll()
 * @method LevelComment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LevelCommentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, LevelComment::class);
    }

    /**
     * Returns a query builder with prefilled info to have easy access to like count
     */
    private function queryBuilderTemplate()
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('c')
            ->from('App\Entity\LevelComment', 'c')
            ->orderBy('c.postedAt', 'DESC');
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
     * Returns the comments on the given level. Sort mode is also given in parameter
     */
    public function commentsForLevel(int $levelID, int $page, bool $top, ?int $count)
    {
        $qb = $this->queryBuilderTemplate()
            ->join('c.level', 'l')
            ->where('l.id = :id')
            ->setParameter('id', $levelID);

        if ($top)
            $qb->orderBy('c.likes', 'DESC');

        return $this->getPaginatedResult($qb, $page, $count);
    }

    /**
     * Returns the comments made by the given author. Sort mode is also given in parameter
     */
    public function commentsByAuthor(int $authorID, int $page, bool $top, ?int $count)
    {
        $qb = $this->queryBuilderTemplate()
            ->join('c.author', 'a')
            ->where('a.id = :id')
            ->setParameter('id', $authorID);

        if ($top)
            $qb->orderBy('c.likes', 'DESC');

        return $this->getPaginatedResult($qb, $page, $count);
    }
	
	public function likeCount($commentID)
	{
		$likes = $this->createQueryBuilder('c')
			->select('COUNT(cl.id)')
			->join('c.likedBy', 'cl')
			->join('cl.account', 'a')
			->where('c.id = :lid')
			->setParameter('lid', $levelID)
			->andWhere('a.isVerified = 1')
			->getQuery()
			->getSingleScalarResult();
		$dislikes = $this->createQueryBuilder('c')
			->select('COUNT(cd.id)')
			->join('c.dislikedBy', 'cd')
			->join('cd.account', 'a')
			->where('c.id = :lid')
			->setParameter('lid', $levelID)
			->andWhere('a.isVerified = 1')
			->getQuery()
			->getSingleScalarResult();
		return $likes - $dislikes;
	}
}
