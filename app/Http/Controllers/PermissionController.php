<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use \Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Log;

class PermissionController extends Controller
{
    public function listJson()
    {
        $permissions = Permission::getPermissions();
        if ($permissions->isNotEmpty()) {
            //Show only user permission
            $permissions = $permissions->filter(function($permission){
                if(strpos($permission->name, 'admin') === false){
                    return true;
                }
                return false;
            });
            return response()->json($permissions);
        }

        return response()->json(['errors' => __('messages.permission.empty')], 422);
    }

    /**
     * Update permissions
     *
     * @param string $account
     * @param Request $request
     * @param int $id
     * @return json
     */
    public function update($account, Request $request, $id)
    {
        $request->validate([
            '*.name' => 'required|string'
        ]);

        try {
            $role        = Role::findById($id);
            $permissions = array_column($request->all(), 'name');

            $role->syncPermissions($permissions);
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        return response()->json(['message' => __('messages.permission.update.success')]);
    }
}
