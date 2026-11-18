<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\PortalNotification;
use App\Models\Setting;
use App\Models\User;
use App\Support\Telegram;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckoutReminderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::flush();
    }

    private function member(string $n = '1'): array
    {
        $user = User::factory()->create(['role' => 'employee']);
        $dept = Department::firstOrCreate(['code' => 'DSN'], ['name' => 'Design']);
        $emp = Employee::create([
            'user_id' => $user->id, 'department_id' => $dept->id, 'employee_id' => 'EMP'.$n,
            'name' => 'Person '.$n, 'email' => "p{$n}@example.com", 'designation' => 'Designer',
            'basic_salary' => 20000, 'joining_date' => today()->subYear(), 'gender' => 'Male', 'status' => true,
        ]);

        return [$user, $emp];
    }

    private function checkIn(Employee $emp, bool $out = false): void
    {
        $date = Attendance::shiftDateFor(now());
        Attendance::create([
            'employee_id' => $emp->id, 'attendance_date' => $date->toDateString(),
            'check_in' => $date->copy()->setTime(19, 0), 'check_out' => $out ? $date->copy()->setTime(23, 0)->addDay()->setTime(5, 0) : null,
            'working_minutes' => 0, 'late_minutes' => 0, 'status' => 'Present', 'shift' => 'Night',
        ]);
    }

    public function test_reminder_goes_only_to_people_still_checked_in(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => []])]);
        Setting::put(Telegram::TOKEN, 'TOKEN123');

        [$open, $openEmp] = $this->member('1');
        [$done, $doneEmp] = $this->member('2');
        $open->forceFill(['telegram_chat_id' => '555'])->save();
        $this->checkIn($openEmp);
        $this->checkIn($doneEmp, true);

        $this->artisan('attendance:remind-checkout')->assertExitCode(0);

        $this->assertSame(1, PortalNotification::where('user_id', $open->id)->count());
        $this->assertSame(0, PortalNotification::where('user_id', $done->id)->count());
        Http::assertSent(fn ($r) => str_contains($r->url(), '/sendMessage') && $r['chat_id'] == '555');
        Http::assertSentCount(1);
    }

    public function test_dinner_reminder_skips_people_already_on_break(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => []])]);
        Setting::put(Telegram::TOKEN, 'TOKEN123');

        [$waiting, $waitingEmp] = $this->member('1');
        [$onBreak, $onBreakEmp] = $this->member('2');
        $waiting->forceFill(['telegram_chat_id' => '555'])->save();
        $this->checkIn($waitingEmp);
        $this->checkIn($onBreakEmp);
        Attendance::where('employee_id', $onBreakEmp->id)->update(['break_start' => now()]);

        $this->artisan('attendance:remind-dinner')->assertExitCode(0);

        $this->assertSame(1, PortalNotification::where('user_id', $waiting->id)->count());
        $this->assertSame(0, PortalNotification::where('user_id', $onBreak->id)->count());
        Http::assertSentCount(1);
    }

    public function test_telegram_start_code_connects_the_member(): void
    {
        Setting::put(Telegram::TOKEN, 'TOKEN123');
        [$user] = $this->member();
        $user->forceFill(['telegram_link_code' => 'abc123'])->save();

        Http::fake([
            '*getUpdates*' => Http::response(['ok' => true, 'result' => [[
                'update_id' => 10,
                'message' => ['chat' => ['id' => 777, 'type' => 'private'], 'text' => '/start abc123'],
            ]]]),
            '*sendMessage*' => Http::response(['ok' => true, 'result' => []]),
        ]);

        $this->artisan('telegram:poll')->assertExitCode(0);

        $user->refresh();
        $this->assertSame('777', $user->telegram_chat_id);
        $this->assertNull($user->telegram_link_code);
        $this->assertSame('11', Setting::get(Telegram::OFFSET));
    }

    public function test_settings_rejects_a_bad_token_and_saves_a_good_one(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $base = ['company_name' => 'EcoTech'];

        Http::fake(['*getMe*' => Http::response(['ok' => false], 401)]);
        $this->actingAs($admin)->put('/settings', $base + ['telegram_bot_token' => 'bad'])
            ->assertSessionHasErrors('telegram_bot_token');
        $this->assertNull(Telegram::token());

        Http::fake(['*getMe*' => Http::response(['ok' => true, 'result' => ['username' => 'eco_bot']])]);
        $this->put('/settings', $base + ['telegram_bot_token' => 'good'])->assertSessionHasNoErrors();
        Setting::flush();
        $this->assertSame('good', Telegram::token());
        $this->assertSame('eco_bot', Telegram::username());

        // Leaving the field empty keeps the saved token.
        $this->put('/settings', $base)->assertSessionHasNoErrors();
        Setting::flush();
        $this->assertSame('good', Telegram::token());
    }

    public function test_member_can_create_a_link_and_disconnect(): void
    {
        Setting::put(Telegram::TOKEN, 'T');
        Setting::put(Telegram::USERNAME, 'eco_bot');
        [$user] = $this->member();

        $this->actingAs($user)->post('/portal/telegram/link')->assertRedirect();
        $code = $user->fresh()->telegram_link_code;
        $this->assertNotEmpty($code);
        $this->get('/portal/profile')->assertOk()->assertSee("t.me/eco_bot?start={$code}", false);

        $user->forceFill(['telegram_chat_id' => '1'])->save();
        $this->delete('/portal/telegram')->assertRedirect();
        $this->assertNull($user->fresh()->telegram_chat_id);
    }
}
