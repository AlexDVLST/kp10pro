<?php

namespace App\Http\Controllers;

use App\Helpers\Bitrix24;
use App\Helpers\UserHelper;
use App\Http\Traits\ClientTrait;
use App\Http\Traits\EmployeeTrait;
use App\Http\Traits\Bitrix24Trait;
use App\Jobs\ScenarioJob;
use App\Models\IntegrationBitrix24DealField;
use App\Models\IntegrationBitrix24DealFieldValue;
use App\Jobs\PolytellEmailParserJob;
use App\Models\Client;
use App\Models\ClientContactPerson;
use App\Models\Currency;
use App\Models\Integration;
use App\Models\IntegrationBitrix24;
use App\Models\IntegrationBitrix24Company;
use App\Models\IntegrationBitrix24Contact;
use App\Models\IntegrationBitrix24CustomField;
use App\Models\IntegrationBitrix24Deal;
use App\Models\IntegrationBitrix24Product;
use App\Models\IntegrationBitrix24User;
use App\Models\Offer;
use App\Models\OfferBitrix24Deal;
use App\Models\OfferVariant;
use App\Models\OfferVariantProduct;
use App\Models\User;
use App\Scopes\IntegrationBitrix24Scope;
use App\Scopes\IntegrationBitrix24UserScope;
use Dompdf\Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cookie;

