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
use App\Entity\OpenedChest;

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
		$smallChest = $em->getRepository(Chest::class)->find(Chest::SMALL);
		$bigChest = $em->getRepository(Chest::class)->find(Chest::BIG);
		if (!$smallChest || !$bigChest) {
			return -1;
		}
		
		$lastSmallChest = $em->getRepository(OpenedChest::class)->findMostRecentChest($player, Chest::SMALL);
		$lastBigChest = $em->getRepository(OpenedChest::class)->findMostRecentChest($player, Chest::BIG);
		$totalOrbs = $em->getRepository(OpenedChest::class)->totalOrbs($player);
		
		$nextSmallChestIn = $lastSmallChest ? max(0, $smallChest->getCooldown() + $lastSmallChest->getOpenedAt()->getTimestamp() - $now) : 0;
		$nextBigChestIn = $lastBigChest ? max(0, $bigChest->getCooldown() + $lastBigChest->getOpenedAt()->getTimestamp() - $now) : 0;
		
		if ($rewardType == 1 && $nextSmallChestIn === 0) {
			$orbs = rand($smallChest->getMinOrbs() / $smallChest->getOrbStep(), $smallChest->getMaxOrbs() / $smallChest->getOrbStep()) * $smallChest->getOrbStep();
			$demonKeys = floor(($totalOrbs + $orbs) / 500) - floor($totalOrbs / 500);
			$diamonds = rand($smallChest->getMinDiamonds(), $smallChest->getMaxDiamonds());
			$shards = rand($smallChest->getMinShards(), $smallChest->getMaxShards());
			$newSmallChest = new OpenedChest($player, Chest::SMALL);
			$newSmallChest->setOrbs($orbs)
				->setDemonKeys($demonKeys)
				->setDiamonds($diamonds)
				->setShards($shards)
				->setOpenedAt(new \DateTime());
			$em->persist($newSmallChest);
			$em->flush();
			$nextSmallChestIn = $smallChest->getCooldown();
			$lastSmallChest = $newSmallChest;
		} elseif ($rewardType == 2 && $nextBigChestIn === 0) {
			$orbs = rand($bigChest->getMinOrbs() / $bigChest->getOrbStep(), $bigChest->getMaxOrbs() / $bigChest->getOrbStep()) * $bigChest->getOrbStep();
			$demonKeys = floor(($totalOrbs + $orbs) / 500) - floor($totalOrbs / 500);
			$diamonds = rand($bigChest->getMinDiamonds(), $bigChest->getMaxDiamonds());
			$shards = rand($bigChest->getMinShards(), $bigChest->getMaxShards());
			$newBigChest = new OpenedChest($player, Chest::BIG);
			$newBigChest->setOrbs($orbs)
				->setDemonKeys($demonKeys)
				->setDiamonds($diamonds)
				->setShards($shards)
				->setOpenedAt(new \DateTime());
			$em->persist($newBigChest);
			$em->flush();
			$nextBigChestIn = $smallChest->getCooldown();
			$lastBigChest = $newBigChest;
		}
		
		$smallChestContents = !$lastSmallChest ? '-' : join(',', [
			$lastSmallChest->getOrbs(),
			$lastSmallChest->getDiamonds(),
			$lastSmallChest->getShards(),
			$lastSmallChest->getDemonKeys(),
		]);
		$bigChestContents = !$lastBigChest ? '-' : join(',', [
			$lastBigChest->getOrbs(),
			$lastBigChest->getDiamonds(),
			$lastBigChest->getShards(),
			$lastBigChest->getDemonKeys(),
		]);
		$chestsString = $b64->encode($xor->cipher(join(':', [
			'IH9Fn',
			$player->getId(),
			$decipheredChk,
			$udid,
			$player->getAccount() ? $player->getAccount()->getId() : 0,
			$nextSmallChestIn,
			$smallChestContents,
			$lastSmallChest ? $lastSmallChest->getId() : 0,
			$nextBigChestIn,
			$bigChestContents,
			$lastBigChest ? $lastBigChest->getId() : 0,
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
