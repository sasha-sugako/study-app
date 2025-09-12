<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['login'])]
#[UniqueEntity(fields: ['email'])]
class AppUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Constraints\NotBlank]
    #[Constraints\Length(min: 1, max: 255)]
    private ?string $login = null;

    #[ORM\Column(length: 255)]
    #[Constraints\NotBlank]
    #[Constraints\Length(min: 1, max: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Constraints\NotBlank]
    #[Constraints\Length(min: 1, max: 255)]
    private ?string $email = null;

    #[ORM\Column]
    private ?bool $is_verified = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $last_active = null;

    #[ORM\Column]
    private ?int $days_without_break = 0;

    /**
     * @var Collection<int, Deck>
     */
    #[ORM\OneToMany(targetEntity: Deck::class, mappedBy: 'owner')]
    private Collection $decks;

    /**
     * @var Collection<int, Review>
     */
    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'reviewed_by')]
    private Collection $reviews;

    /**
     * @var Collection<int, Goal>
     */
    #[ORM\OneToMany(targetEntity: Goal::class, mappedBy: 'owner')]
    private Collection $goals;

    /**
     * @var Collection<int, Bonus>
     */
    #[ORM\OneToMany(targetEntity: Bonus::class, mappedBy: 'owner')]
    private Collection $bonuses;

    /**
     * @var Collection<int, Notification>
     */
    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'person_to_notificate')]
    private Collection $notifications;

    public function __construct()
    {
        $this->decks = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->goals = new ArrayCollection();
        $this->bonuses = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

    // Returns identifier
    public function getId(): ?int
    {
        return $this->id;
    }

    // Returns user login
    public function getLogin(): ?string
    {
        return $this->login;
    }

    // Sets the user's username
    public function setLogin(string $login): static
    {
        $this->login = $login;

        return $this;
    }

    // Returns user's password
    public function getPassword(): ?string
    {
        return $this->password;
    }

    // Sets user's password
    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    // Returns decks that are owned by user
    /**
     * @return Collection<int, Deck>
     */
    public function getDecks(): Collection
    {
        return $this->decks;
    }

    // Add deck to user's deck
    public function addDeck(Deck $deck): static
    {
        if (!$this->decks->contains($deck)) {
            $this->decks->add($deck);
            $deck->setOwner($this);
        }

        return $this;
    }

    // Remove deck from user's decks
    public function removeDeck(Deck $deck): static
    {
        if ($this->decks->removeElement($deck)) {
            // set the owning side to null (unless already changed)
            if ($deck->getOwner() === $this) {
                $deck->setOwner(null);
            }
        }

        return $this;
    }

    // Get user's reviews to decks
    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    // Add user's review
    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setReviewedBy($this);
        }

        return $this;
    }

    // Remove user's review
    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getReviewedBy() === $this) {
                $review->setReviewedBy(null);
            }
        }

        return $this;
    }

    // Get roles for specific user
    public function getRoles(): array
    {
        if ($this->isAdmin())
            return ['ROLE_ADMIN', 'ROLE_USER'];
        return ['ROLE_USER'];
    }

    // Checks if user with specific username is admin
    public function isAdmin(): bool{
        return $this->login === 'admin';
    }

    // Removes sensitive data from the user object
    public function eraseCredentials(): void
    {}

    // Returns user identifier for authentication
    public function getUserIdentifier(): string
    {
        return $this->login;
    }

    // Return user's e-mail
    public function getEmail(): ?string
    {
        return $this->email;
    }

    // Sets specified email to user
    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    // Check if user is verified
    public function isVerified(): ?bool
    {
        return $this->is_verified;
    }

    // Sets verification for the user
    public function setIsVerified(bool $is_verified): static
    {
        $this->is_verified = $is_verified;

        return $this;
    }

    // Return user's goals
    /**
     * @return Collection<int, Goal>
     */
    public function getGoals(): Collection
    {
        return $this->goals;
    }

    // Add goal to user
    public function addGoal(Goal $goal): static
    {
        if (!$this->goals->contains($goal)) {
            $this->goals->add($goal);
            $goal->setOwner($this);
        }

        return $this;
    }

    // Remove goal from user's goals
    public function removeGoal(Goal $goal): static
    {
        if ($this->goals->removeElement($goal)) {
            // set the owning side to null (unless already changed)
            if ($goal->getOwner() === $this) {
                $goal->setOwner(null);
            }
        }

        return $this;
    }

    // Get actual user's goal if exists
    public function getActualGoal(): ?Goal{
        $actual_goals = array_values(array_filter(
            $this->goals->toArray(),
            fn($goal) => $goal->isCurrent()
        ));
        return $actual_goals[0] ?? null;
    }

    // Get previous user's goals
    public function getPreviousGoals(): ?array{
        return array_filter($this->goals->toArray(), fn($goal) => !$goal->isCurrent());
    }

    // Get user's bonuses
    /**
     * @return Collection<int, Bonus>
     */
    public function getBonuses(): Collection
    {
        return $this->bonuses;
    }

    // Add bonus to user
    public function addBonus(Bonus $bonus): static
    {
        if (!$this->bonuses->contains($bonus)) {
            $this->bonuses->add($bonus);
            $bonus->setOwner($this);
        }

        return $this;
    }

    // Remove bonus form user's bonuses
    public function removeBonus(Bonus $bonus): static
    {
        if ($this->bonuses->removeElement($bonus)) {
            // set the owning side to null (unless already changed)
            if ($bonus->getOwner() === $this) {
                $bonus->setOwner(null);
            }
        }

        return $this;
    }

    // Get user's notifications
    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    // Add notification to user
    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setPersonToNotificate($this);
        }

        return $this;
    }

    // Remove notification from user's notifications
    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getPersonToNotificate() === $this) {
                $notification->setPersonToNotificate(null);
            }
        }

        return $this;
    }

    // Gets the date of the user's last activity
    public function getLastActive(): ?\DateTimeImmutable
    {
        return $this->last_active;
    }

    // Sets the date of the user's last activity
    public function setLastActive(\DateTimeImmutable $last_active): static
    {
        $this->last_active = $last_active;

        return $this;
    }

    // Gets the number of days that the user studies continuously
    public function getDaysWithoutBreak(): ?int
    {
        return $this->days_without_break;
    }

    // Sets the number of days that the user studies continuously
    public function setDaysWithoutBreak(int $days_without_break): static
    {
        $this->days_without_break = $days_without_break;

        return $this;
    }

    // Increase the number of days that the user studies continuously by specified count of days
    public function increaseDaysWithoutBreak(int $days = 1): static{
        $this->days_without_break += $days;
        return $this;
    }
}
