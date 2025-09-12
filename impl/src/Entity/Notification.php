<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Constraints\Length(min: 1, max: 255)]
    private ?string $message = null;

    #[ORM\Column]
    private ?bool $is_read = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\ManyToOne(inversedBy: 'notifications')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AppUser $person_to_notificate = null;

    // Returns identifier
    public function getId(): ?int
    {
        return $this->id;
    }

    // Return message of the notification
    public function getMessage(): ?string
    {
        return $this->message;
    }

    // Sets message to notification
    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    // Check if notification was read
    public function isRead(): ?bool
    {
        return $this->is_read;
    }

    // Sets if notification was read
    public function setIsRead(bool $is_read): static
    {
        $this->is_read = $is_read;

        return $this;
    }

    // Return time when notification was created
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    // Sets time when notification was created
    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    // Return person who is notificated
    public function getPersonToNotificate(): ?AppUser
    {
        return $this->person_to_notificate;
    }

    // Sets person to notificate
    public function setPersonToNotificate(?AppUser $person_to_notificate): static
    {
        $this->person_to_notificate = $person_to_notificate;

        return $this;
    }
}
