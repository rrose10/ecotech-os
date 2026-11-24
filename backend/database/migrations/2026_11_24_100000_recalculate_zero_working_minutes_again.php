<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Portal check-outs saved 0 working minutes (the check-in time was read
     * as "today", so it looked like the future). Recalculate those records.
     */
    public function up(): void
    {
        $rows = DB::table('attendances')
            ->whereNotNull('check_in')
            ->whereNotNull('check_out')
            ->where('working_minutes', 0)
            ->get(['id', 'check_in', 'check_out', 'break_minutes']);

        foreach ($rows as $row) {
            $diff = $this->seconds($row->check_out) - $this->seconds($row->check_in);

            if ($diff <= 0) {
                $diff += 86400;
            }

            DB::table('attendances')->where('id', $row->id)->update([
                'working_minutes' => max(0, (int) round($diff / 60) - (int) $row->break_minutes),
            ]);
        }
    }

    public function down(): void
    {
        // Data repair only.
    }

    private function seconds(string $value): int
    {
        $time = strlen($value) > 8 ? substr($value, 11, 8) : $value;
        [$h, $m, $s] = array_pad(array_map('intval', explode(':', $time)), 3, 0);

        return $h * 3600 + $m * 60 + $s;
    }
};
