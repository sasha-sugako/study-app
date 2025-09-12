<?php

namespace App\Entity;

use App\Repository\TestResultRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TestResultRepository::class)]
class TestResult
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private array $user_answer = [];

    #[ORM\ManyToOne(inversedBy: 'testResults')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Test $test = null;

    #[ORM\ManyToOne(inversedBy: 'testResults')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Card $card = null;

    #[ORM\Column(type: 'json')]
    private array $correct_answer = [];

    #[ORM\Column]
    private ?int $question_type = null;

    #[ORM\Column]
    private ?int $question_number = null;

    #[ORM\Column(nullable: true)]
    private ?bool $is_correct = null;

    // Return identifier
    public function getId(): ?int
    {
        return $this->id;
    }

    // Return user's answer
    public function getUserAnswer(): array
    {
        return $this->user_answer;
    }

    // Sets user's answer
    public function setUserAnswer(array $user_answer): static
    {
        $this->user_answer = $user_answer;

        return $this;
    }

    // Return test which question belongs to
    public function getTest(): ?Test
    {
        return $this->test;
    }

    // Sets test which question belongs to
    public function setTest(?Test $test): static
    {
        $this->test = $test;

        return $this;
    }

    // Return card with which question is associated
    public function getCard(): ?Card
    {
        return $this->card;
    }

    // Sets card with which question is associated
    public function setCard(?Card $card): static
    {
        $this->card = $card;

        return $this;
    }

    // Return correct answer
    public function getCorrectAnswer(): array
    {
        return $this->correct_answer;
    }

    // Sets correct answer
    public function setCorrectAnswer(array $correct_answer): static
    {
        $this->correct_answer = $correct_answer;

        return $this;
    }

    // Return question type
    public function getQuestionType(): ?int
    {
        return $this->question_type;
    }

    // Sets question type
    public function setQuestionType(int $question_type): static
    {
        $this->question_type = $question_type;

        return $this;
    }

    // Return the number of the question in the test
    public function getQuestionNumber(): ?int
    {
        return $this->question_number;
    }

    // Sets the number of the question in the test
    public function setQuestionNumber(int $question_number): static
    {
        $this->question_number = $question_number;

        return $this;
    }

    // Check if question is correctly answered
    public function isCorrect(): ?bool
    {
        return $this->is_correct;
    }

    // Sets if question was correctly answered
    public function setIsCorrect(?bool $is_correct): static
    {
        $this->is_correct = $is_correct;

        return $this;
    }
}
