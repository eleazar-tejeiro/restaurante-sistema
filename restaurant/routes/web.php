<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SaleAll;
use App\Http\Controllers\SaleToday;
use App\Http\Controllers\SaleMonthly;

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



Route::get('/saleAll', [SaleAll::class, 'export'])->name('saleAll.pdf');
Route::get('/saleToday', [SaleToday::class, 'export'])->name('saleToday.pdf');
Route::get('/saleMonthly', [SaleMonthly::class, 'export'])->name('saleMonthly.pdf');