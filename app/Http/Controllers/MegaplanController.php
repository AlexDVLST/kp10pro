<?php

namespace App\Http\Controllers;

use App\Http\Controllers\MegaplanV3Controller;
use App\Jobs\ScenarioJob;
use App\Models\IntegrationMegaplanDeal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Cookie;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client as HttpClient;
use App\Helpers\MegaplanV3;
use App\Helpers\UserHelper;
use GuzzleHttp\json_decode;
use App\Models\Integration;
use App\Models\IntegrationMegaplan;
use App\Models\IntegrationMegaplanFieldTypes;
use App\Models\IntegrationMegaplanContractorHuman;
use App\Models\IntegrationMegaplanContractorCompany;
use App\Models\ClientContactPerson;
use App\Models\SystemMegaplanFieldType;
use App\Models\IntegrationMegaplanField;
use App\Models\SystemCrm;
use App\Models\User;
use App\Models\Client;
use App\Models\OfferMegaplanDeal;
use App\Models\IntegrationMegaplanUser;
use App\Models\IntegrationMegaplanFieldValues;
use App\Models\OfferEmployee;
use App\Models\Currency;
// use App\Models\CurrencyData;
use Illuminate\Support\Facades\Storage;
use App\Models\Offer;
use App\Mail\UserRegistered;
use DB;
use Hash;
use Mail;
use function GuzzleHttp\json_encode;
use App\Http\Traits\ClientTrait;
use App\Http\Traits\MegaplanTrait;
use App\Http\Traits\EmployeeTrait;
use App\Scopes\IntegrationMegaplanScope;
use App\Jobs\PolytellEmailParserJob;
use Doctrine\DBAL\Schema\View;

class MegaplanController extends Controller
{
    use ClientTrait, MegaplanTrait, EmployeeTrait;

    public function indexZ(Request $request){

        $user = User::whereId(117)->first();

        $params = [
            'accessToken'  => 'NzE4MTI0NzI2Y2U2ZmMxMmYxZTE5ZDU4MWU3MzhlZjA0ZjExOTczNDE5NGZlYWYzMmMwZmU3MWMyZjY0NjgxYg',
            'url'          => 'https://nnnicolay.megaplan.ua'
        ];
        $MegaplanV3   = new MegaplanV3($params);

        $userToken = '';

        $tokenName = 'Megaplan';
        //Revoke token

        $pasport_token    = $user->createToken($tokenName)->accessToken;

        $MegaplanV3->setUserSetting(['id' => 'kp10Token', 'value' => $pasport_token]);

        $MegaplanV3->setUserSetting(['id' => 'kp10Host', 'value' => 'test.kp10.loc']);

        return response()->json([]);
    }

