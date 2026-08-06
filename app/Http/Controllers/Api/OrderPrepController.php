<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesTenantOrder;
use App\Http\Controllers\Controller;
use App\Services\OrderItemPrepService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class OrderPrepController extends Controller
{
    use ResolvesTenantOrder;

    public function updatePrepStatus(Request $request, string $outletId, string $orderId, string $orderItemId)
    {
        $order = $this->findOwnedOrder($request, $outletId, $orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $orderItem = $order->items()->find($orderItemId);

        if (!$orderItem) {
            return response()->json(['message' => 'Order item not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'prep_status' => 'required|in:preparing,prepared',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $orderItem = OrderItemPrepService::transition($orderItem, $request->prep_status);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Prep status updated successfully',
            'order_item' => $orderItem,
        ]);
    }
}
