<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use FOS\RestBundle\Controller\Annotations as Rest;

use App\Services\EmailNotifier;
use App\Services\GDAuthChecker;
use App\Services\TimeFormatter;
use App\Services\TokenGenerator;
use App\Entity\Account;
use App\Entity\Authorization;
use App\Entity\Player;
use App\Entity\FriendRequest;
use App\Entity\Friend;
use App\Entity\PrivateMessage;

class AccountController extends AbstractController
{
    const USERS_PER_PAGE = 10;
    const FRIEND_REQUESTS_PER_PAGE = 20;
    const PRIVATE_MESSAGES_PER_PAGE = 50;

    /**
     * @Rest\Post("/accounts/registerGJAccount.php", name="account_register")
     *
     * @Rest\RequestParam(name="userName")
     * @Rest\RequestParam(name="email")
     * @Rest\RequestParam(name="password")
     */
    public function accountRegister(EmailNotifier $en, TokenGenerator $tokenGen, $userName, $email, $password)
    {
        $em = $this->getDoctrine()->getManager();

        $existingAccWithName = $em->getRepository(Account::class)->findOneByUsername($userName);

        if ($existingAccWithName)
            return -2;

        $existingAccWithEmail = $em->getRepository(Account::class)->findOneByEmail($email);

        if ($existingAccWithEmail)
            return -6;

        $account = new Account();

        $account->setUsername($userName);
        $account->setPassword(password_hash($password, PASSWORD_BCRYPT));
        $account->setEmail($email);
        $account->setYoutube('');
        $account->setTwitter('');
        $account->setTwitch('');
        $account->setRegisteredAt(new \DateTime());
        $account->setFriendRequestPolicy(0);
        $account->setPrivateMessagePolicy(0);
        $account->setCommentHistoryPolicy(0);
		$account->setIsVerified(false);
		$account->setIsLocked(false);
        $em->persist($account);
        $em->flush();
		
		$auth = $em->getRepository(Authorization::class)->forUser($account->getId(), Authorization::SCOPE_ACCOUNT_VERIFY) ?? new Authorization();
        $auth->setToken($tokenGen->generate($account));
        $auth->setUser($account);
		$auth->setScope(Authorization::SCOPE_ACCOUNT_VERIFY);
        $em->persist($auth);
        $em->flush();
		
		$en->sendAccountVerificationEmail($auth);

        return 1;
    }

    /**
     * @Rest\Post("/accounts/loginGJAccount.php", name="account_login")
     */
    public function accountLogin()
    {
        // Already handled by PlainPasswordAuthenticator, nothing to do here.
    }
	
	/**
	 * @Rest\Get("/accounts/accountManagement.php", name="account_management")
	 */
	public function accountManagement()
	{
		return $this->redirect(getenv('DASHBOARD_ROOT_URL'));
	}
	
	/**
	 * @Rest\Get("/accounts/lostusername.php", name="lost_username")
	 */
	public function forgotUsername()
	{
		return $this->redirect(getenv('DASHBOARD_ROOT_URL') . '/forgot-password');
	}
	
	/**
	 * @Rest\Get("/accounts/lostpassword.php", name="lost_password")
	 */
	public function forgotPassword()
	{
		return $this->redirect(getenv('DASHBOARD_ROOT_URL') . '/forgot-password');
	}

    /**
     * @Rest\Post("/getGJUserInfo20.php", name="get_user_info")
     *
     * @Rest\RequestParam(name="targetAccountID")
     */
    public function getUserInfo(Security $s, TimeFormatter $tf, $targetAccountID)
    {
        $em = $this->getDoctrine()->getManager();
        $player = $s->getUser();

        $target = $em->getRepository(Account::class)->find($targetAccountID);

        if (!$target)
            return -1;

        $acc = $player->getAccount();

        if ($acc->getBlockedBy()->contains($target) || $acc->getBlockedAccounts()->contains($target))
            return -1;

        $self = $player->getAccount() ? $acc->getId() === $target->getId() : false;
        $notifCounters = [];
        $friendState = 0;
        $incomingFR = null;

        if ($self) {
            $notifCounters['messages'] = $em->getRepository(PrivateMessage::class)->countNewPrivateMessages($acc->getId());
            $notifCounters['friends'] = $em->getRepository(Friend::class)->countNewFriends($acc->getId());
            $notifCounters['friendreqs'] = $em->getRepository(FriendRequest::class)->countUnreadIncomingFriendRequests($acc->getId());
        } else {
            if ($em->getRepository(Friend::class)->friendAB($acc->getId(), $target->getId()))
                $friendState = 1;
            elseif ($incomingFR = $em->getRepository(FriendRequest::class)->friendRequestBySenderAndRecipient($target->getId(), $acc->getId()))
                $friendState = 3;
            elseif ($em->getRepository(FriendRequest::class)->friendRequestBySenderAndRecipient($acc->getId(), $target->getId()))
                $friendState = 4;
        }
		
		$roles = $target->getPlayer()->getRoles();
		$modState = 0;
		if (in_array('ROLE_ELDERMOD', $roles)) {
			$modState = 2;
		} elseif (in_array('ROLE_MOD', $roles)) {
			$modState = 1;
		}

        return $this->render('account/get_user_info.html.twig', [
            'account' => $target,
            'globalRank' => $em->getRepository(Player::class)->globalRank($target->getPlayer()),
            'self' => $self,
            'friendState' => $friendState,
            'notifCounters' => $notifCounters,
            'incomingFR' => $incomingFR,
			'modState' => $modState,
            'timeFormatter' => $tf,
        ]);
    }

