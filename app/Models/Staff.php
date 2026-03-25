<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $fillable = [
        'user_id',
        'employee_id',
        'hire_date',
        'is_active'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'assigned_staff_id');
    }

    public function assignedDeliveries()
    {
        return $this->hasMany(Delivery::class, 'assigned_staff_id');
    }
}