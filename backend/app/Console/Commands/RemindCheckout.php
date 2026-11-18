<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\PortalNotification;
use App\Support\Telegram;
use Illuminate\Console\Command;

class RemindCheckout extends Command
{
    protected $signature = 'attendance:remind-checkout';

    protected $description = 'Remind team members who are still checked in a few minutes before the shift ends';

    public function handle(): int
    {
        $shiftDate = Attendance::shiftDateFor(now())->toDateString();
        $sent = 0;
        $users = collect();

        Attendance::with('employee.user')
            ->whereDate('attendance_date', $shiftDate)
            ->whereNotNull('check_in')
            ->whereNull('check_out')
            ->each(function (Attendance $a) use (&$sent, &$users) {
                $user = optional($a->employee)->user;

                if (!$user) {
                    return;
                }

                PortalNotification::create([
                    'user_id' => $user->id,
                    'title'   => 'Your shift ends in 5 minutes. Please check out.',
                    'url'     => route('portal.attendance', [], false),
                ]);

                $users->push($user);

                $sent++;
            });

        Telegram::remind($users, "⏰ শিফট শেষ হতে ৫ মিনিট বাকি। এখনো চেক-আউট করেননি:\n⏰ Shift ends in 5 minutes. Not checked out yet:");

        $this->info("{$sent} reminder(s) sent.");

        return 0;
    }
}
