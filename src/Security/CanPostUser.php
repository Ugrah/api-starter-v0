<?php
namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CanPostUser extends Voter
{
    const POST_ACTION = 'post_action';

    protected function supports($attribute, $subject)
    {
        if (!$subject instanceof User) {
            return false;
        }

        if (!in_array($attribute, array(self::POST_ACTION))) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        // if($this->isGranted('ROLE_ADMIN')) {
        //     return true;
        // }

        $user = $token->getUser();
        if(!$user instanceOf User) {
            return false;
        }

        // if($subject->getOwner() === $user) {
        //     return true;
        // }

        return false;
    }
}