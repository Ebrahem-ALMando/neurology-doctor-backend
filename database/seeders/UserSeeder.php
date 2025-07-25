<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
           User::create([
            'name' => 'Admin',
            'email' => 'admin@neurology.test',
            'password' => 'password', 
            'role' => 'admin',
            'phone' => '0100000000',
            'gender' => 'male',
            'birthdate' => '1990-01-01',
            'is_active' => true,
        ]);
    }
}
