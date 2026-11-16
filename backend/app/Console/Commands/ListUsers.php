<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ListUsers extends Command
{
    protected $signature = 'ecotech:users';

    protected $description = 'List login accounts (no passwords)';

    public function handle(): int
    {
        $this->table(
            ['ID', 'Name', 'Email', 'Role', 'Must change password', 'Created'],
            User::orderBy('id')->get()->map(fn ($u) => [
                $u->id, $u->name, $u->email, $u->role,
                $u->must_change_password ? 'yes' : 'no',
                optional($u->created_at)->format('Y-m-d H:i'),
            ])->all()
        );

        return self::SUCCESS;
    }
}
