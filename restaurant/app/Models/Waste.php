<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Waste extends Model
{
    use HasFactory;
    
    protected $fillable = ['product_id', 'quantity', 'wasted_date', 'reason'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}