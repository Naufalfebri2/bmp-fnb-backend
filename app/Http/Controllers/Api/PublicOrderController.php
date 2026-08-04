<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PublicOrderController extends Controller
{
    public function showMenu(Request $request, string $qrCode)
    {
        $table = Table::where('qr_code', $qrCode)->with('section.outlet')->first();

        if (!$table) {
            return response()->json(['message' => 'Table not found'], 404);
        }

        $outlet = $table->section->outlet;

        if (!$outlet->qr_ordering_enabled) {
            return response()->json([
                'message' => 'QR ordering is not enabled for this outlet',
            ], 403);
        }

        $menus = Menu::where('outlet_id', $outlet->id)
            ->where('is_active', true)
            ->get(['id', 'name', 'price']);

        return response()->json([
            'table' => [
                'id' => $table->id,
                'table_number' => $table->table_number,
            ],
            'outlet' => [
                'id' => $outlet->id,
                'name' => $outlet->name,
            ],
            'menus' => $menus,
        ]);
    }

    public function store(Request $request, string $qrCode)
    {
        $table = Table::where('qr_code', $qrCode)->with('section.outlet')->first();

        if (!$table) {
            return response()->json(['message' => 'Table not found'], 404);
        }

        $outlet = $table->section->outlet;

        if (!$outlet->qr_ordering_enabled) {
            return response()->json([
                'message' => 'QR ordering is not enabled for this outlet',
            ], 403);
        }

        $existingOpenOrder = Order::where('table_id', $table->id)
            ->where('status', 'open')
            ->first();

        $validationRules = [
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|uuid|exists:menus,id',
            'items.*.quantity' => 'required|integer|min:1',
        ];

        if (!$existingOpenOrder) {
            $validationRules['customer_name'] = 'required|string|max:100';
        }

        $validator = Validator::make($request->all(), $validationRules);

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

        $order = DB::transaction(function () use ($request, $outlet, $table, $menus, $existingOpenOrder) {
            if ($existingOpenOrder) {
                $order = $existingOpenOrder;
            } else {
                $order = Order::create([
                    'outlet_id' => $outlet->id,
                    'table_id' => $table->id,
                    'order_number' => 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
                    'customer_name' => $request->customer_name,
                    'order_type' => 'qr_dine_in',
                    'status' => 'open',
                    'opened_by' => null,
                    'acknowledged_at' => null,
                    'acknowledged_by' => null,
                ]);
            }

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
            'message' => 'Order submitted successfully',
            'order_id' => $order->id,
            'order' => $order->load(['items.menu']),
        ], 201);
    }

    public function showStatus(Request $request, string $orderId)
    {
        $order = Order::where('id', $orderId)
            ->whereIn('order_type', ['qr_dine_in', 'online_pickup', 'online_delivery'])
            ->with(['items.menu', 'table'])
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $activeItems = $order->items->where('refund_status', 'none');

        $total = $activeItems->sum(function ($item) {
            return $item->unit_price * $item->quantity;
        });

        return response()->json([
            'order_number' => $order->order_number,
            'table_number' => $order->table?->table_number,
            'customer_name' => $order->customer_name,
            'status' => $order->status,
            'items' => $activeItems->values(),
            'total' => $total,
        ]);
    }
}
