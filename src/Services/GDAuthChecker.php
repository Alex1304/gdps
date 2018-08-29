<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Account;
use App\Entity\Player;

/**
 * Service that authenticates a user from his accountID and encoded password (gjp)
 */
class GDAuthChecker
{
	const ACCOUNT_BAD_REQUEST = 1;
	const ACCOUNT_NOT_FOUND = 2;
	const ACCOUNT_UNAUTHORIZED = 3;

	private $xor;
	private $em;
	private $b64;

	public function __construct(EntityManagerInterface $em, XORCipher $xor, Base64URL $b64)
	{
		$this->xor = $xor;
		$this->em = $em;
		$this->b64 = $b64;
	}
	
	public function check($accountID, $gjp)
	{
		if (empty($accountID) || !is_numeric($accountID) || empty($gjp))
			return self::ACCOUNT_BAD_REQUEST;

		$account = $this->em->getRepository(Account::class)->find($accountID);

		if (!$account)
			return self::ACCOUNT_NOT_FOUND;

		$decodedGJP = $this->xor->cipher($this->b64->decode($gjp), XORCipher::KEY_GJP);

		return $this->checkPlain($account, $decodedGJP) ? $account : self::ACCOUNT_UNAUTHORIZED;
	}

	public function checkFromRequest(Request $r)
	{
		return $this->check($r->request->get('accountID'), $r->request->get('gjp'));
	}

	public function checkPlain($account, $plainPassword)
	{	
		return password_verify($plainPassword, $account->getPassword());
	}
}