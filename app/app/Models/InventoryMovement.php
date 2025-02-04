<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use HasFactory;
    
    protected $table = 'inventory_movements';
    
    protected $fillable = [
        'inventory_id', 'movement_type', 'quantity',
        'movement_date', 'reference_id', 'reference_type'
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
    
    
}