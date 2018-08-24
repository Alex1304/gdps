<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Account;

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

	public function checkFromRequest(Request $r)
	{
		$accountID = $r->request->get('accountID');
		$gjp = $r->request->get('gjp');

		if (empty($accountID) || !is_numeric($accountID) || empty($gjp))
			return self::ACCOUNT_BAD_REQUEST;

		$account = $this->em->getRepository(Account::class)->find($accountID);

		if (!$account)
			return self::ACCOUNT_NOT_FOUND;

		$decodedGJP = $this->xor->cipher($this->b64->decode($gjp), XORCipher::KEY_GJP);

		return $account->getPassword() === password_hash($decodedGJP, PASSWORD_BCRYPT) ? $account : self::ACCOUNT_UNAUTHORIZED;
	}
}