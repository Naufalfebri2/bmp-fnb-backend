<?php

namespace App\Console\Commands;

use App\Models\ShiftSchedule;
use Illuminate\Console\Command;

class MarkAbsentEmployees extends Command
{
    protected $signature = 'attendance:mark-absent';

    protected $description = 'Automatically mark employees as absent if 8 hours have passed since their scheduled shift start with no attendance recorded';

    public function handle(): int
    {
        $schedules = ShiftSchedule::whereDoesntHave('attendance')
            ->with('shift')
            ->where('date', '<=', now()->toDateString())
            ->get()
            ->filter(function ($schedule) {
                $scheduledStart = \Carbon\Carbon::parse($schedule->date->toDateString() . ' ' . $schedule->shift->start_time);

                return now()->greaterThanOrEqualTo($scheduledStart->addHours(8));
            });

        $count = 0;

        foreach ($schedules as $schedule) {
            $schedule->attendance()->create([
                'employee_id' => $schedule->employee_id,
                'date' => $schedule->date,
                'status' => 'absent',
                'late_minutes' => 0,
            ]);

            $count++;
        }

        $this->info("Marked {$count} employee(s) as absent.");

        return self::SUCCESS;
    }
}
