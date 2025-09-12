<?php

namespace App\Entity;

use App\Repository\GoalRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GoalRepository::class)]
class Goal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $start_date = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $end_date = null;

    #[ORM\Column(nullable: true)]
    private ?int $target_cards = null;

    #[ORM\Column(nullable: true)]
    private ?int $achieved_cards = null;

    #[ORM\Column(nullable: true)]
    private ?int $target_tests = null;

    #[ORM\Column(nullable: true)]
    private ?int $achieved_tests = null;

    #[ORM\Column]
    private ?bool $completed = false;

    #[ORM\Column]
    private ?bool $bonus_granted = false;

    #[ORM\ManyToOne(inversedBy: 'goals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AppUser $owner = null;

    #[ORM\Column]
    private ?bool $is_current = false;

    // Return identifier
    public function getId(): ?int
    {
        return $this->id;
    }

    // Return time when goal was started
    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->start_date;
    }

    // Sets time when goal would be started
    public function setStartDate(\DateTimeImmutable $start_date): static
    {
        $this->start_date = $start_date;

        return $this;
    }

    // Return time when goal would be ended
    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->end_date;
    }

    // Sets time when goal would be ended
    public function setEndDate(\DateTimeImmutable $end_date): static
    {
        $this->end_date = $end_date;

        return $this;
    }

    // Get count of target cards to study if exists
    public function getTargetCards(): ?int
    {
        return $this->target_cards;
    }

    // Sets count of target cards to study
    public function setTargetCards(?int $target_cards): static
    {
        $this->target_cards = $target_cards;

        return $this;
    }

    // Return achieved cards if exists
    public function getAchievedCards(): ?int
    {
        return $this->achieved_cards;
    }

    // Sets achieved cards
    public function setAchievedCards(?int $achieved_cards): static
    {
        $this->achieved_cards = $achieved_cards;

        return $this;
    }

    // Get count of target tests to finish if exists
    public function getTargetTests(): ?int
    {
        return $this->target_tests;
    }

    // Sets count of target tests to finish
    public function setTargetTests(?int $target_tests): static
    {
        $this->target_tests = $target_tests;

        return $this;
    }

    // Return achieved tests if exists
    public function getAchievedTests(): ?int
    {
        return $this->achieved_tests;
    }

    // Sets achieved tests
    public function setAchievedTests(?int $achieved_tests): static
    {
        $this->achieved_tests = $achieved_tests;

        return $this;
    }

    // Check if goal is completed
    public function isCompleted(): ?bool
    {
        return $this->completed;
    }

    // Sets completion of goal
    public function setCompleted(bool $completed): static
    {
        $this->completed = $completed;

        return $this;
    }

    // Check if bonus was granted
    public function isBonusGranted(): ?bool
    {
        return $this->bonus_granted;
    }

    // Sets if bonus was granted
    public function setBonusGranted(bool $bonus_granted): static
    {
        $this->bonus_granted = $bonus_granted;

        return $this;
    }

    // Return goal's owner
    public function getOwner(): ?AppUser
    {
        return $this->owner;
    }

    // Sets owner to goal
    public function setOwner(?AppUser $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    // Increase achieved cards and check if goal is completed
    public function increaseAchievedCards(): static{
        $this->achieved_cards++;
        if ($this->achieved_cards >= $this->target_cards){
            if ($this->target_tests && $this->achieved_tests >= $this->target_tests)
                $this->completed = true;
            else if (! $this->target_tests)
                $this->completed = true;
        }
        return $this;
    }

    // Increase achieved tests and check if goal is completed
    public function increaseAchievedTests(): static{
        $this->achieved_tests++;
        if ($this->achieved_tests >= $this->target_tests){
            if ($this->target_cards && $this->achieved_cards >= $this->target_cards)
                $this->completed = true;
            else if (! $this->target_cards)
                $this->completed = true;
        }
        return $this;
    }

    // Check if goal is current
    public function isCurrent(): ?bool
    {
        return $this->is_current;
    }

    // Sets if goal is current
    public function setIsCurrent(bool $is_current): static
    {
        $this->is_current = $is_current;

        return $this;
    }
}
