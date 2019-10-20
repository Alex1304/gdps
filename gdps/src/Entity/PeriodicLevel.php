<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PeriodicLevelRepository")
 *
 * @Serializer\ExclusionPolicy("ALL")
 */
class PeriodicLevel
{
    const DAILY = 0;
    const WEEKLY = 1;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Services\PeriodicIdGenerator")
     * @ORM\Column(type="integer")
     *
     * @Serializer\Expose
     * @Serializer\SerializedName("index")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Level")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Serializer\Expose
     */
    private $level;

    /**
     * @ORM\Column(type="integer")
     *
     * @Serializer\Expose
     */
    private $type;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Serializer\Expose
     */
    private $periodStart;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Serializer\Expose
     */
    private $periodEnd;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLevel(): ?Level
    {
        return $this->level;
    }

    public function setLevel(?Level $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPeriodStart(): ?\DateTimeInterface
    {
        return $this->periodStart;
    }

    public function setPeriodStart(\DateTimeInterface $periodStart): self
    {
        if ($periodStart instanceof \DateTimeImmutable) {
            $this->periodStart = new \DateTime();
            $this->periodStart->setTimestamp($periodStart->getTimestamp());
        } else {
            $this->periodStart = $periodStart;
        }

        return $this;
    }

    public function getPeriodEnd(): ?\DateTimeInterface
    {
        return $this->periodEnd;
    }

    public function setPeriodEnd(\DateTimeInterface $periodEnd): self
    {
        if ($periodEnd instanceof \DateTimeImmutable) {
            $this->periodEnd = new \DateTime();
            $this->periodEnd->setTimestamp($periodEnd->getTimestamp());
        } else {
            $this->periodEnd = $periodEnd;
        }

        return $this;
    }

    public static function offsetForType($type): int
    {
        switch ($type) {
            case self::WEEKLY:
                return 100000;
            default:
                return 0;
        }
    }

    public static function dateStartForType($type): \DateTime
    {
        switch ($type) {
            case self::WEEKLY:
                return new \DateTime("Monday this week");
            default:
                return new \DateTime("today");
        }
    }

    public static function intervalForType($type): \DateInterval
    {
        switch ($type) {
            case self::WEEKLY:
                return \DateInterval::createFromDateString('1 week');
            default:
                return \DateInterval::createFromDateString('1 day');
        }
    }
}
