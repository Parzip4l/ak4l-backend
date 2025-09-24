<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    public function roles()
    {
        $roles = Role::all(['id', 'name', 'guard_name', 'created_at']);
        return response()->json($roles);
    }

    public function permissions()
    {
        $permissions = Permission::all(['id', 'name', 'guard_name', 'created_at']);
        return response()->json($permissions);
    }
}
