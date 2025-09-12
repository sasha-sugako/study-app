<?php

namespace App\Entity;

use App\Repository\TestRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TestRepository::class)]
class Test
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $started_at = null;

    #[ORM\Column]
    private ?int $number_of_questions = null;

    #[ORM\Column]
    private ?int $qurrent_question = 0;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $finished_at = null;

    #[ORM\ManyToOne(inversedBy: 'tests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Deck $deck = null;

    /**
     * @var Collection<int, TestResult>
     */
    #[ORM\OneToMany(targetEntity: TestResult::class, mappedBy: 'test')]
    private Collection $testResults;

    #[ORM\Column(type: 'json')]
    #[Constraints\Count(
        min: 1,
        minMessage: 'Musíte vybrat alespoň jednu možnost.'
    )]
    private array $types_of_questions = [];

    public function __construct()
    {
        $this->started_at = new \DateTimeImmutable();
        $this->testResults = new ArrayCollection();
    }

    // Return identifier
    public function getId(): ?int
    {
        return $this->id;
    }

    // Return time when test was started
    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->started_at;
    }

    // Sets time when test was started
    public function setStartedAt(\DateTimeImmutable $started_at): static
    {
        $this->started_at = $started_at;

        return $this;
    }

    // Return deck which test belongs to
    public function getDeck(): ?Deck
    {
        return $this->deck;
    }

    // Set deck to test
    public function setDeck(?Deck $deck): static
    {
        $this->deck = $deck;

        return $this;
    }

    // Return test's questions
    /**
     * @return Collection<int, TestResult>
     */
    public function getTestResults(): Collection
    {
        return $this->testResults;
    }

    // Return actual question
    public function getActualQuestion(): ?TestResult{
        if ($this->qurrent_question === $this->number_of_questions)
            return null;
        $actual_question =  array_filter($this->testResults->toArray(),
            fn($question) => $question->getQuestionNumber() === $this->qurrent_question);
        if (empty($actual_question))
            return null;
        return reset($actual_question);
    }

    // Check if test has question with associated card and type of question
    public function hasSameQuestion(Card $card, int $type): bool{
        $result = array_filter($this->testResults->toArray(), fn($question) =>
            $question->getCard() === $card && $question->getQuestionType() === $type);
        return !empty($result);
    }

    // Add question to test
    public function addTestResult(TestResult $testResult): static
    {
        if (!$this->testResults->contains($testResult)) {
            $this->testResults->add($testResult);
            $testResult->setTest($this);
        }

        return $this;
    }

    // Remove question from test
    public function removeTestResult(TestResult $testResult): static
    {
        if ($this->testResults->removeElement($testResult)) {
            // set the owning side to null (unless already changed)
            if ($testResult->getTest() === $this) {
                $testResult->setTest(null);
            }
        }

        return $this;
    }

    // Get count of questions
    public function getNumberOfQuestions(): ?int
    {
        return $this->number_of_questions;
    }

    // Sets count of questions
    public function setNumberOfQuestions(int $number_of_questions): static
    {
        $this->number_of_questions = $number_of_questions;

        return $this;
    }

    // Return types of questions
    public function getTypesOfQuestions(): array
    {
        return $this->types_of_questions;
    }

    // Sets types of questions
    public function setTypesOfQuestions(array $types_of_questions): static
    {
        $this->types_of_questions = $types_of_questions;

        return $this;
    }

    // Return number of actual question
    public function getQurrentQuestion(): ?int
    {
        return $this->qurrent_question;
    }

    // Sets number of actual question
    public function setQurrentQuestion(int $qurrent_question): static
    {
        $this->qurrent_question = $qurrent_question;

        return $this;
    }

    // Return time when test was finished
    public function getFinishedAt(): ?\DateTimeImmutable
    {
        return $this->finished_at;
    }

    // Sets time when test was finished
    public function setFinishedAt(?\DateTimeImmutable $finished_at): static
    {
        $this->finished_at = $finished_at;

        return $this;
    }
}