    /**
     * @Rest\Post("/getGJUsers20.php", name="search_users")
     *
     * @Rest\RequestParam(name="str")
     * @Rest\RequestParam(name="page")
     */
    public function searchUsers($str, $page)
    {
        $em = $this->getDoctrine()->getManager();

        $players = $em->getRepository(Player::class)->search($str, $page);

        if (!$players['total'])
            return -1;

        return $this->render('account/search_users.html.twig', [
            'players' => $players['result'],
            'total' => $players['total'],
            'page' => $page,
            'count' => self::USERS_PER_PAGE,
        ]);
    }

    /**
     * @Rest\Post("/updateGJAccSettings20.php", name="update_account_settings")
     *
     * @Rest\RequestParam(name="frS")
     * @Rest\RequestParam(name="mS")
     * @Rest\RequestParam(name="cS")
     * @Rest\RequestParam(name="yt")
     * @Rest\RequestParam(name="twitter")
     * @Rest\RequestParam(name="twitch")
     * 
     * @IsGranted("ROLE_USER")
     */
    public function updateAccountSettings(Security $s, $frS, $mS, $cS, $yt, $twitter, $twitch)
    {
        $em = $this->getDoctrine()->getManager();
        $player = $s->getUser();

        $acc = $player->getAccount();

        $acc->setFriendRequestPolicy($frS);
        $acc->setPrivateMessagePolicy($mS);
        $acc->setCommentHistoryPolicy($cS ?? 0);
        $acc->setYoutube($yt);
        $acc->setTwitter($twitter);
        $acc->setTwitch($twitch);

        $em->flush();

        return 1;
    }

    /**
     * @Rest\Post("/uploadFriendRequest20.php", name="send_friend_request")
     *
     * @Rest\RequestParam(name="toAccountID")
     * @Rest\RequestParam(name="comment")
     * 
     * @IsGranted("ROLE_USER")
     */
    public function sendFriendRequest(Security $s, $toAccountID, $comment)
    {
        $em = $this->getDoctrine()->getManager();
        $player = $s->getUser();
        $acc = $player->getAccount();
        $target = $em->getRepository(Account::class)->find($toAccountID);

        // Exit if target doesn't exist or has friend requests disabled
        if (!$target || $target->getFriendRequestPolicy() === 1)
            return -1;

        // Exit if target is blocked by or has blocked our user
        if ($acc->getBlockedBy()->contains($target) || $acc->getBlockedAccounts()->contains($target))
            return -1;

        // Exit if already friend
        if ($em->getRepository(Friend::class)->friendAB($acc->getId(), $target->getId()))
            return -1;

        $fr = $em->getRepository(FriendRequest::class)->friendRequestBySenderAndRecipient($acc->getId(), $target->getId());

        if (!$fr)
            $fr = new FriendRequest();

        $fr->setSender($acc);
        $fr->setRecipient($target);
        $fr->setMessage($comment);
        $fr->setMadeAt(new \DateTime());
        $fr->setIsUnread(true);

        $em->persist($fr);
        $em->flush();

        return 1;
    }

