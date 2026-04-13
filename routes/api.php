<?php

use App\Http\Controllers\Api\EntityExportController;
use Illuminate\Support\Facades\Route;

Route::get('/sales', [EntityExportController::class, 'sales']);
Route::get('/sales/local', [EntityExportController::class, 'localSales']);
Route::get('/orders', [EntityExportController::class, 'orders']);
Route::get('/orders/local', [EntityExportController::class, 'localOrders']);
Route::get('/stocks', [EntityExportController::class, 'stocks']);
Route::get('/stocks/local', [EntityExportController::class, 'localStocks']);
Route::get('/incomes', [EntityExportController::class, 'incomes']);
Route::get('/incomes/local', [EntityExportController::class, 'localIncomes']);
