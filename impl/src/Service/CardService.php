<?php
declare(strict_types=1);

namespace App\Service;
use App\Entity\Card;
use App\Entity\Deck;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;

class CardService
{
    public function __construct(
        private EntityManagerInterface $manager,
        private KernelInterface $kernel
    ) {}

    // Associates the given card with the specified deck and persists it to the database by store method.
    public function create(Card $card, Deck $deck): void{
        $card->setDeck($deck);
        $this->store($card);
    }

    // Persists the given card to the database and returns its generated ID.
    public function store(Card $card): ?int
    {
        $this->manager->persist($card);
        $this->manager->flush();
        return $card->getId();
    }

    /* Removes the given card from the database.
       Detaches all associated test results by setting their card reference to null and persisting the changes. */
    public function remove(Card $card): void
    {
        foreach($card->getTestResults() as $result){
            $result->setCard(null);
            $this->manager->persist($result);
        }
        $this->manager->remove($card);
        $this->manager->flush();
    }

    /* Creates a deep copy of the given card and associates it with the specified deck.
       Copies all relevant properties including text and images from the original card.
       Persists and flushes the new card to the database before returning it. */
    public function clone(Card $card, Deck $deck): Card{
        $new_card = new Card();
        $new_card->setDeck($deck);
        $new_card->setFrontSide($card->getFrontSide());
        $new_card->setBackSide($card->getBackSide());
        $new_card->setFrontImage($card->getFrontImage());
        $new_card->setBackImage($card->getBackImage());
        $this->manager->persist($new_card);
        $this->manager->flush();
        return $new_card;
    }

    /* Saves the uploaded image file to the designated public directory and
       sets the image filename to the appropriate side (front or back) of the card
       based on the provided string indicator. */
    public function setImage(Card $card, UploadedFile $file, string $side): void{
        $image = $file->move(
            $this->kernel->getProjectDir().'/public/uploads/img',
            $file->getClientOriginalName()
        );
        if ($side === 'front')
            $card->setFrontImage($image->getBasename());
        else
            $card->setBackImage($image->getBasename());
    }

    // Removes the image reference from the specified side (front or back) of the card by setting it to null.
    public function removeImage(Card $card, string $side): void{
        if ($side === 'front')
            $card->setFrontImage(null);
        else
            $card->setBackImage(null);
    }

    /* Updates the learning schedule of a card based on the provided learn score and current time.
       Adjusts the next study time and score according to spaced repetition logic,
       accounting for the card’s current learn score and learning history.
       Finally, sets the last learned timestamp and persists the updated card. */
    public function learn(Card $card, int $learn_score, \DateTimeImmutable $time): void{
        if ($learn_score === 1){
            $card->setToLearn($time->modify('+1 minute'));
            $card->setLearnScore($learn_score);
        }
        else if ($learn_score === 2){
            $card->setToLearn($time->modify('+1 hour'));
            $card->setLearnScore($learn_score);
        }
        else if ($learn_score === 3){
            if ($card->getLearnScore() === 1 || !$card->getLearnScore()){
                $card->setToLearn($time->modify('+1 hour'));
                $card->setLearnScore(2);
            }
            else if ($card->getLearnScore() === 2){
                $card->setToLearn($time->modify('+1 day'));
                $card->setLearnScore($learn_score);
            }
            else{
                $interval = $card->getToLearn()->diff($card->getLastLearned())->days;
                $card->setToLearn($time->modify("{$interval} days"));
            }
        }
        else{
            if (!$card->getLearnScore()){
                $card->setToLearn($time->modify('+1 day'));
                $card->setLearnScore(3);
            }
            else if ($card->getLearnScore() < 3){
                $card->setLearnScore(3);
                $card->setToLearn($time->modify('+3 day'));
            }
            else{
                $interval = $card->getToLearn()->diff($card->getLastLearned())->days + 1;
                $card->setLearnScore($learn_score);
                $card->setToLearn($time->modify("{$interval} days"));
            }
        }
        $card->setLastLearned($time);
        $this->store($card);
    }
}