<?php

namespace App\Services;

use App\Models\DailyStock;
use App\Models\PurchaseOrderItem;
use App\Models\StockAdjustment;

class DailyStockCalculationService
{
    /**
     * Recalculate stock_in, adjustment_quantity, and expected_closing_stock
     * for a given daily stock record. Both stock_in and adjustment_quantity
     * are derived fresh from their source tables (purchase_order_items via
     * received purchase orders, and stock_adjustments) rather than
     * incremented directly, to avoid double-counting on repeated calls.
     * If the daily stock has already been closed (actual_closing_stock is
     * set), variance is recalculated too so it stays consistent.
     */
    public static function recalculate(DailyStock $dailyStock): void
    {
        $totalOutflow = $dailyStock->stockOutflows()->sum('quantity');

        $totalAdjustment = StockAdjustment::where('ingredient_id', $dailyStock->ingredient_id)
            ->whereDate('date', $dailyStock->date)
            ->sum('adjustment_quantity');

        $totalStockIn = PurchaseOrderItem::where('ingredient_id', $dailyStock->ingredient_id)
            ->whereHas('purchaseOrder', function ($query) use ($dailyStock) {
                $query->where('status', 'received')
                    ->whereDate('received_at', $dailyStock->date);
            })
            ->sum('quantity');

        $expectedClosingStock = $dailyStock->opening_stock
            + $totalStockIn
            + $totalAdjustment
            - $totalOutflow;

        $data = [
            'stock_in' => $totalStockIn,
            'adjustment_quantity' => $totalAdjustment,
            'expected_closing_stock' => $expectedClosingStock,
        ];

        if ($dailyStock->actual_closing_stock !== null) {
            $data['variance'] = $dailyStock->actual_closing_stock - $expectedClosingStock;
        }

        $dailyStock->update($data);
    }
}
