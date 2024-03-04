<?php

namespace Vareted\Monobank\Http\Controllers;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use MonoPay\Client;
use MonoPay\Payment;
use MonoPay\Webhook;
use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\InvoiceRepository;

class MonobankController extends Controller
{
    /**
     * OrderRepository $orderRepository
     *
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * InvoiceRepository $invoiceRepository
     *
     * @var InvoiceRepository
     */
    protected $invoiceRepository;

    /**
     * Token
     *
     * @var string
     */
    private string $token;

    /**
     * Status
     *
     * @var bool
     */
    private bool $status;

    /**
     * @var string
     */
    private string $destination;

    /**
     * Create a new controller instance.
     */
    public function __construct(OrderRepository $orderRepository, InvoiceRepository $invoiceRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;

        $this->token = (string)core()->getConfigData('sales.payment_methods.monobank.token');
        $this->status = (boolean)core()->getConfigData('sales.payment_methods.monobank.active');
        $this->destination = (string)core()->getConfigData('sales.payment_methods.monobank.destination');
    }

    public function redirect(): RedirectResponse
    {
        $cart = Cart::getCart();

        if (is_null($cart) or !$this->status) {
            return redirect()->route('shop.home.index');
        }

        $order = $this->orderRepository->create( Cart::prepareDataForOrder() );
        $order->status = 'pending_payment';
        $order->save();

        $monoClient = new Client($this->token);
        $monoPayment = new Payment($monoClient);

        $destination = preg_replace('/{{\s*orderId\s*}}/i', (string)$order->id, $this->destination);

        $invoice = $monoPayment->create(
            $order->grand_total * 100,
            [
                //деталі оплати
                'merchantPaymInfo' => [
                    'reference' => (string)$order->id,
                    'destination' => $destination,
                ],
                'redirectUrl' => route('monobank.result'),
                'webHookUrl' => route('monobank.webhook'),
                'validity' => 3600 * 24 * 7,
                'paymentType' => 'debit',
            ]
        );

        return redirect()->away($invoice['pageUrl']);
    }

    /**
     * Webhook
     *
     * @param Request $request
     *
     * @return Response
     */
    public function webhook(Request $request): Response
    {
        try {

            $monoClient = new Client($this->token);

            $publicKey = $monoClient->getPublicKey();

            $monoWebhook = new Webhook($monoClient, $publicKey, $request->server->get('HTTP_X_SIGN'));

            $body = $request->getContent();

            if ($monoWebhook->verify($body)) {

                $order = $this->orderRepository->findOrFail( (string)$request->get('reference')) ;

                switch ($request->get('status')) {
                    case 'success':
                        if ($order->canInvoice()) {
                            $this->invoiceRepository->create($this->prepareInvoiceData($order));
                        }
                        break;
                    default:

                        break;
                }

            }
        } catch (Exception|GuzzleException) { }

        return response(null, 200);
    }

    /**
     * Result page
     *
     * @return RedirectResponse
     */
    public function result(): RedirectResponse
    {
        if ( is_null(Cart::getCart()) ) {
            return redirect()->route('shop.home.index');
        }

        $order = $this->orderRepository->findOneWhere([
            'cart_id' => Cart::getCart()->id
        ]);

        switch ($order->status) {
            case 'pending':
            case 'pending_payment':
            case 'processing':
                Cart::deActivateCart();
                session()->flash('order', $order);
                return redirect()->route('shop.checkout.onepage.success');
            default:
                return redirect()->route('shop.checkout.cart.index');
        }
    }

    /**
     * Prepares order's invoice data for creation.
     *
     * @param $order
     *
     * @return array
     */
    protected function prepareInvoiceData($order): array
    {
        $invoiceData = [
            'order_id' => $order->id,
            'invoice'  => ['items' => []],
        ];

        foreach ($order->items as $item) {
            $invoiceData['invoice']['items'][$item->id] = $item->qty_to_invoice;
        }

        return $invoiceData;
    }
}

