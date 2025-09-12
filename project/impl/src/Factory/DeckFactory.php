<?php
declare(strict_types=1);

namespace App\Factory;

use App\Entity\AppUser;
use App\Entity\Card;
use App\Entity\Category;
use App\Entity\Deck;
use App\Entity\Review;
use App\Repository\CategoryRepository;
use App\Resource\CardResource;
use App\Resource\CategoryResource;
use App\Resource\DeckDetailResource;
use App\Resource\DeckResource;
use App\Resource\ReviewResource;
use Symfony\Component\Routing\RouterInterface;

class DeckFactory
{
    public function __construct(
        private RouterInterface $router,
        private CategoryRepository $categoryRepository
    ) {}

    // Converts a Deck entity into a DeckResource DTO for API responses.
    public function list(Deck $deck): DeckResource{
        return new DeckResource(
            _self: $this->router->generate('api_deck', ['id' => $deck->getId()]),
            id: $deck->getId(),
            name: $deck->getName(),
            description: $deck->getAbout(),
            rate: $deck->getTotalRate(),
            number_of_cards: count($deck->getCards()),
            categories: $deck->getCategories()->map(fn (Category $category) => $category->getName())->toArray(),
        );
    }

    // Converts a Deck entity into a DeckDetailResource DTO for detailed API responses.
    public function show(Deck $deck): DeckDetailResource{
        return new DeckDetailResource(
            _self: $this->router->generate('api_deck', ['id' => $deck->getId()]),
            id: $deck->getId(),
            name: $deck->getName(),
            description: $deck->getAbout(),
            owner: $deck->getOwner()->getLogin(),
            rate: $deck->getTotalRate(),
            cards: $deck->getCards()->map(fn(Card $card) => new CardResource(
                _self: $this->router->generate('api_card',
                    ['id_deck' => $deck->getId(), 'id_card' => $card->getId()]),
                id: $card->getId(),
                front_side: $card->getFrontSide(),
                front_image: $card->getFrontImage(),
                back_side: $card->getBackSide()
            ))->toArray(),
            categories: $deck->getCategories()->map(fn (Category $category) => new CategoryResource(
                _self: $this->router->generate('api_all_categories'),
                id: $category->getId(),
                name: $category->getName()
            ))->toArray(),
        );
    }

    /* Initializes or updates a Deck entity using data from the DeckDetailResource DTO and
       assigns it to the given user if no owner is set. It resets the deck's categories,
       sets name and description, and returns the modified deck. */
    public function create(DeckDetailResource $resource, Deck $deck, AppUser $user): Deck{
        $deck->removeAllCategories();
        foreach($resource->categories as $category_name){
            $category = $this->categoryRepository->findOneByName($category_name);
            $deck->addCategory($category);
        }
        if (!$deck->getOwner())
            $deck->setOwner($user);
        $deck->setName($resource->name);
        $deck->setAbout($resource->description);
        return $deck;
    }

    /* Creates a new Review entity from the ReviewResource DTO by setting its rating, description, associated deck,
       and author, then returns the populated review object. */
    public function add_review(ReviewResource $resource, Deck $deck, AppUser $user): Review{
        $review = new Review();
        $review->setRate($resource->rate);
        $review->setDescription($resource->description);
        $review->setDeck($deck);
        $review->setReviewedBy($user);
        return $review;
    }
}