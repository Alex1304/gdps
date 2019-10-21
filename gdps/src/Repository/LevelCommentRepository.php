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
            ->select('c, (COUNT(likes) - COUNT(dislikes)) AS HIDDEN likeCount')
            ->from('App\Entity\LevelComment', 'c')
            ->leftJoin('c.likedBy', 'likes')
            ->leftJoin('c.dislikedBy', 'dislikes')
            ->groupBy('c.id')
            ->orderBy('c.postedAt', 'DESC');
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
            $qb->orderBy('likeCount', 'DESC');

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
            $qb->orderBy('likeCount', 'DESC');

        return $this->getPaginatedResult($qb, $page, $count);
    }
}