<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use Illuminate\Database\Seeder;

class BankAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BankAccount::create([
            'account_name' => 'Cash in Hand',
            'type' => 'Cash',
            'bank_name' => null,
            'account_number' => null,
            'opening_balance' => 0,
            'current_balance' => 0,
            'is_active' => true,
            'notes' => 'Default office cash account',
        ]);

        BankAccount::create([
            'account_name' => 'Main Bank Account',
            'type' => 'Bank',
            'bank_name' => 'Demo Bank',
            'account_number' => '000000000000',
            'opening_balance' => 0,
            'current_balance' => 0,
            'is_active' => true,
            'notes' => 'Default company bank account',
        ]);
    }
}