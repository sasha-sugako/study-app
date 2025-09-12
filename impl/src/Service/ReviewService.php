<?php
declare(strict_types=1);

namespace App\Service;
use App\Entity\Deck;
use App\Entity\Review;
use App\Entity\AppUser;
use Doctrine\ORM\EntityManagerInterface;
class ReviewService
{
    public function __construct(
        private EntityManagerInterface $manager
    ) {}

    /* Stores a new review for the given deck and user.
       If the deck has a parent, recursively stores the review for the parent deck as well.
       Persists the review to the database and ensures it's saved properly. */
    public function store(Review $review, Deck $deck, AppUser $user): void
    {
        $new_review = new Review();
        $new_review->setRate($review->getRate());
        $new_review->setDescription($review->getDescription());
        $new_review->setDeck($deck);
        $new_review->setReviewedBy($user);
        $this->manager->persist($new_review);
        if ($deck->getParent()){
            $this->store($review, $deck->getParent(), $user);
        }
        else {
            $this->manager->flush();
        }
    }

    // Removes the given Review entity from the database.
    public function remove(Review $review): void
    {
        $this->manager->remove($review);
        $this->manager->flush();
    }
}