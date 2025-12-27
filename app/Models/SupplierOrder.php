<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierOrder extends Model
{
    public function supplier(){
        return $this->belongsTo(Supplier::class);
    }
    public function products(){
        return $this->belongsToMany(Product::class, 'product_supplier_order')
            ->withPivot('quantity', 'unit_price', 'tax_rate', 'tax_amount')->withTimestamps();
    }
    public function supplierInvoices(){
        return $this->hasMany(SupplierInvoice::class);
    }
    public function stockMovments(){
        return $this->hasMany(StockMovment::class);
    }
}
