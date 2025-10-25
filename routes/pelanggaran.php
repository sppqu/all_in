<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BKDashboardController;
use App\Http\Controllers\Admin\PelanggaranKategoriController;
use App\Http\Controllers\Admin\PelanggaranController;
use App\Http\Controllers\Admin\PelanggaranSiswaController;
use App\Http\Controllers\Admin\BimbinganKonselingController;

/*
|--------------------------------------------------------------------------
| BK (Bimbingan Konseling) Routes
|--------------------------------------------------------------------------
|
| Routes untuk modul Bimbingan Konseling - Pencatatan pelanggaran siswa
| Akses: /manage/bk
|
*/

// BK Routes - Protected by auth and bk.only middleware
Route::middleware(['auth', 'bk.only'])->prefix('manage/bk')->name('manage.bk.')->group(function () {
    
    // Dashboard BK
    Route::get('/', [BKDashboardController::class, 'index'])->name('dashboard');
    
    // Bimbingan Konseling
    Route::get('/bimbingan-konseling', [BKDashboardController::class, 'bimbinganKonseling'])->name('bimbingan-konseling');
    
    // Pelanggaran Kategori Routes
    Route::resource('pelanggaran-kategori', PelanggaranKategoriController::class);
    
    // Pelanggaran (Master Data) Routes
    Route::prefix('pelanggaran')->name('pelanggaran.')->group(function () {
        Route::get('template', [PelanggaranController::class, 'downloadTemplate'])->name('template');
        Route::post('import', [PelanggaranController::class, 'import'])->name('import');
    });
    Route::resource('pelanggaran', PelanggaranController::class);
    
    // Bimbingan Konseling Routes
    Route::resource('bimbingan', BimbinganKonselingController::class);
    
    // Additional routes for Pelanggaran Siswa (BEFORE resource route)
    Route::prefix('pelanggaran-siswa')->name('pelanggaran-siswa.')->group(function () {
        Route::get('search-siswa', [PelanggaranSiswaController::class, 'searchSiswa'])
            ->name('search-siswa');
        Route::get('report', [PelanggaranSiswaController::class, 'report'])
            ->name('report');
        Route::get('report/export-pdf', [PelanggaranSiswaController::class, 'exportPDF'])
            ->name('report.export-pdf');
        Route::get('{id}/cetak-surat', [PelanggaranSiswaController::class, 'cetakSurat'])
            ->name('cetak-surat');
        Route::post('{pelanggaranSiswa}/approve', [PelanggaranSiswaController::class, 'approve'])
            ->name('approve');
        Route::post('{pelanggaranSiswa}/reject', [PelanggaranSiswaController::class, 'reject'])
            ->name('reject');
    });
    
    // Pelanggaran Siswa Resource Routes (AFTER custom routes)
    Route::resource('pelanggaran-siswa', PelanggaranSiswaController::class);
    
});

