<?php

namespace App\Support;

use App\Models\Employee;
use Illuminate\Support\Collection;

class Celebrations
{
    /**
     * Active employees whose birthday or work anniversary is today.
     * Done in PHP so it behaves the same on SQLite and MariaDB.
     *
     * @return array{birthdays: Collection, anniversaries: Collection}
     */
    public static function today(): array
    {
        $today = today();
        $employees = Employee::where('status', true)->get();

        $birthdays = $employees->filter(fn ($e) => $e->date_of_birth
            && $e->date_of_birth->month === $today->month
            && $e->date_of_birth->day === $today->day)->values();

        $anniversaries = $employees->filter(fn ($e) => $e->joining_date
            && $e->joining_date->month === $today->month
            && $e->joining_date->day === $today->day
            && $e->joining_date->year < $today->year)
            ->map(function ($e) use ($today) {
                $e->years_with_us = $today->year - $e->joining_date->year;
                return $e;
            })->values();

        return compact('birthdays', 'anniversaries');
    }
}
