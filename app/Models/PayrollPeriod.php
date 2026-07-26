<?php

namespace App\Models;

use App\Models\Employee;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollPeriod extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'employee_id',
        'month',
        'base_salary',
        'total_late_deduction',
        'total_absence_deduction',
        'net_salary',
        'status',
    ];

    protected $casts = [
        'month' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
