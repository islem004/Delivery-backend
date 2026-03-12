<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'delivery_id',
        'client_id',
        'invoice_date',
        'due_date',
        'status',
        'subtotal',
        'tax_total',
        'discount_total',
        'total',
        'amount_paid',
        'balance_due',
        'payment_date',
        'payment_method',
        'payment_reference',
        'notes',
        'terms',
        'payment_instructions',
        'pdf_url',
        'created_by'
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'payment_date' => 'date',
    ];

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}