<?php
declare(strict_types=1);

namespace App\Service;
use App\Entity\AppUser;
use App\Entity\Category;
use App\Entity\Deck;
use Doctrine\ORM\EntityManagerInterface;
class DeckService
{
    public function __construct(
        private EntityManagerInterface $manager,
        private CategoryService        $categoryService,
        private CardService            $cardService,
        private ReviewService          $reviewService
    ) {}

    /* Persists the given deck to the database.
       Optionally sets the owner if a user is provided.
       Returns the ID of the stored deck. */
    public function store(Deck $deck, ?AppUser $user = null): ?int
    {
        if ($user)
            $deck->setOwner($user);
        $this->manager->persist($deck);
        $this->manager->flush();
        return $deck->getId();
    }

    /* Removes the given deck and all its related entities (cards, reviews, tests).
       Also detaches any child decks by setting their parent to null.
       All changes are persisted to the database. */
    public function remove(Deck $deck): void
    {
        foreach ($deck->getChildDecks() as $childDeck){
            $childDeck->setParent(null);
            $this->manager->persist($childDeck);
        }
        foreach ($deck->getCards() as $card){
            $this->manager->remove($card);
        }
        foreach ($deck->getReviews() as $review){
            $this->manager->remove($review);
        }
        foreach ($deck->getTests() as $test){
            $this->manager->remove($test);
        }
        $this->manager->remove($deck);
        $this->manager->flush();
    }

    /* Creates a private clone of the given deck for the specified user.
       Copies basic deck properties and associated categories, sets the original as parent,
       persists the new deck, and then clones all cards into it. */
    public function clone(Deck $deck, AppUser $user): Deck
    {
        $new_deck = new Deck();
        $new_deck->setName($deck->getName());
        $new_deck->setAbout($deck->getAbout());
        $new_deck->setIsPrivate(true);
        $new_deck->setParent($deck);
        $new_deck->setOwner($user);
        foreach ($deck->getCategories() as $category){
            $new_deck->addCategory($category);
        }
        $this->manager->persist($new_deck);
        foreach ($deck->getCards() as $card){
            $new_card = $this->cardService->clone($card, $new_deck);
            $new_deck->addCard($new_card);
        }
        $this->manager->persist($new_deck);
        $this->manager->flush();
        return $new_deck;
    }

    /* Publishes a private deck by creating a new public parent deck.
       Copies basic deck properties and associated categories,
       sets the original deck as a child and updates the parent-child relationship.
       Clones all cards and reassigns reviews to the new public deck. */
    public function publicate(Deck $deck): Deck{
        $new_deck = new Deck();
        $new_deck->setName($deck->getName());
        $new_deck->setAbout($deck->getAbout());
        $new_deck->setIsPrivate(false);
        $new_deck->setOwner($deck->getOwner());
        $new_deck->addChildDeck($deck);
        foreach ($deck->getCategories() as $category){
            $new_deck->addCategory($category);
        }
        $deck->setParent($new_deck);
        $this->manager->persist($new_deck);
        $this->manager->persist($deck);
        foreach ($deck->getCards() as $card){
            $new_card = $this->cardService->clone($card, $new_deck);
            $new_deck->addCard($new_card);
        }
        foreach($deck->getReviews() as $review){
            $this->reviewService->store($review, $new_deck, $review->getReviewedBy());
        }
        $this->manager->persist($new_deck);
        $this->manager->flush();
        return $new_deck;
    }

    /* Creates a new category with the given name and assigns it to the specified deck.
       Persists both the new category and the updated deck. */
    public function addCategory(Deck $deck, string $name): Category{
        $category = new Category();
        $category->setName($name);
        $category->addDeck($deck);
        $this->categoryService->store($category);
        $deck->addCategory($category);
        $this->store($deck);
        return $category;
    }
}