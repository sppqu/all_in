<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Jurnal\JurnalSiswaController;
use App\Http\Controllers\Jurnal\JurnalGuruController;

/*
|--------------------------------------------------------------------------
| E-Jurnal Harian 7KAIH Routes
|--------------------------------------------------------------------------
*/

// Siswa Routes
Route::middleware(['auth'])->prefix('jurnal/siswa')->name('jurnal.siswa.')->group(function () {
    Route::get('/', [JurnalSiswaController::class, 'index'])->name('index');
    Route::get('/create', [JurnalSiswaController::class, 'create'])->name('create');
    Route::post('/', [JurnalSiswaController::class, 'store'])->name('store');
    Route::get('/{id}', [JurnalSiswaController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [JurnalSiswaController::class, 'edit'])->name('edit');
    Route::put('/{id}', [JurnalSiswaController::class, 'update'])->name('update');
    Route::delete('/{id}', [JurnalSiswaController::class, 'destroy'])->name('destroy');
    Route::get('/rekap/bulanan', [JurnalSiswaController::class, 'rekapBulanan'])->name('rekap-bulanan');
});

// Guru Routes
Route::middleware(['auth'])->prefix('jurnal/guru')->name('jurnal.guru.')->group(function () {
    Route::get('/', [JurnalGuruController::class, 'index'])->name('index');
    Route::get('/laporan-siswa', [JurnalGuruController::class, 'laporanSiswa'])->name('laporan-siswa');
    Route::get('/laporan-siswa-pdf', [JurnalGuruController::class, 'laporanSiswaPdf'])->name('laporan-siswa-pdf');
    Route::get('/laporan-kelas', [JurnalGuruController::class, 'laporanKelas'])->name('laporan-kelas');
    Route::get('/laporan-kelas-pdf', [JurnalGuruController::class, 'laporanKelasPdf'])->name('laporan-kelas-pdf');
    Route::get('/siswa/{siswa_id}/rekap', [JurnalGuruController::class, 'rekapPerSiswa'])->name('rekap-siswa');
    Route::get('/kelas/{kelas_id}/rekap', [JurnalGuruController::class, 'rekapPerKelas'])->name('rekap-kelas');
    Route::get('/{id}/edit', [JurnalGuruController::class, 'edit'])->name('edit');
    Route::put('/{id}', [JurnalGuruController::class, 'update'])->name('update');
    Route::get('/{id}', [JurnalGuruController::class, 'show'])->name('show');
    Route::post('/{id}/verify', [JurnalGuruController::class, 'verify'])->name('verify');
    Route::post('/{id}/revision', [JurnalGuruController::class, 'requestRevision'])->name('revision');
});

