<?php

namespace App\Http\Controllers;

use App\Models\CurrencyData;
use App\Models\Page;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use DB;
use App\Http\Traits\CurrencyTrait;

class CurrenciesController extends Controller
{
    use CurrencyTrait;

    public function index($account)
    {
        $page = Page::whereSlug('settings-currencies')->first();
        $user = Auth::user();

        // Получаем список валют пользователя
        $currencies = $this->getUserCurrencies($user);

        return view(
            'pages.settings.currencies',
            ['page' => $page, 'user' => $user, 'currencies' => $currencies]
        );
    }

    public function create($account)
    {
        $page = Page::whereSlug('settings-currencies')->first();
        $user = Auth::user();

        return view('pages.settings.currency-create', [
            'user' => $user,
            'page' => $page
        ]);
    }

    public function edit($account, $id)
    {
        $user = Auth::user();
        $page = Page::whereSlug('settings-currencies')->first();

        return view('pages.settings.currency-edit', [
            'user' => $user,
            'page' => $page
        ]);
    }

    public function store($account, Request $request)
    {
        $user = Auth::user();

        $currenciesData = CurrencyData::whereCharCode($request->currencie['currencieCharCode'])->get()->first();

        if (!$currenciesData) {
            return response()
                ->json(['status' => 'error', 'fields' => ['currencieCharCode' => 'Такой валюты не существует.']]);
        }

        // Перед сохранением новой валюты проверим, первая ли это валюта в списке
        $currencie        = new Currency();
        $currencie->basic = false;
        //Set base currency if currencies empty
        if (Currency::count() == 0) {
            $currencie->basic = true;
        }

        $currencie->account_id = $user->accountId;
        $currencie->name       = $currenciesData->description;
        $currencie->code       = $currenciesData->code;
        $currencie->sync       = $request->currencie['currencieSync'];
        $currencie->rate       = $request->currencie['currencieRate'];

        // Сохраняем валюту
        $currencie->save();
        // Получаем ID новой валюты
        $currencieId = $currencie->id;

        // Нужно синхронизировать одну валюту
        $this->syncCurrency();
    }

    public function update($account, $id, Request $request)
    {
        if (isset($request->action)) {
            switch ($request->action) {
                case 'change_basic':
                    $currencies = Currency::whereBasic(1)->get();
                    if ($currencies) {
                        foreach ($currencies as $currencie) {
                            $currencie->basic = false;
                            $currencie->save();
                        }
                    }

                    $currencie        = Currency::whereId($id)->first();
                    $currencie->basic = true;
                    $currencie->save();
                    break;
                case 'change_sync':
                    $currencie = Currency::whereId($id)->first();
                    if (isset($currencie->sync)) {
                        $currencie->sync = ($currencie->sync == 0) ? 1 : 0;
                        $currencie->save();
                    } else {
                        $currencie->sync = 1;
                        $currencie->save();
                    }
                    break;
            }
        } else {
            $currenciesData = CurrencyData::whereCharCode($request->currencie['currencieCharCode'])->get()->first();
            if (!$currenciesData) {
                return response()
                    ->json([
                        'status' => 'error',
                        'fields' => ['currencieCharCode' => 'Такой валюты не существует.']
                    ]);
            }
            $currencie          = Currency::whereId($id)->first();
            $currencie->name    = $currenciesData->description;
            $currencie->code    = $currenciesData->code;
            $currencie->sync    = $request->currencie['currencieSync'];
            $currencie->rate    = $request->currencie['currencieRate'];
            $currencie->save();
        }
    }

    public function destroy($account, $id)
    {
        $currencie = Currency::whereId($id)->get()->first();
        if ($currencie) {
            $currencie->delete();
        }
    }

    /**
    * @param $account
    *
    * @return \Illuminate\Http\JsonResponse
    */
    public function listJson($account)
    {
        $user       = Auth::user();
        $currencies = $this->getUserCurrencies($user);
        if ($currencies) {
            return response()->json($currencies, 200, [], JSON_NUMERIC_CHECK);
        }

        return response()->json(['errors' => __('messages.currencyData.not_found')], 422);
    }

    public function syncCurrency()
    {
        /**
         * Синхронизация со сторонним сервисом
         */

        $link   = 'https://www.cbr-xml-daily.ru/daily_json.js';
        $result = json_decode(file_get_contents($link));

        if (isset($result->Valute)) {
            $currenciesData = CurrencyData::all();
            if ($currenciesData) {
                foreach ($currenciesData as $value) {
                    $charCode = $value->char_code;
                    if (isset($result->Valute->$charCode)) {
                        $currencyData = CurrencyData::whereCharCode($charCode)->get()->first();
                        if ($currencyData) {
                            $nominal                   = $result->Valute->$charCode->Nominal;
                            $rate                      = $result->Valute->$charCode->Value;
                            $rate                      = $rate / $nominal;
                            $currencyData->description = $result->Valute->$charCode->Name;
                            $currencyData->rate        = floatval($rate);
                            $currencyData->save();
                        }
                    }
                }
            }
        }
    }

    public function json($account, $id)
    {
        $currency = Currency::whereId($id)->first();
        if ($currency) {
            $data['id']         = $currency->id;
            $data['account_id'] = $currency->account_id;
            $data['name']       = $currency->name;
            $data['code']       = $currency->code;
            $data['sync']       = $currency->sync;
            $data['sign']       = $currency->sign;
            $data['basic']      = $currency->basic;
            $data['rate']       = $currency->rate;
            return response()->json($data, 200, [], JSON_NUMERIC_CHECK);
        }

        return response()->json(['errors' => __('messages.currency.not_found')], 422);
    }

    public function basicDataJson($account)
    {
        $Currencies = CurrencyData::all()->keyBy('code');
        if ($Currencies) {
            return response()->json($Currencies, 200, [], JSON_NUMERIC_CHECK);
        }
        return response()->json(['errors' => __('messages.currencyData.not_found')], 422);
    }
}
