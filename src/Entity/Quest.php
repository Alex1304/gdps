<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\QuestRepository")
 */
class Quest
{
	const ORB_TYPE = 1;
	const COIN_TYPE = 2;
	const STAR_TYPE = 3;
	
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $currency;

    /**
     * @ORM\Column(type="integer")
     */
    private $amount;

    /**
     * @ORM\Column(type="integer")
     */
    private $diamondReward;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $tier;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCurrency(): ?int
    {
        return $this->currency;
    }

    public function setCurrency(int $currency): self
    {
		if (!in_array($currency, [self::ORB_TYPE, self::STAR_TYPE, self::COIN_TYPE])) {
			throw new \Exception("Invalid currency: " . $currency);
		}
        $this->currency = $currency;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDiamondReward(): ?int
    {
        return $this->diamondReward;
    }

    public function setDiamondReward(int $diamondReward): self
    {
        $this->diamondReward = $diamondReward;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getTier(): ?int
    {
        return $this->tier;
    }

    public function setTier(int $tier): self
    {
        $this->tier = $tier;

        return $this;
    }
}
