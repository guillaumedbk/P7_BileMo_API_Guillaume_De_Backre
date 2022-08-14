<?php

namespace App\Security\Voter;

use App\Entity\Customer;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class CustomerVoter extends Voter
{
    public const EDIT = 'CUSTOMER_EDIT';
    public const VIEW = 'CUSTOMER_VIEW';

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW])
            && $subject instanceof \App\Entity\Customer;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($subject, $user);
                break;
            case self::VIEW:
                return $this->canView($subject, $user);
                break;
        }

        return false;
    }

    private function canEdit(Customer $customer, User $user):bool
    {
        return $user === $customer->getUser();
    }

    private function canView(Customer $customer, User $user):bool
    {
        return $user === $customer->getUser();
    }
}
