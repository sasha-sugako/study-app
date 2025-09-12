<?php

namespace App\Entity;

use App\Repository\ReviewRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $rate = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Constraints\Length(min: 1, max: 255)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Deck $deck = null;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AppUser $reviewed_by = null;

    // Return identifier
    public function getId(): ?int
    {
        return $this->id;
    }

    // Return score of review
    public function getRate(): ?int
    {
        return $this->rate;
    }

    // Sets score to review
    public function setRate(int $rate): static
    {
        $this->rate = $rate;

        return $this;
    }

    // Return description of review if exists
    public function getDescription(): ?string
    {
        return $this->description;
    }

    // Sets description to review
    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    // Return deck review belongs to
    public function getDeck(): ?Deck
    {
        return $this->deck;
    }

    // Sets deck review belongs to
    public function setDeck(?Deck $deck): static
    {
        $this->deck = $deck;

        return $this;
    }

    // Return person who reviewed
    public function getReviewedBy(): ?AppUser
    {
        return $this->reviewed_by;
    }

    // Sets person who reviewed
    public function setReviewedBy(?AppUser $reviewed_by): static
    {
        $this->reviewed_by = $reviewed_by;

        return $this;
    }
}
