<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Request;

class XORCipher
{
	const KEY_GJP = '37526';
	const KEY_PRIVATE_MESSAGE_BODY = '14251';
	const KEY_LEVEL_PASS = '26364';

	public function cipher($message, $key): string
	{
		$messageBytes = array_map('ord', str_split($message));
		$keyBytes = array_map('ord', str_split($key));

		$result = "";

		for ($i = 0 ; $i < count($messageBytes) ; $i++)
			$result .= chr($messageBytes[$i] ^ $keyBytes[$i % count($keyBytes)]);

		return $result;
	}
}