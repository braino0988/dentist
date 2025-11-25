<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public function orders(){
        return $this->belongsToMany(Order::class,'order_product')->withPivot('quantity','price')->withTimestamps();
    }
    public function images(){
        return $this->hasMany(Image::class);
    }
    public function category(){
        return $this->belongsTo(Category::class);
    }
}
