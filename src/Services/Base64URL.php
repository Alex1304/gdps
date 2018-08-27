<?php

namespace App\Services;

/**
 * Simple service for Base64URL encoding/decoding
 */
class Base64URL
{
	public function decode($data): string
	{
		return base64_decode(strtr($data, '-_', '+/'));
	}

	public function encode($data)
	{
		return strtr(base64_encode($data), '+/', '-_');
	}
}