<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Services\PlayerManager;
use App\Services\SongProvider;
use App\Services\HashGenerator;
use App\Services\Base64URL;
use App\Services\XORCipher;
use App\Services\TimeFormatter;
use App\Services\DifficultyCalculator;
use App\Entity\Level;
use App\Entity\LevelComment;
use App\Entity\AccountComment;
use App\Entity\LevelStarVote;
use App\Entity\LevelDemonVote;
use App\Entity\Friend;
use App\Entity\LevelScore;

class LevelsController extends AbstractController
{
    const LEVELS_PER_PAGE = 10;

    /**
     * @Route("/uploadGJLevel21.php", name="upload_level")
     */
    public function uploadLevel(Request $r, PlayerManager $pm, Base64URL $b64): Response
    {
        $em = $this->getDoctrine()->getManager();
        $player = $pm->getFromRequest($r);

        if (!$player)
            return new Response('-1');

        $level = null;
        $original = $r->request->get('original') > 0 ? $em->getRepository(Level::class)->find($r->request->get('original')) : null;

        if ($r->request->get('levelID') == 0) {
            $levelWithSameName = $em->getRepository(Level::class)->levelWithSameNameByCreator($player->getId(), $r->request->get('levelName'));
            if (!$levelWithSameName) {
                $level = new Level();
                $level->setCreator($player);
                $level->setVersion(0); // Will be incremented anyway at line 79
                $level->setStars(0);
                $level->setFeatureScore(0);
                $level->setIsEpic(0);
                $level->setName($r->request->get('levelName'));
                $level->setUploadedAt(new \DateTime());
                $level->setIsUnlisted($r->request->get('unlisted') ?? 0);
                $level->setOriginal($original);
                $level->setDifficulty(0);
                $level->setDemonDifficulty(0);
                $level->setIsDemon(false);
                $level->setIsAuto(false);
                $level->setHasCoinsVerified(false);
                $level->setRewardsGivenAt(null);
            } else {
                $level = $levelWithSameName;
            }
        } else
            $level = $em->getRepository(Level::class)->find($r->request->get('levelID'));

        if (!$level)
            return new Response('-1');

        $level->setDescription($r->request->get('levelDesc') ?? '');
        if (substr($r->request->get('levelString'), 0, 3) == 'kS1')
            $level->setData($b64->encode(gzcompress($r->request->get('levelString'))));
        else
            $level->setData($r->request->get('levelString'));
        $level->setAudioTrack($r->request->get('audioTrack'));
        $level->setCustomSongID($r->request->get('songID'));
        $level->setGameVersion($r->request->get('gameVersion'));
        $level->setVersion($level->getVersion() + 1);
        $level->setRequestedStars($r->request->get('requestedStars') ?? 0);
        $level->setLastUpdatedAt(new \DateTime());
        $level->setLength($r->request->get('levelLength'));
        $level->setIsLDM($r->request->get('ldm') ?? 0);
        $level->setPassword($r->request->get('password') ?? 0);
        $level->setObjectCount($r->request->get('objects') ?? 0);
        $level->setExtraString($r->request->get('extraString'));
        $level->setIsTwoPlayer($r->request->get('twoPlayer') ?? 0);
        $level->setCoins($r->request->get('coins'));

        $em->persist($player);
        $em->persist($level);
        $em->flush();

        return new Response($level->getId());
    }

