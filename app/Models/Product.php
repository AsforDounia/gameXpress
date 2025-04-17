<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'slug', 'price', 'stock', 'status', 'subcategory_id'];

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function productImages()
    {
        return $this->hasMany(ProductImage::class);
    }
    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', 1);
    }

    public function user()
    {
        return $this->belongsToMany(User::class);
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function cartItem()
    {
        return $this->hasMany(CartItem::class);
    }
}
