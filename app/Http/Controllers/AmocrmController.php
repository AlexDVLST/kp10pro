<?php

namespace App\Http\Controllers;

use App\Jobs\ScenarioJob;
use App\Models\User;
use App\Models\Offer;
use App\Helpers\Amocrm;
use App\Helpers\UserHelper;
use App\Models\OfferAmocrmDeal;
use App\Models\IntegrationAmocrm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\EmployeeTrait;
use App\Http\Traits\ClientTrait;
use App\Models\IntegrationAmocrmLead;
use App\Models\IntegrationAmocrmCompany;
use App\Models\IntegrationAmocrmContact;
use App\Models\IntegrationAmocrmLeadField;
use App\Models\IntegrationAmocrmCustomField;
use App\Models\IntegrationAmocrmLeadFieldValue;
use App\Models\IntegrationAmocrmUser;
use App\Models\IntegrationAmocrmCompanyField;
use App\Models\IntegrationAmocrmCustomFieldEnum;
use App\Models\IntegrationAmocrmCompanyFieldValue;
use App\Models\Client;
use App\Models\ClientContactPerson;
use App\Http\Traits\AmocrmTrait;
use App\Models\IntegrationAmocrmContactField;
use App\Models\IntegrationAmocrmContactFieldValue;
use App\Scopes\IntegrationAmocrmCustomFieldScope;
use App\Scopes\IntegrationAmocrmScope;
use App\Models\IntegrationAmocrmUserAccessToken;
use App\Models\Integration;
use App\Jobs\PolytellEmailParserJob;
use App\Models\Order;

