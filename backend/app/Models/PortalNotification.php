<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortalNotification extends Model
{
    protected $fillable = ['user_id', 'title', 'body', 'url', 'read_at'];

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
}