    /**
     * Register and show welcome page
     *
     * @param Request $request
     * @return view
     */
    public function index(Request $request)
    {
        // $user     = Auth::user();
        // $account  = $user->domain;

        Auth::logout();
        // $res = $request->all();

        $host             = $request->get('accountId');
        $uuid             = $request->get('applicationUuid');
        $token            = $request->get('applicationToken');
        $userSign         = $request->get('userSign');
        $kp10_account     = Cookie::get('kp10_account');
        $password         = str_random(8); // generate password (8 symbol)
        // $randomDomain     = UserHelper::generateDomain();
        $account          = UserHelper::generateDomain();
        $kp10_account_arr = null;
        // $account          = null;
        $email            = null;

        Cookie::queue('kp10_account', '', 0, null, env('APP_DOMAIN'));
        Cookie::queue(Cookie::forget('kp10_account'));

        if ($kp10_account) {
            $kp10_account_arr = explode('|', $kp10_account);
            $account          = isset($kp10_account_arr[0]) ? $kp10_account_arr[0] : ''; // domain in kp10
            $email            = isset($kp10_account_arr[1]) ? $kp10_account_arr[1] : ''; // email in kp10
        }

        $params = [
            'accessToken'  => $token,
            'url'          => 'https://' . $host,
        ];

        try {
            $MegaplanV3   = new MegaplanV3($params);
            $data         = $MegaplanV3->getUserSign(['uuid' => $uuid, 'userSign' => $userSign]);

            $fio          = $data->name ? $data->name : 'NoName';
            $firstName    = $data->firstName ? $data->firstName : 'NoName';
            $lastName     = $data->lastName ? $data->lastName : '';
            $middleName   = $data->middleName ? $data->middleName : '';
            $contactInfo  = $data->contactInfo;
            $managerId    = $data->id;
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
            return view('pages.integration.megaplan', ['message' => __('messages.megaplan.register.error')]);
        }


        $email   = '';
        $message = 'successfulIntegration';

        // TODO: check email count
        if (!empty($contactInfo)) {
            foreach ($contactInfo as $key => $value) {
                if ($value->type == 'email') {
                    $email = $value->value;
                }
            }
        }

        $clientInfo = [
            'firstName' => $firstName,
            'lastName'  => $lastName,
            'email'     => $email,
            'account'   => $account,
            'host'      => $host
        ];

        $validator = Validator::make([
            'email'   => $email,
            'account' => $account,
            'uuid'    => $uuid,
            'token'   => $token,
            'host'    => $host
        ], [
            'email'   => 'required|email',
            'account' => 'required|string',
            'uuid'    => 'required|string',
            'token'   => 'required|string',
            'host'    => 'required|string'
        ]);

        //True
        if (!$validator->fails()) {

            $findUserByEmail = User::role('user')->whereEmail($email)->first();

            if ($findUserByEmail) {
                return view('pages.integration.megaplan', ['message' => __('messages.register.email.exist')]);
            }

            try {
                DB::transaction(function () use ($host, $uuid, $token, $MegaplanV3, $account, $params, $lastName, $firstName, $middleName, $password, $email, $managerId) {
                    $findUserByHost = IntegrationMegaplan::whereHost($host)->first();

                    if (!$findUserByHost) {

                        $user = User::create([
                            'domain'      => $account,
                            'surname'     => $lastName,
                            'name'        => $firstName,
                            'middle_name' => $middleName,
                            'email'       => $email,
                            'password'    => bcrypt($password),
                        ]);

                        //For email
                        $user->originalPassword = $password;

                        // Start registration event
                        event(new Registered($user));

                        $IntegrationMegaplanUser = IntegrationMegaplanUser::create([
                            'user_id'                   => $user->id,
                            'account_id'                => $user->accountId,
                            'megaplan_user_id'          => $managerId,
                            'megaplan_user_name'        => $lastName,
                            'megaplan_user_middle_name' => $middleName,
                            'megaplan_user_last_name'   => $lastName
                        ]);

                        //Send request via EmailParser
                        PolytellEmailParserJob::dispatch($user, 1);

                          // генеруємо токен паспорт і записуемо його в МП
                        $tokenName = 'Megaplan';
                        //Revoke token
                        $user->tokens->each(function ($token) use ($tokenName) {
                            //Invalidate current tokens
                            if ($token->name == $tokenName) {
                                $token->revoke();
                            }
                        });
                        // Creating a token without scopes...
                        $pasport_token    = $user->createToken($tokenName)->accessToken;

                        $res = $MegaplanV3->setUserSetting(['id' => 'kp10Token', 'value' => $pasport_token]);

                        $res = $MegaplanV3->setUserSetting(['id' => 'kp10Host', 'value' => $account . '.' . env('APP_DOMAIN')]);

                    } else {
                        $user = User::whereId($findUserByHost->account_id)->first();
                    }

                    $accountId = $user->accountId;

                    // $systemCrmId = SystemCrm::whereType('megaplan')->pluck('id')->first();
                    // check the account
                    $integration         = Integration::whereAccountId($accountId)->first();
                    $integrationMegaplan = IntegrationMegaplan::withoutGlobalScope(IntegrationMegaplanScope::class)
                        ->whereAccountId($accountId)->whereHost($host)->first();

                    $accountId  = $user->accountId;
                    // integrations
                    $integrations                = new Integration();
                    $integrations->account_id    = $accountId;
                    $integrations->system_crm_id = 1;
                    $integrations->save();

                    // integration_megaplan
                    $integrationMegaplan             = new IntegrationMegaplan();
                    $integrationMegaplan->host       = $host;
                    $integrationMegaplan->uuid       = $uuid;
                    $integrationMegaplan->api_token  = $token;
                    $integrationMegaplan->account_id = $accountId;
                    $integrationMegaplan->save();

                    $employees = $MegaplanV3->getRecursively('getEmployee', [
                        'limit' => 100
                    ]);

                    //Add Megaplan user
                    if ($employees) {
                        foreach ($employees as $key => $value) {
                            $this->addMegaplanUser($account, $accountId, $value, $params);
                        }
                    }

                });
            } catch (\Exception $e) {
                return view('pages.integration.megaplan', ['message' => $e->getMessage()]);
            }
        } else {
            $error = '';
            foreach ($validator->errors()->all() as $message) {
                $error .= $message."<br>";
            }
            return view('pages.integration.megaplan', ['message' => $error]);
        }

        return view('pages.integration.megaplan', [
            'message'    => $message,
            'clientInfo' => $clientInfo,
            'account'    => 'https://' . $account . '.' . env('APP_DOMAIN'),
            'uid'        => $managerId,
            'token'      => $token
        ]);
    }

