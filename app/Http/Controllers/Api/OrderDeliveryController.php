<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesTenantOrder;
use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Services\OrderCourierService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use InvalidArgumentException;

class OrderDeliveryController extends Controller
{
    use ResolvesTenantOrder;

    public function store(Request $request, string $outletId)
    {
        $outlet = $this->findOwnedOutlet($request, $outletId);

        if (!$outlet) {
            return response()->json(['message' => 'Outlet not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'source_platform' => 'required|in:grab,gojek,shopeefood,direct_call,other',
            'platform_order_id' => 'nullable|string|max:50',
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

        $menus = Menu::where('outlet_id', $outlet->id)
            ->whereIn('id', collect($request->items)->pluck('menu_id'))
            ->get()
            ->keyBy('id');

        foreach ($request->items as $item) {
            if (!$menus->has($item['menu_id'])) {
                return response()->json([
                    'message' => 'One or more menus do not belong to this outlet',
                ], 422);
            }

            if (!$menus[$item['menu_id']]->is_active) {
                return response()->json([
                    'message' => "Menu '{$menus[$item['menu_id']]->name}' is currently inactive and cannot be ordered",
                ], 422);
            }
        }

        $order = DB::transaction(function () use ($request, $outlet, $menus) {
            $staffId = $request->user()->id;

            $order = $outlet->orders()->create([
                'table_id' => null,
                'order_number' => 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
                'customer_name' => null,
                'customer_phone' => null,
                'order_type' => 'online_delivery',
                'status' => 'open',
                'opened_by' => $staffId,
                'acknowledged_at' => now(),
                'acknowledged_by' => $staffId,
                'source_platform' => $request->source_platform,
                'platform_order_id' => $request->platform_order_id,
                'input_method' => 'manual',
                'courier_status' => 'pending',
            ]);

            foreach ($request->items as $item) {
                $menu = $menus[$item['menu_id']];

                $order->items()->create([
                    'menu_id' => $menu->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $menu->price,
                ]);
            }

            return $order;
        });

        return response()->json([
            'message' => 'Delivery order recorded successfully',
            'order' => $order->load(['items.menu']),
        ], 201);
    }

    public function updateCourierStatus(Request $request, string $outletId, string $orderId)
    {
        $order = $this->findOwnedOrder($request, $outletId, $orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'courier_status' => 'required|in:prepared,picked_up_by_courier',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $order = OrderCourierService::transition($order, $request->courier_status);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Courier status updated successfully',
            'order' => $order->load(['items.menu']),
        ]);
    }
}
