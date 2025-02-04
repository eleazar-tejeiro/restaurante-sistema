<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\SaleDetail;
use Illuminate\Support\Facades\Session;
use App\Models\User;
class SaleController extends Controller
{
    public function export()
{
    $startDate = Session::get('pdf_start_date');
    $endDate = Session::get('pdf_end_date');
    $userId = Session::get('pdf_user_id');

    $startDateTime = date('Y-m-d 00:00:00', strtotime($startDate));
    $endDateTime = date('Y-m-d 23:59:59', strtotime($endDate));

    $query = SaleDetail::with(['sale', 'product']);

    if ($userId) {
        $query->whereHas('sale', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    $sales = $query->whereBetween('created_at', [$startDateTime, $endDateTime])
        ->get()
        ->map(function ($sale) {
            $sale->profit = ($sale->subtotal - ($sale->product->purchase_price * $sale->quantity));
            return $sale;
        });

    $totalSales = $sales->sum('subtotal');
    $totalProducts = $sales->sum('quantity');
    $totalProfit = $sales->sum('profit');

    $userName = $userId ? User::find($userId)->name : 'Todos los vendedores';

    $pdf = Pdf::loadView('pdf.salePdf', [
        'sales' => $sales,
        'totalSales' => $totalSales,
        'totalProducts' => $totalProducts,
        'totalProfit' => $totalProfit,
        'startDate' => $startDate,
        'endDate' => $endDate,
        'userName' => $userName
    ]);
    
    return $pdf->download('sale.pdf');
}
}