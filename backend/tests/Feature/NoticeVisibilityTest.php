<?php

namespace Tests\Feature;

use App\Models\Notice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoticeVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private function notice(array $overrides = []): Notice
    {
        return Notice::create(array_merge([
            'title' => 'Test notice',
            'content' => 'Body',
            'type' => 'General',
            'is_published' => true,
            'publish_date' => today(),
            'expiry_date' => null,
            'created_by' => User::factory()->create(['role' => 'admin'])->id,
        ], $overrides));
    }

    public function test_notice_expiring_today_is_still_visible_to_employees(): void
    {
        $notice = $this->notice(['expiry_date' => today()]);
        $this->actingAs(User::factory()->create(['role' => 'employee']));

        $this->get(route('portal.notices'))->assertOk()->assertSee('Test notice');
        $this->get(route('portal.notices.show', $notice))->assertOk();
    }

    public function test_hidden_notices_are_not_visible_to_employees(): void
    {
        $draft = $this->notice(['is_published' => false]);
        $expired = $this->notice(['expiry_date' => today()->subDay()]);
        $future = $this->notice(['publish_date' => today()->addDay()]);
        $this->actingAs(User::factory()->create(['role' => 'employee']));

        foreach ([$draft, $expired, $future] as $n) {
            $this->get(route('portal.notices.show', $n))->assertNotFound();
        }
    }

    public function test_employee_cannot_manage_notices(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'employee']));

        $this->post('/notices', [])->assertForbidden();
    }
}
