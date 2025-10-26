<?php

use Illuminate\Support\Facades\Route;

// SPMB Frontend Routes
Route::prefix('spmb')->name('spmb.')->group(function () {
    // TEMPORARY: Route untuk reset password emergency (HAPUS SETELAH SELESAI!)
    Route::get('/emergency-reset-passwords', function() {
        $users = \App\Models\SPMBRegistration::all();
        $results = [];
        
        foreach ($users as $user) {
            $defaultPassword = substr($user->phone, -6);
            $user->update(['password' => $defaultPassword]);
            $user->refresh();
            
            $results[] = [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'password' => $defaultPassword,
                'status' => $user->checkPassword($defaultPassword) ? 'SUCCESS' : 'FAILED'
            ];
        }
        
        return response()->json([
            'message' => 'All SPMB passwords reset to default (last 6 digits of phone)',
            'total' => count($results),
            'users' => $results
        ], 200);
    });
    
    Route::get('/', [App\Http\Controllers\SPMBController::class, 'index'])->name('index');
    Route::get('/login', [App\Http\Controllers\SPMBController::class, 'showLogin'])->name('login');
    Route::post('/login', [App\Http\Controllers\SPMBController::class, 'login'])->name('login.post');
    Route::get('/register', [App\Http\Controllers\SPMBController::class, 'showRegister'])->name('register');
    Route::post('/register', [App\Http\Controllers\SPMBController::class, 'register'])->name('register.post');
    Route::get('/forgot-password', [App\Http\Controllers\SPMBController::class, 'showForgotPassword'])->name('forgot-password');
    Route::post('/forgot-password', [App\Http\Controllers\SPMBController::class, 'processForgotPassword'])->name('forgot-password.post');
    Route::get('/reset-password/{token}', [App\Http\Controllers\SPMBController::class, 'showResetPassword'])->name('reset-password');
    Route::post('/reset-password', [App\Http\Controllers\SPMBController::class, 'processResetPassword'])->name('reset-password.post');
    Route::get('/dashboard', [App\Http\Controllers\SPMBController::class, 'dashboard'])->name('dashboard');
    Route::get('/step/{step}', [App\Http\Controllers\SPMBController::class, 'showStep'])->name('step');
    Route::post('/step/2', [App\Http\Controllers\SPMBController::class, 'processStep2'])->name('step2.post');
    Route::post('/step/2/transfer', [App\Http\Controllers\SPMBController::class, 'processStep2Transfer'])->name('step2.transfer');
    Route::post('/step/3', [App\Http\Controllers\SPMBController::class, 'processStep3'])->name('step3.post');
    Route::post('/step/4', [App\Http\Controllers\SPMBController::class, 'processStep4'])->name('step4.post');
    Route::post('/step/5', [App\Http\Controllers\SPMBController::class, 'processStep5'])->name('step5.post');
    Route::post('/step/5/transfer', [App\Http\Controllers\SPMBController::class, 'processStep5Transfer'])->name('step5.transfer');
    
    // Specific routes MUST come before parameterized routes
    Route::get('/payment/success', [App\Http\Controllers\SPMBController::class, 'paymentSuccess'])->name('payment.success');
    Route::post('/payment/callback', [App\Http\Controllers\SPMBController::class, 'paymentCallback'])->name('payment.callback');
    
    // Parameterized route comes last
    Route::get('/payment/{id}', [App\Http\Controllers\SPMBController::class, 'showPayment'])->name('payment');
    Route::post('/logout', [App\Http\Controllers\SPMBController::class, 'logout'])->name('logout');
    Route::get('/debug', function() {
        return view('spmb.debug');
    })->name('debug');
    Route::get('/fix-step', [App\Http\Controllers\SPMBController::class, 'fixStep'])->name('fix-step');
    Route::get('/skip-step2', [App\Http\Controllers\SPMBController::class, 'skipStep2'])->name('skip-step2');
    Route::get('/force-skip-to-step3', [App\Http\Controllers\SPMBController::class, 'forceSkipToStep3'])->name('force-skip-to-step3');
    Route::get('/download-form', [App\Http\Controllers\SPMBController::class, 'downloadForm'])->name('download-form');
    Route::get('/documents/{id}/download', [App\Http\Controllers\SPMBController::class, 'downloadDocument'])->name('download-document');
    Route::get('/documents/{id}/view', [App\Http\Controllers\SPMBController::class, 'viewDocument'])->name('view-document');
    Route::delete('/documents/{id}', [App\Http\Controllers\SPMBController::class, 'deleteDocument'])->name('delete-document');
});

