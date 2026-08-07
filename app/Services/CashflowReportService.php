<?php

namespace App\Services;

use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\Outlet;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CashflowReportService
{
    private const ALLOWED_GROUP_BY = ['day', 'week', 'month'];

    public static function forAccount(CashAccount $cashAccount, string $from, string $to, string $groupBy): array
    {
        $query = CashTransaction::where('cash_account_id', $cashAccount->id);

        return self::build($query, $from, $to, $groupBy);
    }

    public static function forOutlet(Outlet $outlet, string $from, string $to, string $groupBy): array
    {
        $query = CashTransaction::whereHas('cashAccount', function (Builder $q) use ($outlet) {
            $q->where('outlet_id', $outlet->id);
        });

        return self::build($query, $from, $to, $groupBy);
    }

    public static function forTenant(string $tenantId, string $from, string $to, string $groupBy): array
    {
        $query = CashTransaction::whereHas('cashAccount.outlet', function (Builder $q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        });

        return self::build($query, $from, $to, $groupBy);
    }

    private static function build(Builder $query, string $from, string $to, string $groupBy): array
    {
        if (!in_array($groupBy, self::ALLOWED_GROUP_BY, true)) {
            throw new InvalidArgumentException("Invalid group_by value: '{$groupBy}'. Allowed: " . implode(', ', self::ALLOWED_GROUP_BY));
        }

        $rows = $query->whereBetween('date', [$from, $to])
            ->select(
                DB::raw("date_trunc('{$groupBy}', date) as period"),
                'type',
                'source',
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('period', 'type', 'source')
            ->orderBy('period')
            ->get();

        $periods = [];

        foreach ($rows as $row) {
            $periodKey = $row->period;

            if (!isset($periods[$periodKey])) {
                $periods[$periodKey] = [
                    'period' => $periodKey,
                    'total_in' => 0,
                    'total_out' => 0,
                    'breakdown' => [],
                ];
            }

            $amount = (float) $row->total;

            if ($row->type === 'in') {
                $periods[$periodKey]['total_in'] += $amount;
            } else {
                $periods[$periodKey]['total_out'] += $amount;
            }

            $periods[$periodKey]['breakdown'][] = [
                'type' => $row->type,
                'source' => $row->source,
                'amount' => $amount,
            ];
        }

        $periods = array_values($periods);

        foreach ($periods as &$period) {
            $period['net'] = round($period['total_in'] - $period['total_out'], 2);
        }

        $summaryIn = array_sum(array_column($periods, 'total_in'));
        $summaryOut = array_sum(array_column($periods, 'total_out'));

        return [
            'from' => $from,
            'to' => $to,
            'group_by' => $groupBy,
            'periods' => $periods,
            'summary' => [
                'total_in' => round($summaryIn, 2),
                'total_out' => round($summaryOut, 2),
                'net' => round($summaryIn - $summaryOut, 2),
            ],
        ];
    }
}