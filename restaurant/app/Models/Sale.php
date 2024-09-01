<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
class Sale extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'total'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }
    public function reduceStock()
    {
        foreach ($this->saleDetails as $detail) {
            $product = $detail->product;
            $product->stock -= $detail->quantity;
            $product->save();
        }
    }
}