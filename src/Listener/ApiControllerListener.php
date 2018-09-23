<?php

namespace App\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Validator\ConstraintViolationList;

use App\Exceptions\InvalidParametersException;

class ApiControllerListener implements EventSubscriberInterface
{
	public static function getSubscribedEvents()
	{
		return [
			'kernel.controller' => ['onKernelController', -100],
		];
	}

	public function onKernelController(FilterControllerEvent $event)
	{
		if (($violations = $event->getRequest()->attributes->get('violations')) instanceof ConstraintViolationList) {
	        if (count($violations))
	            throw new InvalidParametersException($violations[0]->getMessage());
		}
	}
}