<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Money moved between two of our own accounts (for example bank -> cash in hand).
 * It is not income and not an expense; only the two balances change.
 */
class FundTransfer extends Model
{
    protected $fillable = ['from_account_id', 'to_account_id', 'amount', 'transfer_date', 'remarks', 'created_by'];

    protected $casts = [
        'amount'        => 'decimal:2',
        'transfer_date' => 'date',
    ];

    public function fromAccount()
    {
        return $this->belongsTo(BankAccount::class, 'from_account_id');
    }

    public function toAccount()
    {
        return $this->belongsTo(BankAccount::class, 'to_account_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