    /**
     * @Rest\Post("/deleteGJFriendRequests20.php", name="delete_friend_requests")
     *
     * @Rest\RequestParam(name="accounts", nullable=true, default=null)
     * @Rest\RequestParam(name="targetAccountID", nullable=true, default=null)
     * @Rest\RequestParam(name="isSender")
     * 
     * @IsGranted("ROLE_USER")
     */
    public function deleteFriendRequests(Security $s, $accounts, $targetAccountID, $isSender)
    {   
        if (!$accounts && !$targetAccountID) // Both cannot be null simultaneously, there should be at least one supplied
            return -1;

        $em = $this->getDoctrine()->getManager();
        $player = $s->getUser();
        $acc = $player->getAccount();
        $frsToDelete = $accounts ? explode(',', $accounts) : [ $targetAccountID ];

        foreach ($frsToDelete as $targetID) {
            $target = $em->getRepository(Account::class)->find($targetID);

            if ($isSender)
                $fr = $em->getRepository(FriendRequest::class)->friendRequestBySenderAndRecipient($acc->getId(), $target->getId());
            else
                $fr = $em->getRepository(FriendRequest::class)->friendRequestBySenderAndRecipient($target->getId(), $acc->getId());

            if ($fr)
                $em->remove($fr);
        }
        
        $em->flush();

        return 1;
    }

    /**
     * @Rest\Post("/getGJFriendRequests20.php", name="get_friend_requests")
     *
     * @Rest\RequestParam(name="getSent", nullable=true, default=null)
     * @Rest\RequestParam(name="page")
     * 
     * @IsGranted("ROLE_USER")
     */
    public function getFriendRequests(Security $s, TimeFormatter $tf, $getSent, $page)
    {
        $em = $this->getDoctrine()->getManager();
        $player = $s->getUser();

        if ($getSent)
            $frs = $em->getRepository(FriendRequest::class)->outgoingFriendRequestsForAccount($player->getAccount()->getId(), $page);
        else
            $frs = $em->getRepository(FriendRequest::class)->incomingFriendRequestsForAccount($player->getAccount()->getId(), $page);

        if (!$frs['total'])
            return -2;

        return $this->render('account/get_friend_requests.html.twig', [
            'frs' => $frs['result'],
            'total' => $frs['total'],
            'page' => $page,
            'count' => self::FRIEND_REQUESTS_PER_PAGE,
            'timeFormatter' => $tf,
            'incoming' => (bool) $getSent,
        ]);
    }

    /**
     * @Rest\Post("/readGJFriendRequest20.php", name="read_friend_request")
     *
     * @Rest\RequestParam(name="requestID")
     * 
     * @IsGranted("ROLE_USER")
     */
    public function readFriendRequest(Security $s, $requestID)
    {
        $em = $this->getDoctrine()->getManager();
        $player = $s->getUser();

        $fr = $em->getRepository(FriendRequest::class)->find($requestID);
        if (!$fr)
            return -1;

        $fr->setIsUnread(false);
        $em->flush();

        return 1;
    }


    /**
     * @Rest\Post("/acceptGJFriendRequest20.php", name="accept_friend_request")
     *
     * @Rest\RequestParam(name="requestID")
     * 
     * @IsGranted("ROLE_USER")
     */
    public function acceptFriendRequest(Security $s, $requestID)
    {
        $em = $this->getDoctrine()->getManager();
        $player = $s->getUser();

        $fr = $em->getRepository(FriendRequest::class)->find($requestID);
        if (!$fr)
            return -1;

        // Exit if already friend
        $friend = $em->getRepository(Friend::class)->friendAB($fr->getSender()->getId(), $fr->getRecipient()->getId());
        if ($friend)
            return -1;

        // Exit if friend limit reached
        $recipientHasReachedLimit = $em->getRepository(Friend::class)->hasReachedFriendsLimit($fr->getRecipient()->getId());
        $senderHasReachedLimit = $em->getRepository(Friend::class)->hasReachedFriendsLimit($fr->getSender()->getId());

        if ($recipientHasReachedLimit || $senderHasReachedLimit)
            return -1;

        $friend = new Friend();
        $friend->setA($fr->getSender());
        $friend->setB($fr->getRecipient());
        $friend->setIsNewForA(true);
        $friend->setIsNewForB(true);

        $em->persist($friend);
        $em->remove($fr);
        $em->flush();

        return 1;
    }

