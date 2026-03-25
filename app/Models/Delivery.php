<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    protected $fillable = [
        'delivery_number',
        'client_id',
        'assigned_staff_id',
        'pickup_address_id',
        'delivery_address_id',
        'pickup_address_text',
        'delivery_address_text',
        'scheduled_pickup_time',
        'scheduled_delivery_time',
        'actual_pickup_time',
        'actual_delivery_time',
        'status',
        'failure_reason',
        'barcode_value',
        'barcode_format',
        'barcode_image_url',
        'priority',
        'special_instructions',
        'internal_notes',
        'created_by'
    ];

    protected $casts = [
        'scheduled_pickup_time' => 'datetime',
        'scheduled_delivery_time' => 'datetime',
        'actual_pickup_time' => 'datetime',
        'actual_delivery_time' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'assigned_staff_id');
    }

    public function pickupAddress()
    {
        return $this->belongsTo(Address::class, 'pickup_address_id');
    }

    public function deliveryAddress()
    {
        return $this->belongsTo(Address::class, 'delivery_address_id');
    }

    public function items()
    {
        return $this->hasMany(DeliveryItem::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(DeliveryStatusHistory::class);
    }

    public function proofOfDelivery()
    {
        return $this->hasOne(ProofOfDelivery::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function assignedStaff()
{
    return $this->belongsTo(Staff::class, 'assigned_staff_id');
}
}