    /**
     * Parse event from megaplan
     *
     * @param Request $request
     * @return void
     */
    public function events(Request $request)
    {
        // If Request is Deal update field value

        $res = json_decode($request->getContent(), 1);

        $mp_host = isset($res['accountId']) ? $res['accountId'] : '';
        if (!$mp_host) {
            $mp_host = $res['accountInfo']['accountName'];
        }
        
        $account = IntegrationMegaplan::whereHost($mp_host)->orderBy('updated_at', 'desc')->first();//->account_id
        
        if ($account) {
            $accountId = $account->account_id;
            $uuid      = $account->uuid;
            $api_token = $account->api_token;
            $host      = $account->host;

            if ($res['model'] == 'Deal') {
                try {
                    $this->updateDealFields($res['data'], $accountId);
                } catch (Exception $e) {
                    Log::debug($e->getMessage());
                    return response()->json(['errors' => $e->getMessage()], 422);
                }

                // *** check manager ***
                $manager = isset($res['data']['manager'])?$res['data']['manager']:false;
                $dealId  = $res['data']['id'];

                $mpUser = ''; //todo AIM: добавил, так как сыпались ошибки
                // search manager in DB
                if ($manager) {
                    $mpUser  = IntegrationMegaplanUser::whereMegaplanUserId($manager['id'])->whereAccountId($accountId)->first();
                }

                // if user not found then add
                if (!$mpUser) {
                    $email = '';
                    $phone = '';

                    if (isset($manager['contactInfo'])) {
                        foreach ($manager['contactInfo'] as $key => $value) {
                            if ($value['type'] == 'email') {
                                $email = $value['value'];
                            }
                            if ($value['type'] == 'phone') {
                                $phone = $value['value'];
                            }
                        }
                    }

                    $lastName   = isset($manager['lastName']) ? $manager['lastName'] : '';
                    $firstName  = isset($manager['firstName']) ? $manager['firstName'] : '';
                    $middleName = isset($manager['middleName']) ? $manager['middleName'] : '';
                    $signature  = $lastName . ' ' . $firstName;

                    if (isset($manager['position'])) {
                        $signature .= "\n" . $manager['position'];
                    }
                    if ($email) {
                        $signature .= "\n" . $email;
                    }
                    if ($phone) {
                        $signature .= "\n" . $phone;
                    }

                    $employeeParams = [
                        'surname'     => $lastName,
                        'name'        => $firstName,
                        'middle_name' => $middleName,
                        'email'       => $email,
                        'phone'       => $phone,
                        'position'    => isset($manager['position']) ? $manager['position'] : '',
                        'signature'   => $signature,
                        'fileId'      => 0
                    ];

                    $employee = $this->addEmployee($host, new Request($employeeParams));

                    $user_id = $employee->id;
                    //Add Megaplan user
                    try {
                        // DB::transaction(function () use ($account, $accountId, $email, $phone, $manager, &$employee) {
                        $IntegrationMegaplanUser = IntegrationMegaplanUser::create([
                            'user_id'                   => $employee->id,
                            // 'account_id'                => $user->accountId,
                            'account_id'                => $accountId,
                            'megaplan_user_id'          => $manager['id'],
                            'megaplan_user_name'        => $firstName,
                            'megaplan_user_middle_name' => $middleName,
                            'megaplan_user_last_name'   => $lastName
                            ]);
                        // });
                    } catch (Exception $e) {
                        Log::debug($e->getMessage());
                        return response()->json(['errors' => $e->getMessage()], 422);
                    }
                } else {
                    $user_id = $mpUser->user_id;
                }

                if ($dealId && $accountId) {
                    try {
                        $offer_id = OfferMegaplanDeal::with('offerData')->whereDealId($dealId)->whereAccountId($accountId)->first();
                        // TODO: update user_id in offer_employees
                        //$dealId
                        if ($offer_id) {
                            $OfferEmployee          = OfferEmployee::whereOfferId($offer_id->offer_id)->first();
                            $OfferEmployee->user_id = $user_id;
                            $OfferEmployee->save();
                        }

                        $oldDealInfo = IntegrationMegaplanDeal::whereMegaplanDealId($dealId)->first();
                        if($oldDealInfo){
                            $oldStateId = $oldDealInfo['megaplan_state_id'];

                            //Обновляем информацию по сделке
                            IntegrationMegaplanDeal::updateOrCreate(
                                [
                                    'megaplan_deal_id' => $dealId,
                                    'account_id' => $accountId
                                ],
                                [
                                    'megaplan_program_id' => $res['data']['program']['id'],
                                    'megaplan_state_id' => $res['data']['state']['id']
                                ]
                            );

                            //todo AIM: здесь не нужна проверка по схемам, так как сделка всегда находится в пределах одной схемы
                            if($oldStateId != $res['data']['state']['id']){
                                if(isset($offer_id->offerData)){
                                    //Для сценария (Изменился статус в CRM)
                                    ScenarioJob::dispatch(8, 6, '', $offer_id->offerData);
                                }
                            }
                        }
                    } catch (Exception $e) {
                        Log::debug($e->getMessage());
                        // return response()->json(['errors' => $e->getMessage()], 422);
                    }
                }

                return response()->json([], 200);
            }

            // Update access token in megaplan UserSetting when widget enabled
            if ($res['model'] == 'Integration') {
                if ($res['event'] == 'on_enable') {
                    // *** change token ***

                    $user      = User::whereId($accountId)->first();
                    $tokenName = 'Megaplan';

                    $user->tokens->each(function ($token) use ($tokenName) {
                        //Invalidate current tokens
                        if ($token->name == $tokenName) {
                            $token->revoke();
                        }
                    });

                    // Creating a token without scopes...
                    $pasport_token = $user->createToken($tokenName)->accessToken;

                    $params           = [
                        'accessToken'  => $api_token,
                        'url'          => 'https://' . $host
                    ];

                    $MegaplanV3 = new MegaplanV3($params);
                    $MegaplanV3->setUserSetting(['id' => 'kp10Token', 'value' => $pasport_token]);

                    return response()->json([], 200);
                }
            }
        }

        return response()->json([], 200);
    }

