<?php

namespace App\Services;

use App\Models\Sale;
use Illuminate\Support\Carbon;

class SaleSyncService
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
                'sale_date' => $this->normalizeDate($item['date'] ?? null),
                'last_change_date' => $this->normalizeDate($item['last_change_date'] ?? null),
                'supplier_article' => $item['supplier_article'] ?? null,
                'tech_size' => $item['tech_size'] ?? null,
                'barcode' => $this->toStringOrNull($item['barcode'] ?? null),
                'total_price' => $this->toFloatOrNull($item['total_price'] ?? null),
                'discount_percent' => $this->toIntOrNull($item['discount_percent'] ?? null),
                'is_supply' => $this->toBoolOrNull($item['is_supply'] ?? null),
                'is_realization' => $this->toBoolOrNull($item['is_realization'] ?? null),
                'promo_code_discount' => $this->toFloatOrNull($item['promo_code_discount'] ?? null),
                'warehouse_name' => $item['warehouse_name'] ?? null,
                'country_name' => $item['country_name'] ?? null,
                'oblast_okrug_name' => $item['oblast_okrug_name'] ?? null,
                'region_name' => $item['region_name'] ?? null,
                'income_id' => $this->toStringOrNull($item['income_id'] ?? null),
                'sale_id' => $item['sale_id'] ?? null,
                'odid' => $item['odid'] ?? null,
                'spp' => $this->toFloatOrNull($item['spp'] ?? null),
                'for_pay' => $this->toFloatOrNull($item['for_pay'] ?? null),
                'finished_price' => $this->toFloatOrNull($item['finished_price'] ?? null),
                'price_with_disc' => $this->toFloatOrNull($item['price_with_disc'] ?? null),
                'nm_id' => $this->toStringOrNull($item['nm_id'] ?? null),
                'subject' => $item['subject'] ?? null,
                'category' => $item['category'] ?? null,
                'brand' => $item['brand'] ?? null,
                'is_storno' => $this->toBoolOrNull($item['is_storno'] ?? null),
                'payload' => $this->encodeJson($item),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($rows === []) {
            return 0;
        }

        Sale::upsert($rows, ['external_hash'], [
            'date_from',
            'date_to',
            'g_number',
            'sale_date',
            'last_change_date',
            'supplier_article',
            'tech_size',
            'barcode',
            'total_price',
            'discount_percent',
            'is_supply',
            'is_realization',
            'promo_code_discount',
            'warehouse_name',
            'country_name',
            'oblast_okrug_name',
            'region_name',
            'income_id',
            'sale_id',
            'odid',
            'spp',
            'for_pay',
            'finished_price',
            'price_with_disc',
            'nm_id',
            'subject',
            'category',
            'brand',
            'is_storno',
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
