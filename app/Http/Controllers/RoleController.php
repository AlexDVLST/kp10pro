<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Page;
use App\Models\User;
use App\Models\Role;
use Intervention\Image\Response;
use \Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($account)
    {
        $user = Auth::user();
        $page = Page::whereSlug('settings-role')->first();

        return view('pages.settings.roles', [
            'user'      => $user,
            'page'      => $page
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($account, Request $request)
    {
        $request->validate([
            '*.employeeId' => 'required|numeric'
        ]);

        $data      = $request->all();
        $employees = User::whereDomain($account)->pluck('id');
        //Get first registered user
        $adminId = $employees->first();

        if (is_array($data) && !empty($data)) {
            foreach ($data as $value) {
                $employeeId = $value['employeeId'];
                $roles      = $value['roles'];
                //If passed user is employee of the account
                if ($employees->contains($employeeId) && $employeeId != $adminId) {
                    //Get roles
                    if (is_array($roles)) {
                        foreach ($roles as $role) {
                            $roleName = $role['name'];
                            $employee = User::find($employeeId);

                            if ($role['status']) {
                                //TODO: may reduce RoleDoesNotExist
                                $employee->assignRole($roleName);
                            } else {
                                //TODO: may reduce RoleDoesNotExist
                                $employee->removeRole($roleName);
                            }
                        }
                    }
                }
            }
        }

        return response()->json(['message' => __('messages.role.update.success')]);
    }

    /**
     * Get roles list
     *
     * @return json
     */
    public function listJson()
    {
        //Get role except admin,manager
        $roles = Role::with('translationRelation')->get()->whereNotIn('name', ['admin', 'manager']);

        return response()
            ->json($roles);
    }

    /**
     * Show edit role page
     *
     * @param string $account
     * @param int $id
     * @return view
     */
    public function edit($account, $id)
    {
        $user = Auth::user();
        $page = Page::whereSlug('settings-role')->first();

        return view('pages.settings.role-edit', [
            'user' => $user,
            'page' => $page
        ]);
    }

    /**
     * Get role card in json
     *
     * @param string $account
     * @param int $id
     * @return json
     */
    public function json($account, $id)
    {
        $role = Role::findById($id);

        if ($role) {
            $role->permissions;
            $role->translationRelation;

            return response()->json($role);
        }
        return response()->json(['errors' => __('messages.role.not_found')], 422);
    }
}
