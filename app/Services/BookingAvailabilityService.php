<?php

namespace App\Services;

use App\Models\TableBooking;
use Carbon\Carbon;

class BookingAvailabilityService
{
    private const BLOCKING_STATUSES = ['pending', 'awaiting_deposit', 'confirmed', 'seated'];

    public static function isTableAvailable(
        string $tableId,
        Carbon $startTime,
        int $durationMinutes,
        ?string $excludeBookingId = null
    ): bool {
        $endTime = $startTime->copy()->addMinutes($durationMinutes);

        $conflictingDirect = TableBooking::where('table_id', $tableId)
            ->whereIn('status', self::BLOCKING_STATUSES)
            ->when($excludeBookingId, fn($q) => $q->where('id', '!=', $excludeBookingId))
            ->get()
            ->contains(function ($booking) use ($startTime, $endTime) {
                return self::rangesOverlap(
                    $startTime,
                    $endTime,
                    $booking->booking_datetime,
                    $booking->booking_datetime->copy()->addMinutes($booking->duration_minutes)
                );
            });

        if ($conflictingDirect) {
            return false;
        }

        $conflictingViaEvent = TableBooking::whereHas('tableAssignments', fn($q) => $q->where('table_id', $tableId))
            ->whereIn('status', self::BLOCKING_STATUSES)
            ->when($excludeBookingId, fn($q) => $q->where('id', '!=', $excludeBookingId))
            ->get()
            ->contains(function ($booking) use ($startTime, $endTime) {
                return self::rangesOverlap(
                    $startTime,
                    $endTime,
                    $booking->booking_datetime,
                    $booking->booking_datetime->copy()->addMinutes($booking->duration_minutes)
                );
            });

        return !$conflictingViaEvent;
    }

    public static function areTablesAvailable(
        array $tableIds,
        Carbon $startTime,
        int $durationMinutes,
        ?string $excludeBookingId = null
    ): array {
        $unavailable = [];

        foreach ($tableIds as $tableId) {
            if (!self::isTableAvailable($tableId, $startTime, $durationMinutes, $excludeBookingId)) {
                $unavailable[] = $tableId;
            }
        }

        return $unavailable;
    }

    private static function rangesOverlap(Carbon $startA, Carbon $endA, Carbon $startB, Carbon $endB): bool
    {
        return $startA->lt($endB) && $startB->lt($endA);
    }
}
