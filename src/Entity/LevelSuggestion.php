<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LevelSuggestionRepository")
 */
class LevelSuggestion
{
	const SORT_MOST_RECENT_LEVELS = 0;
	const SORT_MOST_SENDS = 1;
	const SORT_MOST_RECENT_SENDS = 2;
	
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
	 *
	 * @Serializer\SerializedName("suggestion_id")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Player", inversedBy="levelSuggestions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $moderator;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Level")
     * @ORM\JoinColumn(nullable=false)
     */
    private $level;

    /**
     * @ORM\Column(type="integer")
     */
    private $stars;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isFeatured;

    /**
     * @ORM\Column(type="datetime")
     */
    private $sentAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModerator(): ?Player
    {
        return $this->moderator;
    }

    public function setModerator(?Player $moderator): self
    {
        $this->moderator = $moderator;

        return $this;
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

    public function getStars(): ?int
    {
        return $this->stars;
    }

    public function setStars(int $stars): self
    {
        $this->stars = $stars;

        return $this;
    }

    public function getIsFeatured(): ?bool
    {
        return $this->isFeatured;
    }

    public function setIsFeatured(bool $isFeatured): self
    {
        $this->isFeatured = $isFeatured;

        return $this;
    }

    public function getSentAt(): ?\DateTimeInterface
    {
        return $this->sentAt;
    }

    public function setSentAt(\DateTimeInterface $sentAt): self
    {
        $this->sentAt = $sentAt;

        return $this;
    }
}
