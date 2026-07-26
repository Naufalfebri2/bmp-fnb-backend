<?php

namespace App\Models;

use App\Models\Outlet;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TableBooking extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'outlet_id',
        'customer_name',
        'phone',
        'guest_count',
        'booking_datetime',
        'status',
        'is_event',
        'notes',
    ];

    protected $casts = [
        'booking_datetime' => 'datetime',
        'is_event' => 'boolean',
    ];

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }
}