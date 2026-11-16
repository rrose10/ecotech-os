<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentReminder extends Model
{
    public const REPEATS = ['none' => 'One time', 'monthly' => 'Every month', 'yearly' => 'Every year'];

    protected $fillable = [
        'title', 'category', 'amount', 'due_date', 'repeat',
        'remind_days', 'is_active', 'last_paid_at', 'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'last_paid_at' => 'date',
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /** Days until the due date (negative when overdue). */
    public function daysLeft(): int
    {
        return (int) today()->diffInDays($this->due_date->copy()->startOfDay(), false);
    }

    /** Overdue, Due soon, Upcoming or Done. */
    public function state(): string
    {
        if (! $this->is_active) {
            return 'Done';
        }

        $left = $this->daysLeft();

        if ($left < 0) {
            return 'Overdue';
        }

        return $left <= $this->remind_days ? 'Due soon' : 'Upcoming';
    }

    /**
     * Mark this payment as paid. Repeating reminders move to the next
     * due date; one-time ones are closed.
     */
    public function markPaid(): void
    {
        $this->last_paid_at = today();

        if ($this->repeat === 'monthly') {
            $this->due_date = $this->nextDate('addMonthNoOverflow');
        } elseif ($this->repeat === 'yearly') {
            $this->due_date = $this->nextDate('addYearNoOverflow');
        } else {
            $this->is_active = false;
        }

        $this->save();
    }

    private function nextDate(string $step)
    {
        $next = $this->due_date->copy()->{$step}();

        // If it was paid very late, skip dates that are already in the past
        while ($next->lt(today())) {
            $next->{$step}();
        }

        return $next;
    }

    /** Reminders that need attention now (overdue or inside their reminder window). */
    public static function needingAttention(int $limit = 8)
    {
        return static::where('is_active', true)
            ->orderBy('due_date')
            ->get()
            ->filter(fn (self $r) => in_array($r->state(), ['Overdue', 'Due soon'], true))
            ->take($limit)
            ->values();
    }
}
