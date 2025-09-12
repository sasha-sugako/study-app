<?php
declare(strict_types=1);

namespace App\Factory;

use App\Entity\Card;
use App\Resource\CardDetailResource;
use App\Resource\CardResource;
use Symfony\Component\Routing\RouterInterface;

class CardFactory
{
    public function __construct(
        private RouterInterface $router,
    ) {}

    // Converts a Card entity into a CardResource DTO for API responses.
    public function list(Card $card): CardResource{
        return new CardResource(
            _self: $this->router->generate('api_card',
                ['id_deck' => $card->getDeck()->getId(), 'id_card' => $card->getId()]),
            id: $card->getId(),
            front_side: $card->getFrontSide(),
            front_image: $card->getFrontImage(),
            back_side: $card->getBackSide(),
        );
    }

    // Converts a Card entity into a CardDetailResource DTO for detailed API responses.
    public function show(Card $card): CardDetailResource{
        return new CardDetailResource(
            _self: $this->router->generate('api_card',
                ['id_deck' => $card->getDeck()->getId(), 'id_card' => $card->getId()]),
            id: $card->getId(),
            front_side: $card->getFrontSide(),
            front_image: $card->getFrontImage(),
            back_side: $card->getBackSide(),
            back_image: $card->getBackImage());
    }

    // Updates the Card entity with data from a CardDetailResource.
    public function create(CardDetailResource $resource, Card $card): Card{
        $card->setFrontSide($resource->front_side);
        $card->setBackSide($resource->back_side);
        return $card;
    }
}