<?php

namespace App\Http\Traits;

use App\Models\Currency;
use App\Models\CurrencyData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\CurrenciesController;
use App\Scopes\CurrencyScope;

trait CurrencyTrait
{
    /**
     * Универсальный метод получения списка валют пользователя.
     * Он нужен для отображения списка валют пользователя на странице "Валюты"
     * и нужен для json отображения этих валют по отдельному url.
     */
    public function getUserCurrencies($user)
    {
        $accountId = $user->accountId;

        $currencies = Currency::withoutGlobalScope(CurrencyScope::class)->whereAccountId($accountId)->get();
        
        $basicCurrencyCode = Currency::withoutGlobalScope(CurrencyScope::class)->whereAccountId($accountId)->whereBasic(1)->get()->first();
        if ($basicCurrencyCode) {
            $basicCurrencyCode = $basicCurrencyCode->code;
            $basicCurrencyRate = CurrencyData::whereCode($basicCurrencyCode)->get()->first()->rate;
        } else {
            $basicCurrencyRate = 1;
        }

        $currencies = Currency::withoutGlobalScope(CurrencyScope::class)->whereAccountId($accountId)->get();
        $currencies->each(function ($item, $key) use (&$currencies, $basicCurrencyRate) {
            $system = $item->system()->get()->first();
            if ($system) {
                $currencies[$key]->syncRate = number_format(($system->rate) / $basicCurrencyRate, 4, '.', ' ');
                $currencies[$key]->charCode = $system->char_code;
            }
        });

        return $currencies;
    }

    public function copyCurrencies($user)
    {
        $accountId = $user->accountId;

        $currenciesData = CurrencyData::all()->keyBy('code');
        if ($currenciesData) {
            $currenciesData->each(function ($currenciesDataValue, $currenciesDataKey) use ($accountId) {
                $currency = new Currency();
                if ($currenciesDataValue->code == 643) {
                    $currency->basic = 1;
                    $currency->sync  = 0;
                } else {
                    $currency->basic = 0;
                    $currency->sync  = 1;
                }
                $currency->account_id = $accountId;
                $currency->name    = $currenciesDataValue->description;
                $currency->code    = $currenciesDataValue->code;
                $currency->rate    = $currenciesDataValue->rate;
                $currency->save();
                unset($currencie);
            });
            $CurrenciseController = new CurrenciesController();
            $CurrenciseController->syncCurrency();
        }
    }
}
