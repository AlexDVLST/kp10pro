<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SanderVanHooft\Invoicable\Invoice;
use App\Models\Order;
use App\Scopes\OrderScope;

class MonetaController extends Controller
{
    private $code         = 'QzE4SkN3VrSXRr7Z';
    private static $mntId = '86509869';

    /**
     * Pay
     *
     * @param Request $request
     * @return void
     */
    public function pay(Request $request)
    {
        $mntTransactionId = $request->input('MNT_TRANSACTION_ID');
        $mntOperationId   = $request->input('MNT_OPERATION_ID') . '';
        $mntAmount        = $request->input('MNT_AMOUNT') . '';
        $mntCurrencyCode  = $request->input('MNT_CURRENCY_CODE') . '';
        $mntSubscriberId  = $request->input('MNT_SUBSCRIBER_ID') . '';
        $mntTestMode      = $request->input('MNT_TEST_MODE') . '';
        $accountId        = $request->input('MNT_CUSTOM1') . '';

        $mntSignature = $request->input('MNT_SIGNATURE');

        //Get order with invoice
        $order = Order::withoutGlobalScope(OrderScope::class)->whereAccountId($accountId)
            ->with(['invoices' => function ($query) use ($mntTransactionId) {
                $query->where('reference', '=', $mntTransactionId);
            }])->first();

        $invoice = $order->invoices->first();

        if ($invoice) {
            $signature = md5(self::$mntId . $mntTransactionId . $mntOperationId . $mntAmount . $mntCurrencyCode . $mntSubscriberId . $mntTestMode . $this->code);

            if ($signature == $mntSignature) {
                OrderController::submit($order, $invoice);

                return response('SUCCESS', 200)
                    ->header('Content-Type', 'text/plain');
            }
        }
        //Repeat one more time
        return response('FAIL', 100)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Success
     *
     * @param Request $request
     * @return view
     */
    public function success(Request $request)
    {
        $mntTransactionId = $request->input('MNT_TRANSACTION_ID');

        $order = Order::findByReference($mntTransactionId);

        if (!$order) {
            abort(404);
        }

        return view('pages.moneta.success');
    }

    /**
     * Failure
     *
     * @param Request $request
     * @return view
     */
    public function failure(Request $request)
    {
        $order = Order::findByReference($mntTransactionId);

        if (!$order) {
            abort(404);
        }

        $mntTransactionId = $request->input('MNT_TRANSACTION_ID');

        return view('pages.moneta.failure');
    }

    /**
     * Payment
     *
     * @param Request $request
     * @return view
     */
    public function payment(Request $request)
    {
        $mntTransactionId = $request->input('MNT_TRANSACTION_ID');

        $order = Order::findByReference($mntTransactionId);

        if (!$order) {
            abort(404);
        }

        return view('pages.moneta.payment');
    }

    /**
     * Processing
     *
     * @param Request $request
     * @return view
     */
    public function processing(Request $request)
    {
        $mntTransactionId = $request->input('MNT_TRANSACTION_ID');

        $order = Order::findByReference($mntTransactionId);

        if (!$order) {
            abort(404);
        }

        //change status of the invoice
        if (!$order->invoice->isPaid) {
            $order->invoice->setWaiting();
        }

        return view('pages.moneta.processing');
    }

    /**
     * Get magazine id
     *
     * @return int
     */
    public static function getMntId()
    {
        return self::$mntId;
    }
}
