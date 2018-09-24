<?php

namespace App\Services;

use Symfony\Component\Validator\Constraints as Assert;

class UserPassword
{
	/**
	 * @Assert\NotBlank
	 * @Assert\Length(min=6, max=72, minMessage="Password too short (min. 6 characters)", maxMessage="Password too long (max. 72 characters)")
	 */
	private $password;

	public function getPassword()
	{
		return $this->password;
	}

	public function getHashedPassword()
	{
		return password_hash($this->password, PASSWORD_BCRYPT);
	}

	public function setPassword($password)
	{
		$this->password = $password;
	}
}