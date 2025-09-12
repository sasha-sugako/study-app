<?php
declare(strict_types=1);

/* This file contains code adapted from the Symfony documentation:
   - Serializer component://symfony.com/doc/current/serializer/.html#deserializing-an-object
   - Validator component: https://symfony.com/doc/current/validation.html
   Used for deserializing JSON data into an object and validating it.
*/

namespace App\Controller\Api;

use App\Entity\Goal;
use App\Entity\AppUser;
use App\Factory\GoalFactory;
use App\Service\UserService;
use App\Resource\CollectionResource;
use App\Resource\GoalResource;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/api/user')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class UserController extends AbstractController
{
    public function __construct(
        private UserService $userService,
        private GoalFactory $goalFactory
    ) {}

    // Returns a JSON response with the user's goals.
    #[Route('/goals', name: 'api_goals', methods: ['GET'])]
    public function goals():Response{
        /** @var AppUser $user */
        $user = $this->getUser();
        $goals = $user->getGoals();
        return $this->json(new CollectionResource(
            _self: $this->generateUrl('api_goals'),
            data: array_map(fn(Goal $goal) => $this->goalFactory->list($goal),
            $goals->toArray())
        ));
    }

    /* Handles creating a new goal for the user if they don't have an existing one.
       Deserializes the request content into a GoalResource object, validates the goal,
       and stores it. Returns the details of the created goal in a JSON response. */
    #[Route('/goals', name: 'api_new_goal', methods: ['POST'])]
    public function new_goal(Request $request, SerializerInterface $serializer, ValidatorInterface $validator): Response{
        /** @var AppUser $user */
        $user = $this->getUser();
        if ($user->getActualGoal())
            throw new \LogicException('Cíl na tento týden již byl nastaven');
        $resource = $serializer->deserialize(
            $request->getContent(),
            GoalResource::class,
            'json'
        );
        $goal = new Goal();
        $this->goalFactory->create($resource, $goal);
        if (
            ($goal->getTargetCards() === null || $goal->getTargetCards() <= 0) &&
            ($goal->getTargetTests() === null || $goal->getTargetTests() <= 0)
        ) {
            return $this->json(['error' => 'Musíte zadat alespoň jeden cíl větší než 0'], 400);
        }
        $violations = $validator->validate($goal);
        if (count($violations) > 0) {
            return $this->json((string) $violations, 400);
        }
        $this->userService->setGoal($user, $goal);
        return $this->json($this->goalFactory->list($goal));
    }
}