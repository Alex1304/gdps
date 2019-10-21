<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Security;
use FOS\RestBundle\Controller\Annotations as Rest;

use App\Services\HashGenerator;
use App\Services\XORCipher;
use App\Services\Base64URL;
use App\Entity\Quest;

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
		return $this->render('rewards/get_quests.html.twig', [
			'quests_string' => 'SaKuJ' . $questsString,
			'hash' => $hg->generateForQuests($questsString)
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
