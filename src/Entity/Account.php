<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation as Serializer;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AccountRepository")
 *
 * @Serializer\ExclusionPolicy("ALL")
 */
class Account implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @Serializer\Expose
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Serializer\Expose
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Serializer\Expose
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Serializer\Expose
     */
    private $youtube;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Serializer\Expose
     */
    private $twitter;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Serializer\Expose
     */
    private $twitch;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Serializer\Expose
     */
    private $registered_at;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Player", inversedBy="account", cascade={"persist", "remove"})
     */
    private $player;

    /**
     * @ORM\Column(type="integer")
     *
     * @Serializer\Expose
     */
    private $friendRequestPolicy;

    /**
     * @ORM\Column(type="integer")
     *
     * @Serializer\Expose
     */
    private $privateMessagePolicy;

    /**
     * @ORM\Column(type="integer")
     *
     * @Serializer\Expose
     */
    private $commentHistoryPolicy;

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

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AccountComment", mappedBy="author", orphanRemoval=true)
     */
    private $accountComments;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\FriendRequest", mappedBy="sender", orphanRemoval=true)
     */
    private $outgoingFriendRequests;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\FriendRequest", mappedBy="recipient", orphanRemoval=true)
     */
    private $incomingFriendRequests;

    private $roles;


    public function __construct()
    {
        $this->blockedBy = new ArrayCollection();
        $this->blockedAccounts = new ArrayCollection();
        $this->outgoingPrivateMessages = new ArrayCollection();
        $this->incomingPrivateMessages = new ArrayCollection();
        $this->accountComments = new ArrayCollection();
        $this->outgoingFriendRequests = new ArrayCollection();
        $this->incomingFriendRequests = new ArrayCollection();

        $this->roles = [ 'ROLE_USER' ];
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

    public function setPlayer(?Player $player): self
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

    /**
     * @return Collection|AccountComment[]
     */
    public function getAccountComments(): Collection
    {
        return $this->accountComments;
    }

    public function addAccountComment(AccountComment $accountComment): self
    {
        if (!$this->accountComments->contains($accountComment)) {
            $this->accountComments[] = $accountComment;
            $accountComment->setAuthor($this);
        }

        return $this;
    }

    public function removeAccountComment(AccountComment $accountComment): self
    {
        if ($this->accountComments->contains($accountComment)) {
            $this->accountComments->removeElement($accountComment);
            // set the owning side to null (unless already changed)
            if ($accountComment->getAuthor() === $this) {
                $accountComment->setAuthor(null);
            }
        }

        return $this;
    }

    public function getCommentHistoryPolicy(): ?int
    {
        return $this->commentHistoryPolicy;
    }

    public function setCommentHistoryPolicy(int $commentHistoryPolicy): self
    {
        $this->commentHistoryPolicy = $commentHistoryPolicy;

        return $this;
    }

    /**
     * @return Collection|FriendRequest[]
     */
    public function getOutgoingFriendRequests(): Collection
    {
        return $this->outgoingFriendRequests;
    }

    public function addOutgoingFriendRequest(FriendRequest $outgoingFriendRequest): self
    {
        if (!$this->outgoingFriendRequests->contains($outgoingFriendRequest)) {
            $this->outgoingFriendRequests[] = $outgoingFriendRequest;
            $outgoingFriendRequest->setSender($this);
        }

        return $this;
    }

    public function removeOutgoingFriendRequest(FriendRequest $outgoingFriendRequest): self
    {
        if ($this->outgoingFriendRequests->contains($outgoingFriendRequest)) {
            $this->outgoingFriendRequests->removeElement($outgoingFriendRequest);
            // set the owning side to null (unless already changed)
            if ($outgoingFriendRequest->getSender() === $this) {
                $outgoingFriendRequest->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|FriendRequest[]
     */
    public function getIncomingFriendRequests(): Collection
    {
        return $this->incomingFriendRequests;
    }

    public function addIncomingFriendRequest(FriendRequest $incomingFriendRequest): self
    {
        if (!$this->incomingFriendRequests->contains($incomingFriendRequest)) {
            $this->incomingFriendRequests[] = $incomingFriendRequest;
            $incomingFriendRequest->setRecipient($this);
        }

        return $this;
    }

    public function removeIncomingFriendRequest(FriendRequest $incomingFriendRequest): self
    {
        if ($this->incomingFriendRequests->contains($incomingFriendRequest)) {
            $this->incomingFriendRequests->removeElement($incomingFriendRequest);
            // set the owning side to null (unless already changed)
            if ($incomingFriendRequest->getRecipient() === $this) {
                $incomingFriendRequest->setRecipient(null);
            }
        }

        return $this;
    }

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {
        return;
    }

    public function getRoles(): ?array
    {
        return [ 'ROLE_USER' ];
    }
}
