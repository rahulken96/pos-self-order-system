<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DapurController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\XenditWebhookController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\TableController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ReportController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'tables' => \App\Models\Table::orderBy('number')->get(),
    ]);
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->role === 'admin') {
        return redirect()->route('admin.reports');
    } elseif ($user->role === 'kasir') {
        return redirect()->route('kasir.index');
    } elseif ($user->role === 'dapur') {
        return redirect()->route('dapur.index');
    }
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Customer (public — hanya butuh table ID dari QR)
Route::get('/order/{table}', [CustomerController::class, 'index'])->name('customer.order');
Route::post('/order/{table}/start', [CustomerController::class, 'start'])->name('customer.start');
Route::post('/order/{order}/item', [CustomerController::class, 'addItem'])->name('customer.addItem');
Route::post('/order/{order}/submit', [CustomerController::class, 'submitOrder'])->name('customer.submitOrder');
Route::patch('/order/{order}/bill', [CustomerController::class, 'requestBill'])->name('customer.requestBill');
Route::post('/order/{order}/pay/online', [CustomerController::class, 'payOnline'])->name('customer.payOnline');
Route::get('/order/{order}/pay/status', [CustomerController::class, 'payStatus'])->name('customer.payStatus');
Route::post('/order/{table}/resume', [CustomerController::class, 'resume'])->name('customer.resume');

// Xendit Webhook (no auth — harus public)
Route::post('/webhook/xendit', [XenditWebhookController::class, 'handle'])->name('webhook.xendit');

// Kasir
Route::middleware(['auth', 'role:kasir,admin'])->prefix('kasir')->group(function () {
    Route::get('/', [KasirController::class, 'index'])->name('kasir.index');
    Route::post('/payment/{order}', [KasirController::class, 'processPayment'])->name('kasir.payment');
    Route::post('/cancel/{order}', [KasirController::class, 'cancelOrder'])->name('kasir.cancel');
    Route::get('/receipt/{order}', [KasirController::class, 'printReceipt'])->name('kasir.receipt');
});

// Dapur
Route::middleware(['auth', 'role:dapur,admin'])->prefix('dapur')->group(function () {
    Route::get('/', [DapurController::class, 'index'])->name('dapur.index');
    Route::patch('/order/{order}/status', [DapurController::class, 'updateStatus'])->name('dapur.updateStatus');
});

// Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::resource('menu', MenuController::class);
    Route::resource('tables', TableController::class);
    Route::post('tables/{table}/qr', [TableController::class, 'generateQr'])->name('tables.qr');
    Route::resource('users', UserController::class);
    Route::get('reports', [ReportController::class, 'index'])->name('admin.reports');
    Route::get('reports/export/excel', [ReportController::class, 'exportExcel'])->name('admin.reports.excel');
    Route::get('reports/export/pdf', [ReportController::class, 'exportPdf'])->name('admin.reports.pdf');
});
