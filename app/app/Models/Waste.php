<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Waste extends Model
{
    use HasFactory;
    protected $table = 'wasted';
    protected $fillable = [
        'inventory_id', 'quantity', 'waste_date', 'reason','description'
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}