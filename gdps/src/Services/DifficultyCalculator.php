<?php

namespace App\Services;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Level;
use App\Entity\LevelStarVote;
use App\Entity\LevelDemonVote;

class DifficultyCalculator
{
	const NA = 0;
	const EASY = 1;
	const NORMAL = 2;
	const HARD = 3;
	const HARDER = 4;
	const INSANE = 5;

	private $em;

	public function __construct(EntityManagerInterface $em)
	{
		$this->em = $em;
	}

	private function getDifficultyForStars($stars)
	{
		if (!$stars || $stars < 0)
			return self::NA;
		if ($stars <= 2)
			return self::EASY;
		if ($stars <= 3)
			return self::NORMAL;
		if ($stars <= 5)
			return self::HARD;
		if ($stars <= 7)
			return self::HARDER;

		return self::INSANE;
	}

	/**
	 * Analyzes the star votes the level has in order to update its difficulty accordingly
	 */
	public function updateDifficulty(Level $level)
	{
		if ($level->getStars() > 0)
			return;

		$avg = $this->em->getRepository(LevelStarVote::class)->averageVotesForLevel($level->getId());

		if (!count($avg))
			$level->setDifficulty(self::NA);
		else
			$level->setDifficulty($this->getDifficultyForStars($avg[0]['avgVotes']));

		$this->em->flush();
	}

	/**
	 * Analyzes the demon votes the level has in order to update its demon difficulty accordingly
	 */
	public function updateDemonDifficulty(Level $level)
	{
		if (!$level->getIsDemon())
			return;

		$avg = $this->em->getRepository(LevelDemonVote::class)->averageVotesForLevel($level->getId());

		if (!count($avg))
			$level->setDemonDifficulty(0);
		else
			$level->setDemonDifficulty(round($avg[0]['avgVotes']));

		$this->em->flush();
	}
}