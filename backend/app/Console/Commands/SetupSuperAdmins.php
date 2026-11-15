<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SetupSuperAdmins extends Command
{
    protected $signature = 'users:setup-admins';

    protected $description = 'Make Rose, Shad and Tushar the only Super Admins (safe to run repeatedly)';

    public function handle(): int
    {
        $admins = [
            'rose@ecotech.com'   => 'Rose',
            'shad@ecotech.com'   => 'Shad',
            'tushar@ecotech.com' => 'Tushar',
        ];

        foreach ($admins as $email => $name) {
            $user = User::firstOrNew(['email' => $email]);

            if (! $user->exists) {
                $user->password = Hash::make('12345678');
            }

            $user->name = $name;
            $user->role = 'super_admin';
            $user->email_verified_at ??= now();
            $user->save();
        }

        // Remove the old demo accounts (never the three above)
        foreach (['admin@ecotech.com', 'rejwanul@ecotech.com', 'manager@ecotech.com', 'hr@ecotech.com'] as $old) {
            try {
                User::where('email', $old)->delete();
            } catch (\Throwable $e) {
                $this->warn("Could not delete {$old}: ".$e->getMessage());
            }
        }

        $this->info('Super admins: '.User::where('role', 'super_admin')->pluck('email')->implode(', '));

        return self::SUCCESS;
    }
}
