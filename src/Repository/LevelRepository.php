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

    /**
     * Returns a query builder with prefilled info to have easy access to like count
     */
    private function qbTemplate()
    {
        return $this->getEntityManager()->createQueryBuilder()
			->select('l')
            ->from('App\Entity\Level', 'l')
            ->where('l.isUnlisted = 0');
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
			->select('COUNT(l.id)')
			->getQuery()
			->getSingleScalarResult();

        return [
            'result' => $result,
            'total' => $totalCount,
        ];
    }
	
	public function downloadCount($levelID)
	{
		return $this->createQueryBuilder('l')
			->select('COUNT(d.id)')
			->join('l.downloadedBy', 'd')
			->join('d.account', 'a')
			->where('l.id = :lid')
			->setParameter('lid', $levelID)
			->andWhere('a.isVerified = 1')
			->getQuery()
			->getSingleScalarResult();
	}
	
	public function likeCount($levelID)
	{
		$likes = $this->createQueryBuilder('l')
			->select('COUNT(ll.id)')
			->join('l.likedBy', 'll')
			->join('ll.account', 'a')
			->where('l.id = :lid')
			->setParameter('lid', $levelID)
			->andWhere('a.isVerified = 1')
			->getQuery()
			->getSingleScalarResult();
		$dislikes = $this->createQueryBuilder('l')
			->select('COUNT(ld.id)')
			->join('l.dislikedBy', 'ld')
			->join('ld.account', 'a')
			->where('l.id = :lid')
			->setParameter('lid', $levelID)
			->andWhere('a.isVerified = 1')
			->getQuery()
			->getSingleScalarResult();
		return $likes - $dislikes;
	}

    /**
     * Finds a level by a specific creator with a specific name
     */
    public function levelWithSameNameByCreator($creatorID, $name): ?Level
    {
        return $this->createQueryBuilder('l')
            ->join('l.creator', 'c')
            ->where('c.id = :cid')
            ->setParameter('cid', $creatorID)
            ->andWhere('l.name = :name')
            ->setParameter('name', $name)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Adds WHERE statements to the query according to the given filters
     */
    private function applyFilters(&$qb, $difficulties, $lengths, bool $uncompleted, bool $onlyCompleted, bool $featured, bool $original, bool $twoPlayer, bool $coins, bool $epic, ?int $demonFilter, ?bool $star, ?bool $noStar, ?int $song, ?bool $customSong, $completedLevels)
    {
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
            $qb->andWhere('l.isTwoPlayer = 1');

        if ($coins)
            $qb->andWhere('l.coins > 0');

        if ($epic)
            $qb->andWhere('l.isEpic = 1');

        if (!empty($demonFilter))
            $qb->andWhere('l.demonDifficulty = :demondiff')
                ->setParameter('demondiff', $demonFilter);

        if ($star)
            $qb->andWhere('l.stars > 0');

        if ($noStar)
            $qb->andWhere('l.stars = 0');

        if ($song && $customSong)
            $qb->andWhere('l.customSongID = :song')->setParameter('song', $song);

        if ($song && !$customSong)
            $qb->andWhere('l.audioTrack = :audio AND l.customSongID = 0')->setParameter('audio', $song - 1);

        if (!$song && $customSong)
            $qb->andWhere('l.customSongID > 0');

        if ($completedLevels && preg_match('#^\(([0-9]+,)*[0-9]+\)$#', $completedLevels)) {
            if ($uncompleted)
                $qb->andWhere($qb->expr()->notIn('l.id', explode(',', substr($completedLevels, 1, strlen($completedLevels) - 1))));
            if ($onlyCompleted)
                $qb->andWhere($qb->expr()->in('l.id', explode(',', substr($completedLevels, 1, strlen($completedLevels) - 1))));
        }

        return $qb;
    }

    /**
     * Returns levels searched by keywords
     */
    public function searchLevels($keywords, $difficulties, $lengths, int $page, bool $uncompleted, bool $onlyCompleted, bool $featured, bool $original, bool $twoPlayer, bool $coins, bool $epic, ?int $demonFilter, ?bool $star, ?bool $noStar, ?int $song, ?int $customSong, $completedLevels)
    {
        $qb = $this->qbTemplate();

        if (is_numeric($keywords)) {
            $qb->andWhere('l.id = :keywordsID')
                ->setParameter('keywordsID', $keywords);
        } else {
            $qb->andWhere('l.name LIKE :keywordsName')
                ->setParameter('keywordsName', $keywords . '%');
        }
		$qb->orderBy('l.likes', 'DESC');

        $this->applyFilters($qb, $difficulties, $lengths, $uncompleted, $onlyCompleted, $featured, $original, $twoPlayer, $coins, $epic, $demonFilter, $star, $noStar, $song, $customSong, $completedLevels);

        return $this->getPaginatedResult($qb, $page);
    }

    /**
     * Returns the most downloaded levels
     */
    public function mostDownloadedLevels($difficulties, $lengths, int $page, bool $uncompleted, bool $onlyCompleted, bool $featured, bool $original, bool $twoPlayer, bool $coins, bool $epic, ?int $demonFilter, ?bool $star, ?bool $noStar, ?int $song, ?int $customSong, $completedLevels)
    {
        $qb = $this->qbTemplate();

        $this->applyFilters($qb, $difficulties, $lengths, $uncompleted, $onlyCompleted, $featured, $original, $twoPlayer, $coins, $epic, $demonFilter, $star, $noStar, $song, $customSong, $completedLevels);
		$qb->orderBy('l.downloads', 'DESC');

        return $this->getPaginatedResult($qb, $page);
    }

    /**
     * Returns the most liked levels
     */
    public function mostLikedLevels($difficulties, $lengths, int $page, bool $uncompleted, bool $onlyCompleted, bool $featured, bool $original, bool $twoPlayer, bool $coins, bool $epic, ?int $demonFilter, ?bool $star, ?bool $noStar, ?int $song, ?int $customSong, $completedLevels)
    {
        $qb = $this->qbTemplate();

        $this->applyFilters($qb, $difficulties, $lengths, $uncompleted, $onlyCompleted, $featured, $original, $twoPlayer, $coins, $epic, $demonFilter, $star, $noStar, $song, $customSong, $completedLevels);
		$qb->orderBy('l.likes', 'DESC');
        
        return $this->getPaginatedResult($qb, $page);
    }

    /**
     * Returns the most liked levels uploaded less than 1 week ago. Levels with a negative amount of likes won't be shown.
     */
    public function trendingLevels($difficulties, $lengths, int $page, bool $uncompleted, bool $onlyCompleted, bool $featured, bool $original, bool $twoPlayer, bool $coins, bool $epic, ?int $demonFilter, ?bool $star, ?bool $noStar, ?int $song, ?int $customSong, $completedLevels)
    {
        $qb = $this->qbTemplate()
            ->andWhere('l.uploadedAt > :interval')
            ->setParameter('interval', new \DateTime("1 week ago"))
            ->andWhere('l.likes > 0')
			->orderBy('l.likes', 'DESC');

        $this->applyFilters($qb, $difficulties, $lengths, $uncompleted, $onlyCompleted, $featured, $original, $twoPlayer, $coins, $epic, $demonFilter, $star, $noStar, $song, $customSong, $completedLevels);
        
        return $this->getPaginatedResult($qb, $page);
    }

    /**
     * Returns the most recently uploaded levels
     */
    public function recentLevels($difficulties, $lengths, int $page, bool $uncompleted, bool $onlyCompleted, bool $featured, bool $original, bool $twoPlayer, bool $coins, bool $epic, ?int $demonFilter, ?bool $star, ?bool $noStar, ?int $song, ?int $customSong, $completedLevels)
    {
        $qb = $this->qbTemplate()
            ->orderBy('l.uploadedAt', 'DESC');

        $this->applyFilters($qb, $difficulties, $lengths, $uncompleted, $onlyCompleted, $featured, $original, $twoPlayer, $coins, $epic, $demonFilter, $star, $noStar, $song, $customSong, $completedLevels);
        
        return $this->getPaginatedResult($qb, $page);
    }

    /**
     * Returns levels by a specific user
     */
    public function levelsByUser($playerID, $player, int $page)
    {
        $qb = $this->qbTemplate()
            ->join('l.creator', 'creator');

        if ($player && $player->getId() == $playerID)
            $qb->where('creator.id = :playerID');
        else
            $qb->andWhere('creator.id = :playerID');

        $qb->setParameter('playerID', $playerID)
            ->orderBy('l.uploadedAt', 'DESC');

        return $this->getPaginatedResult($qb, $page);
    }

    /**
     * Returns the levels that should go in the Featured section, sorted by their scores
     */
    public function featuredLevels(int $page)
    {
        $qb = $this->qbTemplate()
            ->andWhere('l.featureScore <> 0')
            ->orderBy('l.featureScore DESC, l.id', 'DESC');

        return $this->getPaginatedResult($qb, $page);
    }

    /**
     * Returns the most recent levels that meets the following requirements:
     * - the level has more than 25k objects
     * - the level is Long or XL
     * - the level has LDM
     */
    public function magicLevels($difficulties, $lengths, int $page, bool $uncompleted, bool $onlyCompleted, bool $featured, bool $original, bool $twoPlayer, bool $coins, bool $epic, ?int $demonFilter, ?bool $star, ?bool $noStar, ?int $song, ?int $customSong, $completedLevels)
    {
        $qb = $this->qbTemplate()
            ->andWhere('l.objectCount > 25000')
            ->andWhere('l.isLDM = 1')
            ->andWhere('l.length > 2')
            ->orderBy('l.uploadedAt', 'DESC');

        $this->applyFilters($qb, $difficulties, $lengths, $uncompleted, $onlyCompleted, $featured, $original, $twoPlayer, $coins, $epic, $demonFilter, $star, $noStar, $song, $customSong, $completedLevels);
        
        return $this->getPaginatedResult($qb, $page);
    }

    /**
     * Returns levels coming from map packs
     */
    public function mapPackLevels($levelList, int $page)
    {
        $qb = $this->qbTemplate()
            ->andWhere($qb->expr()->in('l.id', explode(',', $levelList)))
            ->orderBy('l.featureScore DESC, l.id', 'DESC');

        return $this->getPaginatedResult($qb, $page);
    }

    /**
     * Returns levels that have a star rating or verified coins
     */
    public function awardedLevels($difficulties, $lengths, int $page, bool $uncompleted, bool $onlyCompleted, bool $featured, bool $original, bool $twoPlayer, bool $coins, bool $epic, ?int $demonFilter, ?bool $star, ?bool $noStar, ?int $song, ?int $customSong, $completedLevels)
    {
        $qb = $this->qbTemplate()
            ->andWhere('l.rewardsGivenAt IS NOT NULL')
            ->andWhere('(l.stars > 0 OR l.hasCoinsVerified = 1)')
            ->orderBy('l.rewardsGivenAt', 'DESC');

        $this->applyFilters($qb, $difficulties, $lengths, $uncompleted, $onlyCompleted, $featured, $original, $twoPlayer, $coins, $epic, $demonFilter, $star, $noStar, $song, $customSong, $completedLevels);
        
        return $this->getPaginatedResult($qb, $page);   
    }

    /**
     * Returns the most recently uploaded levels made by th followed players
     */
    public function levelsByFollowed($difficulties, $lengths, int $page, bool $uncompleted, bool $onlyCompleted, bool $featured, bool $original, bool $twoPlayer, bool $coins, bool $epic, ?int $demonFilter, ?bool $star, ?bool $noStar, ?int $song, ?int $customSong, $completedLevels, $followed)
    {
        $qb = $this->qbTemplate()
            ->orderBy('l.uploadedAt', 'DESC');

        $qb->join('l.creator', 'c')
            ->join('c.account', 'a')
            ->andWhere($qb->expr()->in('a.id', explode(',', $followed)));

        $this->applyFilters($qb, $difficulties, $lengths, $uncompleted, $onlyCompleted, $featured, $original, $twoPlayer, $coins, $epic, $demonFilter, $star, $noStar, $song, $customSong, $completedLevels);
        
        return $this->getPaginatedResult($qb, $page);
    }

    /**
     * Returns the levels that should go in the Hall of Fame section, sorted by their ID
     */
    public function hallOfFame(int $page)
    {
        $qb = $this->qbTemplate()
            ->andWhere('l.isEpic = 1')
            ->orderby('l.id', 'DESC');

        return $this->getPaginatedResult($qb, $page);
    }
}
