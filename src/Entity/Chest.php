<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ChestRepository")
 */
class Chest
{
	const SMALL = 1;
	const BIG = 2;
	
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $cooldown;

    /**
     * @ORM\Column(type="integer")
     */
    private $minOrbs;

    /**
     * @ORM\Column(type="integer")
     */
    private $maxOrbs;

    /**
     * @ORM\Column(type="integer")
     */
    private $minDiamonds;

    /**
     * @ORM\Column(type="integer")
     */
    private $maxDiamonds;

    /**
     * @ORM\Column(type="integer")
     */
    private $minShards;

    /**
     * @ORM\Column(type="integer")
     */
    private $maxShards;

    /**
     * @ORM\Column(type="integer")
     */
    private $orbStep;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCooldown(): ?int
    {
        return $this->cooldown;
    }

    public function setCooldown(int $cooldown): self
    {
        $this->cooldown = $cooldown;

        return $this;
    }

    public function getMinOrbs(): ?int
    {
        return $this->minOrbs;
    }

    public function setMinOrbs(int $minOrbs): self
    {
        $this->minOrbs = $minOrbs;

        return $this;
    }

    public function getMaxOrbs(): ?int
    {
        return $this->maxOrbs;
    }

    public function setMaxOrbs(int $maxOrbs): self
    {
        $this->maxOrbs = $maxOrbs;

        return $this;
    }

    public function getMinDiamonds(): ?int
    {
        return $this->minDiamonds;
    }

    public function setMinDiamonds(int $minDiamonds): self
    {
        $this->minDiamonds = $minDiamonds;

        return $this;
    }

    public function getMaxDiamonds(): ?int
    {
        return $this->maxDiamonds;
    }

    public function setMaxDiamonds(int $maxDiamonds): self
    {
        $this->maxDiamonds = $maxDiamonds;

        return $this;
    }

    public function getMinShards(): ?int
    {
        return $this->minShards;
    }

    public function setMinShards(int $minShards): self
    {
        $this->minShards = $minShards;

        return $this;
    }

    public function getMaxShards(): ?int
    {
        return $this->maxShards;
    }

    public function setMaxShards(int $maxShards): self
    {
        $this->maxShards = $maxShards;

        return $this;
    }

    public function getOrbStep(): ?int
    {
        return $this->orbStep;
    }

    public function setOrbStep(int $orbStep): self
    {
        $this->orbStep = $orbStep;

        return $this;
    }
}
