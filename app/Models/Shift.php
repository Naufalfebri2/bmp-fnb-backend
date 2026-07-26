<?php

namespace App\Models;

use App\Models\Section;
use App\Models\ShiftSchedule;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'section_id',
        'shift_name',
        'start_time',
        'end_time',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function shiftSchedules(): HasMany
    {
        return $this->hasMany(ShiftSchedule::class);
    }
}
