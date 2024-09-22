<?php

namespace App\Controller;

use App\Service\StripePaymentService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class StripeController extends AbstractController
{
    private StripePaymentService $stripePaymentService;

    public function __construct(StripePaymentService $stripePaymentService)
    {
        $this->stripePaymentService = $stripePaymentService;
    }

    #[Route('/api/stripe/checkout', name: 'stripe_checkout', methods: ['POST'])]
    public function createCheckoutSession(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate request data
        if (!isset($data['line_items'], $data['success_url'], $data['cancel_url'])) {
            return new JsonResponse(['error' => 'Invalid request data'], 400);
        }

        try {
            // Create the Stripe Checkout session
            $session = $this->stripePaymentService->createCheckoutSession(
                $data['line_items'],
                $data['success_url'],
                $data['cancel_url']
            );

            // Return the session URL to redirect the user
            return new JsonResponse(['url' => $session->url], 200);
        } catch (\Exception $e) {
            // Return error response
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}