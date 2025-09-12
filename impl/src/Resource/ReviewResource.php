<?php
declare(strict_types=1);

namespace App\Resource;

class ReviewResource
{
    public function __construct(
        public ?string $_self,
        public ?int $id,
        public int $rate,
        public ?string $description,
    )
    {
    }
}