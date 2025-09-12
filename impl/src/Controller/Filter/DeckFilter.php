<?php
declare(strict_types=1);

namespace App\Controller\Filter;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;

class DeckFilter
{
    public function __construct(
        public ?string $name = null,
        public ?Collection $categories = null,
        public ?int $min_rate = null
    ) {}

    /* Applies filtering conditions to a QueryBuilder for fetching decks.
       Filters by exact name (if provided), by associated categories,
       and by minimum average review rating.
       Returns the modified QueryBuilder instance with all applicable filters applied. */
    public function apply(QueryBuilder $qb): QueryBuilder
    {
        if ($this->name !== null) {
            $qb = $qb->andWhere('a.name = :name')
                ->setParameter('name', $this->name);
        }

        if ($this->categories !== null && !$this->categories->isEmpty()){
            $qb->andWhere('EXISTS (
                SELECT 1 FROM App\Entity\Category c 
                WHERE c.id IN (:categories) AND c MEMBER OF a.categories
            )'
            )->setParameter('categories', $this->categories);
        }
        if ($this->min_rate !== null){
            $qb->join('a.reviews', 'r')
                ->groupBy('a.id')
                ->having('AVG(r.rate) >= :minRating')
                ->setParameter('minRating', $this->min_rate);
        }
        return $qb;
    }
}
