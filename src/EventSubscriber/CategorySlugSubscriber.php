<?php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Category;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\String\Slugger\SluggerInterface;

class CategorySlugSubscriber implements EventSubscriberInterface
{
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function onKernelView(ViewEvent $event): void
    {
        $category = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        // Ensure we're working with a Category entity and it's a POST or PUT request
        if (!$category instanceof Category || !in_array($method, ['POST', 'PUT'])) {
            return;
        }

        // Automatically generate a slug if it's not set
        if (empty($category->getSlug()) && !empty($category->getTitle())) {
            $slug = $this->slugger->slug($category->getTitle())->lower();
            $category->setSlug($slug);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onKernelView', EventPriorities::PRE_WRITE], // Set priority if needed
        ];
    }
}
