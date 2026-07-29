<?php

namespace App\Models;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\ShiftSwapRequest;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ShiftSchedule extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'employee_id',
        'shift_id',
        'date',
        'swap_status',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function attendance(): HasOne
    {
        return $this->hasOne(Attendance::class);
    }

    public function swapRequestsAsRequester(): HasMany
    {
        return $this->hasMany(ShiftSwapRequest::class, 'requester_schedule_id');
    }

    public function swapRequestsAsTarget(): HasMany
    {
        return $this->hasMany(ShiftSwapRequest::class, 'target_schedule_id');
    }
}
