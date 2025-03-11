<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name','slug','price','stock','status','subcategory_id'];
    
    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function productImages()
    {
        return $this->hasMany(ProductImage::class);
    }
}
