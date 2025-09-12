<?php

namespace App\Resource;

class GoalResource
{
    public function __construct(
        public ?string $_self,
        public ?int $id,
        public ?\DateTimeImmutable $start_date,
        public ?\DateTimeImmutable $end_date,
        public ?int $target_cards,
        public ?int $target_tests,
        public ?int $achieved_cards,
        public ?int $achieved_tests,
        public ?bool $completed
    )
    {
    }
}