    /**
     * @Route("/getGJLevels21.php", name="get_levels")
     */
    public function getLevels(Request $r, PlayerManager $pm, SongProvider $sp, HashGenerator $hg): Response
    {
        $em = $this->getDoctrine()->getManager();
        $player = $pm->getFromRequest($r);

        $levels = [];
        $songs = [];
        $creators = [];
        $total = 9999;

        // Types table:
        // 0  : regular search
        // 1  : most downloaded
        // 2  : most liked
        // 3  : trending
        // 5  : by user
        // 6  : featured
        // 7  : magic
        // 10 : map packs
        // 11 : awarded
        // 12 : followed
        // 13 : friends
        // 16 : hall of fame
        switch ($r->request->get('type')) {
            case 0:
                $query = $em->getRepository(Level::class)->searchLevels($r->request->get('str'), $r->request->get('diff'), $r->request->get('len'), $r->request->get('page'), $r->request->get('uncompleted'), $r->request->get('onlyCompleted'), $r->request->get('featured'), $r->request->get('original'), $r->request->get('twoPlayer'), $r->request->get('coins'), $r->request->get('epic'), $r->request->get('demonFilter'), $r->request->get('star'), $r->request->get('noStar'), $r->request->get('song'), $r->request->get('customSong'), $r->request->get('completedLevels'));
                break;
            case 1:
                $query = $em->getRepository(Level::class)->mostDownloadedLevels($r->request->get('diff'), $r->request->get('len'), $r->request->get('page'), $r->request->get('uncompleted'), $r->request->get('onlyCompleted'), $r->request->get('featured'), $r->request->get('original'), $r->request->get('twoPlayer'), $r->request->get('coins'), $r->request->get('epic'), $r->request->get('demonFilter'), $r->request->get('star'), $r->request->get('noStar'), $r->request->get('song'), $r->request->get('customSong'), $r->request->get('completedLevels'));
                break;
            case 2:
                $query = $em->getRepository(Level::class)->mostLikedLevels($r->request->get('diff'), $r->request->get('len'), $r->request->get('page'), $r->request->get('uncompleted'), $r->request->get('onlyCompleted'), $r->request->get('featured'), $r->request->get('original'), $r->request->get('twoPlayer'), $r->request->get('coins'), $r->request->get('epic'), $r->request->get('demonFilter'), $r->request->get('star'), $r->request->get('noStar'), $r->request->get('song'), $r->request->get('customSong'), $r->request->get('completedLevels'));
                break;
            case 3:
                $query = $em->getRepository(Level::class)->trendingLevels($r->request->get('diff'), $r->request->get('len'), $r->request->get('page'), $r->request->get('uncompleted'), $r->request->get('onlyCompleted'), $r->request->get('featured'), $r->request->get('original'), $r->request->get('twoPlayer'), $r->request->get('coins'), $r->request->get('epic'), $r->request->get('demonFilter'), $r->request->get('star'), $r->request->get('noStar'), $r->request->get('song'), $r->request->get('customSong'), $r->request->get('completedLevels'));
                break;
            case 4:
                $query = $em->getRepository(Level::class)->recentLevels($r->request->get('diff'), $r->request->get('len'), $r->request->get('page'), $r->request->get('uncompleted'), $r->request->get('onlyCompleted'), $r->request->get('featured'), $r->request->get('original'), $r->request->get('twoPlayer'), $r->request->get('coins'), $r->request->get('epic'), $r->request->get('demonFilter'), $r->request->get('star'), $r->request->get('noStar'), $r->request->get('song'), $r->request->get('customSong'), $r->request->get('completedLevels'));
                break;
            case 5:
                $query = $em->getRepository(Level::class)->levelsByUser($r->request->get('str'), $player, $r->request->get('page'));
                break;
            case 6:
                $query = $em->getRepository(Level::class)->featuredLevels($r->request->get('page'));
                break;
            case 7:
                $query = $em->getRepository(Level::class)->magicLevels($r->request->get('diff'), $r->request->get('len'), $r->request->get('page'), $r->request->get('uncompleted'), $r->request->get('onlyCompleted'), $r->request->get('featured'), $r->request->get('original'), $r->request->get('twoPlayer'), $r->request->get('coins'), $r->request->get('epic'), $r->request->get('demonFilter'), $r->request->get('star'), $r->request->get('noStar'), $r->request->get('song'), $r->request->get('customSong'), $r->request->get('completedLevels'));
                break;
            case 10:
                $query = $em->getRepository(Level::class)->mapPackLevels($r->request->get('str'), $r->request->get('page'));
                break;
            case 11:
                $query = $em->getRepository(Level::class)->awardedLevels($r->request->get('diff'), $r->request->get('len'), $r->request->get('page'), $r->request->get('uncompleted'), $r->request->get('onlyCompleted'), $r->request->get('featured'), $r->request->get('original'), $r->request->get('twoPlayer'), $r->request->get('coins'), $r->request->get('epic'), $r->request->get('demonFilter'), $r->request->get('star'), $r->request->get('noStar'), $r->request->get('song'), $r->request->get('customSong'), $r->request->get('completedLevels'));
                break;
            case 12:
                $query = $em->getRepository(Level::class)->levelsByFollowed($r->request->get('diff'), $r->request->get('len'), $r->request->get('page'), $r->request->get('uncompleted'), $r->request->get('onlyCompleted'), $r->request->get('featured'), $r->request->get('original'), $r->request->get('twoPlayer'), $r->request->get('coins'), $r->request->get('epic'), $r->request->get('demonFilter'), $r->request->get('star'), $r->request->get('noStar'), $r->request->get('song'), $r->request->get('customSong'), $r->request->get('completedLevels'), $r->request->get('followed'));
                break;
            case 13:
                if (!$player->getAccount())
                    return new Response('-1');

                $friends = $em->getRepository(Friend::class)->friendsFor($player->getAccount()->getId());
                $friendsArray = [];
                foreach ($friends as $friend) {
                    $other = $friend->getA()->getId() === $player->getAccount()->getId() ? $friend->getB() : $friend->getA();
                    $friendsArray[] = $other->getId();
                }

                $query = $em->getRepository(Level::class)->levelsByFollowed($r->request->get('diff'), $r->request->get('len'), $r->request->get('page'), $r->request->get('uncompleted'), $r->request->get('onlyCompleted'), $r->request->get('featured'), $r->request->get('original'), $r->request->get('twoPlayer'), $r->request->get('coins'), $r->request->get('epic'), $r->request->get('demonFilter'), $r->request->get('star'), $r->request->get('noStar'), $r->request->get('song'), $r->request->get('customSong'), $r->request->get('completedLevels'), implode(',', $friendsArray));
                break;
            case 16:
                $query = $em->getRepository(Level::class)->hallOfFame($r->request->get('page'));
                break;
            default:
                return new Response('-1');
        }

        $levels = $query['result'];
        $total = $query['total'];

        if (!count($levels))
            return new Response('-1');

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
            'count' => self::LEVELS_PER_PAGE,
            'hash' => $hg->generateForLevelsArray($levels),
        ]);
    }

    /**
     * @Route("/getGJSongInfo.php", name="get_song_info")
     */
    public function getSongInfo(Request $r, SongProvider $sp): Response
    {
        $song = $sp->fetchSong($r->request->get('songID'));

        if ($song == '-1' || $song == '-2')
            return new Response($song);

        return $this->render('levels/song_info.html.twig', [
            'song' => $sp->fetchSong($r->request->get('songID')),
        ]);
    }

    /**
     * @Route("/downloadGJLevel22.php", name="download_level")
     */
    public function downloadLevel(Request $r, PlayerManager $pm, SongProvider $sp, HashGenerator $hg, Base64URL $b64, XORCipher $xor, TimeFormatter $tf): Response
    {
        $em = $this->getDoctrine()->getManager();
        $player = $pm->getFromRequest($r);

        $level = $em->getRepository(Level::class)->find($r->request->get('levelID'));

        if (!$level)
            return new Response('-1');

        if ($player && $r->request->get('inc')) {
            $player->addDownloadedLevel($level);
            $em->persist($player);
            $em->persist($level);
            $em->flush();
        }

        return $this->render('levels/download_level.html.twig', [
            'level' => $level,
            'uploadedAt' => $tf->format($level->getUploadedAt()),
            'lastUpdatedAt' => $tf->format($level->getLastUpdatedAt()),
            'pass' => $level->getPassword() ? $b64->encode($xor->cipher($level->getPassword(), XORCipher::KEY_LEVEL_PASS)) : '0',
            'hash' => $hg->generateForLevel($level),
        ]);
    }

    /**
     * @Route("/likeGJItem211.php", name="like_item")
     */
    public function likeItem(Request $r, PlayerManager $pm): Response
    {
        $em = $this->getDoctrine()->getManager();
        $player = $pm->getFromRequest($r);

        if (!$player)
            return new Response('-1');

        switch ($r->request->get('type')) {
            case 1:
                $level = $em->getRepository(Level::class)->find($r->request->get('itemID'));
                if (!$level)
                    return new Response('-1');

                if ($r->request->get('like')) {
                    $player->addLikedLevel($level);
                    $player->removeDislikedLevel($level);
                } else {
                    $player->addDislikedLevel($level);
                    $player->removeLikedLevel($level);
                }
                break;
            case 2:
                $comment = $em->getRepository(LevelComment::class)->find($r->request->get('itemID'));
                if (!$comment)
                    return new Response('-1');

                if ($r->request->get('like')) {
                    $player->addLikedLevelComment($comment);
                    $player->removeDislikedLevelComment($comment);
                } else {
                    $player->addDislikedLevelComment($comment);
                    $player->removeLikedLevelComment($comment);
                }
                break;
            case 3:
                $comment = $em->getRepository(AccountComment::class)->find($r->request->get('itemID'));
                if (!$comment)
                    return new Response('-1');

                if ($r->request->get('like')) {
                    $player->addLikedAccountComment($comment);
                    $player->removeDislikedAccountComment($comment);
                } else {
                    $player->addDislikedAccountComment($comment);
                    $player->removeLikedAccountComment($comment);
                }
                break;
            default:
                return new Response('-1');
        }

        $em->persist($player);
        $em->flush();

        return new Response('1');
    }

    /**
     * @Route("/deleteGJLevelUser20.php", name="delete_level")
     */
    public function deleteLevel(Request $r, PlayerManager $pm): Response
    {
        $em = $this->getDoctrine()->getManager();
        $player = $pm->getFromRequest($r);

        if (!$player)
            return new Response('-1');

        $level = $em->getRepository(Level::class)->find($r->request->get('levelID'));

        if (!$level || $level->getStars() > 0 || $level->getHasCoinsVerified())
            return new Response('-1');

        $player->removeLevel($level);
        $em->flush();

        return new Response('1');
    }

    /**
     * @Route("/rateGJStars211.php", name="vote_level_stars")
     */
    public function voteLevelStars(Request $r, PlayerManager $pm, DifficultyCalculator $dc): Response
    {
        $em = $this->getDoctrine()->getManager();
        $player = $pm->getFromRequest($r);

        if (!$player)
            return new Response('-1');

        $level = $em->getRepository(Level::class)->find($r->request->get('levelID'));
        if (!$level || $level->getStars() > 0)
            return new Response('-1');

        $vote = $em->getRepository(LevelStarVote::class)->findPlayerVoteForLevel($player->getId(), $level->getId());

        if (!$vote)
            $vote = new LevelStarVote();

        $vote->setPlayer($player);
        $vote->setLevel($level);
        $vote->setStarValue($r->request->get('stars'));

        $em->persist($vote);
        $em->persist($player);
        $em->flush();

        $dc->updateDifficulty($level);

        return new Response('1');
    }

    /**
     * @Route("/rateGJDemon21.php", name="vote_level_demon")
     */
    public function voteLevelDemon(Request $r, PlayerManager $pm, DifficultyCalculator $dc): Response
    {
        $em = $this->getDoctrine()->getManager();
        $player = $pm->getFromRequest($r);

        if (!$player)
            return new Response('-1');

        $level = $em->getRepository(Level::class)->find($r->request->get('levelID'));
        if (!$level || !$level->getIsDemon())
            return new Response('-1');

        $vote = $em->getRepository(LevelDemonVote::class)->findPlayerVoteForLevel($player->getId(), $level->getId());

        if (!$vote)
            $vote = new LevelDemonVote();

        $vote->setPlayer($player);
        $vote->setLevel($level);
        $vote->setDemonValue($r->request->get('rating'));

        $em->persist($vote);
        $em->persist($player);
        $em->flush();

        $dc->updateDemonDifficulty($level);

        return new Response('1');
    }

    /**
     * @Route("/updateGJDesc20.php", name="update_level_desc")
     */
    public function updateLevelDesc(Request $r, PlayerManager $pm): Response
    {
        $em = $this->getDoctrine()->getManager();
        $player = $pm->getFromRequest($r);

        if (!$player)
            return new Response('-1');

        $level = $em->getRepository(Level::class)->find($r->request->get('levelID'));

        if (!$level || $level->getCreator()->getId() !== $player->getId())
            return new Response('-1');

        $level->setDescription($r->request->get('levelDesc'));
        $em->flush();

        return new Response('1');
    }

    /**
     * @Route("/getGJLevelScores211.php", name="get_level_scores")
     */
    public function getLevelScores(Request $r, PlayerManager $pm, TimeFormatter $tf): Response
    {
        $em = $this->getDoctrine()->getManager();
        $player = $pm->getFromRequest($r);

        if (!$player || !$player->getAccount())
            return new Response('-1');

        $level = $em->getRepository(Level::class)->find($r->request->get('levelID'));

        if (!$level)
            return new Response('-1');

        $myScore = $em->getRepository(LevelScore::class)->findExistingScore($player->getAccount()->getId(), $level->getId());
        $percent = $r->request->get('percent');
        $coins = $r->request->get('s9') - 5819;

        if (!$myScore && $percent > 0) {
            $myScore = new LevelScore();
            $myScore->setPercent($percent);
            $myScore->setCoins($coins);
            $myScore->setAccount($player->getAccount());
            $myScore->setLevel($level);
            $myScore->setUpdatedAt(new \DateTime());
            $em->persist($myScore);
        } elseif ($myScore && ($myScore->getPercent() != $percent || $myScore->getCoins() != $coins)) {
            $myScore->setPercent($percent);
            $myScore->setCoins($coins);
            $myScore->setUpdatedAt(new \DateTime());
        }

        $em->flush();

        switch ($r->request->get('type')) {
            case 0:
                $scores = $em->getRepository(LevelScore::class)->friendsLeaderboard($player->getAccount()->getId(), $level->getId());
                break;
            case 1:
                $scores = $em->getRepository(LevelScore::class)->topLeaderboard($level->getId());
                break;
            case 2:
                $scores = $em->getRepository(LevelScore::class)->weekLeaderboard($level->getId());
                break;
            default:
                return new Response('-1');
        }

        if (!count($scores))
            return new Response('-1');

        return $this->render('levels/get_scores.html.twig', [
            'scores' => $scores,
            'timeFormatter' => $tf,
        ]);
    }
}
