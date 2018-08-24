<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Entity\Player;
use App\Services\GDAuthChecker;

class PlayerController extends Controller
{
    /**
     * @Route("/updateGJUserScore22.php", name="update_score")
     */
    public function updateScore(Request $r, GDAuthChecker $gdac)
    {	
    	$em = $this->getDoctrine()->getManager();

    	// Case where user is unregistered
    	if (!empty($r->request->get('udid')) || !empty($r->request->get('uuid'))) {
    		$players = $em->getRepository(Player::class)->findBy([
    			'udid' => $r->request->get('udid'),
    			'uuid' => $r->request->get('uuid'),
    		]);

    		$player = null;

    		foreach($players as $e) {
    			if ($e->getAccount() == null) {
    				$player = $e;
    				break;
    			}
    		}

    		if (!$player) {
    			$player = new Player();
    			$player->setUdid($r->request->get('udid'));
    			$player->setUuid($r->request->get('uuid'));
    		}
    	} else {
    		$account = $gdac->checkFromRequest($r);
    		if (is_numeric($account))
    			return new Response('-1');

    		$player = $account->getPlayer();
    	}

    	$player->setName($r->request->get('userName'));
    	$player->setStars($r->request->get('stars'));
    	$player->setDemons($r->request->get('demons'));
    	$player->setDiamonds($r->request->get('diamonds'));
    	$player->setIcon($r->request->get('icon'));
    	$player->setColor1($r->request->get('color1'));
    	$player->setColor2($r->request->get('color2'));
    	$player->setIconType($r->request->get('iconType'));
    	$player->setCoins($r->request->get('coins'));
    	$player->setUserCoins($r->request->get('userCoins'));
    	$player->setSpecial($r->request->get('special'));
    	$player->setAccIcon($r->request->get('accIcon'));
    	$player->setAccShip($r->request->get('accShip'));
    	$player->setAccBall($r->request->get('accBall'));
    	$player->setAccUFO($r->request->get('accBird'));
    	$player->setAccWave($r->request->get('accDart'));
    	$player->setAccRobot($r->request->get('accRobot'));
    	$player->setAccGlow($r->request->get('accGlow'));
    	$player->setAccSpider($r->request->get('accSpider'));
    	$player->setAccExplosion($r->request->get('accExplosion'));

    	$em->persist($player);
    	$em->flush();

    	return new Response($player->getId());
    }
}
