<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asset extends Model
{
    public const STATUSES = ['Available', 'Assigned', 'Under Repair', 'Retired'];

    protected $fillable = [
        'asset_code', 'name', 'category', 'purchase_date',
        'purchase_price', 'status', 'employee_id', 'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_price' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
