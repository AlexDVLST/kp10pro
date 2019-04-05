<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Invoice;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages.settings.order', ['mntId' => MonetaController::getMntId()]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //Check is exists not paid orders
        $orderNotPaid = Order::with(['invoices' => function ($query) {
            $query->whereNotIn('status', ['paid', 'cancelled']);
        }])->first();

        return view('pages.settings.order-create', ['orderNotPaid' => $orderNotPaid->invoices->isNotEmpty(), 'mntId' => MonetaController::getMntId()]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('pages.settings.order-edit', ['mntId' => MonetaController::getMntId()]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Get orders json list
     *
     * @param string $account
     * @return json
     */
    public function listJson($account)
    {
        $orders = Order::with('invoice')->get();

        if ($orders->isNotEmpty()) {
            $orders->each(function ($order) {
                $order->invoice->statusTranslation = $order->invoice->statusTranslation;
                $order->invoice->isPaid = $order->invoice->isPaid;
                $order->invoice->isCancelled = $order->invoice->isCancelled;
                $order->invoice->isWaiting = $order->invoice->isWaiting;
                $order->invoice->isReady = $order->invoice->isReady;
                $order->created_at_format = $order->created_at->format('H:i j.m.Y');
                $order->started_at_format = $order->started_at ? $order->started_at->format('H:i j.m.Y') : '';
                $order->expired_at_format = $order->expired_at ? $order->expired_at->format('H:i j.m.Y') : '';
            });

            return response()->json($orders);
        }

        return response()->json(['errors' => __('messages.order.list.empty')], 422);
    }

    /**
     * Get active order with invoices
     *
     * @param string $account
     * @return json
     */
    public function activeJson($account)
    {
        $order = Order::with([
            'invoices' => function ($query) {
                $query->whereNotIn('status', ['cancelled']);
            }, 'tariff'])->orderBy('id', 'desc')->first();

        if ($order) {
            // $order->tariff->price         = 330; //Default price for tariff
            $order->isActive              = $order->expired_at ? !$order->expired_at->isPast() : '';
            $order->created_at_format     = $order->created_at->format('H:i j.m.Y');
            $order->started_at_format     = $order->started_at ? $order->started_at->format('H:i j.m.Y') : '';
            $order->expired_at_format     = $order->expired_at ? $order->expired_at->format('H:i j.m.Y') : '';
            $order->daysInPeriod          = $order->expired_at ? $order->expired_at->diffInDays($order->started_at) : '';
            $order->daysToEnd             = $order->expired_at ? $order->expired_at->diffInDays(Carbon::now()) : '';
            $order->invoices->each(function ($invoice) {
                $invoice->statusTranslation = $invoice->statusTranslation;
                $invoice->isPaid = $invoice->isPaid;
                $invoice->isCancelled = $invoice->isCancelled;
                $invoice->isWaiting = $invoice->isWaiting;
                $invoice->isReady = $invoice->isReady;
                $invoice->created_at_format = $invoice->created_at->format('H:i j.m.Y');
                $invoice->payment_info = json_decode($invoice->payment_info);
                $invoice->discount = 0;

                $invoice->lines->each(function ($line) use ($invoice) {
                    if ($line->amount < 0) {
                        $invoice->discount += $line->amount * -1;
                    }
                });
            });

            return response()->json($order);
        }

        return response()->json(['errors' => __('messages.order.list.empty')], 422);
    }

    /**
     * Create order
     *
     * @param string $account
     * @param Request $request
     * @return json
     */
    public function createInvoice($account, Request $request)
    {
        $request->validate([
            'months'       => 'required|numeric|min:1',
            'licenses'     => 'required|numeric|min:1'
        ]);

        $user = Auth::user();

        //Default price of the order
        $months       = $request->input('months');
        $licenses     = $request->input('licenses');

        $order = Order::with(['invoices' => function ($query) {
            $query->whereNotIn('status', ['cancelled']);
        }, 'tariff'])->first();

        $price = $order->tariff->price;

        //Calculate invoice price
        $invoicePrice = $price * $months * $licenses;

        //Create invoice data
        $paymentInfo = ['months' => $months, 'licenses' => $licenses];
        //Invoice title
        $invoiceTitle   = 'Продление заказа';
        $accountBalance = 0;

        if ($order->invoices) {
            $invoice = $order->invoices->filter(function ($invoice) use ($order) {
                return $invoice->id == $order->invoice_id;
            })->first();

            if ($invoice) {
                $dayPrice = round($price / $request->input('daysInPeriod'));

                //Create new
                if (!$request->input('edit')) {
                    //If order is not free, discount 100%
                    if ($invoice->total > 0) {
                        //Account ballance
                        $accountBalance = $dayPrice * $request->input('daysToEnd') * $order->licenses;
                    }

                    if (($invoicePrice - $accountBalance) <= 0) {
                        return response()->json(['errors' => __('messages.invoice.total.error')], 422);
                    }
                } else {
                    //Edit
                    $invoicePrice = $dayPrice * $request->input('daysToEnd') * $months * $licenses;
                }
            }
        }

        //For edit current invoice
        if ($request->input('edit')) {
            $invoiceTitle = 'Редактирование заказа';
            //add marker Order::submit
            $paymentInfo['edit'] = 1;
        }

        try {
            $invoice = $order->invoices()->create(['payment_info' => json_encode($paymentInfo)]);
            //Price
            $invoice = $invoice->addAmountInclTax($invoicePrice, $invoiceTitle, 0);
            //Add discount
            if ($accountBalance > 0) {
                $invoice = $invoice->addAmountInclTax($accountBalance * -1, 'Скидка', 0);
            }
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        return response()->json($invoice);
    }

    /**
     *  Submit order, calculate started,ended dates
     *
     * @param Order $order
     * @param Invoice $invoice
     * @return bool
     */
    public static function submit(Order $order, Invoice $invoice)
    {
        //Submit order
        if ($invoice && $invoice->isReady) {
            $paymentInfo = json_decode($invoice->payment_info);

            if (!isset($paymentInfo->edit)) {
                $startedAt = Carbon::now();
                $expiredAt = Carbon::parse($order->expired_at);

                //If order past
                if ($order->expired_at && $order->expired_at->isPast()) {
                    $expiredAt = $startedAt;
                }

                $order->licenses   = $paymentInfo->licenses;
                $order->started_at = $startedAt->toDateTimeString();
                //Add months
                if (isset($paymentInfo->months) && $paymentInfo->months) {
                    $order->expired_at = $expiredAt->addMonths($paymentInfo->months)->toDateTimeString();
                }
                //Add days
                if (isset($paymentInfo->days) && $paymentInfo->days) {
                    $order->expired_at = $expiredAt->addDays($paymentInfo->days)->toDateTimeString();
                }
                //current active invoice
                $order->invoice_id = $invoice->id;
            } else {
                //Edit license count
                $order->licenses = $order->licenses + $paymentInfo->licenses;
            }

            //Change invoice status
            $invoice->setPaid();

            try {
                $order->save();

                return true;
            } catch (Exception $e) {
                // return response()->json(['errors' => $e->getMessage()], 422);
            }
        }

        return false;
    }
}
