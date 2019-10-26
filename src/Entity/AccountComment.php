<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AccountCommentRepository")
 */
class AccountComment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Account", inversedBy="accountComments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     */
    private $postedAt;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Player", inversedBy="likedAccountComments")
     * @ORM\JoinTable(name="account_comment_likes")
     */
    private $likedBy;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Player", inversedBy="dislikedAccountComments")
     * @ORM\JoinTable(name="account_comment_dislikes")
     */
    private $dislikedBy;

    /**
     * @ORM\Column(type="bigint")
     */
    private $likes;

    public function __construct()
    {
        $this->likedBy = new ArrayCollection();
        $this->dislikedBy = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?Account
    {
        return $this->author;
    }

    public function setAuthor(?Account $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getPostedAt(): ?\DateTimeInterface
    {
        return $this->postedAt;
    }

    public function setPostedAt(\DateTimeInterface $postedAt): self
    {
        $this->postedAt = $postedAt;

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

    public function getLikes(): ?string
    {
        return $this->likes;
    }

    public function setLikes(string $likes): self
    {
        $this->likes = $likes;

        return $this;
    }
}