    /**
     * get program field list
     *
     * @param [type] $id
     * @return void
     */
    public function programListJson()
    {
        //Get field list from Megaplan
        try {
            $integration      = IntegrationMegaplan::first();
            if ($integration) {
                $params           = [
                'accessToken'  => $integration->api_token,
                'url'          => 'https://' . $integration->host,
                'uuid'         => $integration->uuid
            ];

                $MegaplanV3 = new MegaplanV3($params);
                $program    = $MegaplanV3->getDealProgram();
                // $program      = $DealProgram->data;
                return response()->json($program);
            }
            // if ($program) {
            // }
        } catch (Exception $e) {
            Log::debug($e);
            return response()->json([], 422);
        }
        return response()->json([], 422);
    }

    public function programFieldsJson($account, $id)
    {
        if (!$id || !is_numeric($id)) {
            return response()->json([], 422);
        }

        //Get field list from Megaplan
        $integration      = IntegrationMegaplan::first();

        if ($integration) {
            $params           = [
                'accessToken'  => $integration->api_token,
                'url'          => 'https://' . $integration->host,
                'uuid'         => $integration->uuid
            ];

            $MegaplanV3       = new MegaplanV3($params);
            $program          = $MegaplanV3->getProgramField($id);
            // $program          = $DealProgram->data;
        }

        if ($program) {
            return response()->json(['program' => $program]); // , 'type' => self::megaplanFieldType()
        }

        return response()->json([], 422);
    }

