<?php

namespace App\Http\Controllers;

use App\Helpers\StorageHelper;
use App\Jobs\ScenarioJob;
use App\Models\IntegrationBitrix24;
use App\Models\IntegrationBitrix24CustomField;
use App\Models\Offer;
use App\Models\OfferHistory;
use App\Models\Product;
use App\Models\User;
use App\Models\OfferNumber;
use App\Models\Client;
use App\Models\OfferVariant;
use App\Models\Integration;
use App\Models\IntegrationAmocrm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\IntegrationMegaplanField;
use App\Models\IntegrationAmocrmLeadField;
use function GuzzleHttp\json_encode;
use App\Models\IntegrationAmocrmCustomField;
use App\Http\Traits\CurrencyTrait;
use App\Http\Traits\OfferTrait;
use App\Helpers\MegaplanV3;
use App\Models\IntegrationMegaplan;
use Jenssegers\Agent\Agent;
use App\Jobs\UpdateCrmFieldsJob;
use Carbon\Carbon;

class EditorController extends Controller
{
    use CurrencyTrait, OfferTrait;

    public function __construct()
    {
        $this->middleware(['permission:view offer|view-own offer']);
    }

    public function index($account, $offerId)
    {
        $agent = new Agent();

        //For mobile devices
        if ($agent->isMobile() || $agent->isTablet()) {
            return view('editor-mobile');
        }

        $user = Auth::user();

        //Get integration settings
        $integration = Integration::first();

        $style           = '';
        $assets          = StorageHelper::getStorageData();
        $path            = StorageHelper::getUploadPath();
        $storagePath     = StorageHelper::getStoragePath();
        $awesomeIcons    = $this->getFontAwesomeIcons();
        $variantSelected = [];
        $templateVersion = '';

        //Get employess for account
        $employees = User::role('employee')->with('avatarRelation.file', 'signatureRelation')->whereDomain($account)->get();
        //fix for editor
        $employees->each(function ($employee) {
            //Need update current object from model relation
            $employee->displayName = $employee->displayName;
            $employee->avatarUrl = $employee->avatarUrl;
            $employee->signature = $employee->signature;
        });

        //add icons for advantage block
        if ($awesomeIcons) {
            foreach ($awesomeIcons as $icon) {
                $assets[] = [
                    'type' => 'advantage-image',
                    'cls'  => $icon,
                ];
            }
        }

        //Product list
        $products = Product::with('file.file')->get();
        //fix for editor
        $products->each(function (&$product) {
            //Check availability image src
            if ($product->file->first()) {
                $product->photo = $product->file->first()->url;
            } else {
                $product->photo = url('/storage/resource/templates/base/product/empty.png');
            }
        });

        //Получаем данные шаблона
        $offer = Offer::whereId($offerId)
            ->with(
                'variants.products.values',
                'variants.fields',
                'numberRelation',
                'employee.user.smtpEmails',
                'employee.user.signatureRelation',
                'clientRelation.client.emailRelation',
                'contactPersonRelation.client',
                'currency',
                'amocrmDeal.data.fields.values',
                'megaplanDeal.values.field',
                'bitrix24Deal.data.fields.values',
                'userTemplate'
            )
            ->select('id', 'offer_name', 'created_at', 'updated_at', 'url', 'user_id', 'system')
            ->first();

        if ($offer) {
            //Format dates
            $offer->created_at_formatted = $offer->created_at->format('j.m.y');
            $offer->updated_at_formatted = $offer->updated_at->format('H:i j.m.y');
            $offer->number               = $offer->number;

            //Check if has selected variant
            $variantSelected = $offer->variants->filter(function ($variant) {
                return $variant->selected == 1;
            })->first();
            //Fix for js
            if ($offer->clientRelation && $offer->clientRelation->client) {
                $offer->clientRelation->client->displayName = $offer->clientRelation->client->displayName;
            }
            if ($offer->contactPersonRelation && $offer->contactPersonRelation->client) {
                $offer->contactPersonRelation->client->displayName = $offer->contactPersonRelation->client->displayName;
            }

            $templateVersion = $offer->template->version;
        } else {
            abort(403);
        }
        //Get integration fields
        if ($integration) {
            if ($integration->system_crm_id == 1) { //Megaplan
                $integration->fields = IntegrationMegaplanField::with('enums', 'contentTypes', 'program')->get();
                //Create link to deal card
                if ($offer->megaplanDeal) {
                    $integrationCrm      = IntegrationMegaplan::first();
                    if ($integrationCrm) {
                        $offer->dealCardLink = 'https://' . $integrationCrm->host . '/deals/' . $offer->megaplanDeal->deal_id . '/card';
                    }
                }
            }
            if ($integration->system_crm_id == 2) { //Amocrm
                $integration->fields = IntegrationAmocrmCustomField::with('enums')->whereType('lead')->get();
                //Create link to deal card
                if ($offer->amocrmDeal) {
                    $integrationCrm      = IntegrationAmocrm::first();
                    if ($integrationCrm) {
                        $offer->dealCardLink = 'https://' . $integrationCrm->host . '/leads/detail/' . $offer->amocrmDeal->deal_id;
                    }
                }
            }
            if ($integration->system_crm_id == 3) { //Bitrix
                $integration->fields = IntegrationBitrix24CustomField::with('enums')->whereType('lead')->get();
                //Create link to deal card
                if ($offer->bitrix24Deal) {
                    $integrationCrm      = IntegrationBitrix24::first();
                    if ($integrationCrm) {
                        $offer->dealCardLink = 'https://' . $integrationCrm->host . '/crm/deal/details/' . $offer->bitrix24Deal->deal_id . '/';
                    }
                }
            }
        }
        //Currencies
        $currencies = $this->getUserCurrencies($user);

        $webUrlOffer = env('APP_PROTOCOL') . $user->domain . '.' . env('APP_DOMAIN') . '/' . $offer->url;

        return view(
            'editor',
            [
                'user'            => $user,
                'offer'           => $offer,
                'style'           => $style,
                'path'            => $path,
                'storagePath'     => $storagePath,
                'assets'          => json_encode($assets),
                'products'        => json_encode($products),
                'systemOffer'     => $offer ? $offer->system : 1,
                'employees'       => json_encode($employees),
                'userOfferOwner'  => $offer ? $user->id == $offer->user_id : false,
                'urlOffer'        => $offer ? $offer->url : '',
                'variantSelected' => $variantSelected,
                'integration'     => json_encode($integration),
                'currencies'      => json_encode($currencies),
                'templateVersion' => $templateVersion,
                'webUrlOffer'     => $webUrlOffer,
                'isTemplate'      => $offer->userTemplate && $offer->userTemplate->is_template ? true : false
            ]
        );
    }

