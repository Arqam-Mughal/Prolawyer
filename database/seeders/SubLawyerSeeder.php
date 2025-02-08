<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubLawyerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Role::create( [
            'name' => 'Sub lawyer',
            'guard_name' => 'web',
            'price' => 0,
            'quarterly_price' => 0,
            'yearly_price' => 0,
            'type' => 'sub_lawyer',
            'no_cases' => 0,
            'status' => 1,
            'details' => 'Sub lawyer role',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
