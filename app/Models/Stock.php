<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = [
        'date_from',
        'external_hash',
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
    ];

    protected $casts = [
        'date_from' => 'date:Y-m-d',
        'stock_date' => 'date:Y-m-d',
        'last_change_date' => 'date:Y-m-d',
        'quantity' => 'integer',
        'is_supply' => 'boolean',
        'is_realization' => 'boolean',
        'quantity_full' => 'integer',
        'in_way_to_client' => 'integer',
        'in_way_from_client' => 'integer',
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'payload' => 'array',
    ];
}
