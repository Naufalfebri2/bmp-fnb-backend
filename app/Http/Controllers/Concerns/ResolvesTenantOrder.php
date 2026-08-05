<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Order;
use App\Models\Outlet;
use Illuminate\Http\Request;

trait ResolvesTenantOrder
{
    protected function findOwnedOrder(Request $request, string $outletId, string $orderId): ?Order
    {
        $outlet = $this->findOwnedOutlet($request, $outletId);

        if (!$outlet) {
            return null;
        }

        return $outlet->orders()->find($orderId);
    }

    protected function findOwnedOutlet(Request $request, string $outletId): ?Outlet
    {
        return Outlet::where('tenant_id', $request->user()->tenant_id)->find($outletId);
    }
}