// Test route without middleware
Route::get('/test-payment-proof/{id}', [App\Http\Controllers\Admin\SPMBAdminController::class, 'testPaymentProof'])->name('test-payment-proof-no-auth');

// SPMB Admin Routes (within manage prefix)
Route::prefix('manage/spmb')->name('manage.spmb.')->middleware(['auth', 'spmb.admin'])->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\SPMBAdminController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\Admin\SPMBAdminController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\Admin\SPMBAdminController::class, 'store'])->name('store');
    
    // SPMB Settings Routes (must be before /{id} route)
    Route::get('/settings', [App\Http\Controllers\Admin\SPMBSettingsController::class, 'index'])->name('settings');
    Route::get('/settings/create', [App\Http\Controllers\Admin\SPMBSettingsController::class, 'create'])->name('settings.create');
    Route::post('/settings', [App\Http\Controllers\Admin\SPMBSettingsController::class, 'store'])->name('settings.store');
    Route::get('/settings/{id}', [App\Http\Controllers\Admin\SPMBSettingsController::class, 'show'])->name('settings.show');
    Route::get('/settings/{id}/edit', [App\Http\Controllers\Admin\SPMBSettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings/{id}', [App\Http\Controllers\Admin\SPMBSettingsController::class, 'update'])->name('settings.update');
    Route::delete('/settings/{id}', [App\Http\Controllers\Admin\SPMBSettingsController::class, 'destroy'])->name('settings.destroy');
    Route::post('/settings/{id}/toggle-registration', [App\Http\Controllers\Admin\SPMBSettingsController::class, 'toggleRegistration'])->name('settings.toggle-registration');
    
    // SPMB Kejuruan Routes
    Route::get('/kejuruan', [App\Http\Controllers\Admin\SPMBKejuruanController::class, 'index'])->name('kejuruan.index');
    Route::get('/kejuruan/create', [App\Http\Controllers\Admin\SPMBKejuruanController::class, 'create'])->name('kejuruan.create');
    Route::post('/kejuruan', [App\Http\Controllers\Admin\SPMBKejuruanController::class, 'store'])->name('kejuruan.store');
    Route::get('/kejuruan/{id}', [App\Http\Controllers\Admin\SPMBKejuruanController::class, 'show'])->name('kejuruan.show');
    Route::get('/kejuruan/{id}/edit', [App\Http\Controllers\Admin\SPMBKejuruanController::class, 'edit'])->name('kejuruan.edit');
    Route::put('/kejuruan/{id}', [App\Http\Controllers\Admin\SPMBKejuruanController::class, 'update'])->name('kejuruan.update');
    Route::delete('/kejuruan/{id}', [App\Http\Controllers\Admin\SPMBKejuruanController::class, 'destroy'])->name('kejuruan.destroy');
    Route::post('/kejuruan/{id}/toggle-status', [App\Http\Controllers\Admin\SPMBKejuruanController::class, 'toggleStatus'])->name('kejuruan.toggle-status');
    Route::post('/kejuruan/bulk-action', [App\Http\Controllers\Admin\SPMBKejuruanController::class, 'bulkAction'])->name('kejuruan.bulk-action');
    
    // SPMB Wave Routes
    Route::get('/waves', [App\Http\Controllers\Admin\SPMBWaveController::class, 'index'])->name('waves.index');
    Route::get('/waves/create', [App\Http\Controllers\Admin\SPMBWaveController::class, 'create'])->name('waves.create');
    Route::post('/waves', [App\Http\Controllers\Admin\SPMBWaveController::class, 'store'])->name('waves.store');
    Route::get('/waves/{id}', [App\Http\Controllers\Admin\SPMBWaveController::class, 'show'])->name('waves.show');
    Route::get('/waves/{id}/edit', [App\Http\Controllers\Admin\SPMBWaveController::class, 'edit'])->name('waves.edit');
    Route::put('/waves/{id}', [App\Http\Controllers\Admin\SPMBWaveController::class, 'update'])->name('waves.update');
    Route::delete('/waves/{id}', [App\Http\Controllers\Admin\SPMBWaveController::class, 'destroy'])->name('waves.destroy');
    Route::post('/waves/{id}/toggle-status', [App\Http\Controllers\Admin\SPMBWaveController::class, 'toggleStatus'])->name('waves.toggle-status');
    
    // SPMB Additional Fees Routes
    Route::get('/additional-fees', [App\Http\Controllers\Admin\SPMBAdditionalFeeController::class, 'index'])->name('additional-fees.index');
    Route::get('/additional-fees/create', [App\Http\Controllers\Admin\SPMBAdditionalFeeController::class, 'create'])->name('additional-fees.create');
    Route::post('/additional-fees', [App\Http\Controllers\Admin\SPMBAdditionalFeeController::class, 'store'])->name('additional-fees.store');
    Route::get('/additional-fees/{id}', [App\Http\Controllers\Admin\SPMBAdditionalFeeController::class, 'show'])->name('additional-fees.show');
    Route::get('/additional-fees/{id}/edit', [App\Http\Controllers\Admin\SPMBAdditionalFeeController::class, 'edit'])->name('additional-fees.edit');
    Route::put('/additional-fees/{id}', [App\Http\Controllers\Admin\SPMBAdditionalFeeController::class, 'update'])->name('additional-fees.update');
    Route::delete('/additional-fees/{id}', [App\Http\Controllers\Admin\SPMBAdditionalFeeController::class, 'destroy'])->name('additional-fees.destroy');
    Route::post('/additional-fees/{id}/toggle-status', [App\Http\Controllers\Admin\SPMBAdditionalFeeController::class, 'toggleStatus'])->name('additional-fees.toggle-status');
    Route::get('/waves/{waveId}/additional-fees', [App\Http\Controllers\Admin\SPMBAdditionalFeeController::class, 'manageWaveFees'])->name('waves.additional-fees');
    Route::post('/waves/{waveId}/additional-fees', [App\Http\Controllers\Admin\SPMBAdditionalFeeController::class, 'saveWaveFees'])->name('waves.additional-fees.save');
    
    // Other SPMB routes
    Route::get('/payments', [App\Http\Controllers\Admin\SPMBAdminController::class, 'payments'])->name('payments');
    Route::get('/export/registrations', [App\Http\Controllers\Admin\SPMBAdminController::class, 'exportRegistrations'])->name('export-registrations');
    Route::get('/export/payments-pdf', [App\Http\Controllers\Admin\SPMBAdminController::class, 'exportPaymentsPDF'])->name('export-payments-pdf');
    Route::post('/bulk-action', [App\Http\Controllers\Admin\SPMBAdminController::class, 'bulkAction'])->name('bulk-action');
    Route::put('/documents/{id}/status', [App\Http\Controllers\Admin\SPMBAdminController::class, 'updateDocumentStatus'])->name('update-document-status');
        Route::get('/documents/{id}/download', [App\Http\Controllers\Admin\SPMBAdminController::class, 'downloadDocument'])->name('download-document');
        Route::get('/documents/{id}/view', [App\Http\Controllers\Admin\SPMBAdminController::class, 'viewDocument'])->name('view-document');
        Route::get('/{id}/print-form', [App\Http\Controllers\Admin\SPMBAdminController::class, 'printForm'])->name('print-form');
        Route::get('/{id}/edit-documents', [App\Http\Controllers\Admin\SPMBAdminController::class, 'editDocuments'])->name('edit-documents');
        Route::put('/{id}/update-documents', [App\Http\Controllers\Admin\SPMBAdminController::class, 'updateDocuments'])->name('update-documents');
        Route::get('/{id}/edit-payment-registration', [App\Http\Controllers\Admin\SPMBAdminController::class, 'editPaymentRegistration'])->name('edit-payment-registration');
        Route::get('/{id}/edit-payment-spmb', [App\Http\Controllers\Admin\SPMBAdminController::class, 'editPaymentSpmb'])->name('edit-payment-spmb');
        Route::put('/payments/{id}', [App\Http\Controllers\Admin\SPMBAdminController::class, 'updatePayment'])->name('update-payment');
        Route::delete('/payments/{id}', [App\Http\Controllers\Admin\SPMBAdminController::class, 'deletePayment'])->name('delete-payment');
        Route::get('/payments/{id}/print-invoice', [App\Http\Controllers\Admin\SPMBAdminController::class, 'printInvoice'])->name('print-invoice');
        Route::get('/export-pdf', [App\Http\Controllers\Admin\SPMBAdminController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/export-excel', [App\Http\Controllers\Admin\SPMBAdminController::class, 'exportExcel'])->name('export-excel');
        Route::post('/{id}/create-payment-spmb', [App\Http\Controllers\Admin\SPMBAdminController::class, 'createPaymentSpmb'])->name('create-payment-spmb');
        Route::get('/payments/{id}/view-proof', [App\Http\Controllers\Admin\SPMBAdminController::class, 'viewPaymentProof'])->name('view-payment-proof');
        Route::get('/payment-proof/{id}', [App\Http\Controllers\Admin\SPMBAdminController::class, 'getPaymentProof'])->name('get-payment-proof');
        Route::get('/test-payment-proof/{id}', [App\Http\Controllers\Admin\SPMBAdminController::class, 'testPaymentProof'])->name('test-payment-proof');
        Route::post('/payments/{id}/verify', [App\Http\Controllers\Admin\SPMBAdminController::class, 'verifyPayment'])->name('verify-payment');
        Route::post('/payments/{id}/reject', [App\Http\Controllers\Admin\SPMBAdminController::class, 'rejectPayment'])->name('reject-payment');
        
        // SPMB Form Settings Routes
        Route::get('/form-settings', [App\Http\Controllers\Admin\SPMBFormSettingsController::class, 'index'])->name('form-settings.index');
        Route::get('/form-settings/create', [App\Http\Controllers\Admin\SPMBFormSettingsController::class, 'create'])->name('form-settings.create');
        Route::post('/form-settings', [App\Http\Controllers\Admin\SPMBFormSettingsController::class, 'store'])->name('form-settings.store');
        Route::get('/form-settings/{id}', [App\Http\Controllers\Admin\SPMBFormSettingsController::class, 'show'])->name('form-settings.show');
        Route::get('/form-settings/{id}/edit', [App\Http\Controllers\Admin\SPMBFormSettingsController::class, 'edit'])->name('form-settings.edit');
        Route::put('/form-settings/{id}', [App\Http\Controllers\Admin\SPMBFormSettingsController::class, 'update'])->name('form-settings.update');
        Route::delete('/form-settings/{id}', [App\Http\Controllers\Admin\SPMBFormSettingsController::class, 'destroy'])->name('form-settings.destroy');
        Route::post('/form-settings/{id}/toggle-status', [App\Http\Controllers\Admin\SPMBFormSettingsController::class, 'toggleStatus'])->name('form-settings.toggle-status');
        Route::post('/form-settings/update-order', [App\Http\Controllers\Admin\SPMBFormSettingsController::class, 'updateOrder'])->name('form-settings.update-order');
        
        // SPMB Transfer to Students Routes
        Route::get('/transfer-to-students', [App\Http\Controllers\Admin\SPMBAdminController::class, 'transferToStudents'])->name('transfer-to-students');
        Route::post('/transfer-to-students', [App\Http\Controllers\Admin\SPMBAdminController::class, 'processTransferToStudents'])->name('transfer-to-students.process');
    
    // Dynamic routes (must be last)
    Route::get('/{id}', [App\Http\Controllers\Admin\SPMBAdminController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [App\Http\Controllers\Admin\SPMBAdminController::class, 'edit'])->name('edit');
    Route::put('/{id}', [App\Http\Controllers\Admin\SPMBAdminController::class, 'update'])->name('update');
    Route::put('/{id}/status', [App\Http\Controllers\Admin\SPMBAdminController::class, 'updateStatus'])->name('update-status');
    Route::put('/{id}/registration-status', [App\Http\Controllers\Admin\SPMBSettingsController::class, 'updateRegistrationStatus'])->name('update-registration-status');
    Route::delete('/{id}', [App\Http\Controllers\Admin\SPMBAdminController::class, 'destroy'])->name('destroy');
});
