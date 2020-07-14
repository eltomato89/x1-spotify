<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class AuthenticationTokenEventSubscriber implements EventSubscriberInterface
{


    /**
     * RestUserTokenSubscriber constructor.
     */
    private $userProvider;
    private $em;
    private $tokenStorage;
    private $authenticationManager;
    private $eventDispatcher;

    public function __construct(UserProviderInterface $userProvider, EntityManagerInterface $em, TokenStorageInterface $tokenStorage, AuthenticationManagerInterface $authenticationManager, EventDispatcherInterface $dispatcher, LoggerInterface $logger)
    {
        $this->userProvider = $userProvider;
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->eventDispatcher = $dispatcher;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController', 0]
        ];
    }

    public function onKernelController(KernelEvent $event)
    {
        if($event->getRequest()->headers->has('authorization')) {
            $apitoken = $event->getRequest()->headers->get('authorization');

            if(count(explode(" ", $apitoken)) <= 1 && explode(" ", $apitoken)[0] != "bearer") {
                return;
            }

            $apitoken = explode(" ", $apitoken)[1];

            $userEntity = $this->em->getRepository(User::class)->findOneBy(["apiKey" => $apitoken]);

            if($userEntity) {
                /** @var User $userEntity */
                $user = $this->userProvider->loadUserByUsername($userEntity->getNickname());

                $token = new UsernamePasswordToken($user, null, "main");
                $authenticatedToken = $this->authenticationManager->authenticate($token);

                /* tokenStorage is injected in the constructor. TokenStorageInterface $tokenStorage) */
                $this->tokenStorage->setToken($authenticatedToken);

                $event = new InteractiveLoginEvent($event->getRequest(), $authenticatedToken);
                $this->eventDispatcher->dispatch(	$event, SecurityEvents::INTERACTIVE_LOGIN);

            }

        }
    }

}