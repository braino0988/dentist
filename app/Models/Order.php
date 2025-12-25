<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];
    public function products(){
        return $this->belongsToMany(Product::class)->withPivot('quantity', 'unit_price', 'tax_rate', 'tax_amount')->withTimestamps();
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function invoice(){
        return $this->hasOne(Invoice::class);
    }
    public function stockMovments(){
        return $this->hasMany(StockMovment::class);
    }
}
