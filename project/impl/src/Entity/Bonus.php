<?php

namespace App\Entity;

use App\Repository\BonusRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints;

#[ORM\Entity(repositoryClass: BonusRepository::class)]
class Bonus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Constraints\Length(min: 1, max: 100)]
    private ?string $type = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $grantedAt = null;

    #[ORM\Column]
    private ?bool $is_used = false;

    #[ORM\ManyToOne(inversedBy: 'bonuses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AppUser $owner = null;

    // Return identifier
    public function getId(): ?int
    {
        return $this->id;
    }

    // Get type of bonus
    public function getType(): ?string
    {
        return $this->type;
    }

    // Set type to bonus
    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    // Get the date when bonus was granted
    public function getGrantedAt(): ?\DateTimeImmutable
    {
        return $this->grantedAt;
    }

    // Set the date when bonus was granted
    public function setGrantedAt(\DateTimeImmutable $grantedAt): static
    {
        $this->grantedAt = $grantedAt;

        return $this;
    }

    // Check if bonus is used
    public function isUsed(): ?bool
    {
        return $this->is_used;
    }

    // Sets if bonus is used
    public function setIsUsed(bool $is_used): static
    {
        $this->is_used = $is_used;

        return $this;
    }

    // Get bonuses owner
    public function getOwner(): ?AppUser
    {
        return $this->owner;
    }

    // Sets owner to bonus
    public function setOwner(?AppUser $owner): static
    {
        $this->owner = $owner;

        return $this;
    }
}
