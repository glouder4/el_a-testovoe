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

class CabinetDataController extends Controller
{
    use AppliesCabinetFilters;

    private const PER_PAGE_DEFAULT = 25;

    private const PER_PAGE_MAX = 100;

    public function orders(Request $request): JsonResponse
    {
        $v = $this->validateList($request);
        if ($v instanceof JsonResponse) {
            return $v;
        }

        $query = Order::query()->orderByDesc('order_date')->orderByDesc('id');
        $this->applyCommonStringFilters($query, $v);
        $this->applyDateRangeOnColumn($query, $v, 'order_date');

        return $this->paginateJson($query, $v['page'], $v['per_page'], [
            'id', 'order_date', 'last_change_date', 'g_number', 'barcode', 'warehouse_name',
            'supplier_article', 'nm_id', 'total_price', 'discount_percent', 'oblast',
        ]);
    }

    public function sales(Request $request): JsonResponse
    {
        $v = $this->validateList($request);
        if ($v instanceof JsonResponse) {
            return $v;
        }

        $query = Sale::query()->orderByDesc('sale_date')->orderByDesc('id');
        $this->applyCommonStringFilters($query, $v);
        $this->applyDateRangeOnColumn($query, $v, 'sale_date');
        if (! empty($v['sale_id'])) {
            $query->where('sale_id', 'like', '%'.$this->escapeLike($v['sale_id']).'%');
        }

        return $this->paginateJson($query, $v['page'], $v['per_page'], [
            'id', 'sale_date', 'last_change_date', 'sale_id', 'g_number', 'barcode', 'warehouse_name',
            'supplier_article', 'nm_id', 'total_price', 'discount_percent', 'finished_price',
        ]);
    }

    public function stocks(Request $request): JsonResponse
    {
        $v = $this->validateList($request);
        if ($v instanceof JsonResponse) {
            return $v;
        }

        $query = Stock::query()->orderByDesc('stock_date')->orderByDesc('id');
        $this->applyCommonStringFilters($query, $v);
        $this->applyDateRangeOnColumn($query, $v, 'stock_date');
        if (! empty($v['date_from_sync'])) {
            $query->whereDate('date_from', $v['date_from_sync']);
        }

        return $this->paginateJson($query, $v['page'], $v['per_page'], [
            'id', 'date_from', 'stock_date', 'last_change_date', 'barcode', 'warehouse_name',
            'supplier_article', 'nm_id', 'quantity', 'quantity_full', 'is_supply', 'is_realization',
        ]);
    }

    public function incomes(Request $request): JsonResponse
    {
        $v = $this->validateList($request);
        if ($v instanceof JsonResponse) {
            return $v;
        }

        $query = Income::query()->orderByDesc('income_date')->orderByDesc('id');
        $this->applyCommonStringFilters($query, $v);
        $this->applyDateRangeOnColumn($query, $v, 'income_date');
        if (! empty($v['income_id'])) {
            $query->where('income_id', 'like', '%'.$this->escapeLike($v['income_id']).'%');
        }

        return $this->paginateJson($query, $v['page'], $v['per_page'], [
            'id', 'income_id', 'income_date', 'last_change_date', 'barcode', 'warehouse_name',
            'supplier_article', 'nm_id', 'quantity', 'total_price', 'date_close',
        ]);
    }

    private function validateList(Request $request): array|JsonResponse
    {
        $rules = [
            'page' => ['required', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:'.self::PER_PAGE_MAX],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d'],
            'barcode' => ['nullable', 'string', 'max:64'],
            'warehouse_name' => ['nullable', 'string', 'max:255'],
            'supplier_article' => ['nullable', 'string', 'max:255'],
            'nm_id' => ['nullable', 'string', 'max:64'],
            'sale_id' => ['nullable', 'string', 'max:64'],
            'income_id' => ['nullable', 'string', 'max:64'],
            'date_from_sync' => ['nullable', 'date_format:Y-m-d'],
        ];

        $validator = Validator::make($request->query(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['per_page'] = min(
            (int) ($data['per_page'] ?? self::PER_PAGE_DEFAULT),
            self::PER_PAGE_MAX
        );
        if ($data['per_page'] < 1) {
            $data['per_page'] = self::PER_PAGE_DEFAULT;
        }

        return $data;
    }

    private function paginateJson(Builder $query, int $page, int $perPage, array $columns): JsonResponse
    {
        $paginator = $query->paginate(
            perPage: $perPage,
            columns: $columns,
            pageName: 'page',
            page: $page
        );

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ]);
    }
}
