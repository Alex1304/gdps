<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Security;
use FOS\RestBundle\Controller\Annotations as Rest;

use App\Services\SongProvider;
use App\Services\HashGenerator;
use App\Services\Base64URL;
use App\Services\XORCipher;
use App\Services\TimeFormatter;
use App\Services\DifficultyCalculator;
use App\Entity\Level;
use App\Entity\LevelData;
use App\Entity\LevelComment;
use App\Entity\AccountComment;
use App\Entity\LevelStarVote;
use App\Entity\LevelDemonVote;
use App\Entity\Friend;
use App\Entity\LevelScore;
use App\Entity\PeriodicLevel;

class LevelsController extends AbstractController
{
    const LEVELS_PER_PAGE = 10;

    /**
     * @Rest\Post("/uploadGJLevel21.php", name="upload_level")
     *
     * @Rest\RequestParam(name="original")
     * @Rest\RequestParam(name="levelID")
     * @Rest\RequestParam(name="levelName")
     * @Rest\RequestParam(name="unlisted", nullable=true, default=0)
     * @Rest\RequestParam(name="levelDesc")
     * @Rest\RequestParam(name="levelString")
     * @Rest\RequestParam(name="audioTrack")
     * @Rest\RequestParam(name="songID")
     * @Rest\RequestParam(name="gameVersion")
     * @Rest\RequestParam(name="requestedStars")
     * @Rest\RequestParam(name="levelLength")
     * @Rest\RequestParam(name="ldm", nullable=true, default=0)
     * @Rest\RequestParam(name="password")
     * @Rest\RequestParam(name="objects")
     * @Rest\RequestParam(name="extraString")
     * @Rest\RequestParam(name="twoPlayer")
     * @Rest\RequestParam(name="coins")
     */
    public function uploadLevel(Security $s, Base64URL $b64, $original, $levelID, $levelName, $unlisted, $levelDesc, $levelString, $audioTrack, $songID, $gameVersion, $requestedStars, $levelLength, $ldm, $password, $objects, $extraString, $twoPlayer, $coins)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$s->getUser())
            return -1;

        $level = null;
        $original = $original > 0 ? $em->getRepository(Level::class)->find($original) : null;

        if ($levelID == 0) {
            $levelWithSameName = $em->getRepository(Level::class)->levelWithSameNameByCreator($s->getUser()->getId(), $levelName);
            if (!$levelWithSameName) {
                $level = new Level();
                $level->setCreator($s->getUser());
                $level->setVersion(0); // Will be incremented anyway at line 95
                $level->setStars(0);
                $level->setFeatureScore(0);
                $level->setIsEpic(0);
                $level->setName($levelName);
                $level->setUploadedAt(new \DateTime());
                $level->setIsUnlisted($unlisted ?? 0);
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
        } else {
            $level = $em->getRepository(Level::class)->find($levelID);
		}

        if (!$level)
            return -1;

        $level->setDescription($levelDesc ?? '');
		$levelData = new LevelData();
        $levelData->setData($levelString);
		$level->setLevelData($levelData);
        $level->setAudioTrack($audioTrack);
        $level->setCustomSongID($songID);
        $level->setGameVersion($gameVersion);
        $level->setVersion($level->getVersion() + 1);
        $level->setRequestedStars($requestedStars ?? 0);
        $level->setLastUpdatedAt(new \DateTime());
        $level->setLength($levelLength);
        $level->setIsLDM($ldm ?? 0);
        $level->setPassword($password ?? 0);
        $level->setObjectCount($objects ?? 0);
        $level->setExtraString($extraString);
        $level->setIsTwoPlayer($twoPlayer ?? 0);
        $level->setCoins($coins);

        $em->persist($s->getUser());
        $em->persist($level);
        $em->flush();

        return $level->getId();
    }

    /**
     * @Rest\Post("/getGJLevels21.php", name="get_levels")
     *
     * @Rest\RequestParam(name="type", nullable=true, default=0)
     * @Rest\RequestParam(name="str", nullable=true, default="-")
     * @Rest\RequestParam(name="diff", nullable=true, default=null)
     * @Rest\RequestParam(name="len", nullable=true, default=null)
     * @Rest\RequestParam(name="page", nullable=true, default=0)
     * @Rest\RequestParam(name="uncompleted", nullable=true, default=0)
     * @Rest\RequestParam(name="onlyCompleted", nullable=true, default=0)
     * @Rest\RequestParam(name="featured", nullable=true, default=0)
     * @Rest\RequestParam(name="original", nullable=true, default=0)
     * @Rest\RequestParam(name="twoPlayer", nullable=true, default=0)
     * @Rest\RequestParam(name="coins", nullable=true, default=0)
     * @Rest\RequestParam(name="epic", nullable=true, default=0)
     * @Rest\RequestParam(name="demonFilter", nullable=true, default=null)
     * @Rest\RequestParam(name="star", nullable=true, default=0)
     * @Rest\RequestParam(name="noStar", nullable=true, default=null)
     * @Rest\RequestParam(name="song", nullable=true, default=null)
     * @Rest\RequestParam(name="customSong", nullable=true, default=null)
     * @Rest\RequestParam(name="completedLevels", nullable=true, default=null)
     * @Rest\RequestParam(name="followed", nullable=true, default=null)
     */
    public function getLevels(Security $s, SongProvider $sp, HashGenerator $hg, $type, $str, $diff, $len, $page, $uncompleted, $onlyCompleted, $featured, $original, $twoPlayer, $coins, $epic, $demonFilter, $star, $noStar, $song, $customSong, $completedLevels, $followed)
    {
        $em = $this->getDoctrine()->getManager();

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
        switch ($type) {
            case 0:
                $query = $em->getRepository(Level::class)->searchLevels($str, $diff, $len, $page, $uncompleted, $onlyCompleted, $featured, $original, $twoPlayer, $coins, $epic, $demonFilter, $star, $noStar, $song, $customSong, $completedLevels);
                break;
            case 1:
                $query = $em->getRepository(Level::class)->mostDownloadedLevels($diff, $len, $page, $uncompleted, $onlyCompleted, $featured, $original, $twoPlayer, $coins, $epic, $demonFilter, $star, $noStar, $song, $customSong, $completedLevels);
                break;
            case 2:
                $query = $em->getRepository(Level::class)->mostLikedLevels($diff, $len, $page, $uncompleted, $onlyCompleted, $featured, $original, $twoPlayer, $coins, $epic, $demonFilter, $star, $noStar, $song, $customSong, $completedLevels);
                break;
            case 3:
                $query = $em->getRepository(Level::class)->trendingLevels($diff, $len, $page, $uncompleted, $onlyCompleted, $featured, $original, $twoPlayer, $coins, $epic, $demonFilter, $star, $noStar, $song, $customSong, $completedLevels);
                break;
            case 4:
                $query = $em->getRepository(Level::class)->recentLevels($diff, $len, $page, $uncompleted, $onlyCompleted, $featured, $original, $twoPlayer, $coins, $epic, $demonFilter, $star, $noStar, $song, $customSong, $completedLevels);
                break;
            case 5:
                $query = $em->getRepository(Level::class)->levelsByUser($str, $s->getUser(), $page);
                break;
            case 6:
                $query = $em->getRepository(Level::class)->featuredLevels($page);
                break;
            case 7:
                $query = $em->getRepository(Level::class)->magicLevels($diff, $len, $page, $uncompleted, $onlyCompleted, $featured, $original, $twoPlayer, $coins, $epic, $demonFilter, $star, $noStar, $song, $customSong, $completedLevels);
                break;
            case 10:
                $query = $em->getRepository(Level::class)->mapPackLevels($str, $page);
                break;
            case 11:
                $query = $em->getRepository(Level::class)->awardedLevels($diff, $len, $page, $uncompleted, $onlyCompleted, $featured, $original, $twoPlayer, $coins, $epic, $demonFilter, $star, $noStar, $song, $customSong, $completedLevels);
                break;
            case 12:
                $query = $em->getRepository(Level::class)->levelsByFollowed($diff, $len, $page, $uncompleted, $onlyCompleted, $featured, $original, $twoPlayer, $coins, $epic, $demonFilter, $star, $noStar, $song, $customSong, $completedLevels, $followed);
                break;
            case 13:
                if (!$s->getUser()->getAccount())
                    return -1;

                $friends = $em->getRepository(Friend::class)->friendsFor($s->getUser()->getAccount()->getId());
                $friendsArray = [];
                foreach ($friends as $friend) {
                    $other = $friend->getA()->getId() === $s->getUser()->getAccount()->getId() ? $friend->getB() : $friend->getA();
                    $friendsArray[] = $other->getId();
                }

                $query = $em->getRepository(Level::class)->levelsByFollowed($diff, $len, $page, $uncompleted, $onlyCompleted, $featured, $original, $twoPlayer, $coins, $epic, $demonFilter, $star, $noStar, $song, $customSong, $completedLevels, join(',', $friendsArray));
                break;
            case 16:
                $query = $em->getRepository(Level::class)->hallOfFame($page);
                break;
            default:
                return -1;
        }

        $levels = $query['result'];
        $total = $query['total'];

        if (!count($levels))
            return -1;

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
            'page' => $page,
            'count' => self::LEVELS_PER_PAGE,
            'hash' => $hg->generateForLevelsArray($levels),
        ]);
    }

    /**
     * @Rest\Post("/getGJSongInfo.php", name="get_song_info")
     *
     * @Rest\RequestParam(name="songID")
     */
    public function getSongInfo(SongProvider $sp, $songID)
    {
        $song = $sp->fetchSong($songID);

        if ($song == '-1' || $song == '-2')
            return $song;

        return $this->render('levels/song_info.html.twig', [
            'song' => $song,
        ]);
    }

    /**
     * @Rest\Post("/downloadGJLevel22.php", name="download_level")
     *
     * @Rest\RequestParam(name="levelID")
     * @Rest\RequestParam(name="inc", nullable=true, default=0)
     */
    public function downloadLevel(Security $s, SongProvider $sp, HashGenerator $hg, Base64URL $b64, XORCipher $xor, TimeFormatter $tf, $levelID, $inc)
    {
        $em = $this->getDoctrine()->getManager();

        $periodic = null;
        switch ($levelID) {
            case -1:
                $periodic = $em->getRepository(PeriodicLevel::class)->findCurrentOfType(PeriodicLevel::DAILY);
                $level = $periodic ? $periodic->getLevel() : null;
                break;
            case -2:
                $periodic = $em->getRepository(PeriodicLevel::class)->findCurrentOfType(PeriodicLevel::WEEKLY);
                $level = $periodic ? $periodic->getLevel() : null;
                break;
            default:
                $level = $em->getRepository(Level::class)->find($levelID);
        }

        if (!$level)
            return -1;

        if ($inc) {
            $s->getUser()->addDownloadedLevel($level);
            $em->persist($s->getUser());
            $em->persist($level);
            $em->flush();
        }

        $creator = $periodic ? [
            'playerID' => $level->getCreator()->getId(),
            'name' => $level->getCreator()->getName(),
            'accountID' => $level->getCreator()->getAccount() != null ? $level->getCreator()->getAccount()->getId() : 0,
        ] : null;

        $periodicID = $periodic ? $periodic->getId() : 0;

        return $this->render('levels/download_level.html.twig', [
            'level' => $level,
            'uploadedAt' => $tf->format($level->getUploadedAt()),
            'lastUpdatedAt' => $tf->format($level->getLastUpdatedAt()),
            'pass' => $level->getPassword() ? $b64->encode($xor->cipher($level->getPassword(), XORCipher::KEY_LEVEL_PASS)) : '0',
            'hash' => $hg->generateForLevel($level, $periodicID),
            'periodicID' => $periodicID,
            'creator' => $creator,
        ]);
    }

    /**
     * @Rest\Post("/likeGJItem211.php", name="like_item")
     *
     * @Rest\RequestParam(name="type")
     * @Rest\RequestParam(name="itemID")
     * @Rest\RequestParam(name="like")
     */
    public function likeItem(Security $s, $type, $itemID, $like)
    {
        $em = $this->getDoctrine()->getManager();
        $player = $s->getUser();

        switch ($type) {
            case 1:
                $level = $em->getRepository(Level::class)->find($itemID);
                if (!$level)
                    return -1;

                if ($like) {
                    $player->addLikedLevel($level);
                    $player->removeDislikedLevel($level);
                } else {
                    $player->addDislikedLevel($level);
                    $player->removeLikedLevel($level);
                }
                break;
            case 2:
                $comment = $em->getRepository(LevelComment::class)->find($itemID);
                if (!$comment)
                    return -1;

                if ($like) {
                    $player->addLikedLevelComment($comment);
                    $player->removeDislikedLevelComment($comment);
                } else {
                    $player->addDislikedLevelComment($comment);
                    $player->removeLikedLevelComment($comment);
                }
                break;
            case 3:
                $comment = $em->getRepository(AccountComment::class)->find($itemID);
                if (!$comment)
                    return -1;

                if ($like) {
                    $player->addLikedAccountComment($comment);
                    $player->removeDislikedAccountComment($comment);
                } else {
                    $player->addDislikedAccountComment($comment);
                    $player->removeLikedAccountComment($comment);
                }
                break;
            default:
                return -1;
        }

        $em->persist($player);
        $em->flush();

        return 1;
    }

    /**
     * @Rest\Post("/deleteGJLevelUser20.php", name="delete_level")
     *
     * @Rest\RequestParam(name="levelID")
     */
    public function deleteLevel(Security $s, $levelID)
    {
        $em = $this->getDoctrine()->getManager();

        $level = $em->getRepository(Level::class)->find($levelID);

        if (!$level || $level->getStars() > 0 || $level->getHasCoinsVerified())
            return -1;

        $s->getUser()->removeLevel($level);
        $em->flush();

        return 1;
    }

    /**
     * @Rest\Post("/rateGJStars211.php", name="vote_level_stars")
     *
     * @Rest\RequestParam(name="levelID")
     * @Rest\RequestParam(name="stars")
     */
    public function voteLevelStars(Security $s, DifficultyCalculator $dc, $levelID, $stars)
    {
        $em = $this->getDoctrine()->getManager();
        $player = $s->getUser();

        $level = $em->getRepository(Level::class)->find($levelID);
        if (!$level || $level->getStars() > 0)
            return -1;

        $vote = $em->getRepository(LevelStarVote::class)->findPlayerVoteForLevel($player->getId(), $level->getId());

        if (!$vote)
            $vote = new LevelStarVote();

        $vote->setPlayer($player);
        $vote->setLevel($level);
        $vote->setStarValue($stars);

        $em->persist($vote);
        $em->persist($player);
        $em->flush();

        $dc->updateDifficulty($level);

        return 1;
    }

    /**
     * @Rest\Post("/rateGJDemon21.php", name="vote_level_demon")
     *
     * @Rest\RequestParam(name="levelID")
     * @Rest\RequestParam(name="rating")
     */
    public function voteLevelDemon(Security $s, DifficultyCalculator $dc, $levelID, $rating)
    {
        $em = $this->getDoctrine()->getManager();
        $player = $s->getUser();

        $level = $em->getRepository(Level::class)->find($levelID);
        if (!$level || !$level->getIsDemon())
            return -1;

        $vote = $em->getRepository(LevelDemonVote::class)->findPlayerVoteForLevel($player->getId(), $level->getId());

        if (!$vote)
            $vote = new LevelDemonVote();

        $vote->setPlayer($player);
        $vote->setLevel($level);
        $vote->setDemonValue($rating);

        $em->persist($vote);
        $em->persist($player);
        $em->flush();

        $dc->updateDemonDifficulty($level);

        return 1;
    }

    /**
     * @Rest\Post("/updateGJDesc20.php", name="update_level_desc")
     *
     * @Rest\RequestParam(name="levelID")
     * @Rest\RequestParam(name="levelDesc", nullable=true, default="")
     */
    public function updateLevelDesc(Security $s, $levelID, $levelDesc)
    {
        $em = $this->getDoctrine()->getManager();
        $player = $s->getUser();

        if (!$player)
            return -1;

        $level = $em->getRepository(Level::class)->find($levelID);

        if (!$level || $level->getCreator()->getId() !== $player->getId())
            return -1;

        $level->setDescription($levelDesc);
        $em->flush();

        return 1;
    }

    /**
     * @Rest\Post("/getGJLevelScores211.php", name="get_level_scores")
     *
     * @Rest\RequestParam(name="levelID")
     * @Rest\RequestParam(name="percent")
     * @Rest\RequestParam(name="type")
     * @Rest\RequestParam(name="s9")
     */
    public function getLevelScores(Security $s, TimeFormatter $tf, $levelID, $percent, $type, $s9)
    {
        $em = $this->getDoctrine()->getManager();
        $player = $s->getUser();

        if (!$player || !$player->getAccount())
            return -1;

        $level = $em->getRepository(Level::class)->find($levelID);

        if (!$level)
            return -1;

        $myScore = $em->getRepository(LevelScore::class)->findExistingScore($player->getAccount()->getId(), $level->getId());
        $coins = $s9 - 5819;

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

        switch ($type) {
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
                return -1;
        }

        if (!count($scores))
            return -1;

        return $this->render('levels/get_scores.html.twig', [
            'scores' => $scores,
            'timeFormatter' => $tf,
        ]);
    }

    /**
     * @Rest\Post("getGJDailyLevel.php", name="get_daily_level")

     * @Rest\RequestParam(name="weekly", requirements="0|1")
     */
    public function getDailyInfo($weekly)
    {
        $em = $this->getDoctrine()->getManager();

        $periodic = $em->getRepository(PeriodicLevel::class)->findCurrentOfType($weekly);
        if (!$periodic)
            return '|0';

        $secondsLeft = $periodic->getPeriodEnd()->getTimestamp() - (new \DateTime("now"))->getTimestamp();
        $secondsLeft += 5; // Adding a delay of 5 seconds so that the game has time to load the next Daily/Weekly when time runs out

        return $periodic->getId() . '|' . $secondsLeft;
    }
}
