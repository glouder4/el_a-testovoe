<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    protected $fillable = [
        'date_from',
        'date_to',
        'external_hash',
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
    ];

    protected $casts = [
        'date_from' => 'date:Y-m-d',
        'date_to' => 'date:Y-m-d',
        'income_date' => 'date:Y-m-d',
        'last_change_date' => 'date:Y-m-d',
        'quantity' => 'integer',
        'total_price' => 'decimal:2',
        'date_close' => 'date:Y-m-d',
        'payload' => 'array',
    ];
}
