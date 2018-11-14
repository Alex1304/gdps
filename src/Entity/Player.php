<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PlayerRepository")
 * @ORM\Table(name="player", indexes={@ORM\Index(name="leaderboards_idx", columns={"stars"}), @ORM\Index(name="usersearch_idx", columns={"name"}), @ORM\Index(name="cp_leaderboard_idx", columns={"creator_points"})})
 */
class Player implements UserInterface
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

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Level", mappedBy="creator", orphanRemoval=true)
     */
    private $levels;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Level", mappedBy="downloadedBy")
     */
    private $downloadedLevels;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Level", mappedBy="likedBy")
     */
    private $likedLevels;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Level", mappedBy="dislikedBy")
     */
    private $dislikedLevels;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\LevelComment", mappedBy="author", orphanRemoval=true)
     */
    private $levelComments;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\LevelComment", mappedBy="likedBy")
     */
    private $likedLevelComments;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\LevelComment", mappedBy="dislikedBy")
     */
    private $dislikedLevelComments;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\LevelStarVote", mappedBy="player", orphanRemoval=true)
     */
    private $levelStarVotes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\LevelDemonVote", mappedBy="player", orphanRemoval=true)
     */
    private $levelDemonVotes;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\AccountComment", mappedBy="likedBy")
     */
    private $likedAccountComments;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\AccountComment", mappedBy="dislikedBy")
     */
    private $dislikedAccountComments;

    public function __construct()
    {
        $this->levels = new ArrayCollection();
        $this->downloadedLevels = new ArrayCollection();
        $this->likedLevels = new ArrayCollection();
        $this->dislikedLevels = new ArrayCollection();
        $this->levelComments = new ArrayCollection();
        $this->likedLevelComments = new ArrayCollection();
        $this->dislikedLevelComments = new ArrayCollection();
        $this->levelStarVotes = new ArrayCollection();
        $this->levelDemonVotes = new ArrayCollection();
        $this->likedAccountComments = new ArrayCollection();
        $this->dislikedAccountComments = new ArrayCollection();
    }

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
        return $this->account ? $this->account->getUsername() : $this->name;
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

    /**
     * @return Collection|Level[]
     */
    public function getLevels(): Collection
    {
        return $this->levels;
    }

    public function addLevel(Level $level): self
    {
        if (!$this->levels->contains($level)) {
            $this->levels[] = $level;
            $level->setCreator($this);
        }

        return $this;
    }

    public function removeLevel(Level $level): self
    {
        if ($this->levels->contains($level)) {
            $this->levels->removeElement($level);
            // set the owning side to null (unless already changed)
            if ($level->getCreator() === $this) {
                $level->setCreator(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Level[]
     */
    public function getDownloadedLevels(): Collection
    {
        return $this->downloadedLevels;
    }

    public function addDownloadedLevel(Level $downloadedLevel): self
    {
        if (!$this->downloadedLevels->contains($downloadedLevel)) {
            $this->downloadedLevels[] = $downloadedLevel;
            $downloadedLevel->addDownloadedBy($this);
        }

        return $this;
    }

    public function removeDownloadedLevel(Level $downloadedLevel): self
    {
        if ($this->downloadedLevels->contains($downloadedLevel)) {
            $this->downloadedLevels->removeElement($downloadedLevel);
            $downloadedLevel->removeDownloadedBy($this);
        }

        return $this;
    }

    /**
     * @return Collection|Level[]
     */
    public function getLikedLevels(): Collection
    {
        return $this->likedLevels;
    }

    public function addLikedLevel(Level $likedLevel): self
    {
        if (!$this->likedLevels->contains($likedLevel)) {
            $this->likedLevels[] = $likedLevel;
            $likedLevel->addLikedBy($this);
        }

        return $this;
    }

    public function removeLikedLevel(Level $likedLevel): self
    {
        if ($this->likedLevels->contains($likedLevel)) {
            $this->likedLevels->removeElement($likedLevel);
            $likedLevel->removeLikedBy($this);
        }

        return $this;
    }

    /**
     * @return Collection|Level[]
     */
    public function getDislikedLevels(): Collection
    {
        return $this->dislikedLevels;
    }

    public function addDislikedLevel(Level $dislikedLevel): self
    {
        if (!$this->dislikedLevels->contains($dislikedLevel)) {
            $this->dislikedLevels[] = $dislikedLevel;
            $dislikedLevel->addDislikedBy($this);
        }

        return $this;
    }

    public function removeDislikedLevel(Level $dislikedLevel): self
    {
        if ($this->dislikedLevels->contains($dislikedLevel)) {
            $this->dislikedLevels->removeElement($dislikedLevel);
            $dislikedLevel->removeDislikedBy($this);
        }

        return $this;
    }

    /**
     * @return Collection|LevelComment[]
     */
    public function getLevelComments(): Collection
    {
        return $this->levelComments;
    }

    public function addLevelComment(LevelComment $levelComment): self
    {
        if (!$this->levelComments->contains($levelComment)) {
            $this->levelComments[] = $levelComment;
            $levelComment->setAuthor($this);
        }

        return $this;
    }

    public function removeLevelComment(LevelComment $levelComment): self
    {
        if ($this->levelComments->contains($levelComment)) {
            $this->levelComments->removeElement($levelComment);
            // set the owning side to null (unless already changed)
            if ($levelComment->getAuthor() === $this) {
                $levelComment->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|LevelComment[]
     */
    public function getLikedLevelComments(): Collection
    {
        return $this->likedLevelComments;
    }

    public function addLikedLevelComment(LevelComment $likedLevelComment): self
    {
        if (!$this->likedLevelComments->contains($likedLevelComment)) {
            $this->likedLevelComments[] = $likedLevelComment;
            $likedLevelComment->addLikedBy($this);
        }

        return $this;
    }

    public function removeLikedLevelComment(LevelComment $likedLevelComment): self
    {
        if ($this->likedLevelComments->contains($likedLevelComment)) {
            $this->likedLevelComments->removeElement($likedLevelComment);
            $likedLevelComment->removeLikedBy($this);
        }

        return $this;
    }

    /**
     * @return Collection|LevelComment[]
     */
    public function getDislikedLevelComments(): Collection
    {
        return $this->dislikedLevelComments;
    }

    public function addDislikedLevelComment(LevelComment $dislikedLevelComment): self
    {
        if (!$this->dislikedLevelComments->contains($dislikedLevelComment)) {
            $this->dislikedLevelComments[] = $dislikedLevelComment;
            $dislikedLevelComment->addDislikedBy($this);
        }

        return $this;
    }

    public function removeDislikedLevelComment(LevelComment $dislikedLevelComment): self
    {
        if ($this->dislikedLevelComments->contains($dislikedLevelComment)) {
            $this->dislikedLevelComments->removeElement($dislikedLevelComment);
            $dislikedLevelComment->removeDislikedBy($this);
        }

        return $this;
    }

    /**
     * @return Collection|LevelStarVote[]
     */
    public function getLevelStarVotes(): Collection
    {
        return $this->levelStarVotes;
    }

    public function addLevelStarVote(LevelStarVote $levelStarVote): self
    {
        if (!$this->levelStarVotes->contains($levelStarVote)) {
            $this->levelStarVotes[] = $levelStarVote;
            $levelStarVote->setPlayer($this);
        }

        return $this;
    }

    public function removeLevelStarVote(LevelStarVote $levelStarVote): self
    {
        if ($this->levelStarVotes->contains($levelStarVote)) {
            $this->levelStarVotes->removeElement($levelStarVote);
            // set the owning side to null (unless already changed)
            if ($levelStarVote->getPlayer() === $this) {
                $levelStarVote->setPlayer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|LevelDemonVote[]
     */
    public function getLevelDemonVotes(): Collection
    {
        return $this->levelDemonVotes;
    }

    public function addLevelDemonVote(LevelDemonVote $levelDemonVote): self
    {
        if (!$this->levelDemonVotes->contains($levelDemonVote)) {
            $this->levelDemonVotes[] = $levelDemonVote;
            $levelDemonVote->setPlayer($this);
        }

        return $this;
    }

    public function removeLevelDemonVote(LevelDemonVote $levelDemonVote): self
    {
        if ($this->levelDemonVotes->contains($levelDemonVote)) {
            $this->levelDemonVotes->removeElement($levelDemonVote);
            // set the owning side to null (unless already changed)
            if ($levelDemonVote->getPlayer() === $this) {
                $levelDemonVote->setPlayer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|AccountComment[]
     */
    public function getLikedAccountComments(): Collection
    {
        return $this->likedAccountComments;
    }

    public function addLikedAccountComment(AccountComment $likedAccountComment): self
    {
        if (!$this->likedAccountComments->contains($likedAccountComment)) {
            $this->likedAccountComments[] = $likedAccountComment;
            $likedAccountComment->addLikedBy($this);
        }

        return $this;
    }

    public function removeLikedAccountComment(AccountComment $likedAccountComment): self
    {
        if ($this->likedAccountComments->contains($likedAccountComment)) {
            $this->likedAccountComments->removeElement($likedAccountComment);
            $likedAccountComment->removeLikedBy($this);
        }

        return $this;
    }

    /**
     * @return Collection|AccountComment[]
     */
    public function getDislikedAccountComments(): Collection
    {
        return $this->dislikedAccountComments;
    }

    public function addDislikedAccountComment(AccountComment $dislikedAccountComment): self
    {
        if (!$this->dislikedAccountComments->contains($dislikedAccountComment)) {
            $this->dislikedAccountComments[] = $dislikedAccountComment;
            $dislikedAccountComment->addDislikedBy($this);
        }

        return $this;
    }

    public function removeDislikedAccountComment(AccountComment $dislikedAccountComment): self
    {
        if ($this->dislikedAccountComments->contains($dislikedAccountComment)) {
            $this->dislikedAccountComments->removeElement($dislikedAccountComment);
            $dislikedAccountComment->removeDislikedBy($this);
        }

        return $this;
    }


    public function getUsername(): ?string
    {
        return $this->getAccount() ? $this->getAccount()->getUsername() : null;
    }

    public function getPassword(): ?string
    {
        return $this->getAccount() ? $this->getAccount()->getPassword() : null;
    }

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {
        return;
    }

    public function getRoles(): array
    {
        if (!$this->getAccount())
            return [ 'ROLE_UNREGISTERED_USER' ];

        return [ 'ROLE_USER' ];
    }
}
