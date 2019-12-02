<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use FOS\RestBundle\Controller\Annotations as Rest;

use App\Services\ElderCommandHandler;
use App\Services\TimeFormatter;
use App\Entity\Account;
use App\Entity\AccountComment;
use App\Entity\CommentBan;
use App\Entity\Level;
use App\Entity\LevelComment;

class CommentController extends AbstractController
{
    /**
     * @Rest\Post("/uploadGJComment21.php", name="upload_level_comment")
     *
     * @Rest\RequestParam(name="levelID")
     * @Rest\RequestParam(name="comment")
     * @Rest\RequestParam(name="percent", nullable=true, default=0)
     *
     * @IsGranted("ROLE_USER")
     */
    public function uploadLevelComment(Security $s, ElderCommandHandler $ech, $levelID, $comment, $percent)
    {
    	$em = $this->getDoctrine()->getManager();
    	$player = $s->getUser();
		
		// Handling elder mod commands
		$commandResult = $ech->handle($comment, $player);
		if ($commandResult !== false) {
			return $commandResult;
		}
		
    	$level = $em->getRepository(Level::class)->find($levelID);

    	if (!$level)
    		return -1;
		
		// Check if comment banned (exit if so)
		$ban = $em->getRepository(CommentBan::class)->findCurrentBan($player->getId());
		if ($ban) {
			$timeLeft = $ban->getExpiresAt()->getTimestamp() - time();
			return 'temp_' . $timeLeft . ($ban->getReason() ? '_' . $ban->getReason() : '');
		}

    	$lvlcomment = new LevelComment();
    	$lvlcomment->setPostedAt(new \DateTime());
    	$lvlcomment->setContent($comment);
    	$lvlcomment->setAuthor($player);
    	$lvlcomment->setPercent($percent);
    	$lvlcomment->setLikes(0);
    	$level->addLevelComment($lvlcomment);

    	$em->persist($lvlcomment);
    	$em->flush();

        return 1;
    }

    /**
     * @Rest\Post("/getGJComments21.php", name="get_level_comments")
     *
     * @Rest\RequestParam(name="levelID")
     * @Rest\RequestParam(name="page")
     * @Rest\RequestParam(name="mode")
     * @Rest\RequestParam(name="count", nullable=true, default=10)
     */
    public function getLevelComments(TimeFormatter $tf, $levelID, $page, $mode, $count)
    {
    	$em = $this->getDoctrine()->getManager();

    	$comments = $em->getRepository(LevelComment::class)->commentsForLevel($levelID, $page, $mode, $count);

    	if (!count($comments['result']))
    		return -1;

    	return $this->render('comment/get_level_comments.html.twig', [
    		'showLevelID' => false,
    		'comments' => $comments['result'],
    		'total' => $comments['total'],
    		'timeFormatter' => $tf,
    		'page' => $page,
    		'count' => $count,
    	]);
    }

    /**
     * @Rest\Post("/getGJCommentHistory.php", name="get_comment_history")
     *
     * @Rest\RequestParam(name="userID")
     * @Rest\RequestParam(name="page")
     * @Rest\RequestParam(name="mode")
     * @Rest\RequestParam(name="count", nullable=true, default=10)
     */
    public function getCommentHistory(TimeFormatter $tf, $userID, $page, $mode, $count)
    {
    	$em = $this->getDoctrine()->getManager();

    	$comments = $em->getRepository(LevelComment::class)->commentsByAuthor($userID, $page, $mode, $count);

    	if (!count($comments['result']))
    		return -1;

    	return $this->render('comment/get_level_comments.html.twig', [
    		'showLevelID' => true,
    		'comments' => $comments['result'],
    		'total' => $comments['total'],
    		'timeFormatter' => $tf,
    		'page' => $page,
    		'count' => $count,
    	]);
    }

    /**
     * @Rest\Post("/deleteGJComment20.php", name="delete_level_comment")
     *
     * @Rest\RequestParam(name="commentID")
     *
     * @IsGranted("ROLE_USER")
     */
    public function deleteLevelComment(Security $s, $commentID)
    {
    	$em = $this->getDoctrine()->getManager();
    	$player = $s->getUser();

    	$comment = $em->getRepository(LevelComment::class)->find($commentID);

    	if (!$s->isGranted('ROLE_ELDERMOD') && $comment->getAuthor()->getId() !== $player->getId() && $comment->getLevel()->getCreator()->getId() !== $player->getId())
    		return -1;

    	$em->remove($comment);
    	$em->flush();

    	return 1;
    }

    /**
     * @Rest\Post("/uploadGJAccComment20.php", name="upload_account_comment")
     *
     * @Rest\RequestParam(name="comment")
     *
     * @IsGranted("ROLE_USER")
     */
    public function uploadAccountComment(Security $s, $comment)
    {
    	$em = $this->getDoctrine()->getManager();
    	$player = $s->getUser();

    	$accountComment = new AccountComment();
    	$accountComment->setPostedAt(new \DateTime());
    	$accountComment->setContent($comment);
    	$accountComment->setAuthor($player->getAccount());
    	$accountComment->setLikes(0);

    	$em->persist($accountComment);
    	$em->flush();

        return 1;
    }

    /**
     * @Rest\Post("/getGJAccountComments20.php", name="get_account_comments")
     *
     * @Rest\RequestParam(name="accountID")
     * @Rest\RequestParam(name="page")
     */
    public function getAccountComments(TimeFormatter $tf, $accountID, $page)
    {
    	$em = $this->getDoctrine()->getManager();

    	$comments = $em->getRepository(AccountComment::class)->commentsForAccount($accountID, $page);

    	return $this->render('comment/get_account_comments.html.twig', [
    		'comments' => $comments['result'],
    		'total' => $comments['total'],
    		'timeFormatter' => $tf,
    		'page' => $page,
    		'count' => 10,
    	]);
    }

    /**
     * @Rest\Post("/deleteGJAccComment20.php", name="delete_account_comment")
     *
     * @Rest\RequestParam(name="commentID")
     *
     * @IsGranted("ROLE_USER")
     */
    public function deleteAccountComment(Security $s, $commentID)
    {
    	$em = $this->getDoctrine()->getManager();
    	$player = $s->getUser();

    	$comment = $em->getRepository(AccountComment::class)->find($commentID);

    	if (!$s->isGranted('ROLE_ELDERMOD') && $comment->getAuthor()->getId() !== $player->getAccount()->getId())
    		return -1;

    	$em->remove($comment);
    	$em->flush();

    	return 1;
    }
}
