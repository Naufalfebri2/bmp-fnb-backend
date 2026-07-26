<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Ingredient;
use App\Models\StockOutflow;

class DailyStock extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'ingredient_id',
        'date',
        'opening_stock',
        'expected_closing_stock',
        'actual_closing_stock',
        'variance',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function stockOutflows(): HasMany
    {
        return $this->hasMany(StockOutflow::class);
    }
}
