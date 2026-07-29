<?php

namespace App\Services;

use App\Models\Ingredient;

class MenuAvailabilityService
{
    public static function sync(Ingredient $ingredient): void
    {
        $currentStock = self::getCurrentStock($ingredient);

        $isLow = $currentStock !== null && $currentStock <= $ingredient->alert_threshold;

        $ingredient->menus()->update(['is_active' => !$isLow]);
    }

    public static function getCurrentStock(Ingredient $ingredient): ?float
    {
        $latest = $ingredient->dailyStocks()->orderBy('date', 'desc')->first();

        if (!$latest) {
            return null;
        }

        return $latest->actual_closing_stock
            ?? $latest->expected_closing_stock
            ?? $latest->opening_stock;
    }
}
