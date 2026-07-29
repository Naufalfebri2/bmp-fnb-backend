<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Outlet;
use App\Services\MenuAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    public function index(Request $request, string $outletId)
    {
        $outlet = $this->findOwnedOutlet($request, $outletId);

        if (!$outlet) {
            return response()->json(['message' => 'Outlet not found'], 404);
        }

        return response()->json($outlet->menus);
    }

    public function store(Request $request, string $outletId)
    {
        $outlet = $this->findOwnedOutlet($request, $outletId);

        if (!$outlet) {
            return response()->json(['message' => 'Outlet not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'main_ingredient_id' => 'nullable|exists:ingredients,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $menu = Menu::create([
            'outlet_id' => $outlet->id,
            'main_ingredient_id' => $request->main_ingredient_id,
            'name' => $request->name,
            'price' => $request->price,
            'is_active' => true,
        ]);

        if ($menu->mainIngredient) {
            MenuAvailabilityService::sync($menu->mainIngredient);
            $menu->refresh();
        }

        return response()->json([
            'message' => 'Menu created successfully',
            'menu' => $menu,
        ], 201);
    }

    private function findOwnedOutlet(Request $request, string $outletId): ?Outlet
    {
        return Outlet::where('tenant_id', $request->user()->tenant_id)->find($outletId);
    }
}
