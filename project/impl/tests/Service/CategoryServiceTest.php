<?php

namespace App\Tests\Service;

use App\Entity\Category;
use App\Service\CategoryService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CategoryServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private CategoryService $categoryService;

    // Sets up the test environment before each test method is run.
    protected function setUp(): void{
        parent::setUp();
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->categoryService = new CategoryService($this->entityManager);
    }

    /* Tests the store method of CategoryService.
       Ensures that the category is persisted and flushed. */
    public function testStore(): void{
        $category = $this->createMock(Category::class);
        $this->entityManager->expects($this->once())->method('persist')->with($category);
        $this->entityManager->expects($this->once())->method('flush');
        $this->categoryService->store($category);
    }

    // Tests the remove method of CategoryService.
    public function testRemove(): void{
        $category = $this->createMock(Category::class);
        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($category);
        $this->categoryService->remove($category);
    }
}
