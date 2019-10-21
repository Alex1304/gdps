<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Security;
use FOS\RestBundle\Controller\Annotations as Rest;

use App\Services\HashGenerator;
use App\Services\XORCipher;
use App\Services\Base64URL;
use App\Entity\Quest;
use App\Entity\Chest;

class RewardsController extends AbstractController
{
	
    /**
     * @Rest\Post("/getGJChallenges.php", name="get_quests")
     *
     * @Rest\RequestParam(name="chk")
     * @Rest\RequestParam(name="udid")
     */
    public function getQuests(Security $s, HashGenerator $hg, XORCipher $xor, Base64URL $b64, $chk, $udid)
    {
		$em = $this->getDoctrine()->getManager();
		$player = $s->getUser();
		$decipheredChk = $xor->cipher($b64->decode(substr($chk, 5)), XORCipher::KEY_QUESTS);
		
		$questInfo = static::questInfo($em, $player);
		$quests = $em->getRepository(Quest::class)->findAll();
		
		$tier1Quests = array_values(array_filter($quests, function ($q) {
			return $q->getTier() == 1;
		}));
		$tier2Quests = array_values(array_filter($quests, function ($q) {
			return $q->getTier() == 2;
		}));
		$tier3Quests = array_values(array_filter($quests, function ($q) {
			return $q->getTier() == 3;
		}));
		
		if (empty($tier1Quests) || empty($tier2Quests) || empty($tier3Quests)) {
			return -1;
		}
		
		$tier1Quest = $tier1Quests[$questInfo['current_quest_number'] % count($tier1Quests)];
		$tier2Quest = $tier2Quests[$questInfo['current_quest_number'] % count($tier2Quests)];
		$tier3Quest = $tier3Quests[$questInfo['current_quest_number'] % count($tier3Quests)];
		
		$questsString = $b64->encode($xor->cipher(join(':', [
			'SaKuJ',
			$player->getId(),
			$decipheredChk,
			$udid,
			$player->getAccount() ? $player->getAccount()->getId() : 0,
			$questInfo['time_left'],
			static::questToString($questInfo['current_quest_number'], $tier1Quest),
			static::questToString($questInfo['current_quest_number'], $tier2Quest),
			static::questToString($questInfo['current_quest_number'], $tier3Quest),
		]), XORCipher::KEY_QUESTS));
		return $this->render('rewards/get_rewards.html.twig', [
			'rewards_string' => 'SaKuJ' . $questsString,
			'hash' => $hg->generateForQuests($questsString)
		]);
    }
	
