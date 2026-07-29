<?php

namespace App\Models;

use App\Models\Ingredient;
use App\Models\User;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'ingredient_id',
        'date',
        'adjustment_quantity',
        'reason',
        'adjusted_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function adjustedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }
}
