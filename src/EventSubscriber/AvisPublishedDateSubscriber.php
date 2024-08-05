<?php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Avis;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class AvisPublishedDateSubscriber implements EventSubscriberInterface
{
    public function onKernelView(ViewEvent $event): void
    {
        $entity = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        // Check that we have the correct entity and method
        if (!$entity instanceof Avis || !in_array($method, [Request::METHOD_POST, Request::METHOD_PUT])) {
            return;
        }

        // Set publishedAt if it hasn't been set already
        if ($entity->getPublishedAt() === null) {
            $entity->setPublishedAt(new \DateTimeImmutable());
        }

        // You might also want to update the createdAt and updatedAt here
//        if ($method === Request::METHOD_POST && $entity->getCreatedAt() === null) {
//            $entity->setCreatedAt(new \DateTimeImmutable());
//        }
//
//        $entity->setUpdatedAt(new \DateTimeImmutable());
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onKernelView', EventPriorities::PRE_WRITE],
        ];
    }
}
