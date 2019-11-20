<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Account;
use App\Entity\CommentBan;
use App\Services\Base64URL;

/**
 * Service that manages player entities
 */
class ElderCommandHandler
{
	private $em;
	private $b64;

	public function __construct(EntityManagerInterface $em, Base64URL $b64)
	{
		$this->em = $em;
		$this->b64 = $b64;
	}

	public function handle($comment, $moderator)
	{
		if (!in_array('ROLE_ELDERMOD', $moderator->getRoles())) {
			return false;
		}
		$em = $this->em;
		$b64 = $this->b64;
		$tokens = explode(' ', $b64->decode($comment), 4);
		switch ($tokens[0]) {
			case '/ban':
				if (count($tokens) >= 3 && is_numeric($tokens[2])) {
					$target = $em->getRepository(Account::class)->findOneByUsername($tokens[1]);
					if (!$target || in_array('ROLE_ELDERMOD', $target->getPlayer()->getRoles())) {
						return -1;
					}
					$expiresAt = new \DateTime();
					$expiresAt->setTimestamp(time() + $tokens[2]);
					$ban = $em->getRepository(CommentBan::class)->findCurrentBan($target->getPlayer()->getId()) ?? new CommentBan();
					$ban->setTarget($target->getPlayer());
					$ban->setModerator($moderator);
					$ban->setExpiresAt($expiresAt);
					$ban->setReason(isset($tokens[3]) ? $tokens[3] : null);
					$em->persist($ban);
					$em->flush();
					return 1;
				}
				return 1;
			case '/unban':
				if (count($tokens) >= 2) {
					$target = $em->getRepository(Account::class)->findOneByUsername($tokens[1]);
					if (!$target || in_array('ROLE_ELDERMOD', $target->getPlayer()->getRoles())) {
						return -1;
					}
					$ban = $em->getRepository(CommentBan::class)->findCurrentBan($target->getPlayer()->getId());
					if ($ban) {
						$em->remove($ban);
						$em->flush();
					}
					return 1;
				}
				return 1;
			default:
				return false;
		}
	}
}