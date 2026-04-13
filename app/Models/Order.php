<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'date_from',
        'date_to',
        'external_hash',
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
    ];

    protected $casts = [
        'date_from' => 'date:Y-m-d',
        'date_to' => 'date:Y-m-d',
        'order_date' => 'datetime:Y-m-d H:i:s',
        'last_change_date' => 'date:Y-m-d',
        'total_price' => 'decimal:2',
        'discount_percent' => 'integer',
        'is_cancel' => 'boolean',
        'cancel_dt' => 'datetime:Y-m-d H:i:s',
        'payload' => 'array',
    ];
}
