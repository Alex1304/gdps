<?php

namespace App\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Validator\ConstraintViolationList;

use App\Exceptions\InvalidParametersException;
use App\Services\UserPassword;
use App\Services\StrictValidator;

class ApiControllerListener implements EventSubscriberInterface
{
	private $v;

	public function __construct(StrictValidator $v)
	{
		$this->v = $v;
	}

	public static function getSubscribedEvents()
	{
		return [
			'kernel.controller' => [
				['throwExceptionIfViolation', -100],
				['validatePasswordParam', -101],
			],
		];
	}

	public function throwExceptionIfViolation(FilterControllerEvent $event)
	{
		if (($violations = $event->getRequest()->attributes->get('violations')) instanceof ConstraintViolationList) {
	        if (count($violations))
	            throw new InvalidParametersException($violations[0]->getMessage());
		}
	}

	public function validatePasswordParam(FilterControllerEvent $event)
	{
		$r = $event->getRequest();

		if (!$r->attributes->get('password'))
			return;

		$up = new UserPassword();
		$up->setPassword($r->attributes->get('password'));
		$this->v->validate($up);
		$r->attributes->set('password', $up->getHashedPassword());
	}
}