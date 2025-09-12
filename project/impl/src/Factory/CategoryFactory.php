<?php
declare(strict_types=1);

namespace App\Factory;

use App\Entity\Category;
use App\Resource\CategoryResource;
use Symfony\Component\Routing\RouterInterface;

class CategoryFactory
{
    public function __construct(
        private RouterInterface $router,
    ) {}

    // Converts a Category entity into a CategoryResource DTO for API responses.
    public function list(Category $category): CategoryResource{
        return new CategoryResource(
            _self: $this->router->generate('api_all_categories'),
            id: $category->getId(),
            name: $category->getName()
        );
    }

    // Updates the Category entity with data from a CategoryResource.
    public function create(CategoryResource $resource, Category $category): Category{
        $category->setName($resource->name);
        return $category;
    }
}