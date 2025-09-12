<?php
declare(strict_types=1);

/* This file contains code adapted from the Symfony documentation:
   - Serializer component://symfony.com/doc/current/serializer/.html#deserializing-an-object
   - Validator component: https://symfony.com/doc/current/validation.html
   Used for deserializing JSON data into an object and validating it.
*/

namespace App\Controller\Api;

use App\Entity\Category;
use App\Factory\CategoryFactory;
use App\Service\CategoryService;
use App\Repository\CategoryRepository;
use App\Resource\CategoryResource;
use App\Resource\CollectionResource;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/api/categories')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class CategoryController extends AbstractController
{
    public function __construct(
        private CategoryService    $categoryService,
        private CategoryRepository $categoryRepository,
        private CategoryFactory    $categoryFactory
    ){}

    // Returns a JSON list of all available categories.
    #[Route(path: '', name: 'api_all_categories', methods: ['GET'])]
    public function all_categories(): Response{
        return $this->json(new CollectionResource(
            _self: $this->generateUrl('api_all_categories'),
            data: array_map(fn(Category $category) => $this->categoryFactory->list($category),
            $this->categoryRepository->findAll())
        ));
    }

    /* Handles creating a new category.
       Deserializes the request content into a CategoryResource object, validates the category,
       and stores it. Returns the details of the created category in a JSON response. */
    #[Route(path: '', name: 'api_category_create', methods: ['POST'])]
    public function create(Request $request, SerializerInterface $serializer, ValidatorInterface $validator): Response{
        $resource = $serializer->deserialize(
            $request->getContent(),
            CategoryResource::class,
            'json'
        );
        $category = new Category();
        $this->categoryFactory->create($resource, $category);
        $violations = $validator->validate($category);
        if (count($violations) > 0) {
            return $this->json((string) $violations, 400);
        }
        $this->categoryService->store($category);
        return $this->json($this->categoryFactory->list($category));
    }
}