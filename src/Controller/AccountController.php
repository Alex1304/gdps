<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Services\PlayerManager;
use App\Services\GDAuthChecker;
use App\Entity\Account;
use App\Entity\Player;

class AccountController extends AbstractController
{
    /**
     * @Route("/accounts/registerGJAccount.php", name="account_register")
     */
    public function accountRegister(Request $r)
    {
    	$em = $this->getDoctrine()->getManager();

    	$existingAccWithName = $em->getRepository(Account::class)->findOneBy([
    		'username' => $r->request->get('userName'),
    	]);

    	if ($existingAccWithName)
    		return new Response('-2');

    	$existingAccWithEmail = $em->getRepository(Account::class)->findOneBy([
    		'email' => $r->request->get('email'),
    	]);

    	if ($existingAccWithEmail)
    		return new Response('-3');

    	$account = new Account();

    	$account->setUsername($r->request->get('userName'));
    	$account->setPassword(password_hash($r->request->get('password'), PASSWORD_BCRYPT));
    	$account->setEmail($r->request->get('email'));
    	$account->setYoutube('');
    	$account->setTwitter('');
    	$account->setTwitch('');
    	$account->setRegisteredAt(new \DateTime());
    	$account->setFriendRequestPolicy(0);
    	$account->setPrivateMessagePolicy(0);

    	$em->persist($account);
    	$em->flush();

    	return new Response('1');
    }

    /**
     * @Route("/accounts/loginGJAccount.php", name="account_login")
     */
    public function accountLogin(Request $r, GDAuthChecker $gdac)
    {
    	$em = $this->getDoctrine()->getManager();

    	$account = $em->getRepository(Account::class)->findOneBy([
    		'username' => $r->request->get('userName'),
    	]);

    	if (!$account || !$gdac->checkPlain($account, $r->request->get('password')))
    		return new Response('-1');

    	if (!$account->getPlayer()) {
    		$player = $em->getRepository(Player::class)->findOneBy([
    			'deviceID' => $r->request->get('udid'),
    		]);

    		if (!$player) {
    			$player = new Player();
    			$player->setName('Player');
    			$player->setDeviceID('none');
		        $player->setStars(0);
		        $player->setDemons(0);
		        $player->setDiamonds(0);
		        $player->setIcon(0);
		        $player->setColor1(0);
		        $player->setColor2(0);
		        $player->setIconType(0);
		        $player->setCoins(0);
		        $player->setUserCoins(0);
		        $player->setSpecial(0);
		        $player->setAccIcon(0);
		        $player->setAccShip(0);
		        $player->setAccBall(0);
		        $player->setAccUFO(0);
		        $player->setAccWave(0);
		        $player->setAccRobot(0);
		        $player->setAccGlow(0);
		        $player->setAccSpider(0);
		        $player->setAccExplosion(0);
		        $player->setStatsLastUpdatedAt(new \DateTime());
		        $player->setCreatorPoints(0);
    		}

    		$account->setPlayer($player);
    		$em->persist($player);
    		$em->flush();
    	}

    	return new Response($account->getId() . ',' . $account->getPlayer()->getId());
    }

    /**
     * @Route("/getGJUserInfo20.php", name="get_user_info")
     */
    public function getUserInfo(Request $r, PlayerManager $pm)
    {
    	$em = $this->getDoctrine()->getManager();
    	$player = $pm->getFromRequest($r);

    	$target = $em->getRepository(Account::class)->find($r->request->get('targetAccountID'));

    	if (!$target)
    		return new Response('-1');

    	return $this->render('account/get_user_info.html.twig', [
    		'account' => $target,
    		'self' => $player->getAccount() ? $player->getAccount()->getId() === $target->getId() : false,
    	]);
    }
}