<?php
declare(strict_types=1);

namespace App\Resource;

class CategoryResource
{
    public function __construct(
        public ?string $_self,
        public ?int $id,
        public string $name,
    ){

    }
}