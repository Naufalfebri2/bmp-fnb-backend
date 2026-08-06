<?php

namespace App\Services;

use App\Models\OrderItem;
use InvalidArgumentException;

class OrderItemPrepService
{
    private const ALLOWED_TRANSITIONS = [
        'pending' => 'preparing',
        'preparing' => 'prepared',
    ];

    public static function transition(OrderItem $orderItem, string $targetStatus): OrderItem
    {
        if ($orderItem->refund_status === 'refunded') {
            throw new InvalidArgumentException('Cannot update prep status on a refunded item.');
        }

        $currentStatus = $orderItem->prep_status;

        if (!array_key_exists($currentStatus, self::ALLOWED_TRANSITIONS)) {
            throw new InvalidArgumentException("Item is already in a final state ('{$currentStatus}') and cannot be updated further.");
        }

        $expectedNextStatus = self::ALLOWED_TRANSITIONS[$currentStatus];

        if ($targetStatus !== $expectedNextStatus) {
            throw new InvalidArgumentException("Invalid transition: cannot move from '{$currentStatus}' to '{$targetStatus}'. Expected next status is '{$expectedNextStatus}'.");
        }

        $orderItem->update(['prep_status' => $targetStatus]);

        return $orderItem->fresh();
    }
}
