<?php

namespace App\Repository;

use App\Entity\AppUser;
use App\Entity\Bonus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Bonus>
 */
class BonusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bonus::class);
    }

    /* Returns the number of unused "miss_day" bonuses for a specific user by querying the database
       with conditions on owner, bonus type, and usage status. */
    public function getCountNotUsedDayBonuses(AppUser $user): int
    {
        return $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->where('b.owner = :user')
            ->andWhere('b.type = :miss_day')
            ->andWhere('b.is_used = false')
            ->setParameter('user', $user)
            ->setParameter('miss_day', 'miss_day')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /* Returns the number of unused "successful_test" bonuses for a specific user by querying the database
       with conditions on owner, bonus type, and usage status. */
    public function getCountNotUsedTestBonuses(AppUser $user): int
    {
        return $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->where('b.owner = :user')
            ->andWhere('b.type = :test')
            ->andWhere('b.is_used = false')
            ->setParameter('user', $user)
            ->setParameter('test', 'successful_test')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
