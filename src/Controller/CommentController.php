<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Services\PlayerManager;
use App\Services\TimeFormatter;
use App\Entity\LevelComment;
use App\Entity\Level;

class CommentController extends AbstractController
{
    /**
     * @Route("/uploadGJComment21.php", name="upload_level_comment")
     */
    public function postLevelComment(Request $r, PlayerManager $pm): Response
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
    public function getLevelComments(Request $r, PlayerManager $pm, TimeFormatter $tf)
    {
    	$em = $this->getDoctrine()->getManager();

    	$comments = $em->getRepository(LevelComment::class)->commentsForLevel($r->request->get('levelID'), $r->request->get('page'), $r->request->get('mode'), $r->request->get('count'));

    	if (!count($comments['result']))
    		return new Response('-1');

    	return $this->render('comment/get_level_comments.html.twig', [
    		'comments' => $comments['result'],
    		'total' => $comments['total'],
    		'timeFormatter' => $tf,
    		'page' => $r->request->get('page'),
    		'count' => count($comments['result']),
    	]);
    }

    /**
     * @Route("/deleteGJComment20.php", name="delete_level_comment")
     */
    public function deleteLevelComment(Request $r, PlayerManager $pm)
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
}
