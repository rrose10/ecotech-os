<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoTest extends TestCase
{
    use RefreshDatabase;

    public function test_logo_files_exist_and_pages_reference_them(): void
    {
        foreach (['images/ecotech-logo.png', 'images/ecotech-mark.png', 'images/apple-touch-icon.png', 'favicon.ico'] as $f) {
            $this->assertFileExists(public_path($f));
        }

        $this->get('/login')->assertOk()->assertSee('images/ecotech-logo.png', false)->assertSee('favicon.ico', false);

        $this->actingAs(User::factory()->create(['role' => 'super_admin']))
            ->get('/dashboard')->assertOk()->assertSee('images/ecotech-mark.png', false)->assertSee('favicon.ico', false);
    }

    public function test_pwa_files_exist_and_pages_link_the_manifest(): void
    {
        $this->assertFileExists(public_path('manifest.webmanifest'));
        $this->assertFileExists(public_path('sw.js'));
        $this->assertFileExists(public_path('images/icon-512.png'));

        $manifest = json_decode(file_get_contents(public_path('manifest.webmanifest')), true);
        $this->assertSame('standalone', $manifest['display']);

        $this->get('/login')->assertOk()->assertSee('manifest.webmanifest', false);
    }
}
