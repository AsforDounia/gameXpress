<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserRoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(['roles' => Role::all()], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'role_name' => 'required|string|unique:roles,name',
            'permissions' => 'nullable|array',
            'new_permissions' => 'nullable|array',
            'new_permissions.*' => 'nullable|string|unique:permissions,name',
        ]);


        $role = Role::create(['name' => $request->role_name]);

        if ($request->filled('new_permissions')) {
            foreach ($request->new_permissions as $permissionName) {
                $permission = Permission::create(['name' => $permissionName]);
            }
            $role->syncPermissions($request->new_permissions);
        }

        if ($request->filled('permissions')) {
            $filteredPermissions = collect($request->permissions)->filter(fn($value, $key) => $value == 1)->keys()->toArray();
            $validPermissions = Permission::whereIn('name', $filteredPermissions)->pluck('name')->toArray();
            $role->syncPermissions($validPermissions);
        }


        return response()->json([
            'message' => 'Role created and permissions assigned successfully.',
            'role' => $role,
            'permissions' => $role->permissions,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json(['user' => $user->name, 'roles' => $user->getRoleNames()], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $userId)
    {
        $request->validate([
            'role_name' => 'required|string|exists:roles,name',
        ]);

        $user = User::findOrFail($userId);

        $user->syncRoles([$request->role_name]);

        return response()->json(['message' => 'User role updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::find($id);
        return response()->json(['message' => $role ], 200);
        if(!$role){
            return response()->json(['message' => 'Role not found'], 404);
        }

        if ($role->users()->count() > 0) {
            return response()->json(['error' => 'Role is assigned to one or more users, cannot delete.'], 400);
        }

        $role->delete();
        return response()->json(['message' => 'Role deleted successfully.'], 200);
    }
}
