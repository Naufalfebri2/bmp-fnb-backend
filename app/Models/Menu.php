<?php

namespace App\Models;

use App\Models\Ingredient;
use App\Models\Outlet;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Menu extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'outlet_id',
        'main_ingredient_id',
        'name',
        'price',
        'is_active',
        'custom_fields',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'custom_fields' => 'array',
    ];

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function mainIngredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class, 'main_ingredient_id');
    }
}
