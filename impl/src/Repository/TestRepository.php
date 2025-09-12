<?php

namespace App\Repository;

use App\Entity\AppUser;
use App\Entity\Deck;
use App\Entity\Test;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Test>
 */
class TestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Test::class);
    }

    // Counts the number of tests in a given deck that were finished on a specific day.
    public function countOfFinishedPerDay(Deck $deck, \DateTimeImmutable $date): int{
        $startOfDay = $date->setTime(0, 0, 0);
        $endOfDay = $date->setTime(23, 59, 59);
        return $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->andWhere('t.deck = :deck')
            ->andWhere('t.finished_at BETWEEN :start AND :end')
            ->setParameter('deck', $deck)
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
            ->getQuery()
            ->getSingleScalarResult();
    }

    // Counts the number of tests in a given deck that were finished by a specific user on a specific day.
    public function countOfFinishedPerUser(AppUser $user, \DateTimeImmutable $date): int{
        $startOfDay = $date->setTime(0, 0, 0);
        $endOfDay = $date->setTime(23, 59, 59);
        return $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->innerJoin('t.deck', 'd')
            ->andWhere('d.owner = :user')
            ->andWhere('t.finished_at BETWEEN :start AND :end')
            ->setParameter('user', $user)
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
