<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\UserMeta;
use App\Models\Tariff;
use App\Models\Order;
use App\Models\Notification as UserNotify;
use App\Http\Controllers\OrderController;
use App\Mail\AdminUserRegisterBonus;

class NotificationController extends Controller
{
    public function viewed($account, $id, Request $request)
    {
        $notification = UserNotify::whereId($id)->first();

        if (!$notification) {
            return response()->json(['errors' => __('messages.notification.not_found')], 422);
        }

        $order = Order::with(['invoices' => function ($query) {
            $query->where('status', '=', 'paid'); //Only paid
        }])->first();

        //If this is not first invoice
        if ($order->invoices->count() != 1) {
            //Disable notification bonus
            $notification->viewed = 1;
            $notification->save();
            //Some response
            return response()->json([]);
        }
        try {
            DB::transaction(function () use ($request, $notification) {
                $notification->viewed = 1;
                $notification->save();

                //May be null
                if (!empty($request->all())) {
                    $request->validate([
                        'firstName' => 'required|string',
                        'lastName'  => 'required|string',
                        'company'   => 'required|string',
                        'phone'     => 'required|phone',
                    ]);

                    $user          = Auth::user();
                    $user->name    = $request->input('firstName');
                    $user->surname = $request->input('lastName');

                    $user->phoneRelation()->first()
                        ->update([
                            'phone' => $request->input('phone')
                        ]);

                    $user->save();

                    //Save name, phone, company
                    UserMeta::updateOrCreate(
                        ['user_id'    => $user->id, 'meta_key'   => 'bonus-name'],
                        ['meta_value' => $user->name . ' ' . $user->surname]
                    );
                    UserMeta::updateOrCreate(
                        ['user_id'    => $user->id, 'meta_key'   => 'bonus-phone'],
                        ['meta_value' => $request->input('phone')]
                    );
                    UserMeta::updateOrCreate(
                        ['user_id'    => $user->id, 'meta_key'   => 'bonus-company'],
                        ['meta_value' => $request->input('company')]
                    );

                    //Add 15 days for currency order
                    $order = Order::first();
                    //Calculate invoice price
                    $price = Tariff::find(1)->price / 30;

                    $days = 15;
                    $licenses = 10;

                    $invoicePrice = $price * $days * $licenses;
                    $paymentInfo    = ['licenses' => $licenses, 'days' => $days];
                    $invoiceTitle   = 'Бонус для новых клиентов';
                    $invoice = $order->invoices()->create(['payment_info' => json_encode($paymentInfo)]);
                    //Price
                    $invoice = $invoice->addAmountInclTax($invoicePrice, $invoiceTitle, 0);
                    $invoice = $invoice->addAmountInclTax($invoicePrice * -1, 'Скидка', 0);
                    //Pay order
                    OrderController::submit($order, $invoice);
                    
                    //Get data
                    $data = $request->all();
                    $data['email'] = $user->email;
                    $data['domain'] = $user->domain;

                    //Admin notification
                    Mail::to('snn@polytell.ru')
                        ->cc('admin@kp10.pro')
                        ->send(new AdminUserRegisterBonus($data));
                }
            });
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        return response()->json();
    }
}
