<?php

namespace App\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use App\Exceptions\InvalidParametersException;
use App\Services\UserPassword;
use App\Services\StrictValidator;

class ControllerListener implements EventSubscriberInterface
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
			'kernel.view' => [
				['intResponse', -100],
			],
			'kernel.exception' => [
				['accessDeniedToMinus1', -100],
			],
		];
	}

	private static function isApiRoute(Request $r)
	{
		return substr($r->attributes->get('_route'), 0, 4) == 'api_';
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

		if (!static::isApiRoute($r) || !$r->attributes->get('password'))
			return;

		$up = new UserPassword();
		$up->setPassword($r->attributes->get('password'));
		$this->v->validate($up);
		$r->attributes->set('password', $up->getHashedPassword());
	}

	public function intResponse(GetResponseForControllerResultEvent $event)
	{
		if (static::isApiRoute($event->getRequest())) // Shouldn't affect API routes
			return;

		$val = $event->getControllerResult();

		if (is_numeric($val)) {
			$event->setResponse(new Response($val));
		}
	}

	public function accessDeniedToMinus1(GetResponseForExceptionEvent $event)
	{
		if ($event->getException() instanceof AccessDeniedHttpException) {
			$event->setResponse(new Response('-1'));
		}
	}
}