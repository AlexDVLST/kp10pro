<?php

namespace App\Http\Controllers;

use App\Models\IntegrationBitrix24;
use App\Models\IntegrationBitrix24CustomField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Cookie\CookieJar;
use App\Models\Client;
use App\Models\Page;
use App\Models\ClientType;
use App\Models\ClientTypeValue;
use App\Models\ClientDescription;
use App\Models\ClientPhone;
use App\Models\ClientEmail;
use App\Models\ClientCompany;
use App\Models\ClientContactPerson;
use App\Models\SystemCrm;
use App\Models\IntegrationMegaplanFieldTypes;
use App\Models\IntegrationMegaplanProgram;
use App\Models\IntegrationMegaplanField;
use App\Models\Integration;
use App\Models\IntegrationMegaplan;
use App\Models\IntegrationAmocrm;
use App\Models\IntegrationAmocrmUser;
use App\Models\IntegrationMegaplanContentTypes;
use App\Models\IntegrationMegaplanEnumValue;
use App\Helpers\Amocrm;
use App\Helpers\MegaplanV3;
use function GuzzleHttp\json_encode;
use App\Models\IntegrationAmocrmLeadField;
use App\Models\IntegrationAmocrmCustomField;
use PHPUnit\Framework\MockObject\Stub\Exception;

class IntegrationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($account)
    {
//        $user = Auth::user();
//        $account = $user->domain;
//        $email = $user->email;
//        $cookie = $account . '|' . $email;
//        Cookie::queue('kp10_account', $cookie, 10, null, env('APP_DOMAIN'));

        $system_crm = SystemCrm::all();

        return view('pages.settings.integration', ['system_crm' => $system_crm]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Stored
     *
     * @param string $account
     * @param string $crm
     * @param Request $request
     * @return json
     */
    public function store($account, Request $request)
    {
        try {
            $user      = Auth::user();
            $crm       = $request->input('crm');
            $systemCrm = SystemCrm::whereType($crm)->first();

            switch ($crm) {
                case 'megaplan':
                    // IntegrationMegaplan::create(['api_token' => $request['token']]);
                break;
                case 'amocrm':

                    $request->validate([
                        'host'      => 'required|string|min:1',
                        'token'     => 'required|string|min:30',
                        'login'     => 'required|email',
                    ]);

                    $login = $request->input('login');

                    try {
                        $Amocrm = new Amocrm([
                            'login' => $login,
                            'token' => $request->input('token'),
                            'host'  => $request->input('host')
                        ]);

                        //Get user from amoCRM
                        $users = $Amocrm->users();

                        if ($users) {
                            //Find user by user_id
                            $amoUser = collect($users)->filter(function ($user) use ($login) {
                                return $user->login == $login;
                            })->first();

                            if ($amoUser) {
                                $firstName  = $amoUser->name ? $amoUser->name : 'NoName';
                                $lastName   = $amoUser->last_name ? $amoUser->last_name : $firstName;
                                $middleName = '';

                                //Create new user in integration table
                                IntegrationAmocrmUser::create([
                                    'user_id'               => $user->id,
                                    'account_id'            => $user->accountId,
                                    'amocrm_user_id'        => $amoUser->id,
                                    'amocrm_user_name'      => $firstName,
                                    'amocrm_user_last_name' => $lastName,
                                    'amocrm_user_login'     => $amoUser->login
                                ]);

                                IntegrationAmocrm::create([
                                    'account_id' => $user->accountId,
                                    'host'       => $request->input('host'),
                                    'api_token'  => $request->input('token'),
                                    'login'      => $login
                                ]);
                            }
                        }
                    } catch (\Exception $e) {
                        return response()->json(['errors' => $e->getMessage()], 422);
                    }

                break;
                case 'bitrix':

                break;

                default:
                    return response()->json(['errors' => __('messages.integration.update.error')], 422);
                break;
            }
            if ($systemCrm) {
                //Create integration
                Integration::create([
                    'account_id'    => $user->accountId,
                    'system_crm_id' => $systemCrm->id
                ]);
            }
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        return response()->json(['message' => __('messages.integration.update.success')]);
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
    public function update($account, $crm, Request $request)
    {
        $user      = Auth::user();
        $accountId = $user->accountId;
        switch ($crm) {
            case 'megaplan':
                IntegrationMegaplan::whereAccountId($accountId)->update(['api_token' => $request['token']]);
            break;
            case 'amocrm':
                // IntegrationAmocrm::update();
            break;
            case 'bitrix':

            break;

            default:
                return response()->json(['errors' => __('messages.integration.update.error')], 422);
            break;
        }

        return response()->json(['message' => __('messages.integration.update.success')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($account, $crm)
    {
        try {
            DB::transaction(function () use ($crm) {
                $delete = false;

                switch ($crm) {
                    case 'megaplan':
                        $delete = IntegrationMegaplan::first()->delete();
                    break;
                    case 'amocrm':
                        $delete = IntegrationAmocrm::first()->delete();
                    break;
                    case 'bitrix24':
                        $delete = IntegrationBitrix24::first()->delete();
                    break;

                    default:
                    return response()->json(['message' => __('messages.integration.delete.error')]);
                    break;
                }

                if ($delete) {
                    //Reset settings
                    Integration::first()->delete();
                }
            });
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        return response()->json(['message' => __('messages.integration.delete.success')]);
    }

    /**
     * Get current integration
     *
     * @return json
     */
    public function integration()
    {
        $user       = Auth::user();
        $domain     = $user->domain;
        $accountId  = $user->accountId;

        $integrationCrm = [];
        $integration    = Integration::first();
        $fields         = [];

        //Check
        if (!$integration) {
            return response()->json(['errors' => __('messages.integration.not_found')], 422);
        }

        $crm = SystemCrm::whereId($integration->system_crm_id)->first();

        //Check
        if (!$crm) {
            return response()->json(['errors' => __('messages.system_crm.crm_not_found')], 422);
        }

        if ($crm->type == 'megaplan') {
            $integrationCrm = IntegrationMegaplan::first();

            $fields = DB::table('integration_megaplan_fields')
                ->leftJoin('integration_megaplan_programs', 'integration_megaplan_fields.program_id', '=', 'integration_megaplan_programs.program_id')
                //->leftJoin('system_megaplan_field_types', 'system_megaplan_field_types.type_id', '=', 'integration_megaplan_fields.field_type_id')
                ->where('integration_megaplan_fields.account_id', '=', $accountId)
                ->where('integration_megaplan_programs.account_id', '=', $accountId)
                ->orderBy('integration_megaplan_fields.id', 'asc')
                ->get();
        }

        if ($crm->type == 'amocrm') {
            $integrationCrm = IntegrationAmocrm::first();
            $fields         = IntegrationAmocrmCustomField::whereType('lead')->get();
        }

        if ($crm->type == 'bitrix24') {
            $integrationCrm = IntegrationBitrix24::first();
            $fields = IntegrationBitrix24CustomField::all();
        }

        $integrationCrm->crm_type = $crm->type;
        $integrationCrm->crm_name = $crm->name;
        $integrationCrm->fields   = $fields;

        return response()->json($integrationCrm);
    }

    public function addMegaplanProgram($account, Request $request)
    {
        $data      = $request->all();
        $user      = Auth::user();
        $accountId = $user->accountId;

        try {
            DB::transaction(function () use ($data, $account, $accountId) {
                IntegrationMegaplanProgram::firstOrCreate([
                    'account_id'   => $accountId,
                    'program_name' => $data['program_name'],
                    'program_id'   => $data['program_id']
                ]);

                $megaplanField = IntegrationMegaplanField::firstOrCreate([
                    'account_id'   => $accountId,
                    'field_name'   => $data['field_name'],
                    'field_id'     => $data['field_id'],
                    'program_id'   => $data['program_id'],
                    'content_type' => $data['content_type']
                ]);

                if (isset($data['enumValues'])) {
                    foreach ($data['enumValues'] as $key => $value) {
                        IntegrationMegaplanEnumValue::firstOrCreate([
                            'account_id'           => $accountId,
                            'field_id'             => $megaplanField->id,
                            'megaplan_enum_values' => $value
                        ]);
                    }
                } elseif (isset($data['refContentType'])) {
                    foreach ($data['refContentType'] as $key => $value) {
                        IntegrationMegaplanContentTypes::firstOrCreate([
                            'account_id'              => $accountId,
                            'field_id'                => $megaplanField->id,
                            'megaplan_content_values' => $value
                        ]);
                    }
                }
            });
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }
        return response()->json(['message' => __('messages.megaplan.add_program.success')]);
    }

    public function deleteMegaplanProgram($account, $id)
    {
        $user      = Auth::user();
        $accountId = $user->accountId;
        try {
            DB::transaction(function () use ($account, $id, $accountId) {
                $program_id  = IntegrationMegaplanField::whereFieldId($id)->first()->program_id;
                $program_cnt = IntegrationMegaplanField::whereProgramId($program_id)->count();
                $res_del     = IntegrationMegaplanField::whereFieldId($id)->delete();

                // TODO: видаляти залежні пля
                // IntegrationMegaplanEnumValue::whereFieldId($res_del->id)->delete();
                // IntegrationMegaplanContentTypes::whereFieldId($res_del->id)->delete();

                if ($program_cnt == 1) {
                    IntegrationMegaplanProgram::whereProgramId($program_id)->delete();
                }
            });
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }
        return response()->json(['message' => __('messages.megaplan.delete_program.success')]);
    }

    /**
     * Add Amocrm lead field
     *
     * @param string $account
     * @param Request $request
     * @return json
     */
    public function addAmocrmLeadField($account, Request $request)
    {
        $request->validate([
            'id'   => 'required|numeric',
            'name' => 'required|string'
        ]);

        try {
            DB::transaction(function () use ($account, $request) {
                $user = Auth::user();

                $field = IntegrationAmocrmCustomField::firstOrCreate([
                    'amocrm_field_id'      => $request->input('id'),
                    'amocrm_field_name'    => $request->input('name'),
                    'amocrm_field_type_id' => $request->input('field_type'),
                    'type'                 => 'lead',
                    'account_id'           => $user->accountId
                ]);

                $enums = $request->input('enums');

                //If field has enums
                if ($enums && is_array($enums)) {
                    $data = [];
                    //Prepare array
                    foreach ($enums as $id => $enum) {
                        $data[] = [
                            'amocrm_enum_id'    => $id,
                            'amocrm_enum_value' => $enum
                        ];
                    }

                    $field->enums()->createMany($data);
                }
            });
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        return response()->json(['message' => __('messages.integration.field.add.success')]);
    }

    /**
     * Delete lead field amocrm
     *
     * @param string $account
     * @param int $id
     * @return json
     */
    public function deleteAmocrmLeadField($account, $id)
    {
        $field = IntegrationAmocrmCustomField::whereAmocrmFieldId($id)->first();

        if (!$field) {
            return response()->json(['errors' => __('messages.integration.field.delete.error')], 422);
        }

        try {
            $field->delete();
        } catch (\Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        return response()->json(['message' => __('messages.integration.field.delete.success')]);
    }

    /**
     * Добавление нового поля
     *
     * @param $account
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addBitrix24DealField($account, Request $request)
    {
        try {
            DB::transaction(function () use ($account, $request) {
                $user = Auth::user();

                $field = IntegrationBitrix24CustomField::firstOrCreate([
                    'bitrix24_field_id'      => $request->input('id'),
                    'bitrix24_field_name'    => $request->input('name'),
                    'bitrix24_field_type_id' => $request->input('field_type'),
                    'type'                   => 'lead',
                    'account_id'             => $user->accountId
                ]);

                $enums = $request->input('enums');

                //If field has enums
                if ($enums && is_array($enums)) {

                    $data = [];
                    //Prepare array
                    foreach ($enums as $enum) {
                        $data[] = [
                            'bitrix24_enum_id'    => $enum['ID'],
                            'bitrix24_enum_value' => $enum['VALUE']
                        ];
                    }

                    $field->enums()->createMany($data);
                }
            });
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        return response()->json(['message' => __('messages.integration.field.add.success')]);
    }

    /**
     * Удаление выбранного поля
     *
     * @param $account
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteBitrix24DealField($account, $id)
    {
        $field = IntegrationBitrix24CustomField::whereBitrix24FieldId($id)->first();

        if (!$field) {
            return response()->json(['errors' => __('messages.integration.field.delete.error')], 422);
        }

        try {
            $field->delete();
        } catch (\Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        return response()->json(['message' => __('messages.integration.field.delete.success')]);
    }

    /**
     * Установка куки
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setCookies(Request $request)
    {
        $data = $request->all();
        $user = Auth::user();

        //clear Cookie 
        Cookie::queue('kp10_account', '', 0, null, env('APP_DOMAIN'));
        Cookie::forget('kp10_account');

        $cookie = '';
        switch ($data['crm']) {
            case 'megaplan':
                $account = $user->domain;
                $email = $user->email;
                $cookie = $account . '|' . $email;
                break;
            case 'bitrix24':
                $cookie = $user->id . '|' . $data['host'];
                break;
        }

        Cookie::queue('kp10_account', $cookie, 10, null, env('APP_DOMAIN'));

        return response()->json([]);
    }
}
