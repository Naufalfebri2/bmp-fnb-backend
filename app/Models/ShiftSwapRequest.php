<?php

namespace App\Models;

use App\Models\ShiftSchedule;
use App\Models\User;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftSwapRequest extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'requester_schedule_id',
        'target_schedule_id',
        'status',
        'approved_by',
    ];

    public function requesterSchedule(): BelongsTo
    {
        return $this->belongsTo(ShiftSchedule::class, 'requester_schedule_id');
    }

    public function targetSchedule(): BelongsTo
    {
        return $this->belongsTo(ShiftSchedule::class, 'target_schedule_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
