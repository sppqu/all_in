<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PwaController;

// Temporary PWA Routes - add these to web.php when fixed
Route::get('/manifest.json', [PwaController::class, 'manifest']);
Route::get('/offline', [PwaController::class, 'offline']);
