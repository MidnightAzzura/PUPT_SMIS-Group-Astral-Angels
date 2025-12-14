<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // ...existing permissions...
        
        $permissions = [
            'add user',
            'edit user', 
            'delete user',
            'Add-Supply',
            'Edit-Supply',
            'Delete-Supply',
            'view-pending',
            'view-approved',
            'view-released',
            'view-cancelled',
            'Edit-Ris',
            'Deny',
            'Delete-Selected', // Add this permission
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
