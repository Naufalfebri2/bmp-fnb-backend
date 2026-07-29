<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyStock;
use App\Models\Ingredient;
use App\Services\DailyStockCalculationService;
use App\Services\MenuAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StockAdjustmentController extends Controller
{
    public function index(Request $request, string $ingredientId)
    {
        $ingredient = $this->findOwnedIngredient($request, $ingredientId);

        if (!$ingredient) {
            return response()->json(['message' => 'Ingredient not found'], 404);
        }

        return response()->json(
            $ingredient->stockAdjustments()->orderBy('date', 'desc')->get()
        );
    }

    public function store(Request $request, string $ingredientId)
    {
        $ingredient = $this->findOwnedIngredient($request, $ingredientId);

        if (!$ingredient) {
            return response()->json(['message' => 'Ingredient not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'adjustment_quantity' => 'required|numeric',
            'reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $dailyStock = DailyStock::where('ingredient_id', $ingredient->id)
            ->where('date', $request->date)
            ->first();

        if (!$dailyStock) {
            return response()->json([
                'message' => 'Daily stock closing for this date has not been recorded yet',
                'errors' => [
                    'date' => [
                        'Stock adjustment requires a daily stock record for this date. Record the daily closing first.',
                    ],
                ],
            ], 422);
        }

        $adjustment = $ingredient->stockAdjustments()->create([
            'date' => $request->date,
            'adjustment_quantity' => $request->adjustment_quantity,
            'reason' => $request->reason,
            'adjusted_by' => $request->user()->id,
        ]);

        DailyStockCalculationService::recalculate($dailyStock);

        MenuAvailabilityService::sync($ingredient);

        return response()->json([
            'message' => 'Stock adjustment recorded successfully',
            'stock_adjustment' => $adjustment,
            'daily_stock' => $dailyStock->fresh(),
        ], 201);
    }

    private function findOwnedIngredient(Request $request, string $ingredientId): ?Ingredient
    {
        return Ingredient::whereHas('section.outlet', function ($query) use ($request) {
            $query->where('tenant_id', $request->user()->tenant_id);
        })->find($ingredientId);
    }
}
