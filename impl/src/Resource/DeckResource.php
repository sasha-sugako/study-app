<?php
declare(strict_types=1);

namespace App\Resource;

class DeckResource
{
    public function __construct(
        public ?string $_self,
        public ?int $id,
        public string  $name,
        public ?string $description,
        public ?float $rate,
        public ?int $number_of_cards,
        public ?array $categories,
    )
    {
    }
}