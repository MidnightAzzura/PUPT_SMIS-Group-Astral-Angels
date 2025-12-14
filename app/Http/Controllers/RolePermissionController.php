<?php

namespace App\Http\Controllers;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Users;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    public function updatepermission(Request $request, $roleId)
    {
        // 1) Find the role
        $role = Role::findOrFail($roleId);

        // 2) Grab the submitted permissions (or empty array if none checked)
        $permissions = $request->input('permissions', []);

        // 3) Sync them — this removes any old permissions and adds these new ones
        $role->syncPermissions($permissions);

        // 4) Redirect back with a success flash
        return redirect()
            ->back()
            ->with('success', "Permissions updated for role “{$role->name}”.");
    }
}
