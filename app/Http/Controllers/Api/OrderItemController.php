<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesTenantOrder;
use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderItemController extends Controller
{
    use ResolvesTenantOrder;

    public function addItems(Request $request, string $outletId, string $orderId)
    {
        $order = $this->findOwnedOrder($request, $outletId, $orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($order->status !== 'open') {
            return response()->json([
                'message' => 'Cannot add items to an order that is not open',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|uuid|exists:menus,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $menus = Menu::where('outlet_id', $order->outlet_id)
            ->whereIn('id', collect($request->items)->pluck('menu_id'))
            ->get()
            ->keyBy('id');

        foreach ($request->items as $item) {
            if (!$menus->has($item['menu_id']) || !$menus[$item['menu_id']]->is_active) {
                return response()->json([
                    'message' => 'One or more menus are invalid or inactive',
                ], 422);
            }
        }

        foreach ($request->items as $item) {
            $menu = $menus[$item['menu_id']];

            $order->items()->create([
                'menu_id' => $menu->id,
                'quantity' => $item['quantity'],
                'unit_price' => $menu->price,
            ]);
        }

        return response()->json([
            'message' => 'Items added successfully',
            'order' => $order->fresh()->load(['items.menu']),
        ], 201);
    }

    public function assignSplitLabel(Request $request, string $outletId, string $orderId, string $orderItemId)
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
            'split_label' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $orderItem->update(['split_label' => $request->split_label]);

        return response()->json([
            'message' => 'Split label updated successfully',
            'order_item' => $orderItem,
        ]);
    }
}
