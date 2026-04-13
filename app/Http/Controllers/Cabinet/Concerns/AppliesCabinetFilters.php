<?php

namespace App\Http\Controllers\Cabinet\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait AppliesCabinetFilters
{
    protected function applyCommonStringFilters(Builder $query, array $v): void
    {
        if (! empty($v['barcode'])) {
            $like = '%'.$this->escapeLike($v['barcode']).'%';
            $query->where('barcode', 'like', $like);
        }
        if (! empty($v['warehouse_name'])) {
            $like = '%'.$this->escapeLike($v['warehouse_name']).'%';
            $query->where('warehouse_name', 'like', $like);
        }
        if (! empty($v['supplier_article'])) {
            $like = '%'.$this->escapeLike($v['supplier_article']).'%';
            $query->where('supplier_article', 'like', $like);
        }
        if (! empty($v['nm_id'])) {
            $like = '%'.$this->escapeLike($v['nm_id']).'%';
            $query->where('nm_id', 'like', $like);
        }
    }

    protected function applyDateRangeOnColumn(Builder $query, array $v, string $column): void
    {
        if (! empty($v['date_from'])) {
            $query->whereDate($column, '>=', $v['date_from']);
        }
        if (! empty($v['date_to'])) {
            $query->whereDate($column, '<=', $v['date_to']);
        }
    }

    protected function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
