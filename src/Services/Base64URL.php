<?php

namespace App\Services;

class Base64URL
{
	public function decode($data): string
	{
		return base64_decode(strtr($data, '-_', '+/'));
	}

	public function encode($data)
	{
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}
}