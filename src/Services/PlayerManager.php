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
	 * - If uuid is provided, then the player with the given ID is returned, or null if not found
	 * - If uuid isn't provided or is equal to zero, but udid is provided, then the first unregistered
	 * player with the matching udid is returned. If non is found, a new player is created and returned.
	 * - If neither uuid nor udid is provided, then it will attempt to authenticate using a provided accountID/GJP
	 * and return the associated player if found, null otherwise
	 */
    public function getFromRequest(Request $r): ?Player
    {
        $player = null;

        if (!empty($r->request->get('uuid')) && (int) $r->request->get('uuid') > 0)
            $player = $this->em->getRepository(Player::class)->find($r->request->get('uuid'));
        elseif (!empty($r->request->get('udid'))) {
            $player = $this->em->getRepository(Player::class)->findUnregisteredByDeviceID($r->request->get('udid'));
            if ($player === null) {
                $player = new Player();
                $player->setDeviceID($r->request->get('udid'));
            }
        } else {
            $account = $this->gdac->checkFromRequest($r);
            if (!is_numeric($account))
                $player = $account->getPlayer();
        }

        return $player;
    }

    /**
     * Calculates the number of creator points the player should have according to his levels
     */
    public function calculateCreatorPoints(Player $player)
    {
    	return 0; // Not yet implemented
    }
}