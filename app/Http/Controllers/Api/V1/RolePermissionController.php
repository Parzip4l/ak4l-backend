<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    // ======================
    // ROLE CRUD
    // ======================
    public function roles()
    {
        return response()->json(Role::all(['id', 'name', 'guard_name', 'created_at']));
    }

    public function storeRole(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255|unique:roles,name',
                'guard_name' => 'nullable|string|max:255'
            ]);
            $role = Role::create([
                'name' => $data['name'],
                'guard_name' => $data['guard_name'] ?? 'web',
            ]);
            return response()->json($role, 201);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateRole(Request $request, $id)
    {   
        try {
            $role = Role::findOrFail($id);
            $data = $request->validate([
                'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
                'guard_name' => 'nullable|string|max:255'
            ]);
            $role->update($data);
            return response()->json($role);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroyRole($id)
    {
        try {
            Role::findOrFail($id)->delete();
            return response()->json(['message' => 'Role deleted']);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ======================
    // PERMISSION CRUD
    // ======================
    public function permissions()
    {
        return response()->json(Permission::all(['id', 'name', 'guard_name', 'created_at']));
    }

    public function storePermission(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255|unique:permissions,name',
                'guard_name' => 'nullable|string|max:255'
            ]);
            $permission = Permission::create([
                'name' => $data['name'],
                'guard_name' => $data['guard_name'] ?? 'web',
            ]);
            return response()->json($permission, 201);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updatePermission(Request $request, $id)
    {
        try {
            $permission = Permission::findOrFail($id);
            $data = $request->validate([
                'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
                'guard_name' => 'nullable|string|max:255'
            ]);
            $permission->update($data);
            return response()->json($permission);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroyPermission($id)
    {
        try {
            Permission::findOrFail($id)->delete();
            return response()->json(['message' => 'Permission deleted']);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ======================
    // ASSIGN & REVOKE
    // ======================
    public function assignPermissionToRole(Request $request, $roleId)
    {
        try {
            $data = $request->validate([
                'permissions' => 'required|array',
                'permissions.*' => 'string|exists:permissions,name',
            ]);

            $role = Role::findOrFail($roleId);
            $role->syncPermissions($data['permissions']); // replace old with new

            return response()->json([
                'message' => 'Permissions assigned to role',
                'role' => $role->load('permissions')
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function revokePermissionFromRole(Request $request, $roleId)
    {
        try {
            $data = $request->validate([
                'permissions' => 'required|array',
                'permissions.*' => 'string|exists:permissions,name',
            ]);

            $role = Role::findOrFail($roleId);
            foreach ($data['permissions'] as $perm) {
                $role->revokePermissionTo($perm);
            }

            return response()->json([
                'message' => 'Permissions revoked from role',
                'role' => $role->load('permissions')
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
