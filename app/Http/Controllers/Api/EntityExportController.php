<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Income;
use App\Models\Order;
use App\Models\Sale;
use App\Models\Stock;
use App\Services\ExternalApiService;
use App\Services\IncomeSyncService;
use App\Services\OrderSyncService;
use App\Services\SaleSyncService;
use App\Services\StockSyncService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EntityExportController extends Controller
{
    public function __construct(
        private readonly ExternalApiService $externalApiService,
        private readonly StockSyncService $stockSyncService,
        private readonly IncomeSyncService $incomeSyncService,
        private readonly OrderSyncService $orderSyncService,
        private readonly SaleSyncService $saleSyncService
    ) {
    }

    public function sales(Request $request): JsonResponse
    {
        $validated = $this->validateQuery($request, $this->rulesWithDateTo());
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        try {
            $data = $this->externalApiService->fetch('sales', $validated);
            $savedCount = $this->saleSyncService->sync($validated, $data);

            return response()->json(array_merge($data, [
                '_local' => [
                    'saved' => $savedCount,
                ],
            ]));
        } catch (ConnectionException $exception) {
            return response()->json([
                'message' => 'External API is unavailable',
            ], 503);
        } catch (RequestException $exception) {
            $response = $exception->response;

            return response()->json([
                'message' => 'External API request failed',
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ], $response->status());
        }
    }

    public function orders(Request $request): JsonResponse
    {
        $validated = $this->validateQuery($request, $this->rulesWithDateTo());
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        try {
            $data = $this->externalApiService->fetch('orders', $validated);
            $savedCount = $this->orderSyncService->sync($validated, $data);

            return response()->json(array_merge($data, [
                '_local' => [
                    'saved' => $savedCount,
                ],
            ]));
        } catch (ConnectionException $exception) {
            return response()->json([
                'message' => 'External API is unavailable',
            ], 503);
        } catch (RequestException $exception) {
            $response = $exception->response;

            return response()->json([
                'message' => 'External API request failed',
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ], $response->status());
        }
    }

    public function stocks(Request $request): JsonResponse
    {
        $validated = $this->validateQuery($request, $this->rulesWithoutDateTo());
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        try {
            $data = $this->externalApiService->fetch('stocks', $validated);
            $savedCount = $this->stockSyncService->sync($validated['dateFrom'], $data);

            return response()->json(array_merge($data, [
                '_local' => [
                    'saved' => $savedCount,
                ],
            ]));
        } catch (ConnectionException $exception) {
            return response()->json([
                'message' => 'External API is unavailable',
            ], 503);
        } catch (RequestException $exception) {
            $response = $exception->response;

            return response()->json([
                'message' => 'External API request failed',
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ], $response->status());
        }
    }

    public function incomes(Request $request): JsonResponse
    {
        $validated = $this->validateQuery($request, $this->rulesWithDateTo());
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        try {
            $data = $this->externalApiService->fetch('incomes', $validated);
            $savedCount = $this->incomeSyncService->sync($validated, $data);

            return response()->json(array_merge($data, [
                '_local' => [
                    'saved' => $savedCount,
                ],
            ]));
        } catch (ConnectionException $exception) {
            return response()->json([
                'message' => 'External API is unavailable',
            ], 503);
        } catch (RequestException $exception) {
            $response = $exception->response;

            return response()->json([
                'message' => 'External API request failed',
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ], $response->status());
        }
    }

    public function localStocks(Request $request): JsonResponse
    {
        $validated = $this->validateQuery($request, [
            'dateFrom' => ['nullable', 'date_format:Y-m-d'],
            'page' => ['required', 'integer', 'min:1'],
            'limit' => ['required', 'integer', 'min:1', 'max:500'],
        ]);

        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $query = Stock::query()->orderByDesc('stock_date')->orderByDesc('id');

        if (!empty($validated['dateFrom'])) {
            $query->whereDate('date_from', $validated['dateFrom']);
        }

        $paginator = $query->paginate(
            perPage: (int) $validated['limit'],
            columns: ['*'],
            pageName: 'page',
            page: (int) $validated['page']
        );

        return response()->json($paginator);
    }

    public function localIncomes(Request $request): JsonResponse
    {
        $validated = $this->validateQuery($request, [
            'dateFrom' => ['nullable', 'date_format:Y-m-d'],
            'dateTo' => ['nullable', 'date_format:Y-m-d'],
            'page' => ['required', 'integer', 'min:1'],
            'limit' => ['required', 'integer', 'min:1', 'max:500'],
        ]);

        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $query = Income::query()->orderByDesc('income_date')->orderByDesc('id');

        if (!empty($validated['dateFrom'])) {
            $query->whereDate('date_from', $validated['dateFrom']);
        }

        if (!empty($validated['dateTo'])) {
            $query->whereDate('date_to', $validated['dateTo']);
        }

        $paginator = $query->paginate(
            perPage: (int) $validated['limit'],
            columns: ['*'],
            pageName: 'page',
            page: (int) $validated['page']
        );

        return response()->json($paginator);
    }

    public function localOrders(Request $request): JsonResponse
    {
        $validated = $this->validateQuery($request, [
            'dateFrom' => ['nullable', 'date_format:Y-m-d'],
            'dateTo' => ['nullable', 'date_format:Y-m-d'],
            'page' => ['required', 'integer', 'min:1'],
            'limit' => ['required', 'integer', 'min:1', 'max:500'],
        ]);

        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $query = Order::query()->orderByDesc('order_date')->orderByDesc('id');

        if (!empty($validated['dateFrom'])) {
            $query->whereDate('date_from', $validated['dateFrom']);
        }

        if (!empty($validated['dateTo'])) {
            $query->whereDate('date_to', $validated['dateTo']);
        }

        $paginator = $query->paginate(
            perPage: (int) $validated['limit'],
            columns: ['*'],
            pageName: 'page',
            page: (int) $validated['page']
        );

        return response()->json($paginator);
    }

    public function localSales(Request $request): JsonResponse
    {
        $validated = $this->validateQuery($request, [
            'dateFrom' => ['nullable', 'date_format:Y-m-d'],
            'dateTo' => ['nullable', 'date_format:Y-m-d'],
            'page' => ['required', 'integer', 'min:1'],
            'limit' => ['required', 'integer', 'min:1', 'max:500'],
        ]);

        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $query = Sale::query()->orderByDesc('sale_date')->orderByDesc('id');

        if (!empty($validated['dateFrom'])) {
            $query->whereDate('date_from', $validated['dateFrom']);
        }

        if (!empty($validated['dateTo'])) {
            $query->whereDate('date_to', $validated['dateTo']);
        }

        $paginator = $query->paginate(
            perPage: (int) $validated['limit'],
            columns: ['*'],
            pageName: 'page',
            page: (int) $validated['page']
        );

        return response()->json($paginator);
    }

    private function rulesWithDateTo(): array
    {
        return [
            'key' => ['required', 'string'],
            'dateFrom' => ['nullable', 'date_format:Y-m-d'],
            'dateTo' => ['nullable', 'date_format:Y-m-d'],
            'page' => ['required', 'integer', 'min:1'],
            'limit' => ['required', 'integer', 'min:1', 'max:500'],
        ];
    }

    private function rulesWithoutDateTo(): array
    {
        $today = now()->format('Y-m-d');

        return [
            'key' => ['required', 'string'],
            'dateFrom' => ['required', 'date_format:Y-m-d', 'date_equals:' . $today],
            'page' => ['required', 'integer', 'min:1'],
            'limit' => ['required', 'integer', 'min:1', 'max:500'],
        ];
    }

    private function proxy(string $entity, array $validated): JsonResponse
    {
        try {
            $data = $this->externalApiService->fetch($entity, $validated);

            return response()->json($data);
        } catch (ConnectionException $exception) {
            return response()->json([
                'message' => 'External API is unavailable',
            ], 503);
        } catch (RequestException $exception) {
            $response = $exception->response;

            return response()->json([
                'message' => 'External API request failed',
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ], $response->status());
        }
    }

    private function validateQuery(Request $request, array $rules): array|JsonResponse
    {
        $validator = Validator::make($request->query(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        return $validator->validated();
    }
}
