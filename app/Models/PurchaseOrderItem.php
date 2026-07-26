<?php

namespace App\Models;

use App\Models\Ingredient;
use App\Models\PurchaseOrder;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'purchase_order_id',
        'ingredient_id',
        'quantity',
        'unit_price',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}