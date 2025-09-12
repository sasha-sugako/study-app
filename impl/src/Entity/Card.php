<?php

namespace App\Entity;

use App\Repository\CardRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CardRepository::class)]
class Card
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $front_side = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $back_side = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $to_learn = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $last_learned = null;

    #[ORM\Column(nullable: true)]
    private ?int $learn_score = null;

    #[ORM\ManyToOne(inversedBy: 'cards')]
    private ?Deck $deck = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $front_image = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $back_image = null;

    #[ORM\OneToMany(targetEntity: TestResult::class, mappedBy: 'card')]
    private Collection $testResults;

    public function __construct()
    {
        $this->testResults = new ArrayCollection();
    }

    // Return identifier
    public function getId(): ?int
    {
        return $this->id;
    }

    // Return text from the front side of card
    public function getFrontSide(): string
    {
        return $this->front_side;
    }

    // Sets text to front side of card
    public function setFrontSide(string $front_side): static
    {
        $this->front_side = $front_side;

        return $this;
    }

    // Return text from the back side of card
    public function getBackSide(): string
    {
        return $this->back_side;
    }

    // Sets text to back side of card
    public function setBackSide(string $back_side): static
    {
        $this->back_side = $back_side;

        return $this;
    }

    // Return time when card must be learned
    public function getToLearn(): ?\DateTimeInterface
    {
        return $this->to_learn;
    }

    // Sets time when card must be learned
    public function setToLearn(?\DateTimeInterface $to_learn): static
    {
        $this->to_learn = $to_learn;

        return $this;
    }

    // Return time when card was learned for the last time
    public function getLastLearned(): ?\DateTimeImmutable
    {
        return $this->last_learned;
    }

    // Sets time when card was learned for the last time
    public function setLastLearned(?\DateTimeImmutable $last_learned): void
    {
        $this->last_learned = $last_learned;
    }

    // Return score for last learning of card
    public function getLearnScore(): ?int
    {
        return $this->learn_score;
    }

    // Sets score for last learning of card
    public function setLearnScore(?int $learn_score): static
    {
        $this->learn_score = $learn_score;

        return $this;
    }

    // Return deck that the card belongs to
    public function getDeck(): ?Deck
    {
        return $this->deck;
    }

    // Sets deck that the card belongs to
    public function setDeck(?Deck $deck): static
    {
        $this->deck = $deck;

        return $this;
    }

    // Get image on the front side of card if exists
    public function getFrontImage(): ?string
    {
        return $this->front_image;
    }

    // Sets image to the front side of card
    public function setFrontImage(?string $front_image): static
    {
        $this->front_image = $front_image;

        return $this;
    }

    // Get image on the back side of card if exists
    public function getBackImage(): ?string
    {
        return $this->back_image;
    }

    // Sets image to the back side of card
    public function setBackImage(?string $back_image): static
    {
        $this->back_image = $back_image;

        return $this;
    }

    // Return questions that are associated with card
    /**
     * @return Collection<int, TestResult>
     */
    public function getTestResults(): Collection
    {
        return $this->testResults;
    }

    // Add question that is associated with card
    public function addTestResult(TestResult $testResult): static
    {
        if (!$this->testResults->contains($testResult)) {
            $this->testResults->add($testResult);
            $testResult->setCard($this);
        }

        return $this;
    }

    // Remove question associated with card
    public function removeTestResult(TestResult $testResult): static
    {
        if ($this->testResults->removeElement($testResult)) {
            if ($testResult->getCard() === $this) {
                $testResult->setCard(null);
            }
        }

        return $this;
    }

    // Checks if card can be studied now
    public function canStudyNow(): bool{
        if ($this->last_learned){
            return $this->to_learn <= new \DateTimeImmutable();
        }
        return true;
    }
}
