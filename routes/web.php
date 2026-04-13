<?php

use App\Http\Controllers\Api\EntityExportController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Cabinet\CabinetAnalyticsController;
use App\Http\Controllers\Cabinet\CabinetDataController;
use App\Http\Controllers\CabinetController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('cabinet')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('/cabinet', [CabinetController::class, 'index'])->name('cabinet');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::prefix('cabinet/data')->group(function () {
        Route::get('/orders', [CabinetDataController::class, 'orders'])->name('cabinet.data.orders');
        Route::get('/sales', [CabinetDataController::class, 'sales'])->name('cabinet.data.sales');
        Route::get('/stocks', [CabinetDataController::class, 'stocks'])->name('cabinet.data.stocks');
        Route::get('/incomes', [CabinetDataController::class, 'incomes'])->name('cabinet.data.incomes');
    });

    Route::prefix('cabinet/analytics')->group(function () {
        Route::get('/orders', [CabinetAnalyticsController::class, 'orders'])->name('cabinet.analytics.orders');
        Route::get('/sales', [CabinetAnalyticsController::class, 'sales'])->name('cabinet.analytics.sales');
        Route::get('/stocks', [CabinetAnalyticsController::class, 'stocks'])->name('cabinet.analytics.stocks');
        Route::get('/incomes', [CabinetAnalyticsController::class, 'incomes'])->name('cabinet.analytics.incomes');
    });

    Route::get('/tester', function () {
        return view('api-tester');
    });
});

Route::prefix('api')->group(function () {
    Route::get('/sales', [EntityExportController::class, 'sales']);
    Route::get('/sales/local', [EntityExportController::class, 'localSales']);
    Route::get('/orders', [EntityExportController::class, 'orders']);
    Route::get('/orders/local', [EntityExportController::class, 'localOrders']);
    Route::get('/stocks', [EntityExportController::class, 'stocks']);
    Route::get('/stocks/local', [EntityExportController::class, 'localStocks']);
    Route::get('/incomes', [EntityExportController::class, 'incomes']);
    Route::get('/incomes/local', [EntityExportController::class, 'localIncomes']);
});
