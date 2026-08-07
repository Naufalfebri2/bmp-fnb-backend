<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\Outlet;
use App\Services\CashflowReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class CashflowReportController extends Controller
{
    public function forAccount(Request $request, string $cashAccountId)
    {
        $cashAccount = $this->findOwnedCashAccount($request, $cashAccountId);

        if (!$cashAccount) {
            return response()->json(['message' => 'Cash account not found'], 404);
        }

        [$from, $to, $groupBy, $error] = $this->validateRange($request);

        if ($error) {
            return $error;
        }

        try {
            $report = CashflowReportService::forAccount($cashAccount, $from, $to, $groupBy);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($report);
    }

    public function forOutlet(Request $request, string $outletId)
    {
        $outlet = Outlet::where('tenant_id', $request->user()->tenant_id)->find($outletId);

        if (!$outlet) {
            return response()->json(['message' => 'Outlet not found'], 404);
        }

        [$from, $to, $groupBy, $error] = $this->validateRange($request);

        if ($error) {
            return $error;
        }

        try {
            $report = CashflowReportService::forOutlet($outlet, $from, $to, $groupBy);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($report);
    }

    public function forTenant(Request $request)
    {
        [$from, $to, $groupBy, $error] = $this->validateRange($request);

        if ($error) {
            return $error;
        }

        try {
            $report = CashflowReportService::forTenant($request->user()->tenant_id, $from, $to, $groupBy);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($report);
    }

    private function findOwnedCashAccount(Request $request, string $cashAccountId): ?CashAccount
    {
        $cashAccount = CashAccount::whereHas('outlet', function ($query) use ($request) {
            $query->where('tenant_id', $request->user()->tenant_id);
        })->find($cashAccountId);

        if (!$cashAccount) {
            return null;
        }

        $userOutletId = $request->user()->outlet_id;

        if ($userOutletId && $userOutletId !== $cashAccount->outlet_id) {
            return null;
        }

        return $cashAccount;
    }

    private function validateRange(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'group_by' => 'nullable|in:day,week,month',
        ]);

        if ($validator->fails()) {
            return [null, null, null, response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422)];
        }

        return [$request->from, $request->to, $request->group_by ?? 'day', null];
    }
}