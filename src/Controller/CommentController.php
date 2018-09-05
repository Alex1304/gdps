<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Services\PlayerManager;
use App\Services\TimeFormatter;
use App\Entity\LevelComment;
use App\Entity\AccountComment;
use App\Entity\Level;

class CommentController extends AbstractController
{
    const COMMENTS_PER_PAGE = 10;

    /**
     * @Route("/uploadGJComment21.php", name="upload_level_comment")
     */
    public function uploadLevelComment(Request $r, PlayerManager $pm): Response
    {
    	$em = $this->getDoctrine()->getManager();
    	$player = $pm->getFromRequest($r);

    	if (!$player || !$player->getAccount())
    		return new Response('-1');

    	$level = $em->getRepository(Level::class)->find($r->request->get('levelID'));

    	if (!$level)
    		return new Response('-1');

    	$comment = new LevelComment();
    	$comment->setPostedAt(new \DateTime());
    	$comment->setContent($r->request->get('comment'));
    	$comment->setAuthor($player);
    	$comment->setPercent($r->request->get('percent'));
    	$level->addLevelComment($comment);

    	$em->persist($comment);
    	$em->flush();

        return new Response('1');
    }

    /**
     * @Route("/getGJComments21.php", name="get_level_comments")
     */
    public function getLevelComments(Request $r, TimeFormatter $tf): Response
    {
    	$em = $this->getDoctrine()->getManager();

    	$comments = $em->getRepository(LevelComment::class)->commentsForLevel($r->request->get('levelID'), $r->request->get('page'), $r->request->get('mode'), $r->request->get('count'));

    	if (!count($comments['result']))
    		return new Response('-1');

    	return $this->render('comment/get_level_comments.html.twig', [
    		'showLevelID' => false,
    		'comments' => $comments['result'],
    		'total' => $comments['total'],
    		'timeFormatter' => $tf,
    		'page' => $r->request->get('page'),
    		'count' => $r->request->get('count') ?? self::COMMENTS_PER_PAGE,
    	]);
    }

    /**
     * @Route("/getGJCommentHistory.php", name="get_comment_history")
     */
    public function getCommentHistory(Request $r, TimeFormatter $tf): Response
    {
    	$em = $this->getDoctrine()->getManager();

    	$comments = $em->getRepository(LevelComment::class)->commentsByAuthor($r->request->get('userID'), $r->request->get('page'), $r->request->get('mode'), $r->request->get('count'));

    	if (!count($comments['result']))
    		return new Response('-1');

    	return $this->render('comment/get_level_comments.html.twig', [
    		'showLevelID' => true,
    		'comments' => $comments['result'],
    		'total' => $comments['total'],
    		'timeFormatter' => $tf,
    		'page' => $r->request->get('page'),
    		'count' => $r->request->get('count') ?? self::COMMENTS_PER_PAGE,
    	]);
    }

    /**
     * @Route("/deleteGJComment20.php", name="delete_level_comment")
     */
    public function deleteLevelComment(Request $r, PlayerManager $pm): Response
    {
    	$em = $this->getDoctrine()->getManager();
    	$player = $pm->getFromRequest($r);

    	$comment = $em->getRepository(LevelComment::class)->find($r->request->get('commentID'));

    	if ($comment->getAuthor()->getId() !== $player->getId() && $comment->getLevel()->getCreator()->getId() !== $player->getId())
    		return new Response('-1');

    	$em->remove($comment);
    	$em->flush();

    	return new Response('1');
    }

    /**
     * @Route("/uploadGJAccComment20.php", name="upload_account_comment")
     */
    public function uploadAccountComment(Request $r, PlayerManager $pm): Response
    {
    	$em = $this->getDoctrine()->getManager();
    	$player = $pm->getFromRequest($r);

    	if (!$player || !$player->getAccount())
    		return new Response('-1');

    	$comment = new AccountComment();
    	$comment->setPostedAt(new \DateTime());
    	$comment->setContent($r->request->get('comment'));
    	$comment->setAuthor($player->getAccount());

    	$em->persist($comment);
    	$em->flush();

        return new Response('1');
    }

    /**
     * @Route("/getGJAccountComments20.php", name="get_account_comments")
     */
    public function getAccountComments(Request $r, TimeFormatter $tf): Response
    {
    	$em = $this->getDoctrine()->getManager();

    	$comments = $em->getRepository(AccountComment::class)->commentsForAccount($r->request->get('accountID'), $r->request->get('page'));

    	return $this->render('comment/get_account_comments.html.twig', [
    		'comments' => $comments['result'],
    		'total' => $comments['total'],
    		'timeFormatter' => $tf,
    		'page' => $r->request->get('page'),
    		'count' => self::COMMENTS_PER_PAGE,
    	]);
    }

    /**
     * @Route("/deleteGJAccComment20.php", name="delete_account_comment")
     */
    public function deleteAccountComment(Request $r, PlayerManager $pm): Response
    {
    	$em = $this->getDoctrine()->getManager();
    	$player = $pm->getFromRequest($r);

    	$comment = $em->getRepository(AccountComment::class)->find($r->request->get('commentID'));

    	if ($comment->getAuthor()->getId() !== $player->getAccount()->getId())
    		return new Response('-1');

    	$em->remove($comment);
    	$em->flush();

    	return new Response('1');
    }
}
