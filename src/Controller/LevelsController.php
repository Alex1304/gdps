<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Services\PlayerManager;
use App\Entity\Level;

class LevelsController extends AbstractController
{
    /**
     * @Route("/uploadGJLevel21.php", name="upload_level")
     */
    public function uploadLevel(Request $r, PlayerManager $pm): Response
    {
        $em = $this->getDoctrine()->getManager();
        $player = $pm->getFromRequest($r);

        if (!$player)
            return new Response('-1');

        $level = null;
        $original = $r->request->get('original') > 0 ? $em->getRepository(Level::class)->find($r->request->get('original')) : null;
        $levelOverwrite = false;

        if ($r->request->get('levelID') == 0) {
            $levelsWithSameName = $em->getRepository(Level::class)->findByName($r->request->get('levelName'));
            if (count($levelsWithSameName) == 0) {
                $level = new Level();
                $level->setStars(0);
                $level->setFeatureScore(0);
                $level->setIsEpic(0);
                $level->setName($r->request->get('levelName'));
                $level->setUploadedAt(new \DateTime());
                $level->setIsUnlisted($r->request->get('unlisted'));
                $level->setOriginal($original);
            } else {
                $level = $levelsWithSameName[0];
                $levelOverwrite = true;
            }
        } else {
            $level = $em->getRepository(Level::class)->find($r->request->get('levelID'));
        }

        if (!$level)
            return new Response('-1');


        $level->setDescription($r->request->get('levelDesc'));
        $level->setCreator($player);
        $level->setData($r->request->get('levelString'));
        $level->setAudioTrack($r->request->get('audioTrack'));
        $level->setCustomSongID($r->request->get('songID'));
        $level->setGameVersion($r->request->get('gameVersion'));
        $level->setVersion($levelOverwrite ? $level->getVersion() + 1 : $r->request->get('levelVersion'));
        $level->setRequestedStars($r->request->get('requestedStars'));
        $level->setLastUpdatedAt(new \DateTime());
        $level->setLength($r->request->get('levelLength'));
        $level->setIsLDM($r->request->get('ldm'));
        $level->setPassword($r->request->get('password'));
        $level->setObjectCount($r->request->get('objects'));
        $level->setExtraString($r->request->get('extraString'));
        $level->setIsTwoPlayer($r->request->get('twoPlayer'));
        $level->setCoins($r->request->get('coins'));

        $em->persist($player);
        $em->persist($level);
        $em->flush();

        return new Response($level->getId()); // Not yet implemented
    }

    /**
     * @Route("/getGJLevels21.php", name="get_levels")
     */
    public function getLevels(Request $r, PlayerManager $pm)
    {
        return new Response('-1'); // Not yet implemented
    }
}
