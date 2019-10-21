<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Request;

/**
 * Service to use the XOR cipher algorithm
 */
class XORCipher
{
	const KEY_GJP = '37526';
	const KEY_PRIVATE_MESSAGE_BODY = '14251';
	const KEY_LEVEL_PASS = '26364';
	const KEY_QUESTS = '19847';
	const KEY_CHESTS = '59182';

	/**
	 * Ciphers a message with the given key using the XOR algorithm.
	 * Since the algorithm is symmetric, this method may be used for both encryption and decryption.
	 */
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