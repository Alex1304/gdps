<?php

namespace App\Services;

use Doctrine\ORM\Id\AbstractIdGenerator;
use Doctrine\ORM\EntityManager;

use App\Entity\PeriodicLevel;

class PeriodicIdGenerator extends AbstractIdGenerator
{
	public function generate(EntityManager $em, $entity)
	{
		return $em->getRepository(PeriodicLevel::class)->nextId($entity->getType());
	}
}