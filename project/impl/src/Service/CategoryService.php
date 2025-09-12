<?php
declare(strict_types=1);

namespace App\Service;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
class CategoryService
{
    public function __construct(
        private EntityManagerInterface $manager,
    ) {}

    // Persists a new Category entity to the database.
    public function store(Category $category): ?int
    {
        $this->manager->persist($category);
        $this->manager->flush();
        return $category->getId();
    }

    // Removes the given Category entity from the database.
    public function remove(Category $category): void
    {
        $this->manager->remove($category);
        $this->manager->flush();
    }
}