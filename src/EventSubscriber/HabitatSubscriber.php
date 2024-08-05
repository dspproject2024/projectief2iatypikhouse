<?php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Habitat;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Cocur\Slugify\Slugify;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class HabitatSubscriber implements EventSubscriberInterface
{
    public function __construct(private SluggerInterface $slugger, private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function onKernelView(ViewEvent $event): void
    {
        $habitat = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        // Check if the entity is a Habitat instance and the request method is POST or PUT
        if (!$habitat instanceof Habitat || !in_array($method, ['POST', 'PUT'])) {
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

        $habitat->setOwner($owner);



        // Automatically generate a slug if it's not set
        if (empty($habitat->getSlug()) && !empty($habitat->getTitle())) {
            $slug = $this->slugger->slug($habitat->getTitle())->lower();
            $habitat->setSlug($slug);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onKernelView', EventPriorities::PRE_WRITE], // Set priority higher if needed
        ];
    }
}
