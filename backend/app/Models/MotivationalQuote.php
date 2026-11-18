<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MotivationalQuote extends Model
{
    protected $fillable = [
        'quote',
        'quote_bn',
        'author',
        'is_active',
    ];

    /**
     * The quote shown to one person right now.
     *
     * It changes every hour, and two people never see the same quote in the
     * same hour: each person starts at a different spot in the list
     * (user id x a step that shares no factor with the list size), and
     * everyone moves one quote forward each hour.
     */
    public static function forUser(int $userId, ?\Carbon\CarbonInterface $at = null): ?self
    {
        $quotes = static::where('is_active', true)->orderBy('id')->get();
        $count = $quotes->count();

        if ($count === 0) {
            return null;
        }

        $hour = intdiv(($at ?? now())->getTimestamp(), 3600);

        $step = intdiv($count, 7) + 1;
        while (self::gcd($step, $count) !== 1) {
            $step++;
        }

        return $quotes[($hour + $userId * $step) % $count];
    }

    private static function gcd(int $a, int $b): int
    {
        return $b === 0 ? $a : self::gcd($b, $a % $b);
    }
}
