<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\SaleDetail;
use Carbon\Carbon;

class SaleToday extends Controller
{
    public function export()
    {
        $today = Carbon::today();
        
        $sales = SaleDetail::whereHas('sale', function($query) use ($today) {
            $query->whereDate('created_at', $today);
        })->with(['sale', 'product'])->get();
        
        $totalSales = $sales->sum('subtotal');
        $totalProducts = $sales->sum('quantity');
        $totalProfit = $sales->sum(function($sale) {
            return $sale->profit;
        });

        $pdf = Pdf::loadView('pdf.sale', [
            'sales' => $sales,
            'totalSales' => $totalSales,
            'totalProducts' => $totalProducts,
            'totalProfit' => $totalProfit,
            'date' => $today->format('d/m/Y')
        ]);
        
        return $pdf->download('today_sales_report.pdf');
    }
}