<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Inventory;
use Illuminate\Support\Facades\Session;

class InventoryController extends Controller
{
    public function export()
    {
        $startDate = Session::get('pdf_start_date');
        $endDate = Session::get('pdf_end_date');
    
        $startDateTime = date('Y-m-d 00:00:00', strtotime($startDate));
        $endDateTime = date('Y-m-d 23:59:59', strtotime($endDate));
    
        $inventories = Inventory::with('product')
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->get();

        $totalProducts = $inventories->sum('quantity');
        $totalValue = $inventories->sum(function ($inventory) {
            return $inventory->quantity * $inventory->product->purchase_price;
        });

        $pdf = Pdf::loadView('pdf.inventoryPdf', [
            'inventories' => $inventories,
            'totalProducts' => $totalProducts,
            'totalValue' => $totalValue,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
        
        return $pdf->download('inventory.pdf');
    }


}