class AmocrmController extends Controller
{
    use EmployeeTrait, ClientTrait, AmocrmTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'amohash'    => 'required|string',
            'amouser'    => 'required|email',
            'domain'     => 'required|domain',
            'amouser_id' => 'required|numeric',
            'account'    => 'nullable|string',
        ]);

        $userToken = '';
        $domain = '';

        try {
            DB::transaction(function () use ($request, &$userToken, &$domain) {
                $token     = $request->input('amohash');
                $host      = $request->input('domain');
                $email     = $request->input('amouser');
                $amoUserId = $request->input('amouser_id');
                $account   = $request->input('account');

                $amoUser = [];

                //Get user detail
                $Amocrm = new Amocrm([
                    'login' => $email,
                    'token' => $token,
                    'host'  => $host
                ]);
                //Get user from amoCRM
                $users = $Amocrm->users();

                if ($users) {
                    //Find user by user_id
                    $amoUser = collect($users)->filter(function ($user) use ($amoUserId) {
                        return $user->id == $amoUserId;
                    })->first();

                    if (!$amoUser) {
                        return response()->json(['errors' => __('messages.amocrm.user.not_found')], 422);
                    }

                    //Find user in DB by api key
                    $exist = IntegrationAmocrm::withoutGlobalScope(IntegrationAmocrmScope::class)->whereApiToken($token)->whereHost($host)->first();

                    //Token name
                    $tokenName = 'amoCRM';

                    $firstName      = $amoUser->name ? $amoUser->name : 'NoName';
                    $lastName       = $amoUser->last_name ? $amoUser->last_name : $firstName;
                    $middleName     = '';

                    if (!$exist) {

                        //TODO: CHECK
                        //Check if this email already in DB
                        //Find user with same email with role user (account admin)
                        $userEmailExist = User::role('user')->whereEmail($email)->first();
                        if($userEmailExist){
                            return response()->json(['errors' => __('messages.register.email.exist')], 422);
                        }

                        //Create new user
                        $domain   = UserHelper::generateDomain();
                        $password = str_random(8); // generate password (8 symbol)

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

                        // Start registration event
                        event(new Registered($user));

                        //Insert new data
                        IntegrationAmocrm::create([
                            'host'       => $host,
                            'api_token'  => $token,
                            'login'      => $email,
                            'account_id' => $user->accountId
                        ]);
                        
                        //Settings
                        Integration::create([
                            'system_crm_id' => 2, //Amocrm
                            'account_id' => $user->accountId
                        ]);

                        //Send request via EmailParser
                        PolytellEmailParserJob::dispatch($user, 2);
                    } else {
                        //Find user
                        $user = User::whereId($exist->account_id)->first();
                    }

                    //Check if user exist
                    $itengrationUser = IntegrationAmocrmUser::whereAccountId($user->accountId)->whereAmocrmUserId($amoUserId)->first();
                    if (!$itengrationUser) {
                        //Create new user in integration table
                        IntegrationAmocrmUser::create([
                            'user_id'               => $user->id,
                            'account_id'            => $user->accountId,
                            'amocrm_user_id'        => $amoUser->id,
                            'amocrm_user_name'      => $firstName,
                            'amocrm_user_last_name' => $lastName,
                            'amocrm_user_login'     => $amoUser->login
                        ]);
                    }

                    //Revoke token
                    $user->tokens->each(function ($token) use ($tokenName) {
                        //Invalidate current tokens
                        if ($token->name == $tokenName) {
                            $token->revoke();
                        }
                    });

                    // Creating a token without scopes...
                    $token = $user->createToken($tokenName)->accessToken;
                    $userToken = $token;
                    //Save access token
                    IntegrationAmocrmUserAccessToken::updateOrCreate(
                        ['amocrm_user_id' => $amoUserId, 'account_id' => $user->account_id],
                        ['access_token' => $token]
                    );

                    $domain = $user->domain;

                    //Import users from crm
                    collect($users)->each(function ($amoUser) use ($amoUserId, $tokenName, $user) {
                        //Except admin
                        if ($amoUser->id != $amoUserId) {
                            $employee = $this->addAmocrmUser($user, $amoUser);

                            //Revoke token
                            $employee->tokens->each(function ($token) use ($tokenName) {
                                //Invalidate current tokens
                                if ($token->name == $tokenName) {
                                    $token->revoke();
                                }
                            });

                            // Creating a token without scopes...
                            $token = $employee->createToken($tokenName)->accessToken;

                            //Save access token
                            IntegrationAmocrmUserAccessToken::updateOrCreate(
                                ['amocrm_user_id' => $amoUser->id, 'account_id' => $user->account_id],
                                ['access_token' => $token]
                            );
                        }
                    });

                    $webhook = [
                        'url'    => env('APP_URL') . '/integration/amocrm/events',
                        'events' => [
                            'update_lead',
                            'update_contact',
                            'update_company',
                            'update_customer',
                            'delete_lead',
                            'delete_contact',
                            'delete_company',
                            'delete_customer',
                            'status_lead',
                            'restore_contact',
                            'restore_company',
                            'restore_lead'
                        ]
                    ];

                    //Remove webhooks
                    $Amocrm->removeWebhook([
                        'unsubscribe' => [$webhook]
                    ]);
                    //Set new one webhooks
                    $Amocrm->addWebhook([
                        'subscribe' => [$webhook]
                    ]);
                }
            });
            
            return response()->json(['token' => $userToken, 'account' => env('APP_PROTOCOL') . $domain . '.' . env('APP_DOMAIN')]);

        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }
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
        //
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
     * Get user access token
     *
     * @param string $account
     * @param int $id
     * @return json
     */
    public function userAccessToken($account, $id)
    {
        //Check param
        if (!$id) {
            return response()->json(['errors' => __('messages.amocrm.user.not_found')], 422);
        }
        //Find user in DB
        $amoUser = IntegrationAmocrmUser::whereAmocrmUserId($id)->first();
        if (!$amoUser) {
            return response()->json(['errors' => __('messages.amocrm.user.not_found')], 422);
        }

        $user = Auth::user();

        $token = '';

        $amocrmUserToken = IntegrationAmocrmUserAccessToken::whereAmocrmUserId($id)->first();

        if ($amocrmUserToken) {
            $token = $amocrmUserToken->access_token;
        }

        return response()->json(['accessToken' => $token]);
    }

    /**
     * API REQUEST
     * Get deal offer data
     *
     * @param string $account
     * @param int $id
     * @return json
     */
    public function dealOffer($account, $id, Request $request)
    {
        //Get offer relative to deal
        $offer = Offer::with(
            'amoCrmDeal',
            'variants.products.values',
            'variants.fields',
            'variants.specialDiscounts',
            'state.data',
            'numberRelation',
            'currency.data.system'
        )
            ->whereHas('amoCrmDeal', function ($query) use ($id) {
                $query->whereDealId($id);
            })->first();

        if ($offer) {
            $offer->productEmptyImg = '/storage/resource/templates/base/product/empty.png';

            unset($offer->gjs_components, $offer->gjs_css, $offer->gjs_html, $offer->gjs_styles, $offer->gjs_assets);
        }
        return response()->json(['offer' => $offer]);
    }

    /**
     * API REQUEST
     * Get deal offer data
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

        //Get offer
        $offer = Offer::with('amoCrmDeal')->whereId($offerId)->first();

        if (!$offer) {
            return response()->json(['errors' => __('messages.offer.not_found')], 422);
        }

        if ($offer->amoCrmDeal) {
            return response()->json(['errors' => __('messages.offer.integration.deal.relation.error')], 422);
        }

        try {
            DB::transaction(function () use ($id, $account, $offer) {
                //Get auth user
                $user = Auth::user();

                $offer->amoCrmDeal()->create([
                    'deal_id' => $id
                ]);

                //Init
                $Amocrm = new Amocrm($user->accountId);
                //Get deal card
                $lead = $Amocrm->lead($id);
                //Get users
                $amocrmUsers = $Amocrm->users();
                //Responsible id
                $amocrmResponsibleId = $lead->responsible_user_id;
                //Get user data
                $amocrmUser = collect($amocrmUsers)->filter(function ($user) use ($amocrmResponsibleId) {
                    return $user->id == $amocrmResponsibleId;
                })->first();
                //Employee
                $employee = [];
                //Company
                $company = [];

                //Create user with trait
                $employee = $this->addAmocrmUser($user, $amocrmUser);

                //Create or update lead data
                IntegrationAmocrmLead::updateOrCreate(
                    [
                        'amocrm_lead_id' => $id,
                        'account_id'     => $user->accountId,
                    ],
                    [
                        'amocrm_lead_responsible_user_id' => $lead->responsible_user_id,
                        'amocrm_lead_status_id'           => $lead->status_id,
                        'amocrm_lead_sale'                => $lead->sale
                    ]
                );

                //Offer employee
                $offer->employee()
                    ->update(['user_id' => $employee->id]);

                //Get company data
                if ($lead->company && isset($lead->company->id)) {
                    //Check if company not created
                    $amocrmCompanyExist = IntegrationAmocrmCompany::whereAccountId($user->accountId)->whereAmocrmCompanyId($lead->company->id)->first();
                    if ($amocrmCompanyExist) {
                        //Get company model
                        $company = Client::whereId($amocrmCompanyExist->company_id)->first();
                    } else {
                        //Get custom fields
                        // $customFields = $Amocrm->customFields();
                        //Get custom fields companies
                        // $customFieldsCompanies = $customFields->companies;
                        // collect($customFieldsCompanies)->each(function ($customFieldCompany) use ($user) {
                        //     //Create custom field for companies
                        //     $field = IntegrationAmocrmCustomField::updateOrCreate(
                        //         [
                        //             'field_id'      => $customFieldCompany->id,
                        //             'account_id'    => $user->accountId,
                        //             'type'          => 'company',
                        //             'field_type_id' => $customFieldCompany->field_type,
                        //         ],
                        //         [
                        //             'field_name'    => $customFieldCompany->name,
                        //         ]
                        //     );

                        //     if (isset($customFieldCompany->enums)) {
                        //         $enums = collect($customFieldCompany->enums);

                        //         //If field has enums
                        //         if ($enums->isNotEmpty()) {
                        //             $enums->each(function ($enum, $id) use ($field) {
                        //                 IntegrationAmocrmCustomFieldEnum::updateOrCreate(
                        //                     [
                        //                         'custom_field_id' => $field->id,
                        //                         'enum_id'         => $id,
                        //                     ],
                        //                     [
                        //                         'enum_value' => $enum
                        //                     ]
                        //                 );
                        //             });
                        //         }
                        //     }
                        // });

                        //Get company card
                        $amocrmCompany = $Amocrm->company($lead->company->id);
                        //Company emails
                        $emails = [];
                        //Company phones
                        $phones = [];

                        $createdCompany = IntegrationAmocrmCompany::create([
                            'account_id'          => $user->accountId,
                            'company_id'          => 0,
                            'amocrm_company_id'   => $amocrmCompany->id,
                            'amocrm_company_name' => $amocrmCompany->name
                        ]);

                        //Get company custom fields
                        collect($amocrmCompany->custom_fields)->each(function ($companyCustomField) use ($user, $createdCompany, &$emails, &$phones) {
                            //Update field
                            // $companyField = IntegrationAmocrmCompanyField::updateOrCreate(
                            //     [
                            //         'account_id' => $user->accountId,
                            //         'field_id'   => $companyCustomField->id,
                            //         'company_id' => $createdCompany->id
                            //     ],
                            //     [
                            //         'field_name' => $companyCustomField->name,
                            //     ]
                            // );

                            $values = collect($companyCustomField->values);

                            //If field has values
                            if ($values->isNotEmpty()) {
                                $values->each(function ($value) use ($companyCustomField, &$emails, &$phones) {
                                    //Update company field value
                                    // IntegrationAmocrmCompanyFieldValue::updateOrCreate(
                                    //     [
                                    //         'company_field_id' => $companyField->id,
                                    //         'field_enum'       => isset($value->enum) ? $value->enum : 0
                                    //     ],
                                    //     [
                                    //         'field_value' => $value->value,
                                    //     ]
                                    // );
                                    //Fill phones
                                    if ($companyCustomField->name == 'Телефон') {
                                        $phones[] = [
                                            'phone' => $value->value
                                        ];
                                    }
                                    //Fill emails
                                    if ($companyCustomField->name == 'Email') {
                                        $emails[] = [
                                            'email' => $value->value
                                        ];
                                    }
                                });
                            }
                        });

                        //Set create employee as responsible
                        $responsibles = [$employee->id];

                        //Create company in our account
                        $clientParams = [
                            'type'           => 1, //company
                            'surname'        => '',
                            'name'           => $amocrmCompany->name,
                            'middle_name'    => '',
                            'emails'         => $emails,
                            'phones'         => $phones,
                            'position'       => '',
                            'description'    => '',
                            'responsibles'   => $responsibles,
                            'contactPersons' => [0],
                            'companyId'      => 0,
                        ];

                        //Using trait for reating company
                        $company = $this->addClient($account, new Request($clientParams));

                        //Update company id
                        $createdCompany->company_id = $company->id;
                        $createdCompany->save();
                    }

                    //Update client related to offer
                    $offer->clientRelation()->update(['client_id' => $company->id]);
                }
                //Get contacts
                if ($lead->contacts && isset($lead->contacts->id)) {
                    $contacts = $Amocrm->contacts(['id' => $lead->contacts->id]);

                    collect($contacts)->each(function ($contact, $index) use ($user, $account, $company, $amocrmUsers, $offer) {
                        //Check if contact not created
                        if (!IntegrationAmocrmContact::whereAccountId($user->accountId)->whereAmocrmContactId($contact->id)->first()) {
                            //Contact emails
                            $emails = [];
                            //Contact phones
                            $phones = [];

                            //Get contact responsible
                            $amocrmResponsibleId = $contact->responsible_user_id;
                            //Create amocrm contact
                            $createdContact = IntegrationAmocrmContact::create(
                                [
                                    'contact_id'                => $contact->id,
                                    'account_id'                => $user->accountId,
                                    'amocrm_contact_id'         => $contact->id,
                                    'amocrm_contact_name'       => $contact->name,
                                    'amocrm_contact_company_id' => isset($contact->company->id) ? $contact->company->id : 0
                                ]
                            );

                            //Get contact custom fields
                            collect($contact->custom_fields)->each(function ($contactCustomField) use ($user, $createdContact, &$emails, &$phones, $offer) {
                                //Update field
                                // $contactField = IntegrationAmocrmContactField::updateOrCreate(
                                //     [
                                //         'account_id' => $user->accountId,
                                //         'field_id'   => $contactCustomField->id,
                                //         'contact_id' => $createdContact->id
                                //     ],
                                //     [
                                //         'field_name' => $contactCustomField->name,
                                //     ]
                                // );

                                $values = collect($contactCustomField->values);

                                //If field has values
                                if ($values->isNotEmpty()) {
                                    $values->each(function ($value) use ($contactCustomField, &$emails, &$phones) {
                                        //Update company field value
                                        // IntegrationAmocrmContactFieldValue::updateOrCreate(
                                        //     [
                                        //         'contact_field_id' => $contactField->id,
                                        //         'field_enum'       => isset($value->enum) ? $value->enum : 0
                                        //     ],
                                        //     [
                                        //         'field_value' => $value->value,
                                        //     ]
                                        // );
                                        //Fill phones
                                        if ($contactCustomField->name == 'Телефон') {
                                            $phones[] = [
                                                'phone' => $value->value
                                            ];
                                        }
                                        //Fill emails
                                        if ($contactCustomField->name == 'Email') {
                                            $emails[] = [
                                                'email' => $value->value
                                            ];
                                        }
                                    });
                                }
                            });

                            //Get user data
                            $amocrmUser = collect($amocrmUsers)->filter(function ($user) use ($amocrmResponsibleId) {
                                return $user->id == $amocrmResponsibleId;
                            })->first();

                            //Create or return existed
                            $employee = $this->addAmocrmUser($user, $amocrmUser);

                            //Set create employee as responsible
                            $responsibles = [$employee->id];

                            //Create client in our account
                            $clientParams = [
                                'type'           => 3, //contact person
                                'surname'        => '',
                                'name'           => $contact->name,
                                'middle_name'    => '',
                                'emails'         => $emails,
                                'phones'         => $phones,
                                'position'       => '',
                                'description'    => '',
                                'responsibles'   => $responsibles,
                                'contactPersons' => [0],
                                'companyId'      => $company && isset($company->id) ? $company->id : 0,
                            ];

                            //Using trait for reating company
                            $client = $this->addClient($account, new Request($clientParams));

                            //If company exist
                            if ($company && isset($company->id)) {
                                //Update contact persons for relative company
                                ClientContactPerson::firstOrCreate(
                                    [
                                        'client_id'                => $company->id,
                                        'client_contact_person_id' => $client->id
                                    ]
                                );
                            }

                            //Update contact id for relation
                            $createdContact->contact_id = $client->id;
                            $createdContact->save();
                        }
                        //For the first contact
                        if ($index == 0) {
                            $amocrmContact = IntegrationAmocrmContact::whereAccountId($user->accountId)->whereAmocrmContactId($contact->id)->first();
                            $contact = Client::whereId($amocrmContact->contact_id)->first();
                            //Update contact person related to offer
                            $offer->contactPersonRelation()->update(['client_id' => $contact->id]);
                        }
                    });
                }
                //Custom fields
                if ($lead->custom_fields) {
                    $leadFields = IntegrationAmocrmCustomField::whereType('lead')->get();

                    collect($lead->custom_fields)->each(function ($customField) use ($leadFields, $user, $lead) {
                        //If custom field added in settings
                        if ($leadFields->contains('amocrm_field_id', $customField->id)) {
                            $field = IntegrationAmocrmLeadField::create(
                                [
                                    'account_id'             => $user->accountId,
                                    'amocrm_lead_id'         => $lead->id,
                                    'amocrm_field_id'        => $customField->id,
                                    'amocrm_field_name'      => $customField->name,
                                    'amocrm_field_is_system' => $customField->is_system,
                                ]
                            );

                            // if ($customField->values) {
                            //     collect($customField->values)->each(function ($value) use ($field) {

                            //         IntegrationAmocrmLeadFieldValue::create(
                            //             [
                            //                 'lead_field_id'      => $field->id,
                            //                 'amocrm_field_value' => $value->value,
                            //                 //amocrm_field_enum_id
                            //             ]
                            //         );
                            //     });
                            // }

                            //Fix for field type Переключатель
                            if (!isset($customField->values->value)) {
                                //Fill new values
                                collect($customField->values)->each(function ($value) use ($field) {
                                    $enum = isset($value->enum) ? $value->enum : '0';
                                    $value = isset($value->value) ? $value->value : $value;

                                    IntegrationAmocrmLeadFieldValue::create(
                                        [
                                            'lead_field_id'         => $field->id,
                                            'amocrm_field_value'    => $value,
                                            'amocrm_field_enum_id'  => $enum,
                                        ]
                                    );
                                });
                            } else {
                                $enum = isset($customField->values->enum) ? $customField->values->enum : '0';
                                $value = isset($customField->values->value) ? $customField->values->value : '';

                                IntegrationAmocrmLeadFieldValue::create(
                                    [
                                        'lead_field_id'         => $field->id,
                                        'amocrm_field_value'    => $value,
                                        'amocrm_field_enum_id'  => $enum,
                                    ]
                                );
                            }
                        }
                    });
                }
            });
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        return response()->json(['message' => __('messages.offer.save.success')]);
    }

    /**
     * Get leads custom fields
     *
     * @param string $account
     * @return json
     */
    public function leadsFields()
    {
        try {
            $user = Auth::user();

            $Amocrm = new Amocrm($user->accountId);
            //Get custom fields
            $customFields = $Amocrm->customFields();
            //Get deals only
            $data = collect($customFields->leads);

            return response()->json($data->flatten());
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }
    }

    /**
     * Get events from amocrm
     *
     * @param Request $request
     * @return void
     */
    public function events(Request $request)
    {
        // Log::info('event');

        $data = $request->all();

        // Log::debug($data);

        if (isset($data['account']['subdomain'])) {
            $subdomain = $data['account']['subdomain'];

            //Check if subdomain exist in DB and has paid order
            $integration = IntegrationAmocrm::whereHost($subdomain . '.amocrm.ru')->first();

            //Subdomain exist
            if ($integration) {
                //Find user
                $user = User::whereId($integration->account_id)->first();
                //User exist
                if ($user) {
                    $order = Order::whereAccountId($integration->account_id)->first();
                    //Check order if it is active
                    if ($order && $order->expired_at->isFuture()) {
                        //Leads
                        if (isset($data['leads'])) {
                            //Update
                            if (isset($data['leads']['update']) && is_array($data['leads']['update'])) {
                                $customFields = IntegrationAmocrmCustomField::withoutGlobalScope(IntegrationAmocrmCustomFieldScope::class)->whereType('lead')->get();

                                foreach ($data['leads']['update'] as $lead) {
                                    $leadId = $lead['id'];

                                    //Check if this lead in DB
                                    $amocrmLead = IntegrationAmocrmLead::whereAmocrmLeadId($leadId)->whereAccountId($user->accountId)->first();
                                    $oldStatusId = $amocrmLead->amocrm_lead_status_id;

                                    if ($amocrmLead) {
                                        //Responsible
                                        $amocrmLead->amocrm_lead_responsible_user_id = $lead['responsible_user_id'];
                                        //Status
                                        $amocrmLead->amocrm_lead_status_id = $lead['status_id'];
                                        //Sale ?
                                        $amocrmLead->amocrm_lead_sale = $lead['price'];

                                        //Update custom field
                                        if ($customFields->isNotEmpty()) {
                                            if (is_array($lead['custom_fields'])) {
                                                collect($lead['custom_fields'])->each(function ($customField) use ($customFields, $user, $leadId) {
                                                    //If custom field added in settings
                                                    if ($customFields->contains('amocrm_field_id', $customField['id'])) {
                                                        $field = IntegrationAmocrmLeadField::updateOrCreate(
                                                            [
                                                                'account_id'             => $user->accountId,
                                                                'amocrm_lead_id'         => $leadId,
                                                                'amocrm_field_id'        => $customField['id'],
                                                            ],
                                                            [
                                                                'amocrm_field_name'      => $customField['name'],
                                                                'amocrm_field_is_system' => 0
                                                            ]
                                                        );

                                                        if ($customField['values']) {
                                                            //Remove current values
                                                            IntegrationAmocrmLeadFieldValue::whereLeadFieldId($field->id)->delete();
                                                            //Fix for field type Переключатель
                                                            if (!isset($customField['values']['value'])) {
                                                                //Fill new values
                                                                collect($customField['values'])->each(function ($value) use ($field) {
                                                                    $enum = isset($value['enum']) ? $value['enum'] : '0';
                                                                    $value = isset($value['value']) ? $value['value'] : $value;

                                                                    IntegrationAmocrmLeadFieldValue::create(
                                                                        [
                                                                            'lead_field_id'         => $field->id,
                                                                            'amocrm_field_value'    => $value,
                                                                            'amocrm_field_enum_id'  => $enum,
                                                                        ]
                                                                    );
                                                                });
                                                            } else {
                                                                $enum = isset($customField['values']['enum']) ? $customField['values']['enum'] : '0';
                                                                $value = isset($customField['values']['value']) ? $customField['values']['value'] : '';

                                                                IntegrationAmocrmLeadFieldValue::create(
                                                                    [
                                                                        'lead_field_id'         => $field->id,
                                                                        'amocrm_field_value'    => $value,
                                                                        'amocrm_field_enum_id'  => $enum,
                                                                    ]
                                                                );
                                                            }
                                                        }
                                                    }
                                                });
                                            }
                                        }

                                        //Save in db
                                        $amocrmLead->save();

                                        //Изменился статус сделки..
                                        if($oldStatusId != $lead['status_id']){
                                            $offer = OfferAmocrmDeal::with('offerData')->whereDealId($leadId)->first();

                                            //Для сценария (Изменился статус в CRM)
                                            ScenarioJob::dispatch(8, 6, '', $offer->offerData);
                                        }
                                    } else {
                                        // Log::notice('Lead not found. id = ' . $leadId . ' accountId = ' . $user->accountId);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function getEmployeesList() {

        $user                = Auth::user();
        $accountId           = $user->accountId;


        try {
            $Amocrm = new Amocrm();
            $res = $Amocrm->users();

            $result = [];
            foreach ($res as $key => $value) {
                $result[] = $value;
            }

            return response()->json( $result );
        } catch (Exception $e) {
            Log::debug($e->getMessage());

            return response()->json(['errors' => $e->getMessage()], 422);
        }
        
        return response()->json([], 200);
    }
}
