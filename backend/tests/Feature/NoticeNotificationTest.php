<?php

namespace Tests\Feature;

use App\Models\Notice;
use App\Models\PortalNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoticeNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_notice_notifies_each_team_member_once(): void
    {
        $a = User::factory()->create(['role' => 'employee']);
        $b = User::factory()->create(['role' => 'employee']);
        $admin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($admin)->post('/notices', [
            'title' => 'Eid holiday', 'content' => 'Office closed', 'type' => 'Holiday',
            'publish_date' => today()->toDateString(), 'is_published' => 1,
        ])->assertRedirect(route('notices.index'));

        $this->assertSame(1, PortalNotification::where('user_id', $a->id)->count());
        $this->assertSame(1, PortalNotification::where('user_id', $b->id)->count());
        $this->assertSame(0, PortalNotification::where('user_id', $admin->id)->count());

        // Editing the notice must not notify again.
        $notice = Notice::firstOrFail();
        $this->put("/notices/{$notice->id}", [
            'title' => 'Eid holiday (updated)', 'content' => 'Office closed', 'type' => 'Holiday',
            'publish_date' => today()->toDateString(), 'is_published' => 1,
        ]);
        $this->assertSame(1, PortalNotification::where('user_id', $a->id)->count());

        $this->actingAs($a)->get('/portal/notifications')->assertOk()->assertSee('Eid holiday');
    }

    public function test_draft_and_future_notices_wait_until_visible(): void
    {
        $a = User::factory()->create(['role' => 'employee']);
        $admin = User::factory()->create(['role' => 'super_admin']);

        Notice::create([
            'title' => 'Draft', 'content' => 'x', 'type' => 'General', 'is_published' => false,
            'publish_date' => today(), 'created_by' => $admin->id,
        ]);
        $future = Notice::create([
            'title' => 'Next week', 'content' => 'x', 'type' => 'General', 'is_published' => true,
            'publish_date' => today()->addWeek(), 'created_by' => $admin->id,
        ]);

        $this->artisan('notices:announce')->assertExitCode(0);
        $this->assertSame(0, PortalNotification::count());

        $this->travelTo(today()->addWeek()->setTime(8, 5));
        $this->artisan('notices:announce')->assertExitCode(0);
        $this->assertSame(1, PortalNotification::where('notice_id', $future->id)->count());

        $this->artisan('notices:announce');
        $this->assertSame(1, PortalNotification::count());
    }
}
