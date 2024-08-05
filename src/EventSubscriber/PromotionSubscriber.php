<?php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Promotion;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;

class PromotionSubscriber implements EventSubscriberInterface
{
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function onKernelView(ViewEvent $event): void
    {
        $promotion = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        // Ensure we're handling a Promotion entity and a POST or PUT request
        if (!$promotion instanceof Promotion || !in_array($method, [Request::METHOD_POST, Request::METHOD_PUT])) {
            return;
        }

        // Automatically generate a slug if it's not set
        if (empty($promotion->getSlug()) && !empty($promotion->getTitle())) {
            $slug = $this->slugger->slug($promotion->getTitle())->lower();
            $promotion->setSlug($slug);
        }

        // Set createdAt and updatedAt timestamps
        if ($method === Request::METHOD_POST && $promotion->getCreatedAt() === null) {
            $promotion->setCreatedAt(new \DateTimeImmutable());
        }

        $promotion->setUpdatedAt(new \DateTimeImmutable());
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onKernelView', EventPriorities::PRE_WRITE],
        ];
    }
}
