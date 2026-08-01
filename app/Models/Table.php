<?php

namespace App\Models;

use App\Models\Order;
use App\Models\Section;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Table extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'tables';

    protected $fillable = [
        'section_id',
        'table_number',
        'qr_code',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
