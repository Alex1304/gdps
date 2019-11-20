<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LevelDemonVoteRepository")
 */
class LevelDemonVote
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Level", inversedBy="levelDemonVotes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $level;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Player", inversedBy="levelDemonVotes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $player;

    /**
     * @ORM\Column(type="integer")
     */
    private $demonValue;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isModVote;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLevel(): ?Level
    {
        return $this->level;
    }

    public function setLevel(?Level $level): self
    {
        $this->level = $level;

        return $this;
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

    public function getDemonValue(): ?int
    {
        return $this->demonValue;
    }

    public function setDemonValue(int $demonValue): self
    {
        $this->demonValue = $demonValue;

        return $this;
    }

    public function getIsModVote(): ?bool
    {
        return $this->isModVote;
    }

    public function setIsModVote(bool $isModVote): self
    {
        $this->isModVote = $isModVote;

        return $this;
    }
}
