<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'description', 'purchase_price', 'sale_price', 'stock', 'expiration_date'];

    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function wastedProducts()
    {
        return $this->hasMany(WastedProduct::class);
    }
    public function hasEnoughStock($quantity)
    {
        return $this->stock >= $quantity;
    }
}