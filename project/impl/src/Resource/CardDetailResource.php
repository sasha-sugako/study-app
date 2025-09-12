<?php
declare(strict_types=1);

namespace App\Resource;

class CardDetailResource
{
    public function __construct(
        public ?string $_self,
        public ?int $id,
        public string  $front_side,
        public ?string $front_image,
        public string $back_side,
        public ?string $back_image,
    )
    {
    }
}