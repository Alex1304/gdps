<?php

namespace App\Services;

use Symfony\Component\Validator\Validator\ValidatorInterface;

use App\Exceptions\InvalidParametersException;

/**
 * Validator that encapsulates the validator service of Symfony and that throws
 * an exception if the validation fails instead of returning the list of errors.
 */
class StrictValidator
{
	private $validator;

	public function __construct(ValidatorInterface $validator)
	{
		$this->validator = $validator;
	}

	public function validate($object)
	{
		$errors = $this->validator->validate($object);

		if (count($errors))
			throw new InvalidParametersException($errors[0]->getMessage());

		return true;
	}
}