<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\SaleDetail;

class SaleMonthly extends Controller
{
    public function export(Request $request)
    {
        Carbon::setLocale('es');
        
        $month = $request->input('month', Carbon::now()->month);
        $year = Carbon::now()->year;

        $sales = SaleDetail::whereHas('sale', function ($query) use ($month, $year) {
            $query->whereYear('created_at', $year)
                  ->whereMonth('created_at', $month);
        })->with(['sale', 'product'])->get();

        $totalSales = $sales->sum('subtotal');
        $totalProducts = $sales->sum('quantity');
        $totalProfit = $sales->sum(function($sale) {
            return ($sale->unit_price - $sale->product->purchase_price) * $sale->quantity;
        });

        $monthName = Carbon::create()->month($month)->translatedFormat('F');

        $pdf = Pdf::loadView('pdf.sale-monthly', [
            'sales' => $sales,
            'totalSales' => $totalSales,
            'totalProducts' => $totalProducts,
            'totalProfit' => $totalProfit,
            'month' => $monthName,
            'year' => $year
        ]);

        return $pdf->download("sales_report_{$month}_{$year}.pdf");
    }
}