<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\HutangController;

Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::prefix('dashboard')->group(function () {
    // - Admin
    Route::middleware('role:admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('barang', BarangController::class)->except(['show']);
    Route::get('/barang/pdf/download', [BarangController::class, 'generatePdf'])->name('barang.pdf');

    Route::get('/hutang', [HutangController::class, 'index'])->name('hutang.index');
    Route::post('/hutang', [HutangController::class, 'store'])->name('hutang.store');
    Route::post('/hutang/{hutang}/bayar', [HutangController::class, 'bayar'])->name('hutang.bayar');
    Route::delete('/hutang/{hutang}', [HutangController::class, 'destroy'])->name('hutang.destroy');
    });

    // - All Authenticated
    Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
    Route::post('/transaksi', [TransaksiController::class, 'store'])->name('transaksi.store');
    Route::put('/transaksi/{transaksi}', [TransaksiController::class, 'update'])->name('transaksi.update');
    Route::delete('/transaksi/{transaksi}', [TransaksiController::class, 'destroy'])->name('transaksi.destroy');
    Route::get('/transaksi/pdf/{tanggal}', [TransaksiController::class, 'generatePdf'])->name('transaksi.pdf');
    Route::get('/transaksi/data/{tanggal}', [TransaksiController::class, 'getByTanggal'])->name('transaksi.data');

    // - User Hutang
    Route::middleware('auth')->group(function () {
        Route::get('/my-hutang', [HutangController::class, 'myHutang'])->name('hutang.my');
    });
});
