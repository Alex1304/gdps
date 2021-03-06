<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Player;

/**
 * Service that manages player entities
 */
class PlayerManager
{
	private $em;
	private $gdac;

	public function __construct(EntityManagerInterface $em, GDAuthChecker $gdac)
	{
		$this->em = $em;
		$this->gdac = $gdac;
	}

	/**
	 * - First, it will attempt to authenticate using a provided accountID/GJP
	 * and return the associated player if found, null otherwise
	 * - If uuid is provided, then the player with the given ID is returned, or null if not found
	 * - If uuid isn't provided or is equal to zero, but udid is provided, then the first unregistered
	 * player with the matching udid is returned. If none is found, a new player is created and returned.
	 * - Otherwise it returns null
	 */
    public function getFromRequest(Request $r): ?Player
    {
        $player = null;
        $account = $this->gdac->checkFromRequest($r);

        if ($account === GDAuthChecker::ACCOUNT_UNAUTHORIZED)
        	return null;

        if (!is_numeric($account))
            $player = $account->getPlayer();
        elseif ($r->request->get('uuid'))
            $player = $this->em->getRepository(Player::class)->find($r->request->get('uuid'));
        elseif ($r->request->get('udid')) {
            $player = $this->em->getRepository(Player::class)->findUnregisteredByDeviceID($r->request->get('udid'));
            if ($player === null) {
                $player = new Player();
                $player->setDeviceID($r->request->get('udid'));
            }
        }


        return $player;
    }

    /**
     * Calculates the number of creator points the player should have according to his levels
     */
    public function calculateCreatorPoints(Player $player): int
    {
    	$cp = 0;

    	foreach ($player->getLevels() as $level) {
    		if ($level->getStars() > 0)
    			$cp++;

    		if ($level->getFeatureScore() > 0)
    			$cp++;

    		if ($level->getIsEpic())
    			$cp++;
    	}

    	return $cp;
    }

    public function updateCreatorPoints($player): void
    {
    	$player->setCreatorPoints($this->calculateCreatorPoints($player));
    }
}