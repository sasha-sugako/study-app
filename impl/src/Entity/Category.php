<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[UniqueEntity(fields: ['name'])]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Constraints\NotBlank]
    #[Constraints\Length(min: 1, max: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Deck>
     */
    #[ORM\ManyToMany(targetEntity: Deck::class, mappedBy: 'categories')]
    private Collection $decks;

    public function __construct()
    {
        $this->decks = new ArrayCollection();
    }

    // Return identifier
    public function getId(): ?int
    {
        return $this->id;
    }

    // Return category's name
    public function getName(): ?string
    {
        return $this->name;
    }

    // Set name to the category
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    // Return decks that have the category
    /**
     * @return Collection<int, Deck>
     */
    public function getDecks(): Collection
    {
        return $this->decks;
    }

    // Add deck to the category
    public function addDeck(Deck $deck): static
    {
        if (!$this->decks->contains($deck)) {
            $this->decks->add($deck);
            $deck->addCategory($this);
        }

        return $this;
    }

    // Remove deck from the category
    public function removeDeck(Deck $deck): static
    {
        if ($this->decks->removeElement($deck)) {
            $deck->removeCategory($this);
        }

        return $this;
    }
}
