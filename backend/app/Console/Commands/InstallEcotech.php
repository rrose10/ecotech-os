<?php

namespace App\Console\Commands;

use App\Models\BankAccount;
use App\Models\ExpenseCategory;
use App\Models\IncomeCategory;
use App\Models\MotivationalQuote;
use App\Models\User;
use Database\Seeders\BankAccountSeeder;
use Database\Seeders\ExpenseCategorySeeder;
use Database\Seeders\IncomeCategorySeeder;
use Database\Seeders\MotivationalQuoteSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * First-time production setup. Safe to run again: it only fills what is empty
 * and never touches existing users or passwords.
 */
class InstallEcotech extends Command
{
    protected $signature = 'ecotech:install';

    protected $description = 'Seed default categories/accounts and create the three Super Admins with random passwords';

    private const ADMINS = [
        'rose@ecotech.com'   => 'Rose',
        'shad@ecotech.com'   => 'Shad',
        'tushar@ecotech.com' => 'Tushar',
    ];

    public function handle(): int
    {
        foreach ([
            [IncomeCategory::class, IncomeCategorySeeder::class],
            [ExpenseCategory::class, ExpenseCategorySeeder::class],
            [BankAccount::class, BankAccountSeeder::class],
            [MotivationalQuote::class, MotivationalQuoteSeeder::class],
        ] as [$model, $seeder]) {
            if ($model::query()->doesntExist()) {
                $this->call('db:seed', ['--class' => $seeder, '--force' => true]);
            }
        }

        $created = [];

        foreach (self::ADMINS as $email => $name) {
            if (User::where('email', $email)->exists()) {
                continue;
            }

            $password = Str::password(14, symbols: false);

            $admin = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'super_admin',
                'email_verified_at' => now(),
            ]);
            $admin->forceFill(['must_change_password' => true])->save();

            $created[] = [$name, $email, $password];
        }

        if ($created) {
            $this->newLine();
            $this->warn('Super Admin logins (shown once, save them now and change them after login):');
            $this->table(['Name', 'Email', 'Password'], $created);
        } else {
            $this->info('Super Admins already exist. Nothing to create.');
        }

        return self::SUCCESS;
    }
}
