<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\PortalNotification;
use App\Support\Telegram;
use Illuminate\Console\Command;

class RemindDinner extends Command
{
    protected $signature = 'attendance:remind-dinner';

    protected $description = 'Remind team members who are checked in and have not started their dinner break, 5 minutes before dinner time (10:00 PM)';

    public function handle(): int
    {
        $shiftDate = Attendance::shiftDateFor(now())->toDateString();
        $sent = 0;
        $users = collect();

        Attendance::with('employee.user')
            ->whereDate('attendance_date', $shiftDate)
            ->whereNotNull('check_in')
            ->whereNull('break_start')
            ->whereNull('check_out')
            ->each(function (Attendance $a) use (&$sent, &$users) {
                $user = optional($a->employee)->user;

                if (!$user) {
                    return;
                }

                PortalNotification::create([
                    'user_id' => $user->id,
                    'title'   => 'Dinner break starts in 5 minutes. Please press Start break.',
                    'url'     => route('portal.attendance', [], false),
                ]);

                $users->push($user);

                $sent++;
            });

        Telegram::remind($users, "🍽️ ৫ মিনিট পরে ডিনার ব্রেক। এখনো ব্রেক শুরু করেননি:\n🍽️ Dinner break in 5 minutes. Break not started yet:");

        $this->info("{$sent} reminder(s) sent.");

        return 0;
    }
}
