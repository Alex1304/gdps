<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LevelRepository")
 * @ORM\Table(name="level", indexes={@ORM\Index(name="levelsearch_idx", columns={"name"}), @ORM\Index(name="featured_idx", columns={"feature_score"})})
 */
class Level
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
    private $name;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Player", inversedBy="levels")
     * @ORM\JoinColumn(nullable=false)
     */
    private $creator;

    /**
     * @ORM\Column(type="text")
     */
    private $data;

    /**
     * @ORM\Column(type="integer")
     */
    private $audioTrack;

    /**
     * @ORM\Column(type="integer")
     */
    private $customSongID;

    /**
     * @ORM\Column(type="integer")
     */
    private $stars;

    /**
     * @ORM\Column(type="integer")
     */
    private $featureScore;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isEpic;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Player", inversedBy="downloadedLevels")
     * @ORM\JoinTable(name="level_downloads")
     */
    private $downloadedBy;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Player", inversedBy="likedLevels")
     * @ORM\JoinTable(name="level_likes")
     */
    private $likedBy;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Player", inversedBy="dislikedLevels")
     * @ORM\JoinTable(name="level_dislikes")
     */
    private $dislikedBy;

    /**
     * @ORM\Column(type="integer")
     */
    private $gameVersion;

    /**
     * @ORM\Column(type="integer")
     */
    private $version;

    /**
     * @ORM\Column(type="integer")
     */
    private $requestedStars;

    /**
     * @ORM\Column(type="datetime")
     */
    private $uploadedAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $lastUpdatedAt;

    /**
     * @ORM\Column(type="integer")
     */
    private $length;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isLDM;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isUnlisted;

    /**
     * @ORM\Column(type="integer")
     */
    private $password;

    /**
     * @ORM\Column(type="integer")
     */
    private $objectCount;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $extraString;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Level", inversedBy="original")
     */
    private $original;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isTwoPlayer;

    /**
     * @ORM\Column(type="integer")
     */
    private $coins;

    /**
     * @ORM\Column(type="integer")
     */
    private $difficulty;

    /**
     * @ORM\Column(type="integer")
     */
    private $demonDifficulty;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isDemon;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isAuto;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hasCoinsVerified;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $rewardsGivenAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\LevelComment", mappedBy="level", orphanRemoval=true)
     */
    private $levelComments;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\LevelStarVote", mappedBy="level", orphanRemoval=true)
     */
    private $levelStarVotes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\LevelDemonVote", mappedBy="level", orphanRemoval=true)
     */
    private $levelDemonVotes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\LevelScore", mappedBy="level", orphanRemoval=true)
     */
    private $levelScores;

    public function __construct()
    {
        $this->downloadedBy = new ArrayCollection();
        $this->likedBy = new ArrayCollection();
        $this->dislikedBy = new ArrayCollection();
        $this->levelComments = new ArrayCollection();
        $this->levelStarVotes = new ArrayCollection();
        $this->levelDemonVotes = new ArrayCollection();
        $this->levelScores = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCreator(): ?Player
    {
        return $this->creator;
    }

    public function setCreator(?Player $creator): self
    {
        $this->creator = $creator;

        return $this;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(string $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getAudioTrack(): ?int
    {
        return $this->audioTrack;
    }

    public function setAudioTrack(int $audioTrack): self
    {
        $this->audioTrack = $audioTrack;

        return $this;
    }

    public function getCustomSongID(): ?int
    {
        return $this->customSongID;
    }

    public function setCustomSongID(int $customSongID): self
    {
        $this->customSongID = $customSongID;

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

    public function getFeatureScore(): ?int
    {
        return $this->featureScore;
    }

    public function setFeatureScore(int $featureScore): self
    {
        $this->featureScore = $featureScore;

        return $this;
    }

    public function getIsEpic(): ?bool
    {
        return $this->isEpic;
    }

    public function setIsEpic(bool $isEpic): self
    {
        $this->isEpic = $isEpic;

        return $this;
    }

    /**
     * @return Collection|Player[]
     */
    public function getDownloadedBy(): Collection
    {
        return $this->downloadedBy;
    }

    public function addDownloadedBy(Player $downloadedBy): self
    {
        if (!$this->downloadedBy->contains($downloadedBy)) {
            $this->downloadedBy[] = $downloadedBy;
        }

        return $this;
    }

    public function removeDownloadedBy(Player $downloadedBy): self
    {
        if ($this->downloadedBy->contains($downloadedBy)) {
            $this->downloadedBy->removeElement($downloadedBy);
        }

        return $this;
    }

    /**
     * @return Collection|Player[]
     */
    public function getLikedBy(): Collection
    {
        return $this->likedBy;
    }

    public function addLikedBy(Player $likedBy): self
    {
        if (!$this->likedBy->contains($likedBy)) {
            $this->likedBy[] = $likedBy;
        }

        return $this;
    }

    public function removeLikedBy(Player $likedBy): self
    {
        if ($this->likedBy->contains($likedBy)) {
            $this->likedBy->removeElement($likedBy);
        }

        return $this;
    }

    /**
     * @return Collection|Player[]
     */
    public function getDislikedBy(): Collection
    {
        return $this->dislikedBy;
    }

    public function addDislikedBy(Player $dislikedBy): self
    {
        if (!$this->dislikedBy->contains($dislikedBy)) {
            $this->dislikedBy[] = $dislikedBy;
        }

        return $this;
    }

    public function removeDislikedBy(Player $dislikedBy): self
    {
        if ($this->dislikedBy->contains($dislikedBy)) {
            $this->dislikedBy->removeElement($dislikedBy);
        }

        return $this;
    }

    public function getGameVersion(): ?int
    {
        return $this->gameVersion;
    }

    public function setGameVersion(int $gameVersion): self
    {
        $this->gameVersion = $gameVersion;

        return $this;
    }

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function setVersion(int $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getRequestedStars(): ?int
    {
        return $this->requestedStars;
    }

    public function setRequestedStars(int $requestedStars): self
    {
        $this->requestedStars = $requestedStars;

        return $this;
    }

    public function getUploadedAt(): ?\DateTimeInterface
    {
        return $this->uploadedAt;
    }

    public function setUploadedAt(\DateTimeInterface $uploadedAt): self
    {
        $this->uploadedAt = $uploadedAt;

        return $this;
    }

    public function getLastUpdatedAt(): ?\DateTimeInterface
    {
        return $this->lastUpdatedAt;
    }

    public function setLastUpdatedAt(\DateTimeInterface $lastUpdatedAt): self
    {
        $this->lastUpdatedAt = $lastUpdatedAt;

        return $this;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function setLength(int $length): self
    {
        $this->length = $length;

        return $this;
    }

    public function getIsLDM(): ?bool
    {
        return $this->isLDM;
    }

    public function setIsLDM(bool $isLDM): self
    {
        $this->isLDM = $isLDM;

        return $this;
    }

    public function getIsUnlisted(): ?bool
    {
        return $this->isUnlisted;
    }

    public function setIsUnlisted(bool $isUnlisted): self
    {
        $this->isUnlisted = $isUnlisted;

        return $this;
    }

    public function getPassword(): ?int
    {
        return $this->password;
    }

    public function setPassword(int $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getObjectCount(): ?int
    {
        return $this->objectCount;
    }

    public function setObjectCount(int $objectCount): self
    {
        $this->objectCount = $objectCount;

        return $this;
    }

    public function getExtraString(): ?string
    {
        return $this->extraString;
    }

    public function setExtraString(string $extraString): self
    {
        $this->extraString = $extraString;

        return $this;
    }

    public function getOriginal(): ?self
    {
        return $this->original;
    }

    public function setOriginal(?self $original): self
    {
        $this->original = $original;

        return $this;
    }

    public function getIsTwoPlayer(): ?bool
    {
        return $this->isTwoPlayer;
    }

    public function setIsTwoPlayer(bool $isTwoPlayer): self
    {
        $this->isTwoPlayer = $isTwoPlayer;

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

    public function getDifficulty(): ?int
    {
        return $this->difficulty;
    }

    public function setDifficulty(int $difficulty): self
    {
        $this->difficulty = $difficulty;

        return $this;
    }

    public function getDemonDifficulty(): ?int
    {
        return $this->demonDifficulty;
    }

    public function setDemonDifficulty(int $demonDifficulty): self
    {
        $this->demonDifficulty = $demonDifficulty;

        return $this;
    }

    public function getIsDemon(): ?bool
    {
        return $this->isDemon;
    }

    public function setIsDemon(bool $isDemon): self
    {
        $this->isDemon = $isDemon;

        return $this;
    }

    public function getIsAuto(): ?bool
    {
        return $this->isAuto;
    }

    public function setIsAuto(bool $isAuto): self
    {
        $this->isAuto = $isAuto;

        return $this;
    }

    public function getHasCoinsVerified(): ?bool
    {
        return $this->hasCoinsVerified;
    }

    public function setHasCoinsVerified(bool $hasCoinsVerified): self
    {
        $this->hasCoinsVerified = $hasCoinsVerified;

        return $this;
    }

    public function getRewardsGivenAt(): ?\DateTimeInterface
    {
        return $this->rewardsGivenAt;
    }

    public function setRewardsGivenAt(?\DateTimeInterface $rewardsGivenAt): self
    {
        $this->rewardsGivenAt = $rewardsGivenAt;

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
            $levelComment->setLevel($this);
        }

        return $this;
    }

    public function removeLevelComment(LevelComment $levelComment): self
    {
        if ($this->levelComments->contains($levelComment)) {
            $this->levelComments->removeElement($levelComment);
            // set the owning side to null (unless already changed)
            if ($levelComment->getLevel() === $this) {
                $levelComment->setLevel(null);
            }
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
            $levelStarVote->setLevel($this);
        }

        return $this;
    }

    public function removeLevelStarVote(LevelStarVote $levelStarVote): self
    {
        if ($this->levelStarVotes->contains($levelStarVote)) {
            $this->levelStarVotes->removeElement($levelStarVote);
            // set the owning side to null (unless already changed)
            if ($levelStarVote->getLevel() === $this) {
                $levelStarVote->setLevel(null);
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
            $levelDemonVote->setLevel($this);
        }

        return $this;
    }

    public function removeLevelDemonVote(LevelDemonVote $levelDemonVote): self
    {
        if ($this->levelDemonVotes->contains($levelDemonVote)) {
            $this->levelDemonVotes->removeElement($levelDemonVote);
            // set the owning side to null (unless already changed)
            if ($levelDemonVote->getLevel() === $this) {
                $levelDemonVote->setLevel(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|LevelScore[]
     */
    public function getLevelScores(): Collection
    {
        return $this->levelScores;
    }

    public function addLevelScore(LevelScore $levelScore): self
    {
        if (!$this->levelScores->contains($levelScore)) {
            $this->levelScores[] = $levelScore;
            $levelScore->setLevel($this);
        }

        return $this;
    }

    public function removeLevelScore(LevelScore $levelScore): self
    {
        if ($this->levelScores->contains($levelScore)) {
            $this->levelScores->removeElement($levelScore);
            // set the owning side to null (unless already changed)
            if ($levelScore->getLevel() === $this) {
                $levelScore->setLevel(null);
            }
        }

        return $this;
    }
}
