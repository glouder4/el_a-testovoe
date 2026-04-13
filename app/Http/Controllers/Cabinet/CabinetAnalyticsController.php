<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\Cabinet\Concerns\AppliesCabinetFilters;
use App\Http\Controllers\Controller;
use App\Models\Income;
use App\Models\Order;
use App\Models\Sale;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CabinetAnalyticsController extends Controller
{
    use AppliesCabinetFilters;

    private const TOP_WAREHOUSES = 12;

    public function orders(Request $request): JsonResponse
    {
        $v = $this->validateFilters($request);
        if ($v instanceof JsonResponse) {
            return $v;
        }

        $base = Order::query();
        $this->applyCommonStringFilters($base, $v);
        $this->applyDateRangeOnColumn($base, $v, 'order_date');

        return response()->json($this->buildOrderLikePayload($base, 'orders', 'order_date'));
    }

    public function sales(Request $request): JsonResponse
    {
        $v = $this->validateFilters($request);
        if ($v instanceof JsonResponse) {
            return $v;
        }

        $base = Sale::query();
        $this->applyCommonStringFilters($base, $v);
        $this->applyDateRangeOnColumn($base, $v, 'sale_date');
        if (! empty($v['sale_id'])) {
            $base->where('sale_id', 'like', '%'.$this->escapeLike($v['sale_id']).'%');
        }

        return response()->json($this->buildOrderLikePayload($base, 'sales', 'sale_date'));
    }

    public function stocks(Request $request): JsonResponse
    {
        $v = $this->validateFilters($request);
        if ($v instanceof JsonResponse) {
            return $v;
        }

        $base = Stock::query();
        $this->applyCommonStringFilters($base, $v);
        $this->applyDateRangeOnColumn($base, $v, 'stock_date');
        if (! empty($v['date_from_sync'])) {
            $base->whereDate('date_from', $v['date_from_sync']);
        }

        $totals = (clone $base)
            ->selectRaw('COUNT(*) as row_count, COALESCE(SUM(quantity), 0) as sum_qty, COALESCE(SUM(quantity_full), 0) as sum_qty_full')
            ->first();

        $byDay = (clone $base)
            ->selectRaw('DATE(stock_date) as bucket, COUNT(*) as cnt')
            ->whereNotNull('stock_date')
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get()
            ->map(fn ($r) => ['date' => $r->bucket, 'count' => (int) $r->cnt]);

        $byWarehouse = (clone $base)
            ->selectRaw('COALESCE(NULLIF(warehouse_name, ""), "(не указан)") as wh, COUNT(*) as cnt')
            ->groupBy('wh')
            ->orderByDesc('cnt')
            ->limit(self::TOP_WAREHOUSES)
            ->get()
            ->map(fn ($r) => ['label' => $r->wh, 'count' => (int) $r->cnt]);

        return response()->json([
            'resource' => 'stocks',
            'totals' => [
                'rows' => (int) ($totals->row_count ?? 0),
                'sum_quantity' => (float) ($totals->sum_qty ?? 0),
                'sum_quantity_full' => (float) ($totals->sum_qty_full ?? 0),
            ],
            'by_day' => $byDay,
            'by_warehouse' => $byWarehouse,
        ]);
    }

    public function incomes(Request $request): JsonResponse
    {
        $v = $this->validateFilters($request);
        if ($v instanceof JsonResponse) {
            return $v;
        }

        $base = Income::query();
        $this->applyCommonStringFilters($base, $v);
        $this->applyDateRangeOnColumn($base, $v, 'income_date');
        if (! empty($v['income_id'])) {
            $base->where('income_id', 'like', '%'.$this->escapeLike($v['income_id']).'%');
        }

        $totals = (clone $base)
            ->selectRaw('COUNT(*) as row_count, COALESCE(SUM(total_price), 0) as sum_price, COALESCE(SUM(quantity), 0) as sum_qty')
            ->first();

        $byDay = (clone $base)
            ->selectRaw('DATE(income_date) as bucket, COUNT(*) as cnt')
            ->whereNotNull('income_date')
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get()
            ->map(fn ($r) => ['date' => $r->bucket, 'count' => (int) $r->cnt]);

        $byWarehouse = (clone $base)
            ->selectRaw('COALESCE(NULLIF(warehouse_name, ""), "(не указан)") as wh, COUNT(*) as cnt')
            ->groupBy('wh')
            ->orderByDesc('cnt')
            ->limit(self::TOP_WAREHOUSES)
            ->get()
            ->map(fn ($r) => ['label' => $r->wh, 'count' => (int) $r->cnt]);

        return response()->json([
            'resource' => 'incomes',
            'totals' => [
                'rows' => (int) ($totals->row_count ?? 0),
                'sum_total_price' => (string) $totals->sum_price,
                'sum_quantity' => (float) ($totals->sum_qty ?? 0),
            ],
            'by_day' => $byDay,
            'by_warehouse' => $byWarehouse,
        ]);
    }

    private function validateFilters(Request $request): array|JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d'],
            'barcode' => ['nullable', 'string', 'max:64'],
            'warehouse_name' => ['nullable', 'string', 'max:255'],
            'supplier_article' => ['nullable', 'string', 'max:255'],
            'nm_id' => ['nullable', 'string', 'max:64'],
            'sale_id' => ['nullable', 'string', 'max:64'],
            'income_id' => ['nullable', 'string', 'max:64'],
            'date_from_sync' => ['nullable', 'date_format:Y-m-d'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        return $validator->validated();
    }

    private function buildOrderLikePayload(Builder $base, string $resource, string $dateColumn): array
    {
        $totals = (clone $base)
            ->selectRaw('COUNT(*) as row_count, COALESCE(SUM(total_price), 0) as sum_price')
            ->first();

        $byDay = (clone $base)
            ->selectRaw('DATE('.$dateColumn.') as bucket, COUNT(*) as cnt')
            ->whereNotNull($dateColumn)
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get()
            ->map(fn ($r) => ['date' => $r->bucket, 'count' => (int) $r->cnt]);

        $byWarehouse = (clone $base)
            ->selectRaw('COALESCE(NULLIF(warehouse_name, ""), "(не указан)") as wh, COUNT(*) as cnt')
            ->groupBy('wh')
            ->orderByDesc('cnt')
            ->limit(self::TOP_WAREHOUSES)
            ->get()
            ->map(fn ($r) => ['label' => $r->wh, 'count' => (int) $r->cnt]);

        return [
            'resource' => $resource,
            'totals' => [
                'rows' => (int) ($totals->row_count ?? 0),
                'sum_total_price' => (string) $totals->sum_price,
            ],
            'by_day' => $byDay,
            'by_warehouse' => $byWarehouse,
        ];
    }
}
