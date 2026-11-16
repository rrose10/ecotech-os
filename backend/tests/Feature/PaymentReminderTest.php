<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Notice;
use App\Models\PaymentReminder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentReminderTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $role = 'super_admin'): User
    {
        return User::factory()->create(['role' => $role]);
    }

    public function test_admin_can_create_edit_and_delete_a_reminder(): void
    {
        $this->actingAs($this->admin());

        $this->get('/payment-reminders')->assertOk();
        $this->get('/payment-reminders/create')->assertOk();

        $this->post('/payment-reminders', [
            'title' => 'Internet bill',
            'amount' => 1500,
            'due_date' => today()->addDays(2)->toDateString(),
            'repeat' => 'monthly',
            'remind_days' => 3,
            'is_active' => 1,
        ])->assertRedirect('/payment-reminders');

        $reminder = PaymentReminder::firstOrFail();
        $this->assertSame('Internet bill', $reminder->title);

        $this->get("/payment-reminders/{$reminder->id}/edit")->assertOk();
        $this->put("/payment-reminders/{$reminder->id}", [
            'title' => 'Fiber internet',
            'due_date' => today()->addDays(2)->toDateString(),
            'repeat' => 'monthly',
            'remind_days' => 5,
            'is_active' => 1,
        ])->assertRedirect('/payment-reminders');
        $this->assertSame('Fiber internet', $reminder->fresh()->title);

        $this->delete("/payment-reminders/{$reminder->id}")->assertRedirect('/payment-reminders');
        $this->assertDatabaseCount('payment_reminders', 0);
    }

    public function test_state_follows_the_due_date(): void
    {
        $make = fn (int $days) => new PaymentReminder([
            'title' => 'X', 'due_date' => today()->addDays($days), 'repeat' => 'none', 'remind_days' => 3, 'is_active' => true,
        ]);

        $this->assertSame('Overdue', $make(-1)->state());
        $this->assertSame('Due soon', $make(0)->state());
        $this->assertSame('Due soon', $make(3)->state());
        $this->assertSame('Upcoming', $make(4)->state());
    }

    public function test_marking_paid_moves_a_monthly_reminder_forward(): void
    {
        $this->actingAs($this->admin());

        $reminder = PaymentReminder::create([
            'title' => 'Rent', 'due_date' => today()->subDays(2), 'repeat' => 'monthly', 'remind_days' => 3, 'is_active' => true,
        ]);

        $this->post("/payment-reminders/{$reminder->id}/paid")->assertRedirect();

        $fresh = $reminder->fresh();
        $this->assertTrue($fresh->is_active);
        $this->assertTrue($fresh->due_date->gte(today()));
        $this->assertSame(today()->toDateString(), $fresh->last_paid_at->toDateString());
    }

    public function test_marking_paid_closes_a_one_time_reminder(): void
    {
        $this->actingAs($this->admin());

        $reminder = PaymentReminder::create([
            'title' => 'Domain', 'due_date' => today(), 'repeat' => 'none', 'remind_days' => 3, 'is_active' => true,
        ]);

        $this->post("/payment-reminders/{$reminder->id}/paid");

        $this->assertFalse($reminder->fresh()->is_active);
        $this->assertSame('Done', $reminder->fresh()->state());
    }

    public function test_dashboard_lists_payments_that_need_attention(): void
    {
        $this->actingAs($this->admin());

        PaymentReminder::create(['title' => 'Office rent', 'due_date' => today()->subDay(), 'repeat' => 'monthly', 'remind_days' => 3, 'is_active' => true]);
        PaymentReminder::create(['title' => 'Far away bill', 'due_date' => today()->addDays(30), 'repeat' => 'monthly', 'remind_days' => 3, 'is_active' => true]);

        $this->get('/dashboard')->assertOk()->assertSee('Office rent')->assertDontSee('Far away bill');
    }

    public function test_hr_and_team_cannot_open_reminders(): void
    {
        $this->actingAs($this->admin('hr'));
        $this->get('/payment-reminders')->assertRedirect(route('employees.index'));
        $this->post('/payment-reminders', ['title' => 'x'])->assertForbidden();
    }

    public function test_celebration_notices_are_posted_once(): void
    {
        $admin = $this->admin();

        $dept = Department::create(['name' => 'Design', 'code' => 'DSN']);
        Employee::create([
            'department_id' => $dept->id, 'employee_id' => 'EMP9', 'name' => 'Birthday Person',
            'email' => 'bp@example.com', 'designation' => 'Designer', 'basic_salary' => 10000,
            'joining_date' => today()->subYears(3), 'date_of_birth' => today()->subYears(25),
            'gender' => 'Male', 'status' => true,
        ]);

        $this->artisan('notices:celebrations')->assertSuccessful();
        $this->artisan('notices:celebrations')->assertSuccessful();

        $this->assertSame(1, Notice::where('title', 'Happy Birthday, Birthday Person!')->count());
        $this->assertSame($admin->id, Notice::first()->created_by);
    }
}
