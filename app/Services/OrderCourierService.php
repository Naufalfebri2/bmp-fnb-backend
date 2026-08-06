<?php

namespace App\Services;

use App\Models\Order;
use InvalidArgumentException;

class OrderCourierService
{
    private const ALLOWED_TRANSITIONS = [
        'pending' => 'prepared',
        'prepared' => 'picked_up_by_courier',
    ];

    public static function transition(Order $order, string $targetStatus): Order
    {
        if ($order->order_type !== 'online_delivery') {
            throw new InvalidArgumentException('Courier status only applies to online_delivery orders.');
        }

        $currentStatus = $order->courier_status;

        if (!array_key_exists($currentStatus, self::ALLOWED_TRANSITIONS)) {
            throw new InvalidArgumentException("Order is already in a final courier state ('{$currentStatus}') and cannot be updated further.");
        }

        $expectedNextStatus = self::ALLOWED_TRANSITIONS[$currentStatus];

        if ($targetStatus !== $expectedNextStatus) {
            throw new InvalidArgumentException("Invalid transition: cannot move from '{$currentStatus}' to '{$targetStatus}'. Expected next status is '{$expectedNextStatus}'.");
        }

        if ($targetStatus === 'prepared') {
            $unpreparedItemsCount = $order->items()
                ->where('refund_status', 'none')
                ->where('prep_status', '!=', 'prepared')
                ->count();

            if ($unpreparedItemsCount > 0) {
                throw new InvalidArgumentException("Cannot mark order as prepared: {$unpreparedItemsCount} item(s) are still not in 'prepared' status.");
            }
        }

        $updateData = ['courier_status' => $targetStatus];

        if ($targetStatus === 'picked_up_by_courier') {
            $updateData['courier_picked_up_at'] = now();
        }

        $order->update($updateData);

        return $order->fresh();
    }
}
