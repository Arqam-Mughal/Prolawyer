<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Backpack\PermissionManager\app\Models\Role;
use Backpack\PermissionManager\app\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SubLawyerPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create([
            'name' => 'Add sub lawyer',
            'guard_name' => 'web',
            'module_id' => 1,
            'parent_id' => NULL,
            'route' => '',
            'status' => 1,
            'created_by' => 8,
            'updated_by' => 8,
            'type' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Role::where('name', 'Premium')->first()->givePermissionTo('Add sub lawyer');
    }
}
