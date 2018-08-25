<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AccountRepository")
 */
class Account
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $youtube;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $twitter;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $twitch;

    /**
     * @ORM\Column(type="datetime")
     */
    private $registered_at;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Player", inversedBy="account", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $player;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getYoutube(): ?string
    {
        return $this->youtube;
    }

    public function setYoutube(string $youtube): self
    {
        $this->youtube = $youtube;

        return $this;
    }

    public function getTwitter(): ?string
    {
        return $this->twitter;
    }

    public function setTwitter(string $twitter): self
    {
        $this->twitter = $twitter;

        return $this;
    }

    public function getTwitch(): ?string
    {
        return $this->twitch;
    }

    public function setTwitch(string $twitch): self
    {
        $this->twitch = $twitch;

        return $this;
    }

    public function getRegisteredAt(): ?\DateTimeInterface
    {
        return $this->registered_at;
    }

    public function setRegisteredAt(\DateTimeInterface $registered_at): self
    {
        $this->registered_at = $registered_at;

        return $this;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): self
    {
        $this->player = $player;

        return $this;
    }
}