	/**
     * @Rest\Post("/getGJRewards.php", name="get_chests")
     *
     * @Rest\RequestParam(name="chk")
     * @Rest\RequestParam(name="udid")
     * @Rest\RequestParam(name="rewardType")
     */
    public function getChests(Security $s, HashGenerator $hg, XORCipher $xor, Base64URL $b64, $chk, $udid, $rewardType)
    {
		$em = $this->getDoctrine()->getManager();
		$player = $s->getUser();
		$decipheredChk = $xor->cipher($b64->decode(substr($chk, 5)), XORCipher::KEY_CHESTS);
		
		$now = time();
		$smallChest = $em->getRepository(Chest::class)->find(1);
		$bigChest = $em->getRepository(Chest::class)->find(2);
		if (!$smallChest || !$bigChest) {
			return -1;
		}
		if ($player->getLastSmallChestCount() === 0 || $player->getLastBigChestCount() === 0) {
			$nowDate = new \DateTime();
			$nowDate->setTimestamp($now);
			$player->setLastSmallChestCount(1);
			$player->setNextSmallChestAt($nowDate);
			$player->setLastBigChestCount(1);
			$player->setNextBigChestAt($nowDate);
			$em->persist($player);
			$em->flush();
		}
		$nextSmallChestIn = max(0, $player->getNextSmallChestAt()->getTimestamp() - $now);
		$nextBigChestIn = max(0, $player->getNextBigChestAt()->getTimestamp() - $now);
		$smallChestOrbs = $rewardType != 1 ? 0 : rand($smallChest->getMinOrbs() / $smallChest->getOrbStep(), $smallChest->getMaxOrbs() / $smallChest->getOrbStep()) * $smallChest->getOrbStep();
		$bigChestOrbs = $rewardType != 2 ? 0 : rand($bigChest->getMinOrbs() / $bigChest->getOrbStep(), $bigChest->getMaxOrbs() / $bigChest->getOrbStep()) * $bigChest->getOrbStep();
		$smallChestKeys = $rewardType != 1 ? 0 : floor(($player->getManaOrbsCollectedFromChests() + $smallChestOrbs) / 500) - floor($player->getManaOrbsCollectedFromChests() / 500);
		$bigChestKeys = $rewardType != 2 ? 0 : floor(($player->getManaOrbsCollectedFromChests() + $bigChestOrbs) / 500) - floor($player->getManaOrbsCollectedFromChests() / 500);
		$smallChestContents = $rewardType != 1 ? '-' : join(',', [
			$smallChestOrbs,
			rand($smallChest->getMinDiamonds(), $smallChest->getMaxDiamonds()),
			rand($smallChest->getMinShards(), $smallChest->getMaxShards()),
			$smallChestKeys,
		]);
		$bigChestContents = $rewardType != 2 ? '-' : join(',', [
			$bigChestOrbs,
			rand($bigChest->getMinDiamonds(), $bigChest->getMaxDiamonds()),
			rand($bigChest->getMinShards(), $bigChest->getMaxShards()),
			$bigChestKeys,
		]);
		if ($rewardType != 0) {
			if ($rewardType == 1) {
				if ($nextSmallChestIn > 0) {
					return -1;
				}
				$player->setLastSmallChestCount($player->getLastSmallChestCount() + 1);
				$next = new \DateTime();
				$nextSmallChestIn = $smallChest->getCooldown();
				$next->setTimestamp($now + $nextSmallChestIn);
				$player->setNextSmallChestAt($next);
				$player->setManaOrbsCollectedFromChests($player->getManaOrbsCollectedFromChests() + $smallChestOrbs);
			} elseif ($rewardType == 2) {
				if ($nextBigChestIn > 0) {
					return -1;
				}
				$player->setLastBigChestCount($player->getLastBigChestCount() + 1);
				$next = new \DateTime();
				$nextBigChestIn = $bigChest->getCooldown();
				$next->setTimestamp($now + $nextBigChestIn);
				$player->setNextBigChestAt($next);
				$player->setManaOrbsCollectedFromChests($player->getManaOrbsCollectedFromChests() + $bigChestOrbs);
			}
			$em->persist($player);
			$em->flush();
		}
		$chestsString = $b64->encode($xor->cipher(join(':', [
			'IH9Fn',
			$player->getId(),
			$decipheredChk,
			$udid,
			$player->getAccount() ? $player->getAccount()->getId() : 0,
			$nextSmallChestIn,
			$smallChestContents,
			$player->getLastSmallChestCount(),
			$nextBigChestIn,
			$bigChestContents,
			$player->getLastBigChestCount(),
			$rewardType,
		]), XORCipher::KEY_CHESTS));
		return $this->render('rewards/get_rewards.html.twig', [
			'rewards_string' => 'GiUXu' . $chestsString,
			'hash' => $hg->generateForChests($chestsString)
		]);
	}
	
	private static function questInfo($em, $player)
	{
		$today = strtotime('today');
		$tomorrow = strtotime('tomorrow');
		$now = time();
		$timeLeft = ($tomorrow - $now) % 28800;
		if ($player->getNextQuestsAt()->getTimestamp() - $now <= 0) {
			$player->setLastQuestId($player->getLastQuestId() + 1);
			$nextQuests = new \DateTime();
			$nextQuests->setTimestamp($now + $timeLeft);
			$player->setNextQuestsAt($nextQuests);
			$em->persist($player);
			$em->flush();
		}
		return [
			'current_quest_number' => $player->getLastQuestId(),
			'time_left' => $timeLeft,
		];
	}
	
	private static function questToString($currentQuestNumber, $quest)
	{
		return join(',', [$currentQuestNumber, $quest->getCurrency(), $quest->getAmount(), $quest->getDiamondReward(), $quest->getName()]);
	}
}
