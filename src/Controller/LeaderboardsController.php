<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Entity\Player;
use App\Services\PlayerManager;

class LeaderboardsController extends Controller
{
    /**
     * @Route("/updateGJUserScore22.php", name="update_stats")
     */
    public function updateStats(Request $r, PlayerManager $pm): Response
    {	
    	$em = $this->getDoctrine()->getManager();

    	$player = $pm->getFromRequest($r);

    	if (!$player)
    		return new Response('-1');

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
    	$player->setStatsLastUpdatedAt(new \DateTime());
    	$player->setCreatorPoints($pm->calculateCreatorPoints($player));

    	$em->persist($player);
    	$em->flush();

    	return new Response($player->getId());
    }

    /**
     * @Route("/getGJScores20.php", name="get_leaderboard")
     */
    public function getLeaderboard(Request $r, PlayerManager $pm): Response
    {
    	if (empty($r->request->get('type')) || empty($r->request->get('count')) || !is_numeric($r->request->get('count'))
    			|| !in_array($r->request->get('type'), ['relative', 'creators', 'top', 'friends']))
    		return new Response('-1');

    	$em = $this->getDoctrine()->getManager();

    	$player = $pm->getFromRequest($r);
    	$playerList = null;
    	$rankOffset = 1;

    	if (!$player)
    		return new Response('-1');

    	switch ($r->request->get('type')) {
    		case 'relative':
    			$result = $em->getRepository(Player::class)->relativeLeaderboard($player, $r->request->get('count'));
    			$playerList = $result['result'];
    			$rankOffset = max(1, $result['rank'] - (int) $r->request->get('count') / 2);
    			break;
    		case 'creators':
    			$playerList = $em->getRepository(Player::class)->creatorLeaderboard($r->request->get('count'));
    			break;
    		case 'top':
    			$playerList = $em->getRepository(Player::class)->topLeaderboard($r->request->get('count'));
    			break;
    		case 'friends':
    			return new Response('-1'); // Not yet implemented
    			break;
    		default:
    			return new Response('-1');
    	}

    	if ($playerList === null || count($playerList) === 0)
    		return new Response('-1');

    	return $this->render('leaderboards/leaderboards.html.twig', [
    		'playerList' => $playerList,
    		'rankOffset' => $rankOffset,
    	]);
    }
}
