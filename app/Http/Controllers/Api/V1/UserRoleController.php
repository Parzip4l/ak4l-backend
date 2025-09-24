<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{
    public function assignRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        $user = User::findOrFail($id);
        $user->assignRole($request->role);

        return response()->json([
            'message' => "Role '{$request->role}' assigned to user {$user->name}",
            'roles'   => $user->getRoleNames(),
        ]);
    }

    public function revokeRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        $user = User::findOrFail($id);
        $user->removeRole($request->role);

        return response()->json([
            'message' => "Role '{$request->role}' revoked from user {$user->name}",
            'roles'   => $user->getRoleNames(),
        ]);
    }

    public function assignPermission(Request $request, User $user)
    {
        $request->validate([
            'permission' => 'required|string|exists:permissions,name',
        ]);

        $user->givePermissionTo($request->permission);

        return response()->json([
            'message' => "Permission {$request->permission} assigned to {$user->name}",
            'user'    => $user->load('roles', 'permissions'),
        ]);
    }

    public function removePermission(Request $request, User $user)
    {
        $request->validate([
            'permission' => 'required|string|exists:permissions,name',
        ]);

        $user->revokePermissionTo($request->permission);

        return response()->json([
            'message' => "Permission {$request->permission} removed from {$user->name}",
            'user'    => $user->load('roles', 'permissions'),
        ]);
    }
}
