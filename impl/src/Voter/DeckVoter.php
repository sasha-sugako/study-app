<?php

namespace App\Voter;

use App\Entity\AppUser;
use App\Entity\Deck;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DeckVoter extends Voter
{
    public const EDIT_REMOVE = 'deck_edit_remove';
    public const VIEW = 'deck_view';
    public const STUDY_TEST = 'deck_study';
    public const CREATE_TEST = 'deck_create_test';
    public function __construct(
        private AccessDecisionManagerInterface $accessDecisionManager,
    ) {}

    /* Checks if the Voter supports the given attribute and subject.
       This method was extended from Symfony Voter
       Source: https://symfony.com/doc/current/security/voters.html */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT_REMOVE, self::VIEW, self::STUDY_TEST, self::CREATE_TEST])
            && $subject instanceof Deck;
    }

    /* Evaluates if the user has permission to perform the specified action on the subject.
       This method was extended from Symfony Voter
       Source: https://symfony.com/doc/current/security/voters.html */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof AppUser) {
            return false;
        }
        /** @var Deck $deck */
        $deck = $subject;
        return match ($attribute) {
            self::EDIT_REMOVE => $this->canEditOrRemoveDeck($token, $deck),
            self::VIEW => $this->canViewDeck($token, $deck),
            self::STUDY_TEST => $this->canStudyOrTest($token, $deck),
            self::CREATE_TEST => $this->canCreateTest($token, $deck),
            default => throw new \LogicException('This code should not be reached!')
        };
    }

    /* Checks if the user has permission to edit or remove the given Deck.
       The user is granted permission if he is an admin or if he is the owner of the deck
       and the deck is marked as private. */
    private function canEditOrRemoveDeck(TokenInterface $token, Deck $deck): bool{
        $user = $token->getUser();
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN']) ||
            ($deck->getOwner() === $user && $deck->isPrivate()))
            return true;
        return false;
    }

    /* Checks if the user has permission to view the given Deck.
       The user is granted permission if he is an admin or if the deck is public or if the user is an owner of
       the specified deck. */
    private function canViewDeck(TokenInterface $token, Deck $deck): bool{
        $user = $token->getUser();
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN']) ||
            !$deck->isPrivate() || $deck->getOwner() === $user)
            return true;
        return false;
    }

    /* Checks if the user has permission to study or take a test on the given Deck.
       The user is granted permission if the deck is private and user is it's owner. */
    private function canStudyOrTest(TokenInterface $token, Deck $deck): bool{
        $user = $token->getUser();
        return $deck->isPrivate() && $deck->getOwner() === $user;
    }

    /* Checks if the user has permission to create a test on the given Deck.
       The user is granted permission if the deck is private and user is it's owner or user is an admin. */
    private function canCreateTest(TokenInterface $token, Deck $deck): bool{
        $user = $token->getUser();
        return $deck->isPrivate() && ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN']) ||
                $deck->getOwner() === $user);
    }
}