    // +++ NOT IN USE ANYMORE +++
    // public function megaplanFieldType()
    // {
    //     $types = SystemMegaplanFieldType::all()->toArray();
    //     $array = [];

    //     if (is_array($types)) {
    //         foreach ($types as $key => $val) {
    //             $array[$val['type_id']] = $val['type_name'];
    //         }
    //         return $array;
    //     }
    //     return;
    // }

    /**
     * Get deal offer data
     *
     * @param string $account
     * @param int $id
     * @return json
     */
    public function dealOffer($account, $id)
    {
        $accountId = Auth::user($account)->id;
        $api_token = '';
        $offer     = Offer::with('megaplanDeal', 'variants.products.values', 'variants.fields', 'variants.specialDiscounts', 'state.data', 'numberRelation', 'currency.data.system')
            ->whereHas('megaplanDeal', function ($query) use ($id) {
                $query->whereDealId($id);
            })->first();

        if ($offer) {
            $api_token              = IntegrationMegaplan::first()->api_token;
            $offer->productEmptyImg = '/storage/resource/templates/base/product/empty.png';

            unset($offer->gjs_components, $offer->gjs_css, $offer->gjs_html, $offer->gjs_styles, $offer->gjs_assets);
        }
        // return response()->json($offer_card);
        return response()->json(['offer' => $offer, 'api_token' => $api_token]);
    }

