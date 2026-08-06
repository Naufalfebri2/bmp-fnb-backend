<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PublicPickupOrderController extends Controller
{
    public function showMenu(Request $request, string $outletId)
    {
        $outlet = Outlet::find($outletId);

        if (!$outlet) {
            return response()->json(['message' => 'Outlet not found'], 404);
        }

        if (!$outlet->online_pickup_enabled) {
            return response()->json([
                'message' => 'Online pickup ordering is not enabled for this outlet',
            ], 403);
        }

        $menus = Menu::where('outlet_id', $outlet->id)
            ->where('is_active', true)
            ->get(['id', 'name', 'price']);

        return response()->json([
            'outlet' => [
                'id' => $outlet->id,
                'name' => $outlet->name,
            ],
            'menus' => $menus,
        ]);
    }

    public function store(Request $request, string $outletId)
    {
        $outlet = Outlet::find($outletId);

        if (!$outlet) {
            return response()->json(['message' => 'Outlet not found'], 404);
        }

        if (!$outlet->online_pickup_enabled) {
            return response()->json([
                'message' => 'Online pickup ordering is not enabled for this outlet',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:20',
            'requested_pickup_time' => 'nullable|date|after:now',
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
            $order = $outlet->orders()->create([
                'table_id' => null,
                'order_number' => 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'order_type' => 'online_pickup',
                'status' => 'open',
                'opened_by' => null,
                'acknowledged_at' => null,
                'acknowledged_by' => null,
                'requested_pickup_time' => $request->requested_pickup_time,
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
            'message' => 'Pickup order submitted successfully',
            'order_id' => $order->id,
            'order' => $order->load(['items.menu']),
        ], 201);
    }
}
