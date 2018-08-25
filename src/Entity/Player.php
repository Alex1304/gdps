<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PlayerRepository")
 * @ORM\Table(name="player", indexes={@ORM\Index(name="leaderboards_idx", columns={"stars"}), @ORM\Index(name="usersearch_idx", columns={"name"}), @ORM\Index(name="cp_leaderboard_idx", columns={"creator_points"})})
 */
class Player
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
    private $deviceID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $stars;

    /**
     * @ORM\Column(type="integer")
     */
    private $demons;

    /**
     * @ORM\Column(type="integer")
     */
    private $diamonds;

    /**
     * @ORM\Column(type="integer")
     */
    private $icon;

    /**
     * @ORM\Column(type="integer")
     */
    private $color1;

    /**
     * @ORM\Column(type="integer")
     */
    private $color2;

    /**
     * @ORM\Column(type="integer")
     */
    private $iconType;

    /**
     * @ORM\Column(type="integer")
     */
    private $coins;

    /**
     * @ORM\Column(type="integer")
     */
    private $userCoins;

    /**
     * @ORM\Column(type="integer")
     */
    private $special;

    /**
     * @ORM\Column(type="integer")
     */
    private $accIcon;

    /**
     * @ORM\Column(type="integer")
     */
    private $accShip;

    /**
     * @ORM\Column(type="integer")
     */
    private $accBall;

    /**
     * @ORM\Column(type="integer")
     */
    private $accUFO;

    /**
     * @ORM\Column(type="integer")
     */
    private $accWave;

    /**
     * @ORM\Column(type="integer")
     */
    private $accRobot;

    /**
     * @ORM\Column(type="integer")
     */
    private $accGlow;

    /**
     * @ORM\Column(type="integer")
     */
    private $accSpider;

    /**
     * @ORM\Column(type="integer")
     */
    private $accExplosion;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Account", mappedBy="player", cascade={"persist", "remove"})
     */
    private $account;

    /**
     * @ORM\Column(type="datetime")
     */
    private $statsLastUpdatedAt;

    /**
     * @ORM\Column(type="integer")
     */
    private $creatorPoints;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDeviceID(): ?string
    {
        return $this->deviceID;
    }

    public function setDeviceID(string $deviceID): self
    {
        $this->deviceID = $deviceID;

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

    public function getStars(): ?int
    {
        return $this->stars;
    }

    public function setStars(int $stars): self
    {
        $this->stars = $stars;

        return $this;
    }

    public function getDemons(): ?int
    {
        return $this->demons;
    }

    public function setDemons(int $demons): self
    {
        $this->demons = $demons;

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

    public function getIcon(): ?int
    {
        return $this->icon;
    }

    public function setIcon(int $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getColor1(): ?int
    {
        return $this->color1;
    }

    public function setColor1(int $color1): self
    {
        $this->color1 = $color1;

        return $this;
    }

    public function getColor2(): ?int
    {
        return $this->color2;
    }

    public function setColor2(int $color2): self
    {
        $this->color2 = $color2;

        return $this;
    }

    public function getIconType(): ?int
    {
        return $this->iconType;
    }

    public function setIconType(int $iconType): self
    {
        $this->iconType = $iconType;

        return $this;
    }

    public function getCoins(): ?int
    {
        return $this->coins;
    }

    public function setCoins(int $coins): self
    {
        $this->coins = $coins;

        return $this;
    }

    public function getUserCoins(): ?int
    {
        return $this->userCoins;
    }

    public function setUserCoins(int $userCoins): self
    {
        $this->userCoins = $userCoins;

        return $this;
    }

    public function getSpecial(): ?int
    {
        return $this->special;
    }

    public function setSpecial(int $special): self
    {
        $this->special = $special;

        return $this;
    }

    public function getAccIcon(): ?int
    {
        return $this->accIcon;
    }

    public function setAccIcon(int $accIcon): self
    {
        $this->accIcon = $accIcon;

        return $this;
    }

    public function getAccShip(): ?int
    {
        return $this->accShip;
    }

    public function setAccShip(int $accShip): self
    {
        $this->accShip = $accShip;

        return $this;
    }

    public function getAccBall(): ?int
    {
        return $this->accBall;
    }

    public function setAccBall(int $accBall): self
    {
        $this->accBall = $accBall;

        return $this;
    }

    public function getAccUFO(): ?int
    {
        return $this->accUFO;
    }

    public function setAccUFO(int $accUFO): self
    {
        $this->accUFO = $accUFO;

        return $this;
    }

    public function getAccWave(): ?int
    {
        return $this->accWave;
    }

    public function setAccWave(int $accWave): self
    {
        $this->accWave = $accWave;

        return $this;
    }

    public function getAccRobot(): ?int
    {
        return $this->accRobot;
    }

    public function setAccRobot(int $accRobot): self
    {
        $this->accRobot = $accRobot;

        return $this;
    }

    public function getAccGlow(): ?int
    {
        return $this->accGlow;
    }

    public function setAccGlow(int $accGlow): self
    {
        $this->accGlow = $accGlow;

        return $this;
    }

    public function getAccSpider(): ?int
    {
        return $this->accSpider;
    }

    public function setAccSpider(int $accSpider): self
    {
        $this->accSpider = $accSpider;

        return $this;
    }

    public function getAccExplosion(): ?int
    {
        return $this->accExplosion;
    }

    public function setAccExplosion(int $accExplosion): self
    {
        $this->accExplosion = $accExplosion;

        return $this;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): self
    {
        $this->account = $account;

        // set (or unset) the owning side of the relation if necessary
        $newPlayer = $account === null ? null : $this;
        if ($newPlayer !== $account->getPlayer()) {
            $account->setPlayer($newPlayer);
        }

        return $this;
    }

    public function getStatsLastUpdatedAt(): ?\DateTimeInterface
    {
        return $this->statsLastUpdatedAt;
    }

    public function setStatsLastUpdatedAt(\DateTimeInterface $statsLastUpdatedAt): self
    {
        $this->statsLastUpdatedAt = $statsLastUpdatedAt;

        return $this;
    }

    public function getCreatorPoints(): ?int
    {
        return $this->creatorPoints;
    }

    public function setCreatorPoints(int $creatorPoints): self
    {
        $this->creatorPoints = $creatorPoints;

        return $this;
    }
}
