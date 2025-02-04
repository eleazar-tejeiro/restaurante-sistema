<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\InventoryController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/sale', [SaleController::class, 'export'])->name('sale.pdf');
Route::get('/purchase', [PurchaseController::class, 'export'])->name('purchase.pdf');
Route::get('/inventory', [InventoryController::class, 'export'])->name('inventory.pdf');