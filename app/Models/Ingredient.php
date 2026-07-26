<?php

namespace App\Models;

use App\Models\DailyStock;
use App\Models\Menu;
use App\Models\PurchaseOrderItem;
use App\Models\Section;
use App\Models\StockAdjustment;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ingredient extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'section_id',
        'name',
        'unit',
        'risk_category',
        'alert_threshold',
        'custom_fields',
    ];

    protected $casts = [
        'custom_fields' => 'array',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function dailyStocks(): HasMany
    {
        return $this->hasMany(DailyStock::class);
    }

    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class, 'main_ingredient_id');
    }
}
