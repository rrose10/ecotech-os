<?php

namespace Tests\Feature;

use App\Models\MotivationalQuote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MotivationQuoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_adds_500_new_quotes_without_duplicates(): void
    {
        $this->assertGreaterThanOrEqual(500, MotivationalQuote::count());
        $this->assertSame(
            MotivationalQuote::count(),
            MotivationalQuote::pluck('quote')->map(fn ($q) => mb_strtolower($q))->unique()->count()
        );
    }

    public function test_everyone_gets_a_different_quote_and_it_changes_every_hour(): void
    {
        $at = Carbon::parse('2026-11-19 10:15:00');
        $ids = range(1, 40);

        $now = collect($ids)->map(fn ($id) => MotivationalQuote::forUser($id, $at)->id);
        $this->assertSame(40, $now->unique()->count(), 'two people saw the same quote in the same hour');

        // Same hour (later minute): unchanged.
        foreach ([1, 7, 23] as $id) {
            $this->assertSame(
                MotivationalQuote::forUser($id, $at)->id,
                MotivationalQuote::forUser($id, $at->copy()->addMinutes(40))->id
            );
        }

        // Next hour: everyone has a new quote.
        foreach ($ids as $id) {
            $this->assertNotSame(
                MotivationalQuote::forUser($id, $at)->id,
                MotivationalQuote::forUser($id, $at->copy()->addHour())->id
            );
        }
    }

    public function test_dashboard_shows_the_users_own_quote(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $quote = MotivationalQuote::forUser($user->id);

        $this->actingAs($user)->get('/portal/dashboard')->assertOk()->assertSee($quote->quote);
    }
}
