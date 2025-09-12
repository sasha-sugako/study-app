<?php

namespace App\Repository;

use App\Controller\Filter\DeckFilter;
use App\Entity\AppUser;
use App\Entity\Deck;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Deck>
 */
class DeckRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Deck::class);
    }

    /* Retrieves decks based on the provided filter.
       Creates a query builder and, if the all_decks parameter is not set to true, filters out private decks.
       Applies additional filtering criteria from the DeckFilter object and returns the result of the query. */
    public function findByFilter(DeckFilter $filter, ?bool $all_decks = false)
    {
        $builder = $this->createQueryBuilder('a');
        if (!$all_decks){
            $builder->andWhere('a.is_private = false');
        }
        return $filter->apply($builder)->getQuery()->getResult();
    }

    /* Find decks associated with a specific user.
       If the user is not an admin, the query filters for private decks owned by the user. */
    public function findByUser(AppUser $user){
        $builder = $this->createQueryBuilder('a');
        if (!$user->isAdmin()){
            $builder->andWhere('a.is_private = true')
                ->andWhere('a.owner = :owner')
                ->setParameter('owner', $user);
        }
        return $builder->getQuery()->getResult();
    }

    // Find public decks
    public function findPublic(){
        return $this->createQueryBuilder('a')
            ->andWhere('a.is_private = false')
            ->getQuery()
            ->getResult();
    }

    // Find private decks associated with a specific user.
    public function findPrivateByUser(AppUser $user){
        return $this->createQueryBuilder('a')
            ->andWhere('a.is_private = true')
            ->andWhere('a.owner = :owner')
            ->setParameter('owner', $user)
            ->getQuery()
            ->getResult();
    }
}
