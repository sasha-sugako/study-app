<?php

namespace App\Factory;

use App\Entity\Goal;
use App\Resource\GoalResource;
use Symfony\Component\Routing\RouterInterface;

class GoalFactory
{
    public function __construct(
        private RouterInterface $router,
    ) {}

    // Converts a Goal entity into a GoalResource DTO for API responses.
    public function list(Goal $goal): GoalResource{
        return new GoalResource(
            _self: $this->router->generate('api_goals'),
            id: $goal->getId(),
            start_date: $goal->getStartDate(),
            end_date: $goal->getEndDate(),
            target_cards: $goal->getTargetCards() ?? 0,
            target_tests: $goal->getTargetTests() ?? 0,
            achieved_cards: $goal->getAchievedCards() ?? 0,
            achieved_tests: $goal->getAchievedTests() ?? 0,
            completed: $goal->isCompleted()
        );
    }

    // Updates the Goal entity with data from a GoalResource.
    public function create(GoalResource $resource, Goal $goal): Goal{
        if ($resource->target_cards > 0)
            $goal->setTargetCards($resource->target_cards);
        if ($resource->target_tests > 0)
            $goal->setTargetTests($resource->target_tests);
        return $goal;
    }
}