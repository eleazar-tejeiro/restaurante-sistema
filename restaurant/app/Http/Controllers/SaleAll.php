<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\SaleDetail;
use Illuminate\Support\Facades\Session;

class SaleAll extends Controller
{
    
    public function export()
{
    $startDate = Session::get('pdf_start_date');
    $endDate = Session::get('pdf_end_date');

    $startDateTime = date('Y-m-d 00:00:00', strtotime($startDate));
    $endDateTime = date('Y-m-d 23:59:59', strtotime($endDate));

    $sales = SaleDetail::with(['sale', 'product'])
        ->whereBetween('created_at', [$startDateTime, $endDateTime])
        ->get();
    $totalSales = $sales->sum('subtotal');
    $totalProducts = $sales->sum('quantity');
    $totalProfit = $sales->sum('profit');

    $pdf = Pdf::loadView('pdf.sale', [
        'sales' => $sales,
        'totalSales' => $totalSales,
        'totalProducts' => $totalProducts,
        'totalProfit' => $totalProfit,
        'startDate' => $startDate,
        'endDate' => $endDate
    ]);
    
        return $pdf->download('inventory_report.pdf');
    }
}