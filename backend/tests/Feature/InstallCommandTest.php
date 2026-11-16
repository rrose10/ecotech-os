<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstallCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_install_creates_three_super_admins_once(): void
    {
        $this->artisan('ecotech:install')->assertSuccessful();

        $this->assertSame(3, User::where('role', 'super_admin')->count());

        $rose = User::where('email', 'rose@ecotech.com')->first();
        $hash = $rose->password;

        // Running again must not create duplicates or change passwords
        $this->artisan('ecotech:install')->assertSuccessful();

        $this->assertSame(3, User::count());
        $this->assertSame($hash, $rose->fresh()->password);
    }

    public function test_install_never_adds_admins_when_one_already_exists(): void
    {
        // e.g. an owner changed their email after the first install
        User::factory()->create(['role' => 'super_admin', 'email' => 'rose.new@example.com']);

        $this->artisan('ecotech:install')->assertSuccessful();

        $this->assertSame(1, User::where('role', 'super_admin')->count());
        $this->assertDatabaseMissing('users', ['email' => 'shad@ecotech.com']);
    }
}
