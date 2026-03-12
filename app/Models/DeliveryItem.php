<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'delivery_id',
        'item_code',
        'item_name',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'discount_percent',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'subtotal',
        'total',
        'notes',
        'sort_order'
    ];

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }
}