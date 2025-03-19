<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view_dashboard','Assign_Roles',
            'view_products', 'create_products', 'edit_products', 'delete_products',
            'view_categories', 'create_categories', 'edit_categories', 'delete_categories',
            'view_users', 'create_users', 'edit_users', 'delete_users',
            'view_orders', 'modify_orders',
            'add_products_to_cart', 'update_quantities_in_cart', 'remove_items_from_cart', 'view_order',
            'place_orders'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission , 'guard_name' => 'sanctum']);
        }

        $roles = [
            'super_admin' => $permissions,
            'product_manager' => ['view_products', 'create_products', 'edit_products', 'delete_products'],
            'user_manager' => ['view_users', 'create_users', 'edit_users', 'delete_users'],
            'manager' => [ 'view_orders', 'modify_orders'],
            'client' => ['view_products', 'view_categories','add_products_to_cart', 'update_quantities_in_cart', 'remove_items_from_cart', 'view_order','place_orders'],
            'guest' => ['view_products', 'view_categories','add_products_to_cart', 'update_quantities_in_cart', 'remove_items_from_cart', 'view_order']
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName , 'guard_name' => 'sanctum']);
            $role->syncPermissions($rolePermissions);
        }

    }
}
