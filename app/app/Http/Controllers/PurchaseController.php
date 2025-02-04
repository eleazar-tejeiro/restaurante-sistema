<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PurchaseDetail;
use Illuminate\Support\Facades\Session;
use App\Models\User;
class PurchaseController extends Controller
{
    public function export()
    {
        $startDate = Session::get('pdf_start_date');
        $endDate = Session::get('pdf_end_date');
        $userId = Session::get('pdf_user_id');

        $startDateTime = date('Y-m-d 00:00:00', strtotime($startDate));
        $endDateTime = date('Y-m-d 23:59:59', strtotime($endDate));

        $query = PurchaseDetail::with(['purchase', 'product'])
            ->whereBetween('created_at', [$startDateTime, $endDateTime]);

        if ($userId) {
            $query->whereHas('purchase', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }

        $purchases = $query->get();
        
        $totalPurchases = $purchases->sum('subtotal');
        $totalProducts = $purchases->sum('quantity');

        $userName = $userId ? User::find($userId)->name : 'Todos los usuarios';

        $pdf = Pdf::loadView('pdf.purchasePdf', [
            'purchases' => $purchases,
            'totalPurchases' => $totalPurchases,
            'totalProducts' => $totalProducts,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'userName' => $userName
        ]);
        
        return $pdf->download('purchase.pdf');
    }
}