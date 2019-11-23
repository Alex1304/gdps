<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LevelReportRepository")
 */
class LevelReport
{
	const SORT_MOST_RECENT_LEVELS = 0;
	const SORT_MOST_REPORTS = 1;
	const SORT_MOST_RECENT_REPORTS = 2;
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Level", inversedBy="levelReports")
     * @ORM\JoinColumn(nullable=false)
     */
    private $level;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $reporterIp;

    /**
     * @ORM\Column(type="datetime")
     */
    private $reportedAt;

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

    public function getReporterIp(): ?string
    {
        return $this->reporterIp;
    }

    public function setReporterIp(string $reporterIp): self
    {
        $this->reporterIp = $reporterIp;

        return $this;
    }

    public function getReportedAt(): ?\DateTimeInterface
    {
        return $this->reportedAt;
    }

    public function setReportedAt(\DateTimeInterface $reportedAt): self
    {
        $this->reportedAt = $reportedAt;

        return $this;
    }
}
