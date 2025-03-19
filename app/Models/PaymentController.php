<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentController extends Model
{
    protected $fillable = [
        'order_id',
        'payment_type',
        'status',
        'transaction_id',
        'amount',
    ];

    // public function order()
    // {
    //     return $this->belongsTo(Order::class);
    // }
}
