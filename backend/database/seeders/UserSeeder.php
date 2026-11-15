<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::truncate();

        // Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@ecotech.com',
            'password' => Hash::make('12345678'),
            'role' => 'super_admin',
            'email_verified_at' => now(),
        ]);

        // Super Admin 2
        User::create([
            'name' => 'Rejwanul Islam',
            'email' => 'rejwanul@ecotech.com',
            'password' => Hash::make('12345678'),
            'role' => 'super_admin',
            'email_verified_at' => now(),
        ]);

        // Super Admin 3
        User::create([
            'name' => 'Office Admin',
            'email' => 'manager@ecotech.com',
            'password' => Hash::make('12345678'),
            'role' => 'super_admin',
            'email_verified_at' => now(),
        ]);

        // Employee 1
        User::create([
            'name' => 'Employee 1',
            'email' => 'employee1@ecotech.com',
            'password' => Hash::make('12345678'),
            'role' => 'employee',
            'email_verified_at' => now(),
        ]);

        // Employee 2
        User::create([
            'name' => 'Employee 2',
            'email' => 'employee2@ecotech.com',
            'password' => Hash::make('12345678'),
            'role' => 'employee',
            'email_verified_at' => now(),
        ]);

        // Employee 3
        User::create([
            'name' => 'Employee 3',
            'email' => 'employee3@ecotech.com',
            'password' => Hash::make('12345678'),
            'role' => 'employee',
            'email_verified_at' => now(),
        ]);

        // Employee 4
        User::create([
            'name' => 'Employee 4',
            'email' => 'employee4@ecotech.com',
            'password' => Hash::make('12345678'),
            'role' => 'employee',
            'email_verified_at' => now(),
        ]);

        // Employee 5
        User::create([
            'name' => 'Employee 5',
            'email' => 'employee5@ecotech.com',
            'password' => Hash::make('12345678'),
            'role' => 'employee',
            'email_verified_at' => now(),
        ]);
    }
}