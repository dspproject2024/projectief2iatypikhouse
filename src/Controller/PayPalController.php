<?php
namespace App\Controller;

use App\Service\PayPalPaymentService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PayPalController
{
    private PayPalPaymentService $payPalPaymentService;

    public function __construct(PayPalPaymentService $payPalPaymentService)
    {
        $this->payPalPaymentService = $payPalPaymentService;
    }

    /**
     * @Route("/api/paypal/payment", name="paypal_payment", methods={"POST"})
     */
    public function createPayment(Request $request): JsonResponse
    {
        return $this->payPalPaymentService->createPayment($request);
    }
}
?>