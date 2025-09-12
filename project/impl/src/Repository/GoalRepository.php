<?php

namespace App\Repository;

use App\Entity\AppUser;
use App\Entity\Goal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Goal>
 */
class GoalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Goal::class);
    }

    // Finds goals that are currently active but have not been completed and have expired.
    public function findExpiredGoals(): array{
        return $this->createQueryBuilder('g')
            ->andWhere('g.is_current = true')
            ->andWhere('g.completed = false')
            ->andWhere('g.end_date <= :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getResult();
    }
}
