<?php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Status;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;

class StatutSubscriber implements EventSubscriberInterface
{

    public function __construct(private SluggerInterface $slugger)
    {

    }

    public function onKernelView(ViewEvent $event): void
    {
        $status = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        // Check if the entity is a Status and if the method is POST or PUT
        if (!$status instanceof Status || !in_array($method, [Request::METHOD_POST, Request::METHOD_PUT])) {
            return;
        }

        // Automatically generate a slug if it's not set
        if (empty($status->getSlug()) && !empty($status->getTitle())) {
            $slug = $this->slugger->slug($status->getTitle());
            $status->setSlug($slug);
        }

        // Set createdAt and updatedAt dates
        if ($method === Request::METHOD_POST && empty($status->getCreatedAt())) {
            $status->setCreatedAt(new \DateTimeImmutable());
        }
        $status->setUpdatedAt(new \DateTimeImmutable());
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onKernelView', EventPriorities::PRE_WRITE],
        ];
    }
}
