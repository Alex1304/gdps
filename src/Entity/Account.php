<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     */
    private $player;

    /**
     * @ORM\Column(type="integer")
     */
    private $friendRequestPolicy;

    /**
     * @ORM\Column(type="integer")
     */
    private $privateMessagePolicy;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Account")
     * @ORM\JoinTable(name="friends")
     */
    private $friends;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Account", mappedBy="blockedBy")
     * @ORM\JoinTable(name="blocked_accounts")
     */
    private $blockedAccounts;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Account", inversedBy="blockedAccounts")
     * @ORM\JoinTable(name="blocked_accounts")
     */
    private $blockedBy;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PrivateMessage", mappedBy="author", orphanRemoval=true)
     */
    private $outgoingPrivateMessages;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PrivateMessage", mappedBy="recipient", orphanRemoval=true)
     */
    private $incomingPrivateMessages;

    public function __construct()
    {
        $this->friends = new ArrayCollection();
        $this->incomingFriendRequests = new ArrayCollection();
        $this->outgoingFriendRequests = new ArrayCollection();
        $this->blockedBy = new ArrayCollection();
        $this->blockedAccounts = new ArrayCollection();
        $this->outgoingPrivateMessages = new ArrayCollection();
        $this->incomingPrivateMessages = new ArrayCollection();
    }

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

    public function getFriendRequestPolicy(): ?int
    {
        return $this->friendRequestPolicy;
    }

    public function setFriendRequestPolicy(int $friendRequestPolicy): self
    {
        $this->friendRequestPolicy = $friendRequestPolicy;

        return $this;
    }

    public function getPrivateMessagePolicy(): ?int
    {
        return $this->privateMessagePolicy;
    }

    public function setPrivateMessagePolicy(int $privateMessagePolicy): self
    {
        $this->privateMessagePolicy = $privateMessagePolicy;

        return $this;
    }

    /**
     * @return Collection|Account[]
     */
    public function getFriends(): Collection
    {
        return $this->friends;
    }

    public function addFriend(Account $friend): self
    {
        if (!$this->friends->contains($friend)) {
            $this->friends[] = $friend;
        }

        return $this;
    }

    public function removeFriend(Account $friend): self
    {
        if ($this->friends->contains($friend)) {
            $this->friends->removeElement($friend);
        }

        return $this;
    }

    /**
     * @return Collection|Account[]
     */
    public function getIncomingFriendRequests(): Collection
    {
        return $this->incomingFriendRequests;
    }

    public function addIncomingFriendRequest(Account $incomingFriendRequest): self
    {
        if (!$this->incomingFriendRequests->contains($incomingFriendRequest)) {
            $this->incomingFriendRequests[] = $incomingFriendRequest;
            $incomingFriendRequest->addOutgoingFriendRequest($this);
        }

        return $this;
    }

    public function removeIncomingFriendRequest(Account $incomingFriendRequest): self
    {
        if ($this->incomingFriendRequests->contains($incomingFriendRequest)) {
            $this->incomingFriendRequests->removeElement($incomingFriendRequest);
            $incomingFriendRequest->removeOutgoingFriendRequest($this);
        }

        return $this;
    }

    /**
     * @return Collection|Account[]
     */
    public function getBlockedBy(): Collection
    {
        return $this->blockedBy;
    }

    public function addBlockedBy(Account $blockedBy): self
    {
        if (!$this->blockedBy->contains($blockedBy)) {
            $this->blockedBy[] = $blockedBy;
        }

        return $this;
    }

    public function removeBlockedBy(Account $blockedBy): self
    {
        if ($this->blockedBy->contains($blockedBy)) {
            $this->blockedBy->removeElement($blockedBy);
        }

        return $this;
    }

    /**
     * @return Collection|PrivateMessage[]
     */
    public function getOutgoingPrivateMessages(): Collection
    {
        return $this->outgoingPrivateMessages;
    }

    public function addOutgoingPrivateMessage(PrivateMessage $outgoingPrivateMessage): self
    {
        if (!$this->outgoingPrivateMessages->contains($outgoingPrivateMessage)) {
            $this->outgoingPrivateMessages[] = $outgoingPrivateMessage;
            $outgoingPrivateMessage->setAuthor($this);
        }

        return $this;
    }

    public function removeOutgoingPrivateMessage(PrivateMessage $outgoingPrivateMessage): self
    {
        if ($this->outgoingPrivateMessages->contains($outgoingPrivateMessage)) {
            $this->outgoingPrivateMessages->removeElement($outgoingPrivateMessage);
            // set the owning side to null (unless already changed)
            if ($outgoingPrivateMessage->getAuthor() === $this) {
                $outgoingPrivateMessage->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PrivateMessage[]
     */
    public function getIncomingPrivateMessages(): Collection
    {
        return $this->incomingPrivateMessages;
    }

    public function addIncomingPrivateMessage(PrivateMessage $incomingPrivateMessage): self
    {
        if (!$this->incomingPrivateMessages->contains($incomingPrivateMessage)) {
            $this->incomingPrivateMessages[] = $incomingPrivateMessage;
            $incomingPrivateMessage->setRecipient($this);
        }

        return $this;
    }

    public function removeIncomingPrivateMessage(PrivateMessage $incomingPrivateMessage): self
    {
        if ($this->incomingPrivateMessages->contains($incomingPrivateMessage)) {
            $this->incomingPrivateMessages->removeElement($incomingPrivateMessage);
            // set the owning side to null (unless already changed)
            if ($incomingPrivateMessage->getRecipient() === $this) {
                $incomingPrivateMessage->setRecipient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Account[]
     */
    public function getOutgoingFriendRequests(): Collection
    {
        return $this->outgoingFriendRequests;
    }

    public function addOutgoingFriendRequest(Account $outgoingFriendRequest): self
    {
        if (!$this->outgoingFriendRequests->contains($outgoingFriendRequest)) {
            $this->outgoingFriendRequests[] = $outgoingFriendRequest;
        }

        return $this;
    }

    public function removeOutgoingFriendRequest(Account $outgoingFriendRequest): self
    {
        if ($this->outgoingFriendRequests->contains($outgoingFriendRequest)) {
            $this->outgoingFriendRequests->removeElement($outgoingFriendRequest);
        }

        return $this;
    }

    /**
     * @return Collection|Account[]
     */
    public function getBlockedAccounts(): Collection
    {
        return $this->blockedAccounts;
    }

    public function addBlockedAccount(Account $blockedAccount): self
    {
        if (!$this->blockedAccounts->contains($blockedAccount)) {
            $this->blockedAccounts[] = $blockedAccount;
            $blockedAccount->addBlockedBy($this);
        }

        return $this;
    }

    public function removeBlockedAccount(Account $blockedAccount): self
    {
        if ($this->blockedAccounts->contains($blockedAccount)) {
            $this->blockedAccounts->removeElement($blockedAccount);
            $blockedAccount->removeBlockedBy($this);
        }

        return $this;
    }
}
