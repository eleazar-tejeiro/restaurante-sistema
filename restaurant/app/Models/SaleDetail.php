<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleDetail extends Model
{
    use HasFactory;
    protected $fillable = ['sale_id', 'product_id', 'quantity', 'unit_price', 'subtotal'];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function getProfitAttribute()
    {
        $salePrice = $this->unit_price; 
        $purchasePrice = $this->product->purchase_price ?? 0;
        return ($salePrice - $purchasePrice) * $this->quantity;
    }
}