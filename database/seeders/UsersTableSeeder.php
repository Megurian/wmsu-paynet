<?php

namespace Database\Seeders; 

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        // User::updateOrCreate(
        //     ['email' => 'college.admin@gmail.com'],
        //     [
        //         'name' => 'College Admin',
        //         'role' => 'college',
        //         'password' => Hash::make('password123')
        //     ]
        // );

        User::updateOrCreate(
            ['email' => 'osa.admin@gmail.com'],
            [
                'name' => 'OSA Admin',
                'role' => 'osa',
                'password' => Hash::make('password123')
            ]
        );

        // User::updateOrCreate(
        //     ['email' => 'usc.admin@gmail.com'],
        //     [
        //         'name' => 'USC Admin',
        //         'role' => 'usc',
        //         'password' => Hash::make('password123')
        //     ]
        // );
    }
}
