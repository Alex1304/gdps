<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Services\PlayerManager;
use App\Services\SongProvider;
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

        return new Response($level->getId());
    }

    /**
     * @Route("/getGJLevels21.php", name="get_levels")
     */
    public function getLevels(Request $r, PlayerManager $pm, SongProvider $sp)
    {
        $em = $this->getDoctrine()->getManager();
        $player = $pm->getFromRequest($r);

        $levels = [];
        $total = 9999;

        // Types table:
        // 0 : regular search
        // 1 : most downloaded
        // 2 : most liked
        // 3 : trending
        // 5 : by user
        // 6 : featured
        // 16 : hall of fame
        // 7 : magic
        // 10 : map packs
        // 11 : awarded
        // 12 : followed
        // 13 : friends
        switch ($r->request->get('type')) {
            case 0:
                $query = $em->getRepository(Level::class)->searchLevels($r->request->get('str'), $r->request->get('diff'), $r->request->get('len'), $r->request->get('page'), $r->request->get('uncompleted'), $r->request->get('onlyCompleted'), $r->request->get('featured'), $r->request->get('original'), $r->request->get('twoPlayer'), $r->request->get('coins'), $r->request->get('epic'), $r->request->get('demonFilter'), $r->request->get('star'));
                $levels = $query['result'];
                $total = $query['total'];
                break;
            /*case 1:

                break;
            case 2:

                break;
            case 3:

                break;
            case 5:

                break;
            case 6

                break;
            case 7:

                break;
            case 10:

                break;
            case 11:

                break;
            case 12:

                break;
            case 13:

                break;
            case 16:

                break;*/
            default:
                return new Response('-1');
        }

        $songs = [];
        $creators = [];

        foreach ($levels as $level) {
            if ($level->getCustomSongID() > 0) {
                $song = $sp->fetchSong($level->getCustomSongID());
                if (!in_array($song, $songs))
                    $songs[] = $song;
            }

            $creator = [
                'playerID' => $level->getCreator()->getId(),
                'name' => $level->getCreator()->getName(),
                'accountID' => $level->getCreator()->getAccount() != null ? $level->getCreator()->getAccount()->getId() : 0,
            ];
            if (!in_array($creator, $creators))
                $creators[] = $creator;
        }

        return $this->render('levels/get_levels.html.twig', [
            'levels' => $levels,
            'songs' => $songs,
            'total' => $total,
            'creators' => $creators,
            'page' => $r->request->get('page'),
        ]);
    }
}
