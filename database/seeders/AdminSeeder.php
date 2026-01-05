<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $array = [
                    'first_name'    => 'Super',
                    'last_name'     => 'Admin',
                    'username'      => 'superadmin',
                    'email'         => 'development@famoryapp.com',
                    'role_id'       =>  1,
                    'password'      => Hash::make('Admin@321'),
                ];
        $check1 = User::where('email','development@famoryapp.com')->where('role_id',1)->first();  
        
        if(!$check1) {
          $create = User::create($array);
           
        }
    }
}
