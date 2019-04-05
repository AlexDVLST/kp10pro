<?php

namespace App\Listeners;

use App\Models\User;
use App\Models\Offer;
use App\Models\UserMeta;
use App\Models\UserPhone;
use App\Models\UserPosition;
use App\Models\UserSignature;
use App\Models\UserAvatar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Models\OfferNumber;
use App\Models\Order;
use App\Http\Controllers\OrderController;
use App\Models\Tariff;
use App\Models\OfferVariant;
use App\Http\Controllers\EditorController;
use App\Models\OfferTemplate;
use App\Http\Traits\OfferTrait;
use App\Http\Traits\CurrencyTrait;
use App\Http\Controllers\CurrenciesController;
use App\Mail\AdminUserRegistered;
use App\Mail\UserRegistered;
use App\Scopes\OrderScope;
use App\Models\Notification;

class UserEventSubscriber
{
    use OfferTrait, CurrencyTrait;

    /**
     * Handle user registered events.
     * @param $event
     */
    public function onUserRegistered($event)
    {
        $user = $event->user;

        DB::transaction(function () use ($user) {
            //Assign role to new user
            $user->assignRole('user');
            //Asign employee role
            $user->assignRole('employee');

            //Save phone
            $user->phoneRelation()
                ->firstOrCreate(['phone' => '']);
            //Save position
            $user->positionRelation()
                ->save(new UserPosition(['position' => 'Директор']));
            //Save signature
            $user->signatureRelation()
                ->firstOrCreate(['signature' => '']);
            //Save avatar
            $user->avatarRelation()
                ->save(new UserAvatar(['file_id' => 0]));

            //Add meta show tour
            // $userMeta             = new UserMeta();
            // $userMeta->user_id    = $user->id;
            // $userMeta->meta_key   = 'show-tour';
            // $userMeta->meta_value = '1';
            // $userMeta->save();

            //Create notification with bonus
            Notification::create([
                'account_id' => $user->id,
                'type_id'    => 1,
                'view'       => 'register-bonus'
            ]);

            // Currencies
            $this->copyCurrencies($user);

            $offer = $this->copyOffer($user, 1, new Request(['name' => 'Базовый']), 1);
            if ($offer) {
                $offer->numberRelation()->update(['number' => 1]);
                $offer->save();
            }

            //Create order
            $order = Order::create([
                'user_id'    => $user->id,
                'account_id' => $user->accountId,
                'licenses'   => 0,
                'invoice_id' => 0,
                'tariff_id'  => 1
            ]);

            //Calculate invoice price
            $price = Tariff::find(1)->price / 30;

            $days = 14;
            $licenses = 10;

            $invoicePrice = $price * $days * $licenses;
            $paymentInfo    = ['licenses' => $licenses, 'days' => $days];
            $invoiceTitle   = 'Создание заказа';

            $invoice        = $order->invoices()->create(['payment_info' => json_encode($paymentInfo)]);
            //Price
            $invoice = $invoice->addAmountInclTax($invoicePrice, $invoiceTitle, 0);
            $invoice = $invoice->addAmountInclTax($invoicePrice * -1, 'Скидка', 0);
            //Pay order
            OrderController::submit($order, $invoice);

            try {
                //Send mail to registered user
                Mail::to($user->email)
                    ->send(new UserRegistered($user));

                //Admin notification
                Mail::to('snn@polytell.ru')
                    ->cc('admin@kp10.pro')
                    ->send(new AdminUserRegistered($user));
            } catch (Exception $e) {
                Log::debug($e);
            }
        });
    }

    /**
     * Handle user login events.
     * @param $event
     */
    public function onUserLogin($event)
    {
    }

    /**
     * Handle user logout events.
     * @param $event
     */
    public function onUserLogout($event)
    {
        //Clear user online status
        if (Auth::check()) {
            Cache::forget('user-is-online-' . Auth::user()->id);
        }
        //Remove user sessions from table
        DB::table('sessions')->whereId(Session::getId())->delete();
    }

    /**
     * Register the listeners for the subscriber.
     * @param $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Illuminate\Auth\Events\Login',
            'App\Listeners\UserEventSubscriber@onUserLogin'
        );

        $events->listen(
            'Illuminate\Auth\Events\Registered',
            'App\Listeners\UserEventSubscriber@onUserRegistered'
        );

        $events->listen(
            'Illuminate\Auth\Events\Logout',
            'App\Listeners\UserEventSubscriber@onUserLogout'
        );
    }
}
