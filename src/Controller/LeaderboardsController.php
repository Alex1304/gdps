<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Security;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Entity\Player;
use App\Entity\Friend;
use App\Services\PlayerManager;

class LeaderboardsController extends AbstractController
{
    /**
     * @Rest\Post("/updateGJUserScore22.php", name="update_stats")
     *
     * @Rest\RequestParam(name="userName")
     * @Rest\RequestParam(name="stars")
     * @Rest\RequestParam(name="demons")
     * @Rest\RequestParam(name="diamonds")
     * @Rest\RequestParam(name="icon")
     * @Rest\RequestParam(name="color1")
     * @Rest\RequestParam(name="color2")
     * @Rest\RequestParam(name="iconType")
     * @Rest\RequestParam(name="coins")
     * @Rest\RequestParam(name="userCoins")
     * @Rest\RequestParam(name="special")
     * @Rest\RequestParam(name="accIcon")
     * @Rest\RequestParam(name="accShip")
     * @Rest\RequestParam(name="accBall")
     * @Rest\RequestParam(name="accBird")
     * @Rest\RequestParam(name="accDart")
     * @Rest\RequestParam(name="accRobot")
     * @Rest\RequestParam(name="accGlow")
     * @Rest\RequestParam(name="accSpider")
     * @Rest\RequestParam(name="accExplosion")
     */
    public function updateStats(Security $s, PlayerManager $pm, $userName, $stars, $demons, $diamonds, $icon, $color1, $color2, $iconType, $coins, $userCoins, $special, $accIcon, $accShip, $accBall, $accBird, $accDart, $accRobot, $accGlow, $accSpider, $accExplosion)
    {   
        $em = $this->getDoctrine()->getManager();
        $player = $s->getUser();

        $player->setName($userName);
        $player->setStars($stars);
        $player->setDemons($demons);
        $player->setDiamonds($diamonds);
        $player->setIcon($icon);
        $player->setColor1($color1);
        $player->setColor2($color2);
        $player->setIconType($iconType);
        $player->setCoins($coins);
        $player->setUserCoins($userCoins);
        $player->setSpecial($special);
        $player->setAccIcon($accIcon);
        $player->setAccShip($accShip);
        $player->setAccBall($accBall);
        $player->setAccUFO($accBird);
        $player->setAccWave($accDart);
        $player->setAccRobot($accRobot);
        $player->setAccGlow($accGlow);
        $player->setAccSpider($accSpider);
        $player->setAccExplosion($accExplosion);
        $player->setStatsLastUpdatedAt(new \DateTime());
        $pm->updateCreatorPoints($player);

        $em->persist($player);
        $em->flush();

        return $player->getId();
    }

    /**
     * @Rest\Post("/getGJScores20.php", name="get_leaderboard")
     *
     * @Rest\RequestParam(name="type")
     * @Rest\RequestParam(name="count")
     */
    public function getLeaderboard(Security $s, $type, $count)
    {
        if (!in_array($type, ['relative', 'creators', 'top', 'friends']))
            return -1;

        $em = $this->getDoctrine()->getManager();
        $player = $s->getUser();
        $playerList = null;
        $rankOffset = 1;

        switch ($type) {
            case 'relative':
                $result = $em->getRepository(Player::class)->relativeLeaderboard($player, $count);
                $playerList = $result['result'];
                $rankOffset = max(1, $result['rank'] - (int) $count / 2);
                break;
            case 'creators':
                $playerList = $em->getRepository(Player::class)->creatorLeaderboard($count);
                break;
            case 'top':
                $playerList = $em->getRepository(Player::class)->topLeaderboard($count);
                break;
            case 'friends':
                if (!$player->getAccount())
                    return -1;

                $friends = $em->getRepository(Friend::class)->friendsFor($player->getAccount()->getId());
                $friendsArray = [];
                foreach ($friends as $friend) {
                    $other = $friend->getA()->getId() === $player->getAccount()->getId() ? $friend->getB() : $friend->getA();
                    $friendsArray[] = $other->getPlayer()->getId();
                }

                $playerList = $em->getRepository(Player::class)->friendsLeaderboard($player->getId(), $friendsArray);
                break;
            default:
                return -1;
        }

        if ($playerList === null || count($playerList) === 0)
            return -1;

        return $this->render('leaderboards/leaderboards.html.twig', [
            'playerList' => $playerList,
            'rankOffset' => $rankOffset,
        ]);
    }
}
