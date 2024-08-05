<?php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Payment;
use App\Repository\StatusRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PaymentSubscriber implements EventSubscriberInterface
{
    public function __construct(private StatusRepository $repository)
    {
    }

    public function onKernelView(ViewEvent $event): void
    {
        // ...
        $payment = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$payment instanceof Payment || ! in_array($method,[Request::METHOD_POST,Request::METHOD_PUT])) {
            return ;
        }

        if (empty($payment->getStatus())) {
            $status = $this->repository->find($payment->getValide());
            $payment->setStatus($status->getTitle());
        }

        if (empty($payment->getAmount())) {
            $amount = $payment->getReservation()->getTotalPrice();
            $payment->setAmount($amount);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onKernelView',EventPriorities::PRE_WRITE]
        ];
    }
}
