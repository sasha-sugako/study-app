<?php

namespace App\Entity;

use App\Repository\DeckRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints;

#[ORM\Entity(repositoryClass: DeckRepository::class)]
class Deck
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Constraints\NotBlank]
    #[Constraints\Length(min: 1, max: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?bool $is_private = true;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $about = null;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'decks')]
    private Collection $categories;

    /**
     * @var Collection<int, Card>
     */
    #[ORM\OneToMany(targetEntity: Card::class, mappedBy: 'deck')]
    private Collection $cards;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'child_decks')]
    private ?self $parent = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent')]
    private Collection $child_decks;

    #[ORM\ManyToOne(inversedBy: 'decks')]
    private ?AppUser $owner = null;

    /**
     * @var Collection<int, Review>
     */
    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'deck')]
    private Collection $reviews;

    /**
     * @var Collection<int, Test>
     */
    #[ORM\OneToMany(targetEntity: Test::class, mappedBy: 'deck')]
    private Collection $tests;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->cards = new ArrayCollection();
        $this->child_decks = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->tests = new ArrayCollection();
    }

    // Return identifier
    public function getId(): ?int
    {
        return $this->id;
    }

    // Return name of the deck
    public function getName(): ?string
    {
        return $this->name;
    }

    // Sets name to the deck
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    // Check if the deck is private
    public function isPrivate(): ?bool
    {
        return $this->is_private;
    }

    // Sets privacy of the deck
    public function setIsPrivate(bool $is_private): static
    {
        $this->is_private = $is_private;

        return $this;
    }

    // Return description of the deck
    public function getAbout(): ?string
    {
        return $this->about;
    }

    // Sets description to deck
    public function setAbout(?string $about): static
    {
        $this->about = $about;

        return $this;
    }

    // Return deck's categories
    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    // Add category to deck
    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    // Remove category from deck
    public function removeCategory(Category $category): static
    {
        $this->categories->removeElement($category);

        return $this;
    }

    // Remove all deck's categories
    public function removeAllCategories(): static{
        foreach($this->getCategories() as $category){
            $this->removeCategory($category);
        }
        return $this;
    }

    // Return all cards that belongs to the deck
    /**
     * @return Collection<int, Card>
     */
    public function getCards(): Collection
    {
        return $this->cards;
    }

    // Add card to the deck
    public function addCard(Card $card): static
    {
        if (!$this->cards->contains($card)) {
            $this->cards->add($card);
            $card->setDeck($this);
        }

        return $this;
    }

    // Remove card from deck
    public function removeCard(Card $card): static
    {
        if ($this->cards->removeElement($card)) {
            // set the owning side to null (unless already changed)
            if ($card->getDeck() === $this) {
                $card->setDeck(null);
            }
        }

        return $this;
    }

    // Get deck's parent deck if exists
    public function getParent(): ?Deck
    {
        return $this->parent;
    }

    // Set parent deck to the deck
    public function setParent(?self $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    // Get all child decks of the deck
    /**
     * @return Collection<int, self>
     */
    public function getChildDecks(): Collection
    {
        return $this->child_decks;
    }

    // Add child deck to the deck
    public function addChildDeck(self $childDeck): static
    {
        if (!$this->child_decks->contains($childDeck)) {
            $this->child_decks->add($childDeck);
            $childDeck->setParent($this);
        }

        return $this;
    }

    // Remove child deck from the deck
    public function removeChildDeck(self $childDeck): static
    {
        if ($this->child_decks->removeElement($childDeck)) {
            // set the owning side to null (unless already changed)
            if ($childDeck->getParent() === $this) {
                $childDeck->setParent(null);
            }
        }

        return $this;
    }

    // Return deck's owner
    public function getOwner(): ?AppUser
    {
        return $this->owner;
    }

    // Sets owner to the deck
    public function setOwner(?AppUser $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    // Get deck's reviews
    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    // Add review to the deck
    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setDeck($this);
        }

        return $this;
    }

    // Remove review from the deck
    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getDeck() === $this) {
                $review->setDeck(null);
            }
        }

        return $this;
    }

    // Return total deck's score from existing reviews
    public function getTotalRate(): float{
        $reviews = $this->getReviews();
        $count = count($reviews);
        if ($count === 0)
            return 0;
        $score = array_sum(array_map(fn($r) => $r->getRate(), $reviews->toArray()));
        return $score / $count;
    }

    // Return tests, which are created from the deck
    /**
     * @return Collection<int, Test>
     */
    public function getTests(): Collection
    {
        return $this->tests;
    }

    // Add test to the deck
    public function addTest(Test $test): static
    {
        if (!$this->tests->contains($test)) {
            $this->tests->add($test);
            $test->setDeck($this);
        }

        return $this;
    }

    // Remove test from the deck
    public function removeTest(Test $test): static
    {
        if ($this->tests->removeElement($test)) {
            // set the owning side to null (unless already changed)
            if ($test->getDeck() === $this) {
                $test->setDeck(null);
            }
        }

        return $this;
    }

    // Return count of cards that were studied at least once
    public function countOfStudiedCards(): int{
        $studied_cards = array_filter($this->cards->toArray(), fn($card) => $card->getLastLearned() !== null);
        return count($studied_cards);
    }

    // Return count of cards that must be studied
    public function countOfCardsToStudy(): int{
        $studied_cards = array_filter($this->cards->toArray(), fn($card) => $card->canStudyNow());
        return count($studied_cards);
    }
}
