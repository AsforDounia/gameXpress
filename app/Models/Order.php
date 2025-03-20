<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
<<<<<<< HEAD

    protected $fillable = ['user_id', 'total_price', 'status'];

=======
    
    protected $fillable = ['user_id', 'total_price', 'status'];

>>>>>>> ikram
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }


    public function payment()
    {
        return $this->hasMany(Payment::class);
    }
}
