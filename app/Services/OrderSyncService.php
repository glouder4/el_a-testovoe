<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Carbon;

class OrderSyncService
{
    public function sync(array $query, array $payload): int
    {
        $items = $payload['data'] ?? [];

        if (!is_array($items) || $items === []) {
            return 0;
        }

        $rows = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $rows[] = [
                'date_from' => $query['dateFrom'] ?? null,
                'date_to' => $query['dateTo'] ?? null,
                'external_hash' => $this->makeHash($item),
                'g_number' => $item['g_number'] ?? null,
                'order_date' => $this->normalizeDateTime($item['date'] ?? null),
                'last_change_date' => $this->normalizeDate($item['last_change_date'] ?? null),
                'supplier_article' => $item['supplier_article'] ?? null,
                'tech_size' => $item['tech_size'] ?? null,
                'barcode' => $this->toStringOrNull($item['barcode'] ?? null),
                'total_price' => $this->toFloatOrNull($item['total_price'] ?? null),
                'discount_percent' => $this->toIntOrNull($item['discount_percent'] ?? null),
                'warehouse_name' => $item['warehouse_name'] ?? null,
                'oblast' => $item['oblast'] ?? null,
                'income_id' => $this->toStringOrNull($item['income_id'] ?? null),
                'odid' => $item['odid'] ?? null,
                'nm_id' => $this->toStringOrNull($item['nm_id'] ?? null),
                'subject' => $item['subject'] ?? null,
                'category' => $item['category'] ?? null,
                'brand' => $item['brand'] ?? null,
                'is_cancel' => $this->toBoolOrNull($item['is_cancel'] ?? null),
                'cancel_dt' => $this->normalizeDateTime($item['cancel_dt'] ?? null),
                'payload' => $this->encodeJson($item),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($rows === []) {
            return 0;
        }

        Order::upsert($rows, ['external_hash'], [
            'date_from',
            'date_to',
            'g_number',
            'order_date',
            'last_change_date',
            'supplier_article',
            'tech_size',
            'barcode',
            'total_price',
            'discount_percent',
            'warehouse_name',
            'oblast',
            'income_id',
            'odid',
            'nm_id',
            'subject',
            'category',
            'brand',
            'is_cancel',
            'cancel_dt',
            'payload',
            'updated_at',
        ]);

        return count($rows);
    }

    private function makeHash(array $item): string
    {
        return hash('sha256', json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function normalizeDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeDateTime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }

    private function toIntOrNull(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function toFloatOrNull(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    private function toBoolOrNull(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (bool) $value;
    }

    private function encodeJson(array $item): string
    {
        return json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }

    private function toStringOrNull(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }
}
