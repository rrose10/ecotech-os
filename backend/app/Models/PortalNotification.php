<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortalNotification extends Model
{
    protected $fillable = ['user_id', 'notice_id', 'title', 'body', 'url', 'read_at'];

    protected $casts = ['read_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Tell the team member that their salary was paid.
     * Does nothing when the person has no login account.
     */
    public static function salaryPaid(Payroll $payroll): ?self
    {
        $payroll->loadMissing('employee');
        $userId = optional($payroll->employee)->user_id;

        if (!$userId) {
            return null;
        }

        $month = date('F', mktime(0, 0, 0, (int) $payroll->month, 1));

        return static::create([
            'user_id' => $userId,
            'title'   => "Your salary for {$month} {$payroll->year} has been paid",
            'body'    => 'Net salary: ৳' . number_format((float) $payroll->net_salary, 2),
            'url'     => route('portal.payslip', $payroll, false),
        ]);
    }

    /**
     * A notice that team members can see right now (published, started,
     * not expired).
     */
    public static function noticeIsVisible(Notice $notice): bool
    {
        return $notice->is_published
            && $notice->publish_date->lte(today())
            && (!$notice->expiry_date || $notice->expiry_date->gte(today()));
    }

    /**
     * Tell every team member about a notice, once each. Safe to call again
     * (edits, daily schedule): people who already got it are skipped.
     * Returns how many new notifications were created.
     */
    public static function announceNotice(Notice $notice): int
    {
        if (!static::noticeIsVisible($notice)) {
            return 0;
        }

        $already = static::where('notice_id', $notice->id)->pluck('user_id');
        $created = 0;

        User::where('role', 'employee')->whereNotIn('id', $already)->each(
            function (User $user) use ($notice, &$created) {
                static::create([
                    'user_id'   => $user->id,
                    'notice_id' => $notice->id,
                    'title'     => ($notice->type === 'Important' ? 'Important notice: ' : 'New notice: ') . $notice->title,
                    'url'       => route('portal.notices.show', $notice, false),
                ]);
                $created++;
            }
        );

        return $created;
    }

    /** Announce every notice that is visible today and not yet announced. */
    public static function announceVisibleNotices(): int
    {
        $total = 0;

        Notice::where('is_published', true)->whereDate('publish_date', '<=', today())
            ->where(fn ($q) => $q->whereNull('expiry_date')->orWhereDate('expiry_date', '>=', today()))
            ->each(function (Notice $notice) use (&$total) {
                $total += static::announceNotice($notice);
            });

        return $total;
    }
}
