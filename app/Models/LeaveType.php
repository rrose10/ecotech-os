<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    protected $fillable = [
        'name',
        'days_per_year',
        'is_paid',
        'status',
    ];


    protected $casts = [
        'is_paid' => 'boolean',
        'status' => 'boolean',
    ];



    /**
     * Leave Type has many Leaves
     */
    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }



    /**
     * Leave Type has many Leave Balances
     */
    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }
}