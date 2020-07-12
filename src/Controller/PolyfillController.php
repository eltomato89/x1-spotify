<?php

namespace App\Controller;

use App\Entity\User;
use MsgPhp\User\Infrastructure\Security\UserIdentityProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PolyfillController extends AbstractController
{
    /**
     * PolyfillController constructor.
     */
    private $userIdentityProvider;
    public function __construct(UserIdentityProvider $userIdentityProvider)
    {
        $this->userIdentityProvider = $userIdentityProvider;
    }


    /**
     * @param UserIdentityProvider $userIdentityProvider
     * @return User
     */
    public function getUserDomain() {
        if(is_null($this->getUser())) return null;

        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $user = $this->userIdentityProvider->refreshUser($this->getUser());

        /** @var User $domainUser */
        $domainUser = $userRepo->find($user->getUserId());

        if($domainUser) {
            return $domainUser;
        }

        return null;
    }
}
