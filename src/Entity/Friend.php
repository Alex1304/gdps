<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FriendRepository")
 */
class Friend
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Account")
     * @ORM\JoinColumn(nullable=false)
     */
    private $a;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Account")
     * @ORM\JoinColumn(nullable=false)
     */
    private $b;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isNewForA;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isNewForB;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getA(): ?Account
    {
        return $this->a;
    }

    public function setA(?Account $a): self
    {
        $this->a = $a;

        return $this;
    }

    public function getB(): ?Account
    {
        return $this->b;
    }

    public function setB(?Account $b): self
    {
        $this->b = $b;

        return $this;
    }

    public function getIsNewForA(): ?bool
    {
        return $this->isNewForA;
    }

    public function setIsNewForA(bool $isNewForA): self
    {
        $this->isNewForA = $isNewForA;

        return $this;
    }

    public function getIsNewForB(): ?bool
    {
        return $this->isNewForB;
    }

    public function setIsNewForB(bool $isNewForB): self
    {
        $this->isNewForB = $isNewForB;

        return $this;
    }
}