    /**
     * Get template by id
     * @param $account
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function load($account, $id, Request $request)
    {
        $offer = Offer::find($id);

        if (!$offer) {
            return response()
                ->json([
                    'gjs-html' => '<div><h2 class="alert alert-danger"><strong>Ошибка! </strong>Шаблон не найден</h2></div>',
                ]);
        }

        return response()
            ->json([
                'gjs-assets'     => $offer->gjs_assets,
                'gjs-components' => $offer->gjs_components,
                'gjs-css'        => $offer->gjs_css,
                'gjs-html'       => $offer->gjs_html,
                'gjs-styles'     => $offer->gjs_styles,
            ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function assetsLoad(Request $request)
    {
        $awesomeIcons    = $this->getFontAwesomeIcons();
        $assets          = StorageHelper::getStorageData();
        //add icons for advantage block
        if ($awesomeIcons) {
            foreach ($awesomeIcons as $icon) {
                $assets[] = [
                    'type' => 'advantage-image',
                    'cls'  => $icon,
                ];
            }
        }
        return response()
            ->json($assets);
    }

    /**
     * Create emty offer
     *
     * @param Request $request
     * @return json
     */
    public function createEmptyOffer($account, Request $request)
    {
        //Создание нового шаблона.
        /* При создании нового шаблона отправляем запрос для создании записи с новым названием и parent_id */
        /* После чего мы возвращаем id новой записи и подставляем её в конфиг модуля js. Вызываем метод Store. */

        /* Создание короткой ссылки */

        $parentOfferId   = $request->get('parentOfferId');
        $name            = $request->get('name');
        $settings        = $request->get('settings');
        $clientId        = 0;
        $contactPersonId = 0;

        $parentOffer = Offer::whereId($parentOfferId)->first();

        if (!$parentOffer) {
            return response()->json(['errors' => __('messages.offer.not_found')], 422);
        }

        //Settings
        if (isset($settings['client']) && isset($settings['client']['id'])) {
            $clientId = (int)$settings['client']['id'];
        }
        if (isset($settings['contactPerson']) && isset($settings['contactPerson']['id'])) {
            $contactPersonId = (int)$settings['contactPerson']['id'];
        }

        //Get latest offer number
        $latestNumber = Offer::latest()->first()->number;

        $user = Auth::user();

        $offer                   = new Offer();
        $offer->user_id          = $user->id;
        $offer->offer_name       = $name;
        $offer->url              = self::generateUrl(5);
        $offer->system           = false;
        $offer->parent_offer_id  = $parentOfferId;
        // $offer->template        = $parentOffer->template;
        $offer->account_id       = $user->accountId;

        try {
            $offer->save();

            //Save number after offer was saved
            $offer->numberRelation()
                ->save(new OfferNumber(['number' => $latestNumber + 1]));

            //Create client relation
            $offer->clientRelation()
                ->create(['client_id' => $clientId]);

            $offer->contactPersonRelation()
                ->create(['client_id' => $contactPersonId]);

            //Create state
            $offer->state()
                ->create(['state_id' => 1]);

            //Template information
            $offer->template()
                ->create([
                    'name'    => $parentOffer->template->name,
                    'version' => $parentOffer->template->version,
                ]);

            //Create employee of the template
            $offer->employee()
                 ->create(['user_id' => Auth::user()->id]);

            //Variants
            OfferVariant::insertDefault($offer->id);
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        return response()
            ->json(['id' => $offer->id, 'message' => __('messages.offer.save.success')]);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($account, $id, Request $request)
    {
        $user            = Auth::user();
        $accountId       = $user->accountId;
        $components      = $request->get('gjs-components');
        $css             = $request->get('gjs-css');
        $html            = $request->get('gjs-html');
        $styles          = $request->get('gjs-styles');
        $settings        = $request->get('settings');
        $storeData       = $request->get('storeData');
        $currencyId      = 0;

        $offer = Offer::with(
            'clientRelation',
            'contactPersonRelation',
            'variants.products.values',
            'variants.fields',
            'variants.specialDiscounts',
            'employee',
            'currency',
            'amocrmDeal',
            'megaplanDeal',
            'bitrix24Deal'
        )->find($id);

        if (!$offer) {
            return response()->json(['errors' => __('messages.offer.not_found')], 422);
        }

        //Check if has selected variant
        $variantSelected = $offer->variants->filter(function ($variant) {
            return $variant->selected == 1;
        })->first();

        if ($variantSelected) {
            return response()->json(['errors' => __('messages.offer.variant.already_selected')], 422);
        }

        //Restore previous client/contact person
        $clientId        = $offer->clientRelation ? $offer->clientRelation->client_id : 0;
        $contactPersonId = $offer->contactPersonRelation ? $offer->contactPersonRelation->client_id : 0;

        //Settings offer
        //Client
        if (isset($settings['client']) && isset($settings['client']['id'])) {
            $clientId = (int)$settings['client']['id'];
        }

        //Contact person
        if (isset($settings['contactPerson']) && isset($settings['contactPerson']['id'])) {
            $contactPersonId = (int)$settings['contactPerson']['id'];
        }

        //Currency
        if (isset($settings['currency']) && isset($settings['currency']['id'])) {
            $currencyId = (int)$settings['currency']['id'];
        }

        //Update variant data
        if (isset($settings['variants']) && !empty($settings['variants'])) {
            foreach ($settings['variants'] as $variant) {
                //Find variant
                $offerVariant = $offer->variants()->whereId($variant['id'])->first();
                if ($offerVariant) {
                    if (isset($variant['name'])) {
                        $offerVariant->name   = $variant['name'];
                    }
                    if (isset($variant['price'])) {
                        $offerVariant->price  = $variant['price'];
                    }
                    if (isset($variant['active'])) {
                        $offerVariant->active = $variant['active'];
                    }
                    if (isset($variant['tax'])) {
                        $offerVariant->tax = $variant['tax'];
                    }
                    if (isset($variant['recommended'])) {
                        $offerVariant->recommended = $variant['recommended'];
                    }

                    try {
                        $offerVariant->save();

                        //Save products
                        if (isset($variant['products']) && is_array($variant['products'])) {
                            foreach ($variant['products'] as $product) {
                                //Fix
                                if (!isset($product['db-id'])) {
                                    continue;
                                }
                                //Check if product already in DB
                                $exist = $offerVariant->products->filter(function ($value) use ($product) {
                                    return $product['db-id'] == $value['id'];
                                })->first();

                                if ($exist) {
                                    //Delete product
                                    if (isset($product['delete'])) {
                                        $exist->delete();
                                        continue;
                                    }

                                    if (isset($product['description'])) {
                                        $exist->description = $product['description'];
                                    }
                                    if (isset($product['image'])) {
                                        $exist->image = $product['image'];
                                    }
                                    if (isset($product['index'])) {
                                        $exist->index = $product['index'];
                                    }
                                    $exist->save();
                                } elseif (!isset($product['delete'])) {
                                    $create = [
                                        'offer_id'        => $id,
                                        'product_id'      => isset($product['id']) ? $product['id'] : 0,
                                        'group'           => isset($product['group']) ? $product['group'] : 0,
                                        'description'     => isset($product['description']) ? $product['description'] : '',
                                        'fake_product_id' => isset($product['fakeProductId']) ? $product['fakeProductId'] : 0,
                                        'image'           => isset($product['image']) ? $product['image'] : '',
                                        'index'           => $product['index']
                                    ];

                                    $exist = $offerVariant->products()->create($create);
                                }
                                //For product field values
                                if (!isset($product['delete']) && isset($product['values']) && is_array($product['values'])) {
                                    foreach ($product['values'] as $fieldValue) {
                                        if (!isset($fieldValue['db-id'])) {
                                            continue;
                                        }

                                        //Check if exists
                                        $valueExist = $exist->values->filter(function ($value) use ($fieldValue) {
                                            return $fieldValue['db-id'] == $value['id'];
                                        })->first();

                                        if ($valueExist) {
                                            //Delete field value
                                            if (isset($fieldValue['delete'])) {
                                                $valueExist->delete();
                                                continue;
                                            }
                                            if (isset($fieldValue['index'])) {
                                                $valueExist->index = $fieldValue['index'];
                                            }
                                            if (isset($fieldValue['value'])) {
                                                $valueExist->value = $fieldValue['value'];
                                            }
                                            if (isset($fieldValue['type'])) {
                                                $valueExist->type = $fieldValue['type'];
                                            }
                                            if (isset($fieldValue['valueInPrice'])) {
                                                $valueExist->value_in_price = $fieldValue['valueInPrice'];
                                            }

                                            $valueExist->save();
                                        } elseif (!isset($fieldValue['delete'])) {
                                            //Create
                                            $exist->values()->create([
                                                'variant_product_id' => $exist->id,
                                                'index'              => $fieldValue['index'],
                                                'value'              => $fieldValue['value'],
                                                'type'               => $fieldValue['type'],
                                                'value_in_price'     => isset($fieldValue['valueInPrice']) ? $fieldValue['valueInPrice'] : 0
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                        //Save fields
                        if (isset($variant['fields']) && is_array($variant['fields'])) {
                            foreach ($variant['fields'] as $field) {
                                if (!isset($field['db-id'])) {
                                    continue;
                                }

                                //Check if field already in DB
                                $exist = $offerVariant->fields->filter(function ($value) use ($field) {
                                    //Find by index becouse column cannot be move
                                    return $field['db-id'] == $value['id'];
                                })->first();

                                if ($exist) {
                                    //Delete field value
                                    if (isset($field['delete'])) {
                                        $exist->delete();
                                        continue;
                                    }
                                    if (isset($field['name'])) {
                                        $exist->name  = $field['name'];
                                    }
                                    if (isset($field['index'])) {
                                        $exist->index = $field['index'];
                                    }
                                    if (isset($field['type'])) {
                                        $exist->type  = $field['type'];
                                    }
                                    $exist->save();
                                } elseif (!isset($field['delete'])) {
                                    $create = [
                                        'name'  => $field['name'],
                                        'index' => $field['index'],
                                        'type'  => $field['type']
                                    ];

                                    $offerVariant->fields()->create($create);
                                }
                            }
                        }
                        //Special discount
                        if (isset($variant['special_discounts']) && is_array($variant['special_discounts'])) {
                            foreach ($variant['special_discounts'] as $sDiscount) {
                                if (!isset($sDiscount['db-id'])) {
                                    continue;
                                }

                                //Check if field already in DB
                                $exist = $offerVariant->specialDiscounts->filter(function ($value) use ($sDiscount) {
                                    //Find by index becouse column cannot be move
                                    return $sDiscount['db-id'] == $value['id'];
                                })->first();

                                if ($exist) {
                                    //Delete field value
                                    if (isset($sDiscount['delete'])) {
                                        $exist->delete();
                                        continue;
                                    }
                                    if (isset($sDiscount['name'])) {
                                        $exist->name  = $sDiscount['name'];
                                    }
                                    if (isset($sDiscount['index'])) {
                                        $exist->index = $sDiscount['index'];
                                    }
                                    if (isset($sDiscount['value'])) {
                                        $exist->value  = $sDiscount['value'];
                                    }
                                    $exist->save();
                                } elseif (!isset($sDiscount['delete'])) {
                                    $create = [
                                        'name'   => $sDiscount['name'],
                                        'index'  => $sDiscount['index'],
                                        'value'  => $sDiscount['value']
                                    ];

                                    $offerVariant->specialDiscounts()->create($create);
                                }
                            }
                        }
                    } catch (Exception $e) {
                    }
                }
            }
        }

        // Integration update fields
        if ($storeData && isset($settings['integration'])) {
            Log::debug('Integration update fields');
            UpdateCrmFieldsJob::dispatch($offer, $settings, $user);
        }

        $offer->gjs_components = $components ? $components : $offer->gjs_components;
        $offer->gjs_css        = $css ? $css : $offer->gjs_css;
        $offer->gjs_html       = $html ? $html : $offer->gjs_html;
        $offer->gjs_styles     = $styles ? $styles : $offer->gjs_styles;
        $offer->user_id        = Auth::user()->id;

        if ($offer->system == '1') {
            $offer->offer_name = 'Базовый';
        }

        //Update client
        if ($offer->clientRelation) {
            $offer->clientRelation()->update(['client_id' => $clientId]);
        } else {
            $offer->clientRelation()->create(['client_id' => $clientId]);
        }

        //Update contact person
        if ($offer->contactPersonRelation) {
            $offer->contactPersonRelation()->update(['client_id' => $contactPersonId]);
        } else {
            $offer->contactPersonRelation()->create(['client_id' => $contactPersonId]);
        }

        //Update employee
        if (isset($settings['employee']) && isset($settings['employee']['id'])) {
            $offer->employee()->update(['user_id' => $settings['employee']['id']]);
        }

        //Update contact person
        if ($offer->currency) {
            $offer->currency()->update(['currency_id' => $currencyId]);
        } else {
            $offer->currency()->create(['currency_id' => $currencyId]);
        }

        try {
            $offer->save();

            //Сохраняют кп
            if ($storeData) {
                //Для сценария (Менеджер сохранил КП)
                ScenarioJob::dispatch(6, 4, $user, $offer);
            }
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        return response()->json(['message' => __('messages.offer.save.success')]);
    }

    /**
     * @param int $length
     * @return string
     */
    public static function generateUrl($length = 6)
    {
        $chars    = 'abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $n        = rand(0, strlen($chars) - 1);
            $password .= $chars[$n];
        }

        if (Offer::whereUrl($password)->first()) {
            $password = self::generateUrl($length);
        }

        return $password;
    }

    /**
     * Save html offer
     *
     * @param string $account
     * @param Request $request
     * @return json
     */
    public function saveHtml($account, Request $request)
    {
        //html data
        $file = $request->file('blob');
        //offer
        $id   = $request->input('id');
        //find offer
        $offer = Offer::with(
            'variants.products.values',
            'variants.fields',
            'variants.specialDiscounts',
            'numberRelation',
            'clientRelation.client.emailRelation',
            'contactPersonRelation.client',
            'employee.user.smtpEmails',
            'employee.user.signatureRelation'
            )->find($id);

        //Offer not found
        if (!$offer) {
            return response()->json(['errors' => __('messages.offer.not_found')], 422);
        }

        //Empty url
        if (!$offer->url) {
            return response()->json(['errors' => __('messages.offer.url_empty')], 422);
        }

        //Check if has selected variant
        $variantSelected = $offer->variants->filter(function ($variant) {
            return $variant->selected == 1;
        })->first();

        if ($variantSelected) {
            return response()->json(['errors' => __('messages.offer.variant.already_selected')], 422);
        }

        try {
            Storage::putFileAs('public/offers/' . $offer->url, $file, 'index.html');
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        //Format dates
        $offer->created_at_formatted = $offer->created_at->format('j.m.y');
        $offer->updated_at_formatted = $offer->updated_at->format('H:i j.m.y');

        //Fix for js
        if ($offer->clientRelation && $offer->clientRelation->client) {
            $offer->clientRelation->client->displayName = $offer->clientRelation->client->displayName;
        }
        if ($offer->contactPersonRelation && $offer->contactPersonRelation->client) {
            $offer->contactPersonRelation->client->displayName = $offer->contactPersonRelation->client->displayName;
        }

        if ($offer) {
            unset($offer->gjs_components, $offer->gjs_css, $offer->gjs_html, $offer->gjs_styles, $offer->gjs_assets);
        }

        return response()->json(['message' => __('messages.offer.save.success'), 'offer' => $offer]);
    }

    /**
     * Icon list
     * @return array
     */
    public function getFontAwesomeIcons()
    {
        return [
            'fa-glass',
            'fa-music',
            'fa-search',
            'fa-envelope-o',
            'fa-heart',
            'fa-star',
            'fa-star-o',
            'fa-user',
            'fa-film',
            'fa-th-large',
            'fa-th',
            'fa-th-list',
            'fa-check',
            'fa-remove',
            'fa-close',
            'fa-times',
            'fa-search-plus',
            'fa-search-minus',
            'fa-power-off',
            'fa-signal',
            'fa-gear',
            'fa-cog',
            'fa-trash-o',
            'fa-home',
            'fa-file-o',
            'fa-clock-o',
            'fa-road',
            'fa-download',
            'fa-arrow-circle-o-down',
            'fa-arrow-circle-o-up',
            'fa-inbox',
            'fa-play-circle-o',
            'fa-rotate-right',
            'fa-repeat',
            'fa-refresh',
            'fa-list-alt',
            'fa-lock',
            'fa-flag',
            'fa-headphones',
            'fa-volume-off',
            'fa-volume-down',
            'fa-volume-up',
            'fa-qrcode',
            'fa-barcode',
            'fa-tag',
            'fa-tags',
            'fa-book',
            'fa-bookmark',
            'fa-print',
            'fa-camera',
            'fa-font',
            'fa-bold',
            'fa-italic',
            'fa-text-height',
            'fa-text-width',
            'fa-align-left',
            'fa-align-center',
            'fa-align-right',
            'fa-align-justify',
            'fa-list',
            'fa-dedent',
            'fa-outdent',
            'fa-indent',
            'fa-video-camera',
            'fa-photo',
            'fa-image',
            'fa-picture-o',
            'fa-pencil',
            'fa-map-marker',
            'fa-adjust',
            'fa-tint',
            'fa-edit',
            'fa-pencil-square-o',
            'fa-share-square-o',
            'fa-check-square-o',
            'fa-arrows',
            'fa-step-backward',
            'fa-fast-backward',
            'fa-backward',
            'fa-play',
            'fa-pause',
            'fa-stop',
            'fa-forward',
            'fa-fast-forward',
            'fa-step-forward',
            'fa-eject',
            'fa-chevron-left',
            'fa-chevron-right',
            'fa-plus-circle',
            'fa-minus-circle',
            'fa-times-circle',
            'fa-check-circle',
            'fa-question-circle',
            'fa-info-circle',
            'fa-crosshairs',
            'fa-times-circle-o',
            'fa-check-circle-o',
            'fa-ban',
            'fa-arrow-left',
            'fa-arrow-right',
            'fa-arrow-up',
            'fa-arrow-down',
            'fa-mail-forward',
            'fa-share',
            'fa-expand',
            'fa-compress',
            'fa-plus',
            'fa-minus',
            'fa-asterisk',
            'fa-exclamation-circle',
            'fa-gift',
            'fa-leaf',
            'fa-fire',
            'fa-eye',
            'fa-eye-slash',
            'fa-warning',
            'fa-exclamation-triangle',
            'fa-plane',
            'fa-calendar',
            'fa-random',
            'fa-comment',
            'fa-magnet',
            'fa-chevron-up',
            'fa-chevron-down',
            'fa-retweet',
            'fa-shopping-cart',
            'fa-folder',
            'fa-folder-open',
            'fa-arrows-v',
            'fa-arrows-h',
            'fa-bar-chart-o',
            'fa-bar-chart',
            'fa-twitter-square',
            'fa-facebook-square',
            'fa-camera-retro',
            'fa-key',
            'fa-gears',
            'fa-cogs',
            'fa-comments',
            'fa-thumbs-o-up',
            'fa-thumbs-o-down',
            'fa-star-half',
            'fa-heart-o',
            'fa-sign-out',
            'fa-linkedin-square',
            'fa-thumb-tack',
            'fa-external-link',
            'fa-sign-in',
            'fa-trophy',
            'fa-github-square',
            'fa-upload',
            'fa-lemon-o',
            'fa-phone',
            'fa-square-o',
            'fa-bookmark-o',
            'fa-phone-square',
            'fa-twitter',
            'fa-facebook-f',
            'fa-facebook',
            'fa-github',
            'fa-unlock',
            'fa-credit-card',
            'fa-feed',
            'fa-rss',
            'fa-hdd-o',
            'fa-bullhorn',
            'fa-bell',
            'fa-certificate',
            'fa-hand-o-right',
            'fa-hand-o-left',
            'fa-hand-o-up',
            'fa-hand-o-down',
            'fa-arrow-circle-left',
            'fa-arrow-circle-right',
            'fa-arrow-circle-up',
            'fa-arrow-circle-down',
            'fa-globe',
            'fa-wrench',
            'fa-tasks',
            'fa-filter',
            'fa-briefcase',
            'fa-arrows-alt',
            'fa-group',
            'fa-users',
            'fa-chain',
            'fa-link',
            'fa-cloud',
            'fa-flask',
            'fa-cut',
            'fa-scissors',
            'fa-copy',
            'fa-files-o',
            'fa-paperclip',
            'fa-save',
            'fa-floppy-o',
            'fa-square',
            'fa-navicon',
            'fa-reorder',
            'fa-bars',
            'fa-list-ul',
            'fa-list-ol',
            'fa-strikethrough',
            'fa-underline',
            'fa-table',
            'fa-magic',
            'fa-truck',
            'fa-pinterest',
            'fa-pinterest-square',
            'fa-google-plus-square',
            'fa-google-plus',
            'fa-money',
            'fa-caret-down',
            'fa-caret-up',
            'fa-caret-left',
            'fa-caret-right',
            'fa-columns',
            'fa-unsorted',
            'fa-sort',
            'fa-sort-down',
            'fa-sort-desc',
            'fa-sort-up',
            'fa-sort-asc',
            'fa-envelope',
            'fa-linkedin',
            'fa-rotate-left',
            'fa-undo',
            'fa-legal',
            'fa-gavel',
            'fa-dashboard',
            'fa-tachometer',
            'fa-comment-o',
            'fa-comments-o',
            'fa-flash',
            'fa-bolt',
            'fa-sitemap',
            'fa-umbrella',
            'fa-paste',
            'fa-clipboard',
            'fa-lightbulb-o',
            'fa-exchange',
            'fa-cloud-download',
            'fa-cloud-upload',
            'fa-user-md',
            'fa-stethoscope',
            'fa-suitcase',
            'fa-bell-o',
            'fa-coffee',
            'fa-cutlery',
            'fa-file-text-o',
            'fa-building-o',
            'fa-hospital-o',
            'fa-ambulance',
            'fa-medkit',
            'fa-fighter-jet',
            'fa-beer',
            'fa-h-square',
            'fa-plus-square',
            'fa-angle-double-left',
            'fa-angle-double-right',
            'fa-angle-double-up',
            'fa-angle-double-down',
            'fa-angle-left',
            'fa-angle-right',
            'fa-angle-up',
            'fa-angle-down',
            'fa-desktop',
            'fa-laptop',
            'fa-tablet',
            'fa-mobile-phone',
            'fa-mobile',
            'fa-circle-o',
            'fa-quote-left',
            'fa-quote-right',
            'fa-spinner',
            'fa-circle',
            'fa-mail-reply',
            'fa-reply',
            'fa-github-alt',
            'fa-folder-o',
            'fa-folder-open-o',
            'fa-smile-o',
            'fa-frown-o',
            'fa-meh-o',
            'fa-gamepad',
            'fa-keyboard-o',
            'fa-flag-o',
            'fa-flag-checkered',
            'fa-terminal',
            'fa-code',
            'fa-mail-reply-all',
            'fa-reply-all',
            'fa-star-half-empty',
            'fa-star-half-full',
            'fa-star-half-o',
            'fa-location-arrow',
            'fa-crop',
            'fa-code-fork',
            'fa-unlink',
            'fa-chain-broken',
            'fa-question',
            'fa-info',
            'fa-exclamation',
            'fa-superscript',
            'fa-subscript',
            'fa-eraser',
            'fa-puzzle-piece',
            'fa-microphone',
            'fa-microphone-slash',
            'fa-shield',
            'fa-calendar-o',
            'fa-fire-extinguisher',
            'fa-rocket',
            'fa-maxcdn',
            'fa-chevron-circle-left',
            'fa-chevron-circle-right',
            'fa-chevron-circle-up',
            'fa-chevron-circle-down',
            'fa-html5',
            'fa-css3',
            'fa-anchor',
            'fa-unlock-alt',
            'fa-bullseye',
            'fa-ellipsis-h',
            'fa-ellipsis-v',
            'fa-rss-square',
            'fa-play-circle',
            'fa-ticket',
            'fa-minus-square',
            'fa-minus-square-o',
            'fa-level-up',
            'fa-level-down',
            'fa-check-square',
            'fa-pencil-square',
            'fa-external-link-square',
            'fa-share-square',
            'fa-compass',
            'fa-toggle-down',
            'fa-caret-square-o-down',
            'fa-toggle-up',
            'fa-caret-square-o-up',
            'fa-toggle-right',
            'fa-caret-square-o-right',
            'fa-euro',
            'fa-eur',
            'fa-gbp',
            'fa-dollar',
            'fa-usd',
            'fa-rupee',
            'fa-inr',
            'fa-cny',
            'fa-rmb',
            'fa-yen',
            'fa-jpy',
            'fa-ruble',
            'fa-rouble',
            'fa-rub',
            'fa-won',
            'fa-krw',
            'fa-bitcoin',
            'fa-btc',
            'fa-file',
            'fa-file-text',
            'fa-sort-alpha-asc',
            'fa-sort-alpha-desc',
            'fa-sort-amount-asc',
            'fa-sort-amount-desc',
            'fa-sort-numeric-asc',
            'fa-sort-numeric-desc',
            'fa-thumbs-up',
            'fa-thumbs-down',
            'fa-youtube-square',
            'fa-youtube',
            'fa-xing',
            'fa-xing-square',
            'fa-youtube-play',
            'fa-dropbox',
            'fa-stack-overflow',
            'fa-instagram',
            'fa-flickr',
            'fa-adn',
            'fa-bitbucket',
            'fa-bitbucket-square',
            'fa-tumblr',
            'fa-tumblr-square',
            'fa-long-arrow-down',
            'fa-long-arrow-up',
            'fa-long-arrow-left',
            'fa-long-arrow-right',
            'fa-apple',
            'fa-windows',
            'fa-android',
            'fa-linux',
            'fa-dribbble',
            'fa-skype',
            'fa-foursquare',
            'fa-trello',
            'fa-female',
            'fa-male',
            'fa-gittip',
            'fa-gratipay',
            'fa-sun-o',
            'fa-moon-o',
            'fa-archive',
            'fa-bug',
            'fa-vk',
            'fa-weibo',
            'fa-renren',
            'fa-pagelines',
            'fa-stack-exchange',
            'fa-arrow-circle-o-right',
            'fa-arrow-circle-o-left',
            'fa-toggle-left',
            'fa-caret-square-o-left',
            'fa-dot-circle-o',
            'fa-wheelchair',
            'fa-vimeo-square',
            'fa-turkish-lira',
            'fa-try',
            'fa-plus-square-o',
            'fa-space-shuttle',
            'fa-slack',
            'fa-envelope-square',
            'fa-wordpress',
            'fa-openid',
            'fa-institution',
            'fa-bank',
            'fa-university',
            'fa-mortar-board',
            'fa-graduation-cap',
            'fa-yahoo',
            'fa-google',
            'fa-reddit',
            'fa-reddit-square',
            'fa-stumbleupon-circle',
            'fa-stumbleupon',
            'fa-delicious',
            'fa-digg',
            'fa-pied-piper-pp',
            'fa-pied-piper-alt',
            'fa-drupal',
            'fa-joomla',
            'fa-language',
            'fa-fax',
            'fa-building',
            'fa-child',
            'fa-paw',
            'fa-spoon',
            'fa-cube',
            'fa-cubes',
            'fa-behance',
            'fa-behance-square',
            'fa-steam',
            'fa-steam-square',
            'fa-recycle',
            'fa-automobile',
            'fa-car',
            'fa-cab',
            'fa-taxi',
            'fa-tree',
            'fa-spotify',
            'fa-deviantart',
            'fa-soundcloud',
            'fa-database',
            'fa-file-pdf-o',
            'fa-file-word-o',
            'fa-file-excel-o',
            'fa-file-powerpoint-o',
            'fa-file-photo-o',
            'fa-file-picture-o',
            'fa-file-image-o',
            'fa-file-zip-o',
            'fa-file-archive-o',
            'fa-file-sound-o',
            'fa-file-audio-o',
            'fa-file-movie-o',
            'fa-file-video-o',
            'fa-file-code-o',
            'fa-vine',
            'fa-codepen',
            'fa-jsfiddle',
            'fa-life-bouy',
            'fa-life-buoy',
            'fa-life-saver',
            'fa-support',
            'fa-life-ring',
            'fa-circle-o-notch',
            'fa-rebel',
            'fa-empire',
            'fa-git-square',
            'fa-git',
            'fa-y-combinator-square',
            'fa-hacker-news',
            'fa-tencent-weibo',
            'fa-qq',
            'fa-wechat',
            'fa-weixin',
            'fa-send',
            'fa-paper-plane',
            'fa-send-o',
            'fa-paper-plane-o',
            'fa-history',
            'fa-circle-thin',
            'fa-header',
            'fa-paragraph',
            'fa-sliders',
            'fa-share-alt',
            'fa-share-alt-square',
            'fa-bomb',
            'fa-soccer-ball-o',
            'fa-futbol-o',
            'fa-tty',
            'fa-binoculars',
            'fa-plug',
            'fa-slideshare',
            'fa-twitch',
            'fa-yelp',
            'fa-newspaper-o',
            'fa-wifi',
            'fa-calculator',
            'fa-paypal',
            'fa-google-wallet',
            'fa-cc-visa',
            'fa-cc-mastercard',
            'fa-cc-discover',
            'fa-cc-amex',
            'fa-cc-paypal',
            'fa-cc-stripe',
            'fa-bell-slash',
            'fa-bell-slash-o',
            'fa-trash',
            'fa-copyright',
            'fa-at',
            'fa-eyedropper',
            'fa-paint-brush',
            'fa-birthday-cake',
            'fa-area-chart',
            'fa-pie-chart',
            'fa-line-chart',
            'fa-lastfm',
            'fa-lastfm-square',
            'fa-toggle-off',
            'fa-toggle-on',
            'fa-bicycle',
            'fa-bus',
            'fa-ioxhost',
            'fa-angellist',
            'fa-cc',
            'fa-shekel',
            'fa-sheqel',
            'fa-ils',
            'fa-meanpath',
            'fa-buysellads',
            'fa-connectdevelop',
            'fa-dashcube',
            'fa-forumbee',
            'fa-leanpub',
            'fa-sellsy',
            'fa-shirtsinbulk',
            'fa-simplybuilt',
            'fa-skyatlas',
            'fa-cart-plus',
            'fa-cart-arrow-down',
            'fa-diamond',
            'fa-ship',
            'fa-user-secret',
            'fa-motorcycle',
            'fa-street-view',
            'fa-heartbeat',
            'fa-venus',
            'fa-mars',
            'fa-mercury',
            'fa-intersex',
            'fa-transgender',
            'fa-transgender-alt',
            'fa-venus-double',
            'fa-mars-double',
            'fa-venus-mars',
            'fa-mars-stroke',
            'fa-mars-stroke-v',
            'fa-mars-stroke-h',
            'fa-neuter',
            'fa-genderless',
            'fa-facebook-official',
            'fa-pinterest-p',
            'fa-whatsapp',
            'fa-server',
            'fa-user-plus',
            'fa-user-times',
            'fa-hotel',
            'fa-bed',
            'fa-viacoin',
            'fa-train',
            'fa-subway',
            'fa-medium',
            'fa-yc',
            'fa-y-combinator',
            'fa-optin-monster',
            'fa-opencart',
            'fa-expeditedssl',
            'fa-battery-4',
            'fa-battery',
            'fa-battery-full',
            'fa-battery-3',
            'fa-battery-three-quarters',
            'fa-battery-2',
            'fa-battery-half',
            'fa-battery-1',
            'fa-battery-quarter',
            'fa-battery-0',
            'fa-battery-empty',
            'fa-mouse-pointer',
            'fa-i-cursor',
            'fa-object-group',
            'fa-object-ungroup',
            'fa-sticky-note',
            'fa-sticky-note-o',
            'fa-cc-jcb',
            'fa-cc-diners-club',
            'fa-clone',
            'fa-balance-scale',
            'fa-hourglass-o',
            'fa-hourglass-1',
            'fa-hourglass-start',
            'fa-hourglass-2',
            'fa-hourglass-half',
            'fa-hourglass-3',
            'fa-hourglass-end',
            'fa-hourglass',
            'fa-hand-grab-o',
            'fa-hand-rock-o',
            'fa-hand-stop-o',
            'fa-hand-paper-o',
            'fa-hand-scissors-o',
            'fa-hand-lizard-o',
            'fa-hand-spock-o',
            'fa-hand-pointer-o',
            'fa-hand-peace-o',
            'fa-trademark',
            'fa-registered',
            'fa-creative-commons',
            'fa-gg',
            'fa-gg-circle',
            'fa-tripadvisor',
            'fa-odnoklassniki',
            'fa-odnoklassniki-square',
            'fa-get-pocket',
            'fa-wikipedia-w',
            'fa-safari',
            'fa-chrome',
            'fa-firefox',
            'fa-opera',
            'fa-internet-explorer',
            'fa-tv',
            'fa-television',
            'fa-contao',
            'fa-500px',
            'fa-amazon',
            'fa-calendar-plus-o',
            'fa-calendar-minus-o',
            'fa-calendar-times-o',
            'fa-calendar-check-o',
            'fa-industry',
            'fa-map-pin',
            'fa-map-signs',
            'fa-map-o',
            'fa-map',
            'fa-commenting',
            'fa-commenting-o',
            'fa-houzz',
            'fa-vimeo',
            'fa-black-tie',
            'fa-fonticons',
            'fa-reddit-alien',
            'fa-edge',
            'fa-credit-card-alt',
            'fa-codiepie',
            'fa-modx',
            'fa-fort-awesome',
            'fa-usb',
            'fa-product-hunt',
            'fa-mixcloud',
            'fa-scribd',
            'fa-pause-circle',
            'fa-pause-circle-o',
            'fa-stop-circle',
            'fa-stop-circle-o',
            'fa-shopping-bag',
            'fa-shopping-basket',
            'fa-hashtag',
            'fa-bluetooth',
            'fa-bluetooth-b',
            'fa-percent',
            'fa-gitlab',
            'fa-wpbeginner',
            'fa-wpforms',
            'fa-envira',
            'fa-universal-access',
            'fa-wheelchair-alt',
            'fa-question-circle-o',
            'fa-blind',
            'fa-audio-description',
            'fa-volume-control-phone',
            'fa-braille',
            'fa-assistive-listening-systems',
            'fa-american-sign-language-interpreting',
            'fa-deaf',
            'fa-glide',
            'fa-glide-g',
            'fa-sign-language',
            'fa-low-vision',
            'fa-viadeo',
            'fa-viadeo-square',
            'fa-snapchat',
            'fa-snapchat-ghost',
            'fa-snapchat-square',
            'fa-pied-piper',
            'fa-first-order',
            'fa-yoast',
            'fa-themeisle',
            'fa-google-plus-official',
            'fa-font-awesome',
            'fa-handshake-o',
            'fa-envelope-open',
            'fa-envelope-open-o',
            'fa-linode',
            'fa-address-book',
            'fa-address-book-o',
            'fa-address-card',
            'fa-address-card-o',
            'fa-user-circle',
            'fa-user-circle-o',
            'fa-user-o',
            'fa-id-badge',
            'fa-id-card',
            'fa-id-card-o',
            'fa-quora',
            'fa-free-code-camp',
            'fa-telegram',
            'fa-shower',
            'fa-bath',
            'fa-podcast',
            'fa-window-maximize',
            'fa-window-minimize',
            'fa-window-restore',
            'fa-window-close',
            'fa-window-close-o',
            'fa-bandcamp',
            'fa-grav',
            'fa-etsy',
            'fa-imdb',
            'fa-ravelry',
            'fa-eercast',
            'fa-microchip',
            'fa-snowflake-o',
            'fa-superpowers',
            'fa-wpexplorer',
        ];
    }
}
