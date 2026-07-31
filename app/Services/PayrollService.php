<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use Carbon\Carbon;

class PayrollService
{
    private const LATE_DEDUCTION_PER_BLOCK = 5000;
    private const LATE_BLOCK_MINUTES = 5;
    private const STANDARD_WORKING_DAYS_PER_MONTH = 26;

    /**
     * Generate (or regenerate, if still draft) the payroll period for one
     * employee for a given month. Deductions are recalculated from scratch
     * every time based on that month's attendance records, never accumulated
     * manually — consistent with the recalculate-from-source pattern used
     * throughout Phase 4.
     */
    public static function generateForEmployee(Employee $employee, string $month): PayrollPeriod
    {
        $monthStart = Carbon::parse($month)->startOfMonth();
        $monthEnd = Carbon::parse($month)->endOfMonth();

        $existing = PayrollPeriod::where('employee_id', $employee->id)
            ->where('month', $monthStart->toDateString())
            ->first();

        if ($existing && $existing->status !== 'draft') {
            return $existing;
        }

        $attendanceRecords = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get();

        $totalLateDeduction = 0;

        foreach ($attendanceRecords->where('status', 'late') as $record) {
            $blocks = (int) ceil($record->late_minutes / self::LATE_BLOCK_MINUTES);
            $totalLateDeduction += $blocks * self::LATE_DEDUCTION_PER_BLOCK;
        }

        $absenceCount = $attendanceRecords->whereIn('status', ['sick_without_letter', 'absent'])->count();
        $dailyRate = $employee->base_salary / self::STANDARD_WORKING_DAYS_PER_MONTH;
        $totalAbsenceDeduction = round($absenceCount * $dailyRate, 2);

        $netSalary = $employee->base_salary - $totalLateDeduction - $totalAbsenceDeduction;

        return PayrollPeriod::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'month' => $monthStart->toDateString(),
            ],
            [
                'base_salary' => $employee->base_salary,
                'total_late_deduction' => $totalLateDeduction,
                'total_absence_deduction' => $totalAbsenceDeduction,
                'net_salary' => $netSalary,
                'status' => 'draft',
            ]
        );
    }
}
