<?php

namespace App\Security\Voter;

use App\Entity\Customer;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class CustomerVoter extends Voter
{
    public const GET = 'GET';
    public const DELETE = 'DELETE';
    public const GET_ALL = 'GET_ALL';

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::GET, self::DELETE, self::GET_ALL])
            && $subject instanceof \App\Entity\Customer || gettype($subject) === "string";
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::DELETE:
            case self::GET:
                return $user->getId() === $subject->getUser()->getId();
            case self::GET_ALL:
                return $user->getId() === $subject;
        }
        return false;
    }
}
