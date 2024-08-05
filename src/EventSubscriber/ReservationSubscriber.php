<?php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Reservation;
use App\Repository\StatusRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ReservationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private StatusRepository $statusRepository,
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public function onKernelView(ViewEvent $event): void
    {
        $reservation = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        // Check if the entity is a Reservation and the HTTP method is POST or PUT
        if (!$reservation instanceof Reservation || !in_array($method, [Request::METHOD_POST, Request::METHOD_PUT])) {
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

        $reservation->setUser($owner);

        // Calculate totalPrice based on the duration of the stay
        $startDate = $reservation->getStartDate();
        $endDate = $reservation->getEndDate();

        if ($startDate && $endDate) {
            $interval = $startDate->diff($endDate);
            $days = $interval->days; // Get the number of days between the dates
            $pricePerNight = $reservation->getHabitat()->getPricePerNight();
            $reservation->setTotalPrice($pricePerNight * $days);
        }

        // Set the default status if not already set
        if (!$reservation->getStatus()) {
            $status = $this->statusRepository->find(105);
            if ($status) {
                $reservation->setStatus($status);
            } else {
                throw new \LogicException('Default status not found.');
            }
        }

        // Automatically set createdAt and updatedAt
        if ($method === Request::METHOD_POST && $reservation->getCreatedAt() === null) {
            $reservation->setCreatedAt(new \DateTimeImmutable());
        }
        $reservation->setUpdatedAt(new \DateTimeImmutable());
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onKernelView', EventPriorities::PRE_WRITE],
        ];
    }
}