    /**
     * @Rest\Post("/getGJUserList20.php", name="get_user_list")
     *
     * @Rest\RequestParam(name="type")
     * 
     * @IsGranted("ROLE_USER")
     */
    public function getUserList(Security $s, $type)
    {
        $em = $this->getDoctrine()->getManager();
        $player = $s->getUser();
        $isFriendList = !((bool) $type);

        if ($isFriendList) {
            $friends = $em->getRepository(Friend::class)->friendsFor($player->getAccount()->getId());

            if (!count($friends))
                return -2;

            $newForA = [];
            $newForB = [];
            $users = [];

            foreach ($friends as $friend) {
                if ($player->getAccount()->getId() === $friend->getA()->getId()) {
                    $users[] = $friend->getB();
                    if ($friend->getIsNewForA())
                        $newForA[] = $friend->getB();
                    $friend->setIsNewForA(false);
                } else {
                    $users[] = $friend->getA();
                    if ($friend->getIsNewForB())
                        $newForB[] = $friend->getA();
                    $friend->setIsNewForB(false);
                }
            }

            $newFriends = array_merge($newForA, $newForB);

            $em->flush();
        } else {
            $users = $player->getAccount()->getBlockedAccounts()->toArray();
        }

        if (!count($users))
            return -2;

        uasort($users, function ($userA, $userB) {
            return strcasecmp($userA->getUsername(), $userB->getUsername());
        });

        return $this->render('account/get_user_list.html.twig', [
            'users' => $users,
            'newFriends' => $newFriends ?? [],
            'isFriendList' => $isFriendList,
        ]);
    }

    /**
     * @Rest\Post("/removeGJFriend20.php", name="remove_friend")
     *
     * @Rest\RequestParam(name="targetAccountID")
     * 
     * @IsGranted("ROLE_USER")
     */
    public function removeFriend(Security $s, $targetAccountID)
    {
        $em = $this->getDoctrine()->getManager();
        $player = $s->getUser();
        $acc = $player->getAccount();
        $target = $em->getRepository(Account::class)->find($targetAccountID);

        // Exit if target doesn't exist
        if (!$target)
            return -1;

        // Exit if already not friend
        $friend = $em->getRepository(Friend::class)->friendAB($acc->getId(), $target->getId());
        if (!$friend)
            return -1;

        $em->remove($friend);
        $em->flush();

        return 1;
    }

    /**
     * @Rest\Post("/uploadGJMessage20.php", name="send_private_message")
     *
     * @Rest\RequestParam(name="toAccountID")
     * @Rest\RequestParam(name="subject")
     * @Rest\RequestParam(name="body")
     * 
     * @IsGranted("ROLE_USER")
     */
    public function sendPrivateMessage(Security $s, $toAccountID, $subject, $body)
    {
        $em = $this->getDoctrine()->getManager();
        $player = $s->getUser();
        $acc = $player->getAccount();
        $target = $em->getRepository(Account::class)->find($toAccountID);

        // Exit if target:
        // - doesn't exist
        // - has blocked or is blocked by our user
        // - has disabled messages from everyone
        // - allows only messages from friends and our user isn't a friend
        if (!$target || $acc->getBlockedBy()->contains($target) || $acc->getBlockedAccounts()->contains($target) || $target->getPrivateMessagePolicy() === 2 || ($target->getPrivateMessagePolicy() === 1 && !$em->getRepository(Friend::class)->friendAB($acc->getId(), $target->getId())))
            return -1;

        $message = new PrivateMessage();
        $message->setAuthor($acc);
        $message->setRecipient($target);
        $message->setIsUnread(true);
        $message->setSubject($subject);
        $message->setBody($body);
        $message->setPostedAt(new \DateTime());
        $message->setAuthorHasDeleted(false);
        $message->setRecipientHasDeleted(false);

        $em->persist($message);
        $em->flush();

        return 1;
    }

    /**
     * @Rest\Post("/getGJMessages20.php", name="get_messages")
     *
     * @Rest\RequestParam(name="getSent", nullable=true, default=null)
     * @Rest\RequestParam(name="page")
     * 
     * @IsGranted("ROLE_USER")
     */
    public function getPrivateMessages(Security $s, TimeFormatter $tf, $getSent, $page)
    {
        $em = $this->getDoctrine()->getManager();
        $player = $s->getUser();

        $incoming = !((bool) $getSent ?? false);
        $messages = $em->getRepository(PrivateMessage::class)->privateMessagesFor($player->getAccount()->getId(), $page, !$incoming);

        if (!$messages['total'])
            return -2;

        return $this->render('account/get_messages.html.twig', [
            'messages' => $messages['result'],
            'total' => $messages['total'],
            'page' => $page,
            'count' => self::PRIVATE_MESSAGES_PER_PAGE,
            'timeFormatter' => $tf,
            'incoming' => $incoming,
        ]);
    }

