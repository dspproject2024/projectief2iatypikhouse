<?php
namespace App\Service; use PayPal\Api\Amount; use PayPal\Api\Payer; use PayPal\Api\Payment; use
    PayPal\Api\PaymentExecution; use PayPal\Api\RedirectUrls; use PayPal\Api\Refund; use PayPal\Api\Sale; use
    PayPal\Api\Transaction; use PayPal\Auth\OAuthTokenCredential; use PayPal\Exception\PayPalConnectionException; use
    PayPal\Rest\ApiContext; use Symfony\Component\HttpFoundation\JsonResponse; use
    Symfony\Component\HttpFoundation\Request; class PayPalPaymentService { private string $clientId; private string
    $secret; private ApiContext $apiContext; public function __construct(string $clientId, string $secret) { $this->
    clientId = $clientId;
    $this->secret = $secret;

    // Setup the API Context with the credentials
    $this->apiContext = new ApiContext(
    new OAuthTokenCredential($this->clientId, $this->secret)
    );
    $this->apiContext->setConfig([
    'mode' => 'sandbox', // Change to 'live' for production
    'http.ConnectionTimeOut' => 30,
    'log.LogEnabled' => false,
    'log.FileName' => '',
    'log.LogLevel' => 'FINE', // Logging level: 'DEBUG', 'INFO', 'WARN', or 'ERROR'
    'validation.level' => 'log',
    'cache.enabled' => true,
    ]);
    }

    /**
    * Create a PayPal payment.
    *
    * @param Request $request
    * @return JsonResponse
    */
    public function createPayment(Request $request): JsonResponse
    {
    $data = json_decode($request->getContent(), true);

    // Validate request data
    if (!isset($data['amount']) || !is_numeric($data['amount'])) {
    return new JsonResponse(['error' => 'Invalid or missing amount'], 400);
    }
    if (!isset($data['currency']) || !is_string($data['currency'])) {
    return new JsonResponse(['error' => 'Invalid or missing currency'], 400);
    }
    if (!isset($data['return_url']) || !filter_var($data['return_url'], FILTER_VALIDATE_URL)) {
    return new JsonResponse(['error' => 'Invalid or missing return URL'], 400);
    }
    if (!isset($data['cancel_url']) || !filter_var($data['cancel_url'], FILTER_VALIDATE_URL)) {
    return new JsonResponse(['error' => 'Invalid or missing cancel URL'], 400);
    }

    $amount = (float) $data['amount'];
    $returnUrl = $data['return_url'];
    $cancelUrl = $data['cancel_url'];

    $payer = new Payer();
    $payer->setPaymentMethod('paypal');

    $amountObj = new Amount();
    $amountObj->setCurrency($data['currency'])->setTotal($amount);

    $transaction = new Transaction();
    $transaction->setAmount($amountObj)->setDescription('Purchase Description');

    $redirectUrls = new RedirectUrls();
    $redirectUrls->setReturnUrl($returnUrl)->setCancelUrl($cancelUrl);

    $payment = new Payment();
    $payment->setIntent('sale')
    ->setPayer($payer)
    ->setTransactions([$transaction])
    ->setRedirectUrls($redirectUrls);

    try {
    $payment->create($this->apiContext);

    // Ensure links are an array before iterating
    $links = $payment->getLinks();
    if (is_array($links)) {
    foreach ($links as $link) {
    if ($link->getRel() === 'approval_url') {
    return new JsonResponse(['approval_url' => $link->getHref()], 200);
    }
    }
    } else {
    throw new \Exception('Links are not an array');
    }
    } catch (PayPalConnectionException $ex) {
    return new JsonResponse(['error' => 'Error creating PayPal payment: ' . $ex->getMessage()], 500);
    }

    return new JsonResponse(['error' => 'Approval URL not found in PayPal response'], 500);
    }

    // Other methods remain unchanged...

    public function executePayment(string $paymentId, string $payerId): JsonResponse
    {
    $payment = Payment::get($paymentId, $this->apiContext);
    $execution = new PaymentExecution();
    $execution->setPayerId($payerId);

    try {
    $result = $payment->execute($execution, $this->apiContext);
    return new JsonResponse($result->toArray(), 200);
    } catch (PayPalConnectionException $ex) {
    return new JsonResponse(['error' => 'Error executing PayPal payment: ' . $ex->getMessage()], 500);
    }
    }
    }