<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Services\PlayerManager;
use App\Services\GDAuthChecker;
use App\Services\TimeFormatter;
use App\Entity\Account;
use App\Entity\Player;
use App\Entity\FriendRequest;
use App\Entity\Friend;

class AccountController extends AbstractController
{
    /**
     * @Route("/accounts/registerGJAccount.php", name="account_register")
     */
    public function accountRegister(Request $r): Response
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
    public function accountLogin(Request $r, GDAuthChecker $gdac): Response
    {
        $em = $this->getDoctrine()->getManager();

        $account = $em->getRepository(Account::class)->findOneBy([
            'username' => $r->request->get('userName'),
        ]);

        if (!$account || !$gdac->checkPlain($account, $r->request->get('password')))
            return new Response('-1');

        // Finding an unregistered player with same deviceID as the person who attempts to login
        $player = $em->getRepository(Player::class)->findUnregisteredByDeviceID($r->request->get('udid'));

        if (!$account->getPlayer()) {
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
        } else {
            // If the account already has an associated player, the other player instance wiht the same deviceID will be destroyed, unless he has some levels uploaded.
            if ($player && !count($player->getLevels())) {
                $em->remove($player);
                $em->flush();
            }
        }

        return new Response($account->getId() . ',' . $account->getPlayer()->getId());
    }

    /**
     * @Route("/getGJUserInfo20.php", name="get_user_info")
     */
    public function getUserInfo(Request $r, PlayerManager $pm, TimeFormatter $tf): Response
    {
        $em = $this->getDoctrine()->getManager();
        $player = $pm->getFromRequest($r);

        $target = $em->getRepository(Account::class)->find($r->request->get('targetAccountID'));

        if (!$target)
            return new Response('-1');

        $acc = $player->getAccount();
        $self = $player->getAccount() ? $acc->getId() === $target->getId() : false;
        $notifCounters = [];
        $friendState = 0;
        $incomingFR = null;

        if ($self) {
            $notifCounters['messages'] = 0;
            $notifCounters['friends'] = $em->getRepository(Friend::class)->countNewFriends($acc->getId()) ?? 0;
            $notifCounters['friendreqs'] = $em->getRepository(FriendRequest::class)->countUnreadIncomingFriendRequests($acc->getId()) ?? 0;
        } else {
            if ($em->getRepository(Friend::class)->friendAB($acc->getId(), $target->getId()))
                $friendState = 1;
            elseif ($incomingFR = $em->getRepository(FriendRequest::class)->friendRequestBySenderAndRecipient($target->getId(), $acc->getId()))
                $friendState = 3;
            elseif ($em->getRepository(FriendRequest::class)->friendRequestBySenderAndRecipient($acc->getId(), $target->getId()))
                $friendState = 4;
        }

        return $this->render('account/get_user_info.html.twig', [
            'account' => $target,
            'globalRank' => $em->getRepository(Player::class)->globalRank($target->getPlayer()),
            'self' => $self,
            'friendState' => $friendState,
            'notifCounters' => $notifCounters,
            'incomingFR' => $incomingFR,
            'timeFormatter' => $tf,
        ]);
    }

    /**
     * @Route("/getGJUsers20.php", name="search_users")
     */
    public function searchUsers(Request $r): Response
    {
        $em = $this->getDoctrine()->getManager();

        $players = $em->getRepository(Player::class)->search($r->request->get('str'), $r->request->get('page'));

        if (!$players['total'])
            return new Response('-1');

        return $this->render('account/search_users.html.twig', [
            'players' => $players['result'],
            'total' => $players['total'],
            'page' => $r->request->get('page'),
            'count' => count($players['result']),
        ]);
    }

    /**
     * @Route("/updateGJAccSettings20.php", name="update_account_settings")
     */
    public function updateAccountSettings(Request $r, PlayerManager $pm): Response
    {
        $em = $this->getDoctrine()->getManager();
        $player = $pm->getFromRequest($r);

        if (!$player || !$player->getAccount())
            return new Response('-1');

        $acc = $player->getAccount();

        $acc->setFriendRequestPolicy($r->request->get('frS'));
        $acc->setPrivateMessagePolicy($r->request->get('mS'));
        $acc->setCommentHistoryPolicy($r->request->get('cS') ?? 0);
        $acc->setYoutube($r->request->get('yt'));
        $acc->setTwitter($r->request->get('twitter'));
        $acc->setTwitch($r->request->get('twitch'));

        $em->flush();

        return new Response('1');
    }

    /**
     * @Route("/uploadFriendRequest20.php", name="send_friend_request")
     */
    public function sendFriendRequest(Request $r, PlayerManager $pm): Response
    {
        $em = $this->getDoctrine()->getManager();
        $player = $pm->getFromRequest($r);

        if (!$player || !$player->getAccount())
            return new Response('-1');

        $acc = $player->getAccount();
        $target = $em->getRepository(Account::class)->find($r->request->get('toAccountID'));

        if (!$target || $target->getFriendRequestPolicy() === 1)
            return new Response('-1');

        $friend = $em->getRepository(Friend::class)->friendAB($acc->getId(), $target->getId());

        if ($friend)
            return new Response('-1');

        $fr = $em->getRepository(FriendRequest::class)->friendRequestBySenderAndRecipient($acc->getId(), $target->getId());

        if (!$fr)
            $fr = new FriendRequest();

        $fr->setSender($acc);
        $fr->setRecipient($target);
        $fr->setMessage($r->request->get('comment'));
        $fr->setMadeAt(new \DateTime());
        $fr->setIsUnread(true);

        $em->persist($fr);
        $em->flush();

        return new Response('1');
    }

    /**
     * @Route("/deleteGJFriendRequests20.php", name="delete_friend_request")
     */
    public function deleteFriendRequest(Request $r, PlayerManager $pm): Response
    {
        $em = $this->getDoctrine()->getManager();
        $player = $pm->getFromRequest($r);

        if (!$player || !$player->getAccount())
            return new Response('-1');

        $acc = $player->getAccount();
        $target = $em->getRepository(Account::class)->find($r->request->get('targetAccountID'));

        if (!$target)
            return new Response('-1');

        if ($r->request->get('isSender'))
            $fr = $em->getRepository(FriendRequest::class)->friendRequestBySenderAndRecipient($acc->getId(), $target->getId());
        else
            $fr = $em->getRepository(FriendRequest::class)->friendRequestBySenderAndRecipient($target->getId(), $acc->getId());

        if (!$fr)
            return new Response('-1');

        $em->remove($fr);
        $em->flush();

        return new Response('1');
    }

    /**
     * @Route("/getGJFriendRequests20.php", name="get_friend_requests")
     */
    public function getFriendRequests(Request $r, PlayerManager $pm, TimeFormatter $tf): Response
    {
        $em = $this->getDoctrine()->getManager();
        $player = $pm->getFromRequest($r);

        if (!$player || !$player->getAccount())
            return new Response('-1');

        if ($r->request->get('getSent'))
            $frs = $em->getRepository(FriendRequest::class)->outgoingFriendRequestsForAccount($player->getAccount()->getId(), $r->request->get('page'));
        else
            $frs = $em->getRepository(FriendRequest::class)->incomingFriendRequestsForAccount($player->getAccount()->getId(), $r->request->get('page'));

        if (!$frs['total'])
            return new Response('-2');

        return $this->render('account/get_friend_requests.html.twig', [
            'frs' => $frs['result'],
            'total' => $frs['total'],
            'page' => $r->request->get('page'),
            'count' => count($frs['result']),
            'timeFormatter' => $tf,
            'incoming' => (bool) $r->request->get('getSent'),
        ]);
    }

    /**
     * @Route("/readGJFriendRequest20.php", name="read_friend_request")
     */
    public function readFriendRequest(Request $r, PlayerManager $pm)
    {
        $em = $this->getDoctrine()->getManager();
        $player = $pm->getFromRequest($r);

        if (!$player || !$player->getAccount())
            return new Response('-1');

        $fr = $em->getRepository(FriendRequest::class)->find($r->request->get('requestID'));

        if (!$fr)
            return new Response('-1');

        $fr->setIsUnread(false);
        $em->flush();

        return new Response('1');
    }


    /**
     * @Route("/acceptGJFriendRequest20.php", name="accept_friend_request")
     */
    public function acceptFriendRequest(Request $r, PlayerManager $pm): Response
    {
        $em = $this->getDoctrine()->getManager();
        $player = $pm->getFromRequest($r);

        if (!$player || !$player->getAccount())
            return new Response('-1');

        $fr = $em->getRepository(FriendRequest::class)->find($r->request->get('requestID'));

        if (!$fr)
            return new Response('-1');

        $friend = $em->getRepository(Friend::class)->friendAB($fr->getSender()->getId(), $fr->getRecipient()->getId());

        if ($friend)
            return new Response('-1');

        $friend = new Friend();
        $friend->setA($fr->getSender());
        $friend->setB($fr->getRecipient());
        $friend->setIsNewForA(true);
        $friend->setIsNewForB(true);

        $em->persist($friend);
        $em->remove($fr);
        $em->flush();

        return new Response('1');
    }

    /**
     * @Route("/getGJUserList20.php", name="get_friends")
     */
    public function getFriends(Request $r, PlayerManager $pm): Response
    {
        $em = $this->getDoctrine()->getManager();
        $player = $pm->getFromRequest($r);

        if (!$player || !$player->getAccount())
            return new Response('-1');

        $friends = $em->getRepository(Friend::class)->friendsFor($player->getAccount()->getId());

        if (!count($friends))
            return new Response('-2');

        $newForA = [];
        $newForB = [];

        foreach ($friends as $friend) {
            if ($player->getAccount()->getId() === $friend->getA()->getId()) {
                if ($friend->getIsNewForA())
                    $newForA[] = $friend;
                $friend->setIsNewForA(false);
            } else {
                if ($friend->getIsNewForB())
                    $newForB[] = $friend;
                $friend->setIsNewForB(false);
            }
        }

        $newFriends = array_merge($newForA, $newForB);

        $em->flush();

        return $this->render('account/get_friends.html.twig', [
            'friends' => $friends,
            'newFriends' => $newFriends,
            'me' => $player->getAccount()->getId(),
        ]);
    }

    /**
     * @Route("removeGJFriend20.php", name="remove_friend")
     */
    public function removeFriend(Request $r, PlayerManager $pm): Response
    {
        $em = $this->getDoctrine()->getManager();
        $player = $pm->getFromRequest($r);

        if (!$player || !$player->getAccount())
            return new Response('-1');

        $acc = $player->getAccount();
        $target = $em->getRepository(Account::class)->find($r->request->get('targetAccountID'));

        if (!$target)
            return new Response('-1');

        $friend = $em->getRepository(Friend::class)->friendAB($acc->getId(), $target->getId());

        if (!$friend)
            return new Response('-1');

        $em->remove($friend);
        $em->flush();

        return new Response('1');
    }
}