<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashTransaction;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClosingSummaryController extends Controller
{
    public function section(Request $request, string $sectionId)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $section = Section::whereHas('outlet', function ($query) use ($request) {
            $query->where('tenant_id', $request->user()->tenant_id);
        })->with('outlet')->find($sectionId);

        if (!$section) {
            return response()->json(['message' => 'Section not found'], 404);
        }

        $date = $request->date;

        $ingredients = $section->ingredients()->with(['dailyStocks' => function ($query) use ($date) {
            $query->where('date', $date);
        }])->get();

        $stockSummary = $ingredients->map(function ($ingredient) {
            $dailyStock = $ingredient->dailyStocks->first();

            return [
                'ingredient_id' => $ingredient->id,
                'ingredient_name' => $ingredient->name,
                'unit' => $ingredient->unit,
                'has_daily_stock' => $dailyStock !== null,
                'opening_stock' => $dailyStock->opening_stock ?? null,
                'stock_in' => $dailyStock->stock_in ?? null,
                'adjustment_quantity' => $dailyStock->adjustment_quantity ?? null,
                'expected_closing_stock' => $dailyStock->expected_closing_stock ?? null,
                'actual_closing_stock' => $dailyStock->actual_closing_stock ?? null,
                'variance' => $dailyStock->variance ?? null,
                'is_closed' => $dailyStock !== null && $dailyStock->actual_closing_stock !== null,
            ];
        });

        $isSectionFullyClosed = $stockSummary->every(fn ($item) => $item['is_closed']);

        $cashTransactions = CashTransaction::whereHas('cashAccount', function ($query) use ($section) {
            $query->where('outlet_id', $section->outlet_id);
        })->where('date', $date)->get();

        $totalCashIn = $cashTransactions->where('type', 'in')->sum('amount');
        $totalCashOut = $cashTransactions->where('type', 'out')->sum('amount');

        return response()->json([
            'section_id' => $section->id,
            'section_name' => $section->name,
            'date' => $date,
            'is_fully_closed' => $isSectionFullyClosed,
            'stock_summary' => $stockSummary,
            'cash_summary' => [
                'total_cash_in' => $totalCashIn,
                'total_cash_out' => $totalCashOut,
                'net_cash_flow' => $totalCashIn - $totalCashOut,
            ],
        ]);
    }
}