    /**
     * Set deal offer data
     *
     * @param string $account
     * @param int $id
     * @return json
     */
    public function setDealOffer($account, $id, Request $request)
    {
        $request->validate([
            'id' => 'required|numeric'
        ]);

        $offerId = $request->input('id');

        $user      = Auth::user($account);
        $accountId = $user->accountId;

        //Get offer
        $offer = Offer::with('megaplanDeal')->whereId($offerId)->first();

        if (!$offer) {
            return response()->json(['errors' => __('messages.offer.not_found')], 422);
        }

        try {
            DB::transaction(function () use ($id, $accountId, $offer, $account) {
                if ($offer->megaplanDeal) {
                    $offer->megaplanDeal()->update([
                        'deal_id'    => $id,
                        'account_id' => $accountId
                    ]);
                } else {
                    $offer->megaplanDeal()->create([
                        'deal_id'    => $id,
                        'account_id' => $accountId
                    ]);
                }

                // save/update deal info
                $this->setDealData($account, $id, $offer, $accountId);

                // transfer fields from deal
                // $id id угоди

                $integrationMegaplan = IntegrationMegaplan::first();
                $user                = Auth::user();

                $params           = [
                    'accessToken'  => $integrationMegaplan->api_token,
                    'url'          => 'https://' . $integrationMegaplan->host
                ];

                $MegaplanV3 = new MegaplanV3($params);

                // get dial info from megaplan by id
                $dealInfo  = $MegaplanV3->getDeal($id);
                $programId = $dealInfo->program->id;
                $stateId = $dealInfo->state->id;

                IntegrationMegaplanDeal::updateOrCreate(
                    [
                        'megaplan_deal_id' => $dealInfo->id,
                        'account_id' => $accountId
                    ],
                    [
                        'megaplan_program_id' => $programId,
                        'megaplan_state_id' => $stateId
                    ]
                );

                // save megaplan field value
                // <- from stdClass object ot array -> //
                $this->updateDealFields(json_decode(json_encode($dealInfo), true), $accountId);

                $IntegrationFields = IntegrationMegaplanField::whereProgramId($programId)->get();
            });
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        return response()->json(['message' => __('messages.offer.save.success')]);
    }

    public function setDealData($account, $id, $offer, $accountId)
    {
        $integrationMegaplan = IntegrationMegaplan::first();
        $user                = Auth::user();


        $params           = [
            'accessToken'  => $integrationMegaplan->api_token,
            'url'          => 'https://' . $integrationMegaplan->host
        ];

        $MegaplanV3 = new MegaplanV3($params);

        // get dial info from megaplan by id

        try {
            $dealInfo = $MegaplanV3->getDeal($id);
            if ($dealInfo) {
                DB::transaction(function () use ($id, $account, $dealInfo, $user, $MegaplanV3, $offer, $params) {
                    $responsibles      = $dealInfo->contractor->responsibles;
                    $employee = '';
                    $accountId           = $user->accountId;

                    if ($responsibles) {
                        // Create user with MegaplanTrait
                        if ($responsibles) {
                            foreach ($responsibles as $key => $value) {
                                $employee = $this->addMegaplanUser($account, $accountId, $value, $params);
                            }
                        }
                    }

                    if ($employee) {
                        // Set create employee as responsible
                        $responsibles = [$employee->id];

                        // Client info
                        if ($dealInfo->contractor) {
                            $contractor     = $dealInfo->contractor;
                            $contentType    = $contractor->contentType;
                            $contractor_id  = $contractor->id;
                            $megaplanClient = '';
                            $email          = [];
                            $phone          = [];

                            foreach ($contractor->contactInfo as $key => $value) {
                                if ($value->type == 'email') {
                                    $email[] = ['email' => $value->value];
                                }
                                if ($value->type == 'phone') {
                                    if (strlen($value->value) <= 12) {
                                        $phone[] = ['phone' => $value->value];
                                    } else {
                                        $phone[] = ['phone' => ''];
                                    }

                                }
                            }

                            // if client type Human
                            if ($contentType == 'ContractorHuman') {
                                // check client in DB
                                $megaplanClient = IntegrationMegaplanContractorHuman::whereAccountId($user->accountId)->whereMegaplanHumanId($contractor_id)->first();

                                if ($megaplanClient) {
                                    //Get company model
                                    $client = Client::whereId($megaplanClient->human_id)->first();
                                } else {
                                    // Get display name for user
                                    $firstName   = isset($contractor->firstName) ? $contractor->firstName : '';
                                    $middleName  = isset($contractor->middleName) ? $contractor->middleName : '';
                                    $lastName    = isset($contractor->lastName) ? $contractor->lastName : '';
                                    $displayName = '';

                                    if ($lastName) {
                                        $displayName .= $lastName . ' ';
                                    }
                                    if ($firstName) {
                                        $displayName .= $firstName . ' ';
                                    }
                                    if ($middleName) {
                                        $displayName .= $middleName . ' ';
                                    }

                                    // Create client in our account
                                    $clientParams = [
                                    'type'           => 2, // Human
                                    'surname'        => $lastName,
                                    'name'           => $firstName,
                                    'middle_name'    => $middleName,
                                    'emails'         => $email,
                                    'phones'         => $phone,
                                    'position'       => '',
                                    'description'    => '',
                                    'responsibles'   => $responsibles,
                                    'contactPersons' => [0],
                                    'companyId'      => 0, // no need for human
                                ];

                                    // Using trait for reating company
                                    $client = $this->addClient($account, new Request($clientParams));

                                    // Create megaplan client
                                    IntegrationMegaplanContractorHuman::create([
                                    'account_id'          => $user->accountId,
                                    'human_id'            => $client->id,
                                    'megaplan_human_id'   => $contractor_id,
                                    'megaplan_human_name' => $displayName
                                ]);
                                }
                                //Update client related to offer
                                $offer->clientRelation()->update(['client_id' => $client->id]);
                            }

                            // if client type Company
                        if ($contentType == 'ContractorCompany') { // IntegrationMegaplanContractorCompany
                            $megaplanClient = IntegrationMegaplanContractorCompany::whereAccountId($user->accountId)->whereMegaplanCompanyId($contractor_id)->first();

                            if ($megaplanClient) {
                                //Get company model

                                $client = Client::whereId($megaplanClient->company_id)->first();

                                // Update client related to offer
                                if ($client) {
                                    $offer->clientRelation()->update(['client_id' => $client->id]);

                                    $ContactPerson = ClientContactPerson::whereClientId($client->id)
                                        ->whereNotNull('client_contact_person_id')
                                        // ->orderBy('client_contact_person_id', 'desc')
                                        ->first();

                                    if ($ContactPerson && $ContactPerson->client_contact_person_id) {
                                        $offer->contactPersonRelation()->update(['client_id' => $ContactPerson->client_contact_person_id]);
                                    }
                                }
                            } else {
                                $displayName = $contractor->name ? $contractor->name : 'NoName';

                                $res = $MegaplanV3->getContractorCompany($contractor_id);

                                if (!$phone) {
                                    $phone = [];
                                    array_push($phone, ['phone' => '']);
                                }

                                if (!$email) {
                                    $email = [];
                                    array_push($email, ['email' => '']);
                                }

                                // Create client in our account
                                $clientParams = [
                                    'type'           => 1, // Company
                                    'surname'        => '',
                                    'name'           => $displayName,
                                    'middle_name'    => '',
                                    'emails'         => $email,
                                    'phones'         => $phone,
                                    'position'       => '',
                                    'description'    => '',
                                    'responsibles'   => $responsibles,
                                    'contactPersons' => [0],
                                    'companyId'      => 0,
                                ];

                                // Using trait for reating company
                                $company = $this->addClient($account, new Request($clientParams));

                                // Create megaplan client
                                if ($company) {
                                    IntegrationMegaplanContractorCompany::create([
                                        'company_id'            => $company->id,
                                        'account_id'            => $user->accountId,
                                        'megaplan_company_id'   => $contractor_id,
                                        'megaplan_company_name' => $displayName
                                    ]);

                                    // Update client related to offer
                                    $offer->clientRelation()->update(['client_id' => $company->id]);
                                }

                                // отримати картку компанії з мп і взяти контактні особи
                                $ContractorCompany = $MegaplanV3->getContractorCompany($contractor_id);

                                $contacts = isset($ContractorCompany->contacts) ? $ContractorCompany->contacts : '';

                                $contactPersons = [];

                                $emails  = [];
                                $phones  = [];
                                if ($contacts) {
                                    foreach ($contacts as $key => $value) {
                                        foreach ($value->contactInfo as $k => $v) {
                                            if ($v->type == 'email') {
                                                $emails[] = ['email' => $v->value];
                                            }
                                            if ($v->type == 'phone') {
                                                if (strlen($v->value) <= 12) {
                                                    $phones[] = ['phone' => $v->value];
                                                } else {
                                                    $phones[] = ['phone' => ''];
                                                }
                                            }
                                        }

                                        if (!$phones) {
                                            $phones = [];
                                            array_push($phones, ['phone' => '']);
                                        }

                                        if (!$emails) {
                                            $emails = [];
                                            array_push($emails, ['email' => '']);
                                        }

                                        // Create client in our account
                                        $clientParams = [
                                            'type'           => 3, // contact person
                                            'surname'        => $value->lastName ? $value->lastName : '',
                                            'name'           => $value->firstName ? $value->firstName : 'NoName',
                                            'middle_name'    => $value->middleName ? $value->middleName : '',
                                            'emails'         => $emails,
                                            'phones'         => $phones,
                                            'position'       => $value->position ? $value->position : '',
                                            'description'    => $value->textDescription ? $value->textDescription : '',
                                            'responsibles'   => $responsibles,
                                            'contactPersons' => [0],
                                            'companyId'      => $company->id,
                                        ];

                                        // Using trait for reating company
                                        $client = $this->addClient($account, new Request($clientParams));

                                        $contactPersons[] = $client->id;

                                        if ($key == 0) {
                                            $offer->contactPersonRelation()->update(['client_id' => $client->id]);
                                        }
                                    }
                                }
                                //prepare contact persons
                                foreach ($contactPersons as &$value) {
                                    $value = ['client_contact_person_id' => $client->id];
                                }

                                //Save contact person
                                // $company->contactPersonRelation()->createMany($contactPersons);
                            }
                        }
                        }
                    }
                });
            }
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        return response()->json(['errors' => __('messages.offer.not_found')], 422);
    }

    /**
     * Update megaplan deal fields in DB
     *
     * @param [object] $res
     * @return void
     */
    public function updateDealFields($res, $accountId = 0)
    {
        Log::debug($res);
        $IntegrationFields = IntegrationMegaplanField::withoutGlobalScope(App\Scopes\IntegrationMegaplanFieldScope::class)
            ->whereAccountId($accountId)
            ->whereProgramId($res['program']['id'])->get();

        // перебираємо, якщо є, всі збережені в БД поля
        if ($IntegrationFields) {
            if (!$accountId) {
                $user      = Auth::user();
                $accountId = $user->accountId;
            }

            $dealId = $res['id'];
Log::debug($accountId);
Log::debug($dealId);
            $MegaplanDeal = OfferMegaplanDeal::whereAccountId($accountId)->whereDealId($dealId)->first();//App\Scopes\ClientScope::class

            if ($MegaplanDeal) {
                foreach ($IntegrationFields as $key => $val) {
                    $mp_field_id = $val->field_id;
                    $field_id    = $val->id;

                    Log::debug('array_key_exists');
                    Log::debug($res);
                    Log::debug($mp_field_id);

                    // перевіряємо чи присутнє поле в масиві
                    if (array_key_exists($mp_field_id, $res)) {
                        $content     = $res[$mp_field_id];
                        $value       = '';

                        //---------------------------------+
                        // незрозумілі пропускаємо         |
                        // зовнішній контент - ПРОПУСКАЄМО |
                        // доставка          - ПРОПУСКАЄМО |
                        // файли             - ПРОПУСКАЄМО |
                        //---------------------------------+

                        /*----------------------------------------+
                        | клієнт та користувач - не зрозумілі     |
                        | типи, треба уточнити що з ними робити   |
                        +----------------------------------------*/

                        $content_type = $val->content_type;

                        if ($content_type == 'MoneyField') {
                            $value = $content['value'];
                        }

                        if ($content_type == 'DateTimeField') {
                            $value = $content['value']; // 2018-09-10T21:00:00+00:00
                        }

                        if ($content_type == 'DateField') {
                            if ($content['year'] && $content['month'] && $content['day']) {
                                $value = $content['year'] . '-' . str_pad($content['month'], 2, '0', STR_PAD_LEFT) . '-' . str_pad($content['day'], 2, '0', STR_PAD_LEFT);
                            }
                        }

                        if ($content_type == 'BoolField') { // да/нет
                            if ($content) {
                                $value = '1';
                            } else {
                                $value = '0';
                            }
                        }

                        if ($content_type == 'EnumField') {
                            $value        = $content;
                        }

                        if ($content_type == 'FloatField') {
                            $value        = $content;
                        }

                        if ($content_type == 'StringField') {
                            $value        = $content;
                        }
                        if (isset($value) && $value != '') {
                            try {
                                DB::transaction(function () use ($accountId, $value, $dealId, $field_id) {
                                    Log::debug('update crm field');
                                    Log::debug($accountId);
                                    Log::debug($value);
                                    Log::debug($dealId);
                                    Log::debug($field_id);
                                    $result = IntegrationMegaplanFieldValues::updateOrCreate(
                                        [
                                            'account_id'            => $accountId,
                                            'field_id'              => $field_id,
                                            'deal_id'               => $dealId
                                        ],
                                        [
                                            'megaplan_field_values' => (string)$value
                                        ]
                                    );
                                });
                            } catch (Exception $e) {
                                Log::debug($e->getMessage());
                                return response()->json(['errors' => $e->getMessage()], 422);
                            }
                        }
                    }
                }
            }
        }
        return;
    }

    public function getEmployeesList()
    {
        $integrationMegaplan = IntegrationMegaplan::first();
        $user                = Auth::user();
        $accountId           = $user->accountId;

        try {
            $MegaplanV3    = new MegaplanV3();
            // $EmployeesList = $MegaplanV3->getEmployee();

            $EmployeesList = $MegaplanV3->getRecursively('getEmployee', [
                'limit' => 100
            ]);

            return response()->json($EmployeesList);
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        return response()->json([], 200);
    }
}
