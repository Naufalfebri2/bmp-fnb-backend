<?php

namespace App\Models;

use App\Models\Table;
use App\Models\TableBooking;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingTableAssignment extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'booking_id',
        'table_id',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(TableBooking::class, 'booking_id');
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }
}
