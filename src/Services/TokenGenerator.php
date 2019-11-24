<?php

namespace App\Services;

use Symfony\Component\Security\Core\User\UserInterface;

use App\Services\Base64URL;

class TokenGenerator
{
	private $b64;

	public function __construct(Base64URL $b64)
	{
		$this->b64 = $b64;
	}

	public function generate($user)
	{
		$length = 32;
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }

		return str_replace('=', '', $this->b64->encode(time()) . '.' . $this->b64->encode($user->getId())) . '.' . $randomString;
	}
}