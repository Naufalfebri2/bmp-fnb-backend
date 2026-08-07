<?php

namespace App\Models;

use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\User;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashReconciliation extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'cash_account_id',
        'date',
        'system_balance',
        'physical_balance',
        'difference',
        'status',
        'reconciled_by',
        'approved_by',
        'adjustment_transaction_id',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function cashAccount(): BelongsTo
    {
        return $this->belongsTo(CashAccount::class);
    }

    public function reconciledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function adjustmentTransaction(): BelongsTo
    {
        return $this->belongsTo(CashTransaction::class, 'adjustment_transaction_id');
    }
}