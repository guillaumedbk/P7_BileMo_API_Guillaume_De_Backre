<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class UserVoter extends Voter
{
    public const GET = 'GET';
    public const DELETE = 'DELETE';
    public const PUT = 'PUT';

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::GET, self::DELETE, self::PUT])
            && $subject instanceof \App\Entity\User;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::GET:
            case self::DELETE:
            case self::PUT:
                return $user->getId() === $subject->getId();
        }

        return false;
    }
}