    /**
     * @Rest\Post("/downloadGJMessage20.php", name="read_private_message")
     *
     * @Rest\RequestParam(name="messageID")
     * 
     * @IsGranted("ROLE_USER")
     */
    public function readPrivateMessage(Security $s, TimeFormatter $tf, $messageID)
    {
        $em = $this->getDoctrine()->getManager();
        $player = $s->getUser();

        $message = $em->getRepository(PrivateMessage::class)->find($messageID);
        if (!$message)
            return -1;

        $isSender = false;

        if ($message->getAuthor()->getId() === $player->getAccount()->getId()) {
            $isSender = true;
        } elseif ($message->getRecipient()->getId() !== $player->getAccount()->getId()) {
            return -1; // in this case our user is trying to read someone else's message that is not intended for him.
        }

        if (!$isSender) { // Mark as read
            $message->setIsUnread(false);
            $em->flush();
        }

        return $this->render('account/read_message.html.twig', [
            'message' => $message,
            'timeFormatter' => $tf,
            'isSender' => $isSender,
        ]);
    }

    /**
     * @Rest\Post("/deleteGJMessages20.php", name="delete_private_messages")
     *
     * @Rest\RequestParam(name="messageID", nullable=true, default=null)
     * @Rest\RequestParam(name="messages", nullable=true, default=null)
     * 
     * @IsGranted("ROLE_USER")
     */
    public function deletePrivateMessage(Security $s, $messages, $messageID)
    {
        if (!$messages && !$messageID)
            return -1;

        $em = $this->getDoctrine()->getManager();
        $player = $s->getUser();

        $messagesToDelete = $messages ? explode(',', $messages) : [ $messageID ];

        foreach ($messagesToDelete as $messageID) {
            $message = $em->getRepository(PrivateMessage::class)->find($messageID);
            if (!$message)
                return -1;

            $isSender = false;
            if ($message->getAuthor()->getId() === $player->getAccount()->getId())
                $isSender = true;
            elseif ($message->getRecipient()->getId() !== $player->getAccount()->getId())
                return -1; // in this case the user is trying to delete someone else's message that is not intended for him.

            if ($isSender)
                $message->setAuthorHasDeleted(true);
            else
                $message->setRecipientHasDeleted(true);

            if ($message->getAuthorHasDeleted() && $message->getRecipientHasDeleted())
                $em->remove($message);
        }

        $em->flush();

        return 1;
    }

    /**
     * @Rest\Post("/blockGJUser20.php", name="block_account")
     *
     * @Rest\RequestParam(name="targetAccountID")
     * 
     * @IsGranted("ROLE_USER")
     */
    public function blockAccount(Security $s, $targetAccountID)
    {
        $em = $this->getDoctrine()->getManager();
        $player = $s->getUser();

        $target = $em->getRepository(Account::class)->find($targetAccountID);
        if (!$target)
            return -1;

        // Force unfriend
        $friend = $em->getRepository(Friend::class)->friendAB($player->getAccount()->getId(), $target->getId());
        if ($friend)
            $em->remove($friend);

        // Delete any incoming friend request from that user
        $fr = $em->getRepository(FriendRequest::class)->friendRequestBySenderAndRecipient($target->getId(), $player->getAccount()->getId());
        if ($fr)
            $em->remove($fr);

        // Delete any outgoing friend request to that user
        $fr = $em->getRepository(FriendRequest::class)->friendRequestBySenderAndRecipient($player->getAccount()->getId(), $target->getId());
        if ($fr)
            $em->remove($fr);

        // Delete all messages sent from that user
        foreach ($player->getAccount()->getIncomingPrivateMessages() as $message) {
            if ($message->getAuthor()->getId() === $target->getId()) {
                $message->setRecipientHasDeleted(true);
                if ($message->getAuthorHasDeleted())
                    $em->remove($message);
            }
        }

        $player->getAccount()->addBlockedAccount($target);
        $em->flush();

        return 1;
    }

    /**
     * @Rest\Post("/unblockGJUser20.php", name="unblock_account")
     *
     * @Rest\RequestParam(name="targetAccountID")
     * 
     * @IsGranted("ROLE_USER")
     */
    public function unblockAccount(Security $s, $targetAccountID)
    {
        $em = $this->getDoctrine()->getManager();
        $player = $s->getUser();

        $target = $em->getRepository(Account::class)->find($targetAccountID);
        if (!$target)
            return -1;

        $player->getAccount()->removeBlockedAccount($target);
        $em->flush();

        return 1;
    }
	
	/**
     * @Rest\Post("/requestUserAccess.php", name="request_mod")
     * 
     * @IsGranted("ROLE_USER")
     */
	public function requestMod(Security $s)
	{
		$player = $s->getUser();
		$roles = $player->getRoles();
		
		if (in_array('ROLE_ELDERMOD', $roles)) {
			return 2;
		} elseif (in_array('ROLE_MOD', $roles)) {
			return 1;
		}
		
		return -1;
	}
}
