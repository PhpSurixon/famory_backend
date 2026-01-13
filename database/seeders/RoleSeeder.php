<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'Super Admin'],
            ['name' => 'User'],
            ['name' => 'Advertiser'],
            ['name' => 'Admin'],
        ];

        foreach ($roles as $role) {
            Role::updateOrInsert(
                ['name' => $role['name']],
                [
                    'name' => $role['name'] 
                ]
            );
        }
    }
}
