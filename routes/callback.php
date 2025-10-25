<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CallbackController;
use Illuminate\Support\Facades\Log;

// Callback routes tanpa middleware apapun
Route::group(['middleware' => []], function () {
    Route::post('/callback', [CallbackController::class, 'tripayCallback']);
    Route::post('/webhook', [CallbackController::class, 'tripayCallback']);
    Route::post('/tripay-webhook', [CallbackController::class, 'tripayCallback']);
    
    Route::get('/callback-test', function() {
        return response()->json([
            'success' => true,
            'message' => 'Callback endpoint is accessible',
            'timestamp' => now(),
            'url' => request()->url()
        ]);
    });
    
    Route::post('/callback-test', function(Request $request) {
        Log::info('Test callback received', $request->all());
        return response()->json(['success' => true, 'message' => 'Test callback received']);
    });
}); 