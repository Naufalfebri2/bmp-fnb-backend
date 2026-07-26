<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Employee;


class Attendance extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'attendance';

    protected $fillable = [
        'employee_id',
        'date',
        'check_in_time',
        'check_out_time',
        'check_in_photo',
        'check_out_photo',
        'location_lat',
        'location_long',
        'late_minutes',
        'status',
        'supporting_document',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
