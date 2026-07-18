<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Income extends Model
{
    protected $fillable = [
        'income_category_id',
        'bank_account_id',
        'title',
        'amount_bdt',
        'amount_usd',
        'income_date',
        'payment_method',
        'remarks',
        'created_by',
    ];

    /**
     * Income Category
     */
    public function incomeCategory(): BelongsTo
    {
        return $this->belongsTo(IncomeCategory::class);
    }

    /**
     * Bank Account
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    /**
     * Created By
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Search Scope
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, function ($query) use ($search) {
            $query->where('title', 'like', "%{$search}%");
        });
    }
}