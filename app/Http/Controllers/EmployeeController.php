<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\Page;
use App\Models\User;
use App\Models\Offer;
use App\Models\UserPhone;
use App\Models\Integration;
use App\Models\UserPosition;
use App\Models\UserSignature;
use App\Models\Client;
use App\Models\ClientResponsible;
use App\Models\UserAvatar;
use App\Mail\UserRegistered;
use App\Http\Traits\EmployeeTrait;
use App\Models\IntegrationAmocrmUser;
use App\Models\IntegrationMegaplanUser;
use App\Models\IntegrationBitrix24User;
use App\Http\Traits\MegaplanTrait;
use App\Http\Traits\AmocrmTrait;
use App\Http\Traits\Bitrix24Trait;

class EmployeeController extends Controller
{
    use EmployeeTrait, MegaplanTrait, AmocrmTrait, Bitrix24Trait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($account)
    {
        $user = Auth::user();
        $page = Page::whereSlug('settings-employee')->first();
        //Get employess for account
        $employees = User::with('positionRelation', 'avatarRelation', 'signatureRelation', 'phoneRelation')
                ->whereDomain($account)
                ->withTrashed()
                ->orderBy('deleted_at')
                ->orderBy('id')
                ->get();

        $adminId = $employees->first()->id;

        $integration = Integration::first();

        return view('pages.settings.employees', ['user' => $user, 'page' => $page, 'employees' => $employees, 'integration' => $integration, 'adminId' => $adminId]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = Auth::user();
        $page = Page::whereSlug('settings-employee-create')->first();

        return view('pages.settings.employee-create', ['user' => $user, 'page' => $page]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($account, Request $request)
    {
        //Using trait for this
        $user = $this->addEmployee($account, $request);

        return response()->json($user);
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
    public function edit($account, $id)
    {
        $user     = Auth::user();
        $page     = Page::whereSlug('settings-employee-edit')->first();
        $employee = User::whereDomain($account)
            ->withTrashed()->whereId($id)->first();

        return view('pages.settings.employee-edit', [
            'user'     => $user,
            'page'     => $page,
            'id'       => $id,
            'employee' => $employee
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($account, Request $request, $id)
    {
        //Validate input data
        $request->validate([
            'id'               => 'bail|required|numeric|exists:users',
            'surname'          => 'required|max:255',
            'name'             => 'required|max:255',
            'middleName'       => 'nullable|string',
            'email'            => 'required|email|unique:users,email,' . $id . ',id,domain,' . $account, //validate unique by email, domain columns',
            'phone'            => 'nullable|phone',
            'position'         => 'nullable|string',
            'signature'        => 'nullable|string',
            'fileId'           => 'nullable|numeric'
        ]);

        //Parse phone number
        $phone = preg_replace('/[^0-9]/', '', $request->input('phone'));
        //Get employee
        $employee = User::whereDomain($account)->whereId($id)->first();
        //Error if employee not found
        if (!$employee) {
            return response()->json(['errors' => __('messages.employee.not_found')], 422);
        }

        $employee->surname     = $request->input('surname');
        $employee->name        = $request->input('name');
        $employee->middle_name = $request->input('middleName');
        $employee->email       = $request->input('email');
        //Update relations
        $employee->phoneRelation()->update(['phone' => $phone]);
        $employee->positionRelation()->update(['position' => $request->input('position')]);
        $employee->signatureRelation()->update(['signature' => $request->input('signature')]);
        $employee->avatarRelation()->update(['file_id' => $request->input('fileId')]);

        try {
            //Store
            $employee->save();
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        return response()->json(['message' => __('messages.employee.update_success')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($account, $id, $employeeForReplacement)
    {
        //Get employee
        $employee = User::whereDomain($account)->whereId($id)->first();

        //Error if employee not found
        if (!$employee) {
            return response()->json(['errors' => __('messages.employee.not_found')], 422);
        }
        //If user is admin, show error
        if ($employee && $employee->hasRole('user')) {
            return response()->json(['errors' => __('messages.employee.delete_role_user_error')], 422);
        }

        try {
            DB::transaction(function () use ($employee, $id, $employeeForReplacement) {
                $user = Auth::user();

                $employee->forceDelete();

                //Check amoCrm integration
                if ($amoUser = IntegrationAmocrmUser::whereUserId($employee->id)->first()) {
                    $amoUser->delete();
                }
                //Check megaplan integration
                if ($megaplanUser = IntegrationMegaplanUser::whereUserId($employee->id)->first()) {
                    $megaplanUser->delete();
                }
                //Check bitrix24 integration
                if ($Bitrix24User = IntegrationBitrix24User::whereUserId($employee->id)->first()) {
                    $Bitrix24User->delete();
                }
                // --- update client responsible and offrer ---
                // update client responsible
                $clients = Client::with('responsibleRelation')
                ->WhereHas('responsibleRelation', function ($query) use ($id) {
                    $query->where('user_id', '=', $id);
                })
                ->orWhere('user_id', '=', $id)
                ->get();

                $clients->each(function ($item, $key) use ($id, $employeeForReplacement) {
                    $item->user_id = $employeeForReplacement;
                    $item->save();
                    $item->responsibleRelation->each(function ($item, $key) use ($id, $employeeForReplacement) {
                        if (intval($id) == intval($item->user_id)) {
                            $item->user_id = $employeeForReplacement;
                            $item->save();
                        }
                    });
                });

                // update offrers
                $offrers = Offer::whereUserId($id)->get();
                $offrers->each(function ($item, $key) use ($employeeForReplacement) {
                    $item->user_id = $employeeForReplacement;
                    $item->save();
                });
            });
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        return response()->json(['message' => __('messages.employee.delete_success')]);
    }

    /**
     * Get employee card in json
     *
     * @param stinr $account
     * @param int $id
     * @return json
     */
    public function json($account, $id)
    {
        //Get employee
        $employee = User::with('phoneRelation', 'positionRelation', 'signatureRelation', 'avatarRelation.file', 'roles', 'smtpEmails')
            ->whereDomain($account)
            ->withTrashed()
            ->whereId($id)->first();

        if ($employee) {
            $employee->phone          = $employee->phone;
            $employee->position       = $employee->position;
            $employee->signature      = $employee->signature;
            $employee->avatarUrl      = $employee->avatarUrl;
            $employee->avatarFileId   = $employee->avatarRelation->file_id;
            $employee->trashed        = $employee->trashed();

            $employee->roles = $employee->roles->map(function ($role) {
                return $role['name'];
            });

            return response()->json($employee);
        }

        return response()->json(['errors' => __('messages.employee.not_found')], 422);
    }

    /**
     * Get employee list in json
     *
     * @param string $account
     * @return json
     */
    public function listJson($account)
    {
        //Get employees
        $employees = User::whereDomain($account)
            ->with('positionRelation', 'avatarRelation.file', 'signatureRelation', 'roles', 'megaplan', 'responsibleRelation')
            ->get();
        //Get first registered user
        $adminId = $employees->first()->id;

        if ($employees->isNotEmpty()) {
            //Fix for relations
            $employees->each(function ($employee) use ($adminId) {
                $employee->displayName = $employee->displayName;
                $employee->avatarUrl = $employee->avatarUrl;
                //Add admin marker
                if ($employee->id == $adminId) {
                    $employee->isAdmin = true;
                }
            });

            return response()->json($employees);
        }

        return response()->json(['errors' => __('messages.employee.list.empty')], 422);
    }

    /**
     * Block user. Soft delete
     *
     * @param string $account
     * @param int $id
     * @return json
     */
    public function block($account, $id)
    {
        //Get employee
        $employee = User::role('employee')->whereDomain($account)->whereId($id)->first();
        //If user not found
        if (!$employee) {
            return response()->json(['errors' => __('messages.employee.not_found')], 422);
        }

        try {
            $employee->delete();
            //Clear user online status
            if (Auth::check()) {
                Cache::forget('user-is-online-' . $id);
            }
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        return response()->json(['message' => __('messages.employee.block.success')]);
    }

    /**
     * Restore deleted employee
     *
     * @param string $account
     * @param int $id
     * @return void
     */
    public function unBlock($account, $id)
    {
        //Get employee
        $employee = User::role('employee')->whereDomain($account)
                ->withTrashed()->whereId($id)->first();
        //If user not found
        if (!$employee) {
            return response()->json(['errors' => __('messages.employee.not_found')], 422);
        }

        try {
            $employee->restore();
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        return response()->json(['message' => __('messages.employee.unBlock.success')]);
    }

    /**
     * Change user password
     *
     * @param string $account
     * @param int $id
     * @param Request $request
     * @return json
     */
    public function changePassword($account, $id, Request $request)
    {
        //Get employee
        $employee = User::role('employee')->whereDomain($account)->whereId($id)->first();
        //If user not found
        if (!$employee) {
            return response()->json(['errors' => __('messages.employee.not_found')], 422);
        }
        //If user can edit passwords
        // if (!$employee->can('employee edit')) {
        //     return response()->json(['errors' => __('messages.permission.denied')], 422);
        // }

        $request->validate([
            'password'              => 'required|same:password',
            'passwordConfirm'       => 'required|same:password',
        ]);
        //Set new password
        $employee->password = Hash::make($request->input('password'));

        try {
            $employee->save();
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        return response()->json(['message' => __('messages.employee.update_success')]);
    }

    public function importFromCRM(Request $request)
    {
        $input = $request->input();
        $data  = $input['employee'];
        $type  = $input['type'];

        $user      = Auth::user();
        $accountId = $user->accountId;
        $account   = $user->domain;

        //Add Megaplan user
        if ($type == 'megaplan') {
            $res = [];
            foreach ($data as $key => $value) {
                foreach ($value as $k => $v) {
                    if ($k == 'email') {
                        $res[$key]['contactInfo'][0]['type']  = 'email';
                        $res[$key]['contactInfo'][0]['value'] = $v;
                    } elseif ($k == 'phone') {
                        $res[$key]['contactInfo'][1]['type']  = 'phone';
                        $res[$key]['contactInfo'][1]['value'] = $v;
                    } else {
                        $res[$key][$k] = $v;
                    }
                }
            }
            //make object
            // $data = json_decode(json_encode($res), FALSE);
            $data = array_to_object($res);

            //Add Megaplan user
            if ($data) {
                foreach ($data as $key => $value) {
                    $employee = $this->addMegaplanUser($account, $accountId, $value);
                }
            }
        }

        //Add AmoCrm user
        if ($type == 'amocrm') {
            if ($data) {
                foreach ($data as $val) {
                    $val      = array_to_object($val);
                    $employee = $this->addAmocrmUser($user, $val);
                }
            }
        }

        //Add Bitrix24 user
        if ($type == 'bitrix24') {
            if ($data) {
                foreach ($data as $key => $value) {
                    $this->addBitrix24User($user, $value);
                }
            }
        }

        return response()->json([], 200);
    }

    /**
     * Отримуємо список кліентів і комерційних пропозицій співробітника 
     *
     * @param [int] $id (id співробітника)
     * @return void
     */
    public function getClientsOffersList($account, $id)
    {
        $user      = Auth::user();
        // $accountId = $user->accountId;
        // $account   = $user->domain;

        $offersCnt = Offer::whereUserId($id)->count();
        // $clients  = ClientResponsible::whereUserId($id)->get();
        $clients = Client::WhereHas('responsibleRelation', function ($query) use ($id) {
            $query->where('user_id', '=', $id);
        })->get();

        //Get employees
        $employees = User::whereDomain($account)
            ->with('positionRelation', 'avatarRelation.file', 'signatureRelation', 'roles', 'megaplan', 'responsibleRelation')
            ->get();

        if ($employees->isNotEmpty()) {
            //Fix for relations
            $employees->each(function ($employee) {
                $employee->displayName = $employee->displayName;
                $employee->avatarUrl = $employee->avatarUrl;
            });
        }

        $res = [
            'offers'   => $offersCnt,
            'clients'  => $clients,
            'userList' => $employees
        ];

        $time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];

        // Log::debug('getClientsOffersList виконувався ' . $time . 'секунд');

        return response()->json($res);
    }
}
    // make object from array
    function array_to_object($array)
    {
        $obj = new \stdClass;
        foreach ($array as $k => $v) {
            if (strlen($k)) {
                if (is_array($v)) {
                    $obj->{$k} = array_to_object($v); //RECURSION
                } else {
                    $obj->{$k} = $v;
                }
            }
        }
        return $obj;
    }