class Bitrix24Controller extends Controller
{
    use EmployeeTrait;
    use Bitrix24Trait;
    use ClientTrait;

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        return view('pages.integration.bitrix24', ['data' => $request->all()]);
    }

    public function test(){
//        $B24 = new Bitrix24(117);
//        $employeesList = [];
//        $B24->getUsers(0, 1, $employeesList);
//
//        Log::debug(print_r($employeesList, 1));
        Log::debug('do test');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkAccount(Request $request)
    {
        $settings = $request->get('settings');
        $userSettings = $request->get('user');
//        $employees = $request->get('employees');

        $bitrix24Domain = $settings['DOMAIN'];
        $accessToken = $settings['AUTH_ID'];
        $refreshToken = $settings['REFRESH_ID'];

        //Token name
        $tokenName = 'bitrix24';

        //настройки юзера-инициатора установки приложения
        $bitrixUserId = $userSettings['current_user_id'];
        $email = $userSettings['current_user_email'];
        $firstName = $userSettings['current_user_name'] ? $userSettings['current_user_name'] : 'NoName';
        $lastName = $userSettings['current_user_last_name'] ? $userSettings['current_user_last_name'] : $firstName;
        $middleName = '';

        //проверяем куки
        $kp10 = Cookie::get('kp10_account');
        $kp10_account = explode('|', $kp10);

        $kp10_userId = isset($kp10_account[0]) ? $kp10_account[0] : '';
        $kp10_host = isset($kp10_account[1]) ? $kp10_account[1] : '';

        $integration = false;

        if ($kp10_host && $kp10_host == $bitrix24Domain) {
            /**
             * нашли совпадение (устанавливает виджет, тот же юзер, который инициировал установку интеграции с кп10)
             */

            //очищаем куки
            Cookie::queue('kp10_account', '', 0, null, env('APP_DOMAIN'));
            Cookie::forget('kp10_account');

            $user = User::whereId($kp10_userId)->first();

            if ($user) {
                $domain = $user->domain;
                $accountId = $user->accountId;

                //Создаем запись интеграции
                IntegrationBitrix24::create([
                    'host'          => $bitrix24Domain,
                    'access_token'  => $accessToken,
                    'refresh_token' => $refreshToken,
                    'account_id'    => $accountId
                ]);

                //не создаем юзера, который инициировал установку с Bitrix24, так как это тот же человек, что нажал "Установить" на КП10
                //а значит просто "сливаем" их как одного юзера
                IntegrationBitrix24User::updateOrCreate(
                    [
                        'user_id'    => $accountId,
                        'account_id' => $accountId
                    ],
                    [
                        'bitrix24_user_id'        => $bitrixUserId,
                        'bitrix24_user_name'      => $firstName,
                        'bitrix24_user_last_name' => $lastName,
                        'bitrix24_user_login'     => $email
                    ]
                );

                //Creating a token without scopes...
                $token = $user->createToken($tokenName)->accessToken;

                //Импортируем сотрудников с Bitrix24
                $tokens = self::addBitrix24Employees($domain, $tokenName, $accountId, $bitrixUserId);

                $tokens = array_merge([0 => ['id' => 'kp-' . $bitrixUserId, 'token' => $token]], $tokens);

                Integration::create([
                    'system_crm_id' => 3,
                    'account_id'    => $accountId
                ]);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Что-то пошло не так... Нам не удалось совершить интеграцию с Вашим Bitrix24! Обратитесь за помощью в нашу техническую поддержку, мы обязательно Вам поможем.'], 422);
            }
        } else {

            //Find integration in DB by host name
            $exist = IntegrationBitrix24::withoutGlobalScope(IntegrationBitrix24Scope::class)->whereHost($bitrix24Domain)->first();

            $accountId = '';
            if (!$exist) {

                if (!$email) {
                    return response()->json(['status' => 'error', 'message' => 'Невозможно создать новый аккаунт КП10, у Вас отсутствует email']);
                }

                $ifCurrentUserExist = User::role('user')->whereEmail($email)->first();

                if ($ifCurrentUserExist) {
                    return response()->json(['status' => 'error', 'message' => "Такой email уже зарегистрирован как администратор аккаунта.\nВойдите в <a href='https://".$ifCurrentUserExist['domain'].".kp10.pro/settings/integration/crm' target='_blank'>Ваш аккаунт</a> и настройте интеграцию."]);
                }

                $domain = UserHelper::generateDomain();
                $password = str_random(8); // generate password (8 symbol)

                //todo AIM добавить номер телефона (в скором будущем)
                try {
                    $user = User::create([
                        'domain'      => $domain,
                        'surname'     => $lastName,
                        'name'        => $firstName,
                        'middle_name' => $middleName,
                        'email'       => $email,
                        'password'    => bcrypt($password),
                    ]);

                    //For email
                    $user->originalPassword = $password;

                    //Start registration event
                    event(new Registered($user));

                    $accountId = $user->accountId;

                    //Insert new integration data
                    IntegrationBitrix24::create([
                        'host'          => $bitrix24Domain,
                        'access_token'  => $accessToken,
                        'refresh_token' => $refreshToken,
                        'account_id'    => $accountId
                    ]);

                    //Create new user in integration table
                    IntegrationBitrix24User::create([
                        'user_id'                 => $user->id,
                        'account_id'              => $accountId,
                        'bitrix24_user_id'        => $bitrixUserId,
                        'bitrix24_user_name'      => $firstName,
                        'bitrix24_user_last_name' => $lastName,
                        'bitrix24_user_login'     => $email
                    ]);

                    //Creating a token without scopes...
                    $token = $user->createToken($tokenName)->accessToken;

                    //Импортируем сотрудников с Bitrix24
                    $empTokens = $this->addBitrix24Employees($domain, $tokenName, $accountId, $bitrixUserId);

                    $tokens = array_merge([0 => ['id' => 'kp-' . $bitrixUserId, 'token' => $token]], $empTokens);

                    Integration::create([
                        'system_crm_id' => 3,
                        'account_id'    => $accountId
                    ]);

                    //Send request via EmailParser
                    PolytellEmailParserJob::dispatch($user, 2);

                    $integration = true;
                } catch (\Exception $e) {
                    return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
                }
            } else { //интеграция существует

                $accountId = $exist->account_id;

                $Account = User::whereId($accountId)->first();
                $domain = $Account->domain;

//                $employees = array_merge([0 => [
//                    'user_id'        => $bitrixUserId,
//                    'user_name'      => $firstName,
//                    'user_last_name' => $lastName,
//                    'user_email'     => $email,
//                    'user_phone'     => '']
//                ], $employees);

                //Импортируем сотрудников с Bitrix24
                $tokens = self::addBitrix24Employees($domain, $tokenName, $accountId);
            }
        }

        return response()->json([
            'status'  => 'success',
            'tokens'  => $tokens,
            'account' => env('APP_PROTOCOL') . $domain . '.' . env('APP_DOMAIN'),
            'domain'  => $domain . '.' . env('APP_DOMAIN'),
            'integration' => $integration
        ]);
    }

    /**
     * Записать всех сотрудников к нам в базу - вернуть токены для работы с апи
     *
     * @param $account
     * @param $tokenName
     * @param $accountId
     * @param int $removedUserId
     * @return array
     */
    public function addBitrix24Employees($account, $tokenName, $accountId, $removedUserId = 0)
    {
        $B24 = new Bitrix24($accountId);
        $result = [];
        $employeesList = $B24->getUsers();

        if($employeesList){
            foreach ($employeesList as $employee) {
                $userBitrixId = $employee['ID'];

                if($userBitrixId != $removedUserId){
                    $name = $employee['NAME'] ? $employee['NAME'] : 'NoName';
                    $surname = $employee['LAST_NAME'] ? $employee['LAST_NAME'] : $name;
                    $email = $employee['EMAIL'];
                    $phone = $employee['PERSONAL_MOBILE'] ? $employee['PERSONAL_MOBILE'] : '';

                    $signature = $name . $surname . $email . $phone;

                    $employeeParams = [
                        'name'        => $name,
                        'surname'     => $surname,
                        'middle_name' => '',
                        'email'       => $email,
                        'phone'       => $phone,
                        'position'    => '',
                        'signature'   => $signature,
                        'fileId'      => 0
                    ];

                    //проверяем существуют ли такой пользователь
                    $exist = IntegrationBitrix24User::withoutGlobalScope(IntegrationBitrix24UserScope::class)
                        ->whereAccountId($accountId)->whereBitrix24UserId($userBitrixId)->first();

                    if (!$exist) {
                        $User = $this->addEmployee($account, new Request($employeeParams), false);

                        //Create new user in integration table
                        IntegrationBitrix24User::create([
                            'user_id'                 => $User->id,
                            'account_id'              => $accountId,
                            'bitrix24_user_id'        => $userBitrixId,
                            'bitrix24_user_name'      => $name,
                            'bitrix24_user_last_name' => $surname,
                            'bitrix24_user_login'     => $email
                        ]);
                    } else {

                        $User = User::whereId($exist->user_id)->first();

                        //убиваем существующий токен юзера
                        if ($User->tokens) {
                            //Revoke token
                            $User->tokens->each(function ($token) use ($tokenName) {
                                //Invalidate current tokens
                                if ($token->name == $tokenName) {
                                    $token->revoke();
                                }
                            });
                        }
                    }

                    //Creating a token without scopes...
                    $token = $User->createToken($tokenName)->accessToken;

                    $result[] = [
                        'id'    => 'kp-' . $userBitrixId,
                        'token' => $token
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * Получаем список полей с Bitrix24
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function dealFields()
    {
        try {
            $user = Auth::user();
            $B24 = new Bitrix24($user->account_id);

            //Get custom fields
            $customFields = $B24->customFields();

            return response()->json($customFields);
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }
    }

    /**
     * Получаем КП по заказу
     *
     * @param $account
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function dealOffer($account, $id, Request $request)
    {
        $user = Auth::user();

        //Get offer relative to deal
        $offer = Offer::with(
            'bitrix24Deal',
            'variants.products.values',
            'variants.fields',
            'variants.specialDiscounts',
            'state.data',
            'numberRelation',
            'currency.data.system'
        )
            ->whereHas('bitrix24Deal', function ($query) use ($id) {
                $query->whereDealId($id);
            })->first();

        if ($offer) {
            $offer->productEmptyImg = '/storage/resource/templates/base/product/empty.png';

            unset($offer->gjs_components, $offer->gjs_css, $offer->gjs_html, $offer->gjs_styles, $offer->gjs_assets);
        }

        //поиск токена
        $tokenId = '';
        if ($user->tokens) {

            $tokenName = 'bitrix24';
            $tokens = json_decode($user->tokens, true);
            foreach ($tokens as $token) {
                if ($token['name'] == $tokenName) {
                    $tokenId = $token['id'];
                }
            }
        }

        return response()->json(['offer' => $offer, 'token' => $tokenId, 'user' => $user->id]);
    }

    /**
     * @param $account
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setDealOffer($account, $id, Request $request)
    {
        $request->validate([
            'id' => 'required|numeric'
        ]);

        $offerId = $request->input('id');

        //Get offer
        $offer = Offer::with('bitrix24Deal')->whereId($offerId)->first();

        if (!$offer) {
            return response()->json(['errors' => __('messages.offer.not_found')], 422);
        }

        if ($offer->bitrix24Deal) {
            return response()->json(['errors' => __('messages.offer.integration.deal.relation.error')], 422);
        }

        try {
            DB::transaction(function () use ($id, $account, $offer) {
                //Get auth user
                $user = Auth::user();

                $offer->bitrix24Deal()->create([
                    'deal_id' => $id
                ]);

                //Init
                $B24 = new Bitrix24($user->account_id);
                //Get deal card
                $deal = $B24->getDealCard($id);

                if ($deal) {
                    //Responsible id
                    $bitrix24ResponsibleId = $deal['ASSIGNED_BY_ID'];
                    //Get users
                    $bitrix24Users = $B24->getUsers();
                    $bitrix24User = '';
                    if ($bitrix24Users) {
                        foreach ($bitrix24Users as $b24User) {
                            if ($b24User['ID'] == $bitrix24ResponsibleId) {
                                $bitrix24User = $b24User;

                                break;
                            }
                        }
                    }

                    //Company
                    $company = [];
                    //Create user with trait
                    $employee = $this->addBitrix24User($user, $bitrix24User);

                    //Create or update deal data
                    IntegrationBitrix24Deal::updateOrCreate(
                        [
                            'bitrix24_deal_id' => $id,
                            'account_id'       => $user->accountId,
                        ],
                        [
                            'bitrix24_deal_responsible_user_id' => $bitrix24ResponsibleId,
                            'bitrix24_deal_stage_id'            => $deal['STAGE_ID']
                        ]
                    );

                    //Offer employee
                    $offer->employee()
                        ->update(['user_id' => $employee->id]);

                    //Если сделка с компанией
                    if (isset($deal['COMPANY_ID']) && $deal['COMPANY_ID'] > 0) {

                        //Check if company not created
                        $bitrix24CompanyExist = IntegrationBitrix24Company::whereAccountId($user->accountId)->whereBitrix24CompanyId($deal['COMPANY_ID'])->first();
                        if ($bitrix24CompanyExist) {
                            //Get company model
                            $company = Client::whereId($bitrix24CompanyExist->company_id)->first();
                        } else {
                            //Get company card
                            $bitrix24Company = $B24->getCompanyInfo($deal['COMPANY_ID']);

                            if ($bitrix24Company) {
                                $createdCompany = IntegrationBitrix24Company::create([
                                    'account_id'            => $user->accountId,
                                    'company_id'            => 0,
                                    'bitrix24_company_id'   => $bitrix24Company['ID'],
                                    'bitrix24_company_name' => $bitrix24Company['TITLE']
                                ]);

                                //Get company custom fields//

                                //Company emails
                                $emails = [];
                                //Company phones
                                $phones = [];

                                //Fill phones
                                if (!empty($bitrix24Company['PHONE'])) {
                                    foreach ($bitrix24Company['PHONE'] as $phone) {
                                        $phones[] = [
                                            'phone' => $phone['VALUE']
                                        ];
                                    }
                                }
                                //Fill emails
                                if (!empty($bitrix24Company['EMAIL'])) {
                                    foreach ($bitrix24Company['EMAIL'] as $email) {
                                        $emails[] = [
                                            'email' => $email['VALUE']
                                        ];
                                    }
                                }

                                //Set create employee as responsible
                                $responsibles = [$employee->id];

                                //Create company in our account
                                $clientParams = [
                                    'type'           => 1, //company
                                    'surname'        => '',
                                    'name'           => $bitrix24Company['TITLE'],
                                    'middle_name'    => '',
                                    'emails'         => $emails,
                                    'phones'         => $phones,
                                    'position'       => '',
                                    'description'    => '',
                                    'responsibles'   => $responsibles,
                                    'contactPersons' => [0],
                                    'companyId'      => 0,
                                ];

                                //Using trait for creating company
                                $company = $this->addClient($account, new Request($clientParams));

                                //Update company id
                                $createdCompany->company_id = $company->id;
                                $createdCompany->save();
                            }
                        }

                        if (isset($company->id)) {

                            //Update client related to offer
                            $offer->clientRelation()->update(['client_id' => $company->id]);
                        }
                    }
                    //Get contacts (если есть хотя бы один контакт)
                    if (!empty($deal['CONTACT_ID']) && $deal['CONTACT_ID'] > 0) {

                        $contacts = $B24->getContactsFromDeal($id);

                        if ($contacts) {
                            foreach ($contacts as $item => $contact) {
                                if (!IntegrationBitrix24Contact::whereAccountId($user->accountId)->whereBitrix24ContactId($contact['CONTACT_ID'])->first()) {
                                    //Contact emails
                                    $emails = [];
                                    //Contact phones
                                    $phones = [];

                                    //получаем информацию по контакту
                                    $contactInfo = $B24->getContactInfo($contact['CONTACT_ID']);

                                    //Fill phones
                                    if (!empty($contactInfo['PHONE'])) {
                                        foreach ($contactInfo['PHONE'] as $phone) {
                                            $phones[] = [
                                                'phone' => $phone['VALUE']
                                            ];
                                        }
                                    }
                                    //Fill emails
                                    if (!empty($contactInfo['EMAIL'])) {
                                        foreach ($contactInfo['EMAIL'] as $email) {
                                            $emails[] = [
                                                'email' => $email['VALUE']
                                            ];
                                        }
                                    }

                                    //Get contact responsible
                                    $bitrix24ContactResponsibleId = $contactInfo['ASSIGNED_BY_ID'];
                                    $bitrix24ResponsibleUser = '';

                                    //find contact responsible
                                    foreach ($bitrix24Users as $b24User) {
                                        if ($b24User['ID'] == $bitrix24ContactResponsibleId) {
                                            $bitrix24ResponsibleUser = $b24User;

                                            break;
                                        }
                                    }

                                    //Create contact responsible or return existed
                                    $employee = $this->addBitrix24User($user, $bitrix24ResponsibleUser);

                                    //Set create employee as responsible
                                    $responsibles = [$employee->id];

                                    $contactName = $contactInfo['NAME'] ? $contactInfo['NAME'] : 'NoName';
                                    $contactSurName = $contactInfo['LAST_NAME'] ? $contactInfo['LAST_NAME'] : '';
                                    $contactSecondName = $contactInfo['SECOND_NAME'] ? $contactInfo['SECOND_NAME'] : '';

                                    $companyId = 0;
                                    //если данный контакт привязан к той комании, которую мы создали и которая привязана к сделке
                                    if (!empty($contactInfo['COMPANY_ID']) && $contactInfo['COMPANY_ID'] == $deal['COMPANY_ID']) {
                                        $companyId = isset($company->id) ? $company->id : 0;
                                    }

                                    //Create client in our account
                                    $clientParams = [
                                        'type'           => 3, //contact person
                                        'surname'        => $contactSurName,
                                        'name'           => $contactName,
                                        'middle_name'    => $contactSecondName,
                                        'emails'         => $emails,
                                        'phones'         => $phones,
                                        'position'       => '',
                                        'description'    => '',
                                        'responsibles'   => $responsibles,
                                        'contactPersons' => [0],
                                        'companyId'      => $companyId
                                    ];

                                    //Using trait for creating company
                                    $client = $this->addClient($account, new Request($clientParams));

                                    //If company exist
                                    if ($companyId) {
                                        //Update contact persons for relative company
                                        ClientContactPerson::firstOrCreate([
                                                'client_id'                => $companyId,
                                                'client_contact_person_id' => $client->id
                                            ]
                                        );
                                    }

                                    //Create bitrix24 contact
                                    IntegrationBitrix24Contact::create(
                                        [
                                            'contact_id'                  => $client->id,
                                            'account_id'                  => $user->accountId,
                                            'bitrix24_contact_id'         => $contactInfo['ID'],
                                            'bitrix24_contact_name'       => $contactName,
                                            'bitrix24_contact_company_id' => $companyId
                                        ]
                                    );
                                }

                                //For the first contact
                                if ($item == 0) {
                                    $bitrix24Contact = IntegrationBitrix24Contact::whereAccountId($user->accountId)->whereBitrix24ContactId($contact['CONTACT_ID'])->first();
                                    $contact = Client::whereId($bitrix24Contact->contact_id)->first();

                                    //Update contact person related to offer
                                    $offer->contactPersonRelation()->update(['client_id' => $contact->id]);
                                }
                            }
                        }
                    }

                    //Custom fields
                    $customFields = $B24->customFields();
                    $bitrix24Fields = json_decode(IntegrationBitrix24CustomField::whereType('lead')->get(), true);

                    if ($bitrix24Fields) {
                        foreach ($bitrix24Fields as $integrationField) {

                            $fieldId = $integrationField['bitrix24_field_id'];

                            if (isset($deal[$fieldId])) {

                                $field = IntegrationBitrix24DealField::updateOrCreate(
                                    [
                                        'account_id'        => $user->accountId,
                                        'bitrix24_deal_id'  => $id,
                                        'bitrix24_field_id' => $fieldId
                                    ],
                                    [
                                        'bitrix24_field_name' => $integrationField['bitrix24_field_name']
                                    ]
                                );

                                $enum = 0;
                                $value = $deal[$fieldId];
                                if ($integrationField['bitrix24_field_type_id'] == 'enumeration') {
                                    foreach ($customFields as $csField) {
                                        if ($csField['field_id'] == $fieldId) {
                                            foreach ($csField['items'] as $item) {
                                                if ($item['ID'] == $value) {
                                                    $enum = $item['ID'];
                                                    $value = $item['VALUE'];

                                                    break;
                                                }
                                            }

                                            break;
                                        }
                                    }
                                }

                                IntegrationBitrix24DealFieldValue::updateOrCreate(
                                    [
                                        'deal_field_id' => $field->id,
                                    ],
                                    [
                                        'bitrix24_field_value'   => $value,
                                        'bitrix24_field_enum_id' => $enum
                                    ]
                                );
                            }
                        }
                    }
                } else {
                    return response()->json(['errors' => __('messages.bitrix24.deal.not_found')], 422);
                }
            });
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        return response()->json(['message' => __('messages.offer.save.success')]);
    }

    /**
     * @param $account
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEmployeesList($account, Request $request)
    {
        try {
            $Bitrix24 = new Bitrix24();
            $result = $Bitrix24->getUsers();

            return response()->json($result);
        } catch (Exception $e) {
            Log::debug($e->getMessage());

            return response()->json(['errors' => $e->getMessage()], 422);
        }
    }

    /**
     * Принимаем событие обновления сделки с Bitrix24
     *
     * @param Request $request
     */
    public function events(Request $request)
    {
        /*$data = [
            'event' => 'ONCRMDEALUPDATE',
            'data'  => [
                'FIELDS' => [
                    'ID' => 60
                ]
            ],
            'auth'  => [
                'domain'            => 'worksman.bitrix24.ru'
            ]
        ];*/
        $data = $request->all();

        if (isset($data['event']) && $data['event'] == 'ONCRMDEALUPDATE') {
            if (!empty($data['data']['FIELDS']['ID'])) {
                $dealId = $data['data']['FIELDS']['ID'];
                $host = isset($data['auth']['domain']) ? $data['auth']['domain'] : '';

                $integration = IntegrationBitrix24::whereHost($host)->select('account_id')->first();

                if ($integration) {
                    $accountId = $integration['account_id'];

                    //проверяем наличие у нас в БД такой сделки
                    $existDeal = IntegrationBitrix24Deal::whereBitrix24DealId($dealId)->first();

                    if ($existDeal) {
                        $oldStageId = $existDeal['bitrix24_deal_stage_id'];

                        $B24 = new Bitrix24($accountId);

                        $getDealCard = $B24->getDealCard($dealId);

                        if ($getDealCard) {

                            $currentStageId = $getDealCard['STAGE_ID'];

                            try {
                                IntegrationBitrix24Deal::whereId($existDeal['id'])->update([
                                        'bitrix24_deal_stage_id'            => $currentStageId,
                                        'bitrix24_deal_responsible_user_id' => $getDealCard['ASSIGNED_BY_ID']
                                    ]
                                );

                                //получаем список сохраненных полей
                                $customFields = IntegrationBitrix24CustomField::whereType('lead')->whereAccountId($accountId)->get();
                                $dealFields = IntegrationBitrix24DealField::whereBitrix24DealId($dealId)->whereAccountId($accountId)->get();

                                $b24CustomFields = [];
                                if ($customFields) {
                                    foreach ($customFields as $customField) {
                                        $customFieldId = $customField['bitrix24_field_id'];
                                        $customFieldName = $customField['bitrix24_field_name'];

                                        $newField = '';
                                        if ($dealFields) {
                                            $same = false;
                                            foreach ($dealFields as $dealField) {
                                                if ($customFieldId == $dealField['bitrix24_field_id']) {
                                                    $same = true;
                                                    $newField = $dealField['id'];

                                                    break;
                                                }
                                            }

                                            //данного поля нет в БД по сделке
                                            if (!$same) {
                                                $newField = IntegrationBitrix24DealField::create([
                                                    'account_id'          => $accountId,
                                                    'bitrix24_deal_id'    => $dealId,
                                                    'bitrix24_field_id'   => $customFieldId,
                                                    'bitrix24_field_name' => $customFieldName
                                                ])->id;
                                            }
                                        } else {
                                            //в БД вообще нет информации по полям в сделке

                                            $newField = IntegrationBitrix24DealField::create([
                                                'account_id'          => $accountId,
                                                'bitrix24_deal_id'    => $dealId,
                                                'bitrix24_field_id'   => $customFieldId,
                                                'bitrix24_field_name' => $customFieldName
                                            ])->id;
                                        }

                                        if (isset($getDealCard[$customFieldId])) {
                                            $enum = 0;
                                            $newValue = $getDealCard[$customFieldId];
                                            if ($customField['bitrix24_field_type_id'] == 'enumeration') {

                                                //уже был запрос на битрикс, повторный запрос не делаем
                                                if (!$b24CustomFields) {
                                                    $b24CustomFields = $B24->customFields();
                                                }

                                                foreach ($b24CustomFields as $csField) {
                                                    if ($csField['field_id'] == $customFieldId) {
                                                        foreach ($csField['items'] as $item) {
                                                            if ($item['ID'] == $newValue) {
                                                                $enum = $item['ID'];
                                                                $newValue = $item['VALUE'];

                                                                break;
                                                            }
                                                        }

                                                        break;
                                                    }
                                                }
                                            }

                                            IntegrationBitrix24DealFieldValue::updateOrCreate(
                                                [
                                                    'deal_field_id' => $newField
                                                ],
                                                [
                                                    'bitrix24_field_value'   => $newValue,
                                                    'bitrix24_field_enum_id' => $enum
                                                ]
                                            );
                                        }
                                    }
                                }
                            } catch (Exception $e) {
                                //todo AIM сделать оповещение об неудаче при записе данных в БД
                            }

                            //изменился статус сделки
                            if ($oldStageId != $currentStageId) {
                                //получаем job
                                $offer = OfferBitrix24Deal::with('offerData')->whereDealId($dealId)->first();

                                if ($offer->offerData) {

                                    //Для сценария (Изменился статус в CRM)
                                    ScenarioJob::dispatch(8, 6, '', $offer->offerData);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
