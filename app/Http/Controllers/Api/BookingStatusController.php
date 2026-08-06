<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\TableBooking;
use App\Services\BookingStatusService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class BookingStatusController extends Controller
{
    public function advance(Request $request, string $outletId, string $bookingId)
    {
        $booking = $this->findOwnedBooking($request, $outletId, $bookingId);

        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        try {
            $booking = BookingStatusService::advance($booking);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Booking status advanced successfully',
            'booking' => $booking,
        ]);
    }

    public function cancel(Request $request, string $outletId, string $bookingId)
    {
        $booking = $this->findOwnedBooking($request, $outletId, $bookingId);

        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        try {
            $booking = BookingStatusService::cancel($booking);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Booking cancelled successfully',
            'booking' => $booking,
        ]);
    }

    public function markNoShow(Request $request, string $outletId, string $bookingId)
    {
        $booking = $this->findOwnedBooking($request, $outletId, $bookingId);

        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        try {
            $booking = BookingStatusService::markNoShow($booking);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Booking marked as no-show successfully',
            'booking' => $booking,
        ]);
    }

    private function findOwnedBooking(Request $request, string $outletId, string $bookingId): ?TableBooking
    {
        $outlet = $this->findOwnedOutlet($request, $outletId);

        if (!$outlet) {
            return null;
        }

        return TableBooking::where('outlet_id', $outlet->id)->find($bookingId);
    }

    private function findOwnedOutlet(Request $request, string $outletId): ?Outlet
    {
        return Outlet::where('tenant_id', $request->user()->tenant_id)->find($outletId);
    }
}
