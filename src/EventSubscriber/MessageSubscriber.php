<?php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Habitat;
use App\Entity\Message;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MessageSubscriber implements EventSubscriberInterface
{
    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function onKernelView(ViewEvent $event): void
    {
        $entity = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        // Check if the entity is a Message or Habitat and if the method is POST or PUT
        if ((!$entity instanceof Message && !$entity instanceof Habitat) || !in_array($method, [Request::METHOD_POST, Request::METHOD_PUT])) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if ($token === null) {
            throw new AccessDeniedException('No user is currently authenticated.');
        }
        $user = $token->getUser();

        if ($user === null || !is_object($user)) {
            throw new AccessDeniedException('You need to be authenticated to perform this action.');
        }

        // Automatically set the sender and receiver for Message on creation
        if ($entity instanceof Message) {
            if ($method === Request::METHOD_POST) {
                $entity->setSender($user);

                // Assuming there is a mechanism to set the receiver for the message
                // Possibly using a relationship with Habitat or some other entity
                // Here we need to define how the receiver is determined
                if ($entity->getReceiver() === null) {
                    throw new \LogicException('A receiver must be set for the message.');
                }

                $entity->setCreatedAt(new \DateTimeImmutable());
            }

            // Update the updatedAt timestamp on message updates
            $entity->setUpdatedAt(new \DateTimeImmutable());
        }

//        // Handle Habitat-specific logic if necessary
//        if ($entity instanceof Habitat) {
//            if ($method === Request::METHOD_POST) {
//                // Set owner or any other necessary properties if needed
//                $entity->setOwner(); // Example: setting the owner to the current user
//                $entity->setCreatedAt(new \DateTimeImmutable());
//            }
//
//            $entity->setUpdatedAt(new \DateTimeImmutable());
//        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onKernelView', EventPriorities::PRE_WRITE],
        ];
    }
}
