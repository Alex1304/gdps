<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PrivateMessageRepository")
 */
class PrivateMessage
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Account", inversedBy="privateMessages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Account", inversedBy="incomingPrivateMessages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $recipient;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $subject;

    /**
     * @ORM\Column(type="text")
     */
    private $body;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isUnread;

    /**
     * @ORM\Column(type="datetime")
     */
    private $postedAt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $authorHasDeleted;

    /**
     * @ORM\Column(type="boolean")
     */
    private $recipientHasDeleted;

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

    public function getRecipient(): ?Account
    {
        return $this->recipient;
    }

    public function setRecipient(?Account $recipient): self
    {
        $this->recipient = $recipient;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

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

    public function getPostedAt(): ?\DateTimeInterface
    {
        return $this->postedAt;
    }

    public function setPostedAt(\DateTimeInterface $postedAt): self
    {
        $this->postedAt = $postedAt;

        return $this;
    }

    public function getAuthorHasDeleted(): ?bool
    {
        return $this->authorHasDeleted;
    }

    public function setAuthorHasDeleted(bool $authorHasDeleted): self
    {
        $this->authorHasDeleted = $authorHasDeleted;

        return $this;
    }

    public function getRecipientHasDeleted(): ?bool
    {
        return $this->recipientHasDeleted;
    }

    public function setRecipientHasDeleted(bool $recipientHasDeleted): self
    {
        $this->recipientHasDeleted = $recipientHasDeleted;

        return $this;
    }
}
