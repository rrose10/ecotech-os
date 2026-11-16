<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ListUsers extends Command
{
    protected $signature = 'ecotech:users {--remove= : ID of a login account to delete}';

    protected $description = 'List login accounts (no passwords)';

    public function handle(): int
    {
        if ($id = $this->option('remove')) {
            return $this->remove((int) $id);
        }

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

    private function remove(int $id): int
    {
        $user = User::find($id);

        if (!$user) {
            $this->error("No account with ID {$id}.");

            return self::FAILURE;
        }

        if ($user->role === 'super_admin' && User::where('role', 'super_admin')->count() <= 1) {
            $this->error('Refusing to delete the only Super Admin.');

            return self::FAILURE;
        }

        if ($user->employee()->exists()) {
            $this->error('This account belongs to an employee record; delete the employee in the app instead.');

            return self::FAILURE;
        }

        $user->delete();
        $this->info("Deleted account #{$id} ({$user->email}).");

        return self::SUCCESS;
    }
}
