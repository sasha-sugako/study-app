<?php

namespace App\Repository;

use App\Entity\AppUser;
use App\Entity\Card;
use App\Entity\Deck;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Card>
 */
class CardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Card::class);
    }

    /* Finds and returns all cards from the given deck that are either not studied
       or are due for review at or before the current time. */
    public function findCardsToStudy(Deck $deck): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.deck = :deck')
            ->andWhere('c.to_learn is NULL OR
                (c.to_learn <= :now)')
            ->setParameter('deck', $deck)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getResult();
    }

    /* Returns the number of cards in a given deck that have been studied at least once.
       It does so by querying the database for all cards associated with the specified deck
       that have a non-null last_learned timestamp, indicating they have been learned before. */
    public function countOfStudied(Deck $deck): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.deck = :deck')
            ->andWhere('c.last_learned is not NULL')
            ->setParameter('deck', $deck)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /* Retrieves a list of cards from a given deck that have been studied at least once.
       The cards are filtered based on the last_learned timestamp, which indicates that they have been studied.
       If an excludedCard is provided, that specific card will be excluded from the result.
       Additionally, cards with the same back side are also excluded.
       The method returns an array of the matching cards. */
    public function findStudied(Deck $deck, ?Card $excludedCard = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.deck = :deck')
            ->andWhere('c.last_learned is not NULL')
            ->setParameter('deck', $deck);
        if ($excludedCard) {
            $qb->andWhere('c != :excludedCard')
                ->setParameter('excludedCard', $excludedCard)
                ->andWhere('c.back_side != :excludedBack')
                ->setParameter('excludedBack', $excludedCard->getBackSide());
        }
        return $qb->getQuery()->getResult();
    }

    /* Retrieves a random set of studied cards from a given deck,
       with optional filtering by card type and previously used cards.
       Gets all studied cards from the deck, then filters out cards that have already been used
       for a specific question type if provided. The remaining cards are shuffled,
       and a subset of them, based on the limit, is returned. */
    public function getRandomCards(Deck $deck, int $limit, ?int $type = null,
                                   ?Card $card = null, ?array $used_questions = null): array
    {
        $cards = $this->findStudied($deck, $card);
        if ($type){
            $used_cards = array_map(
                fn($q) => $q->getCard()->getId(),
                array_filter($used_questions, fn($q) => $q->getQuestionType() === $type)
            );
            $cards = array_filter($cards, fn($card) => !in_array($card->getId(), $used_cards));
        }
        shuffle($cards);
        return array_slice($cards, 0, $limit);
    }

    /* Retrieves four random back sides of cards from a given deck, excluding a specific card.
       Fetches all studied cards from the deck, shuffles them, and selects three random cards.
       Collects the back sides of these cards, adds the back side of the specified card, and
       shuffles the answers before returning them. */
    public function get4RandomBacks(Deck $deck, Card $card): array
    {
        $cards = $this->findStudied($deck, $card);
        shuffle($cards);
        $cards_4 = array_slice($cards, 0, 3);
        $answers = array_map(fn($c) => $c->getBackSide(), $cards_4);
        $answers[] = $card->getBackSide();
        shuffle($answers);
        return $answers;
    }

    // Counts the number of studied cards in a given deck that were learned on a specific day.
    public function countOfStudiedPerDay(Deck $deck, \DateTimeImmutable $date): int{
        $startOfDay = $date->setTime(0, 0, 0);
        $endOfDay = $date->setTime(23, 59, 59);
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.deck = :deck')
            ->andWhere('c.last_learned BETWEEN :start AND :end')
            ->setParameter('deck', $deck)
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
            ->getQuery()
            ->getSingleScalarResult();
    }

    // Counts the number of studied cards in a given deck that were learned by a specific user.
    public function countOfStudiedByUser(AppUser $user, \DateTimeImmutable $date): int{
        $startOfDay = $date->setTime(0, 0, 0);
        $endOfDay = $date->setTime(23, 59, 59);
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->innerJoin('c.deck', 'd')
            ->andWhere('d.owner = :user')
            ->andWhere('c.last_learned BETWEEN :start AND :end')
            ->setParameter('user', $user)
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
