<?php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Avis;
use App\Entity\Habitat;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class PutGetUserSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function onKernelView(ViewEvent $event): void
    {
        $entity = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        // Ensure we only proceed for Avis entities on POST and PUT requests
        if ((!$entity instanceof Avis) || !in_array($method, [Request::METHOD_POST, Request::METHOD_PUT])) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            throw new \LogicException('No token found in token storage.');
        }

        $owner = $token->getUser();
        if (!$owner instanceof UserInterface) {
            throw new \LogicException('The user must be an instance of UserInterface.');
        }

        // Set the current authenticated user as the owner of the Avis
        $entity->setUser($owner);
        $entity->setHabitat($entity->getHabitat());
        $entity->setComment($entity->getComment());
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onKernelView', EventPriorities::PRE_WRITE],
        ];
    }
}
