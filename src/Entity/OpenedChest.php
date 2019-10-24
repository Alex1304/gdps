<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OpenedChestRepository")
 */
class OpenedChest
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Player")
     * @ORM\JoinColumn(nullable=false)
     */
    private $player;

    /**
     * @ORM\Column(type="integer")
     */
    private $orbs;

    /**
     * @ORM\Column(type="integer")
     */
    private $diamonds;

    /**
     * @ORM\Column(type="integer")
     */
    private $shards;

    /**
     * @ORM\Column(type="integer")
     */
    private $demonKeys;

    /**
     * @ORM\Column(type="datetime")
     */
    private $openedAt;

    /**
     * @ORM\Column(type="integer")
     */
    private $type;
	
	public function __construct($player, $type)
	{
		$this->player = $player;
		$this->type = $type;
		$this->openedAt = new \DateTime('0000-00-00 00:00:00');
	}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    public function getOrbs(): ?int
    {
        return $this->orbs;
    }

    public function setOrbs(int $orbs): self
    {
        $this->orbs = $orbs;

        return $this;
    }

    public function getDiamonds(): ?int
    {
        return $this->diamonds;
    }

    public function setDiamonds(int $diamonds): self
    {
        $this->diamonds = $diamonds;

        return $this;
    }

    public function getShards(): ?int
    {
        return $this->shards;
    }

    public function setShards(int $shards): self
    {
        $this->shards = $shards;

        return $this;
    }

    public function getDemonKeys(): ?int
    {
        return $this->demonKeys;
    }

    public function setDemonKeys(int $demonKeys): self
    {
        $this->demonKeys = $demonKeys;

        return $this;
    }

    public function getOpenedAt(): ?\DateTimeInterface
    {
        return $this->openedAt;
    }

    public function setOpenedAt(\DateTimeInterface $openedAt): self
    {
        $this->openedAt = $openedAt;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }
}
