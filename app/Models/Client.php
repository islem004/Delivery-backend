<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'user_id',
        'company_name',
        'tax_id',
        'billing_address',
        'shipping_address',
        'contact_person',
        'contact_email',
        'contact_phone',
        'logo_url',
        'payment_terms',
        'credit_limit',
        'is_active'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}