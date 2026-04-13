<?php

namespace App\Services;

use App\Models\Stock;
use Illuminate\Support\Carbon;

class StockSyncService
{
    public function sync(string $dateFrom, array $payload): int
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
                'date_from' => $dateFrom,
                'external_hash' => $this->makeHash($item),
                'stock_date' => $this->normalizeDate($item['date'] ?? null),
                'last_change_date' => $this->normalizeDate($item['last_change_date'] ?? null),
                'supplier_article' => $item['supplier_article'] ?? null,
                'tech_size' => $item['tech_size'] ?? null,
                'barcode' => $this->toStringOrNull($item['barcode'] ?? null),
                'quantity' => $this->toIntOrNull($item['quantity'] ?? null),
                'is_supply' => $this->toBoolOrNull($item['is_supply'] ?? null),
                'is_realization' => $this->toBoolOrNull($item['is_realization'] ?? null),
                'quantity_full' => $this->toIntOrNull($item['quantity_full'] ?? null),
                'warehouse_name' => $item['warehouse_name'] ?? null,
                'in_way_to_client' => $this->toIntOrNull($item['in_way_to_client'] ?? null),
                'in_way_from_client' => $this->toIntOrNull($item['in_way_from_client'] ?? null),
                'nm_id' => $this->toStringOrNull($item['nm_id'] ?? null),
                'subject' => $item['subject'] ?? null,
                'category' => $item['category'] ?? null,
                'brand' => $item['brand'] ?? null,
                'sc_code' => $this->toStringOrNull($item['sc_code'] ?? null),
                'price' => $this->toFloatOrNull($item['price'] ?? null),
                'discount' => $this->toFloatOrNull($item['discount'] ?? null),
                'payload' => $this->encodeJson($item),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($rows === []) {
            return 0;
        }

        Stock::upsert($rows, ['external_hash'], [
            'date_from',
            'stock_date',
            'last_change_date',
            'supplier_article',
            'tech_size',
            'barcode',
            'quantity',
            'is_supply',
            'is_realization',
            'quantity_full',
            'warehouse_name',
            'in_way_to_client',
            'in_way_from_client',
            'nm_id',
            'subject',
            'category',
            'brand',
            'sc_code',
            'price',
            'discount',
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

    private function toStringOrNull(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    private function encodeJson(array $item): string
    {
        return json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }
}
