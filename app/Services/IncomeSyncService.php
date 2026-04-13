<?php

namespace App\Services;

use App\Models\Income;
use Illuminate\Support\Carbon;

class IncomeSyncService
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
                'income_id' => $this->toStringOrNull($item['income_id'] ?? null),
                'number' => $item['number'] ?? null,
                'income_date' => $this->normalizeDate($item['date'] ?? null),
                'last_change_date' => $this->normalizeDate($item['last_change_date'] ?? null),
                'supplier_article' => $item['supplier_article'] ?? null,
                'tech_size' => $item['tech_size'] ?? null,
                'barcode' => $this->toStringOrNull($item['barcode'] ?? null),
                'quantity' => $this->toIntOrNull($item['quantity'] ?? null),
                'total_price' => $this->toFloatOrNull($item['total_price'] ?? null),
                'date_close' => $this->normalizeDate($item['date_close'] ?? null),
                'warehouse_name' => $item['warehouse_name'] ?? null,
                'nm_id' => $this->toStringOrNull($item['nm_id'] ?? null),
                'payload' => $this->encodeJson($item),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($rows === []) {
            return 0;
        }

        Income::upsert($rows, ['external_hash'], [
            'date_from',
            'date_to',
            'income_id',
            'number',
            'income_date',
            'last_change_date',
            'supplier_article',
            'tech_size',
            'barcode',
            'quantity',
            'total_price',
            'date_close',
            'warehouse_name',
            'nm_id',
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
