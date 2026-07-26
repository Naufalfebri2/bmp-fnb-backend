<?php

namespace App\Models;

use App\Models\DailyStock;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOutflow extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'daily_stock_id',
        'category',
        'quantity',
    ];

    public function dailyStock(): BelongsTo
    {
        return $this->belongsTo(DailyStock::class);
    }
}
