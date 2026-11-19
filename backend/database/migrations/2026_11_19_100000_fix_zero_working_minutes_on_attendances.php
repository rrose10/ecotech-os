<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Night-shift records that were saved with 0 working minutes because the
     * check-out time (e.g. 06:00 AM) is earlier than the check-in (7:00 PM).
     * Recalculate them: next-morning check-out, minus the dinner break.
     */
    public function up(): void
    {
        $rows = DB::table('attendances')
            ->whereNotNull('check_in')
            ->whereNotNull('check_out')
            ->where('working_minutes', 0)
            ->get(['id', 'check_in', 'check_out', 'break_minutes']);

        foreach ($rows as $row) {
            $in  = $this->seconds($row->check_in);
            $out = $this->seconds($row->check_out);

            $diff = $out - $in;

            if ($diff <= 0) {
                $diff += 86400;
            }

            $minutes = max(0, (int) round($diff / 60) - (int) $row->break_minutes);

            DB::table('attendances')->where('id', $row->id)->update(['working_minutes' => $minutes]);
        }
    }

    public function down(): void
    {
        // Data repair only.
    }

    /** Seconds since midnight from a TIME or DATETIME value. */
    private function seconds(string $value): int
    {
        $time = strlen($value) > 8 ? substr($value, 11, 8) : $value;
        [$h, $m, $s] = array_pad(array_map('intval', explode(':', $time)), 3, 0);

        return $h * 3600 + $m * 60 + $s;
    }
};
