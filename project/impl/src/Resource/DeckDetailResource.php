<?php
declare(strict_types=1);

namespace App\Resource;

class DeckDetailResource
{
    public function __construct(
        public ?string $_self,
        public ?int $id,
        public string  $name,
        public ?string $description,
        public ?string $owner,
        public ?float $rate,
        public ?array $cards,
        public ?array $categories,
    )
    {
    }
}