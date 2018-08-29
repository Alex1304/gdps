<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Services\PlayerManager;
use App\Entity\Comment;

class CommentController extends AbstractController
{
    /**
     * 
     */
    public function postLevelComment(Request $r, PlayerManager $pm): Response
    {
        return new Response('-1');
    }
}
