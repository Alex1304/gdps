<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FriendRequestRepository")
 */
class FriendRequest
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $message;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Account", inversedBy="outgoingFriendRequests")
     * @ORM\JoinColumn(nullable=false)
     */
    private $sender;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Account", inversedBy="incomingFriendRequests")
     * @ORM\JoinColumn(nullable=false)
     */
    private $recipient;

    /**
     * @ORM\Column(type="datetime")
     */
    private $madeAt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isUnread;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getSender(): ?Account
    {
        return $this->sender;
    }

    public function setSender(?Account $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function getRecipient(): ?Account
    {
        return $this->recipient;
    }

    public function setRecipient(?Account $recipient): self
    {
        $this->recipient = $recipient;

        return $this;
    }

    public function getMadeAt(): ?\DateTimeInterface
    {
        return $this->madeAt;
    }

    public function setMadeAt(\DateTimeInterface $madeAt): self
    {
        $this->madeAt = $madeAt;

        return $this;
    }

    public function getIsUnread(): ?bool
    {
        return $this->isUnread;
    }

    public function setIsUnread(bool $isUnread): self
    {
        $this->isUnread = $isUnread;

        return $this;
    }
}
