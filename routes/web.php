<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;



// Include PWA routes
require __DIR__.'/activation.php';
require __DIR__.'/pwa.php';

// Include SPMB routes
require __DIR__.'/spmb.php';

// Include Pelanggaran routes
require __DIR__.'/pelanggaran.php';

// Include E-Jurnal Harian 7KAIH routes
require __DIR__.'/jurnal.php';

// Include E-Perpustakaan routes
require __DIR__.'/library.php';

use App\Http\Controllers\PeriodController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\GeneralSettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OnlinePaymentController;
use App\Http\Controllers\StudentAuthController;
use App\Http\Controllers\CallbackController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TabunganController;
use App\Http\Controllers\AccountCodeController;

use App\Http\Controllers\BulkWhatsAppController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ArusKasController;
use App\Http\Controllers\RealisasiPosController;

use App\Http\Controllers\LaporanTunggakanSiswaController;
// TripayService removed - using iPaymu now


use Illuminate\Http\Request;

// ============================================================================
// TRIPAY CALLBACK - MOVED TO routes/api.php
// URL: /api/manage/tripay/callback
// ============================================================================

// ============================================================================
// MANUAL ADDON ACTIVATION (FOR ADMIN) - OUTSIDE manage GROUP
// ============================================================================
Route::get('/activate/{userId}/{slug}', [App\Http\Controllers\AddonController::class, 'manualActivate'])
    ->name('addon.manual.activate');
Route::get('/deactivate/{userId}/{slug}', [App\Http\Controllers\AddonController::class, 'manualDeactivate'])
    ->name('addon.manual.deactivate');

// ============================================================================
// Manage Routes - Pindah ke atas untuk menghindari konflik
// ============================================================================
Route::prefix('manage')->name('manage.')->middleware('auth', 'check.subscription')->group(function () {
    // Manage Login - Langsung ke OTP
    Route::get('/', function () {
        return redirect()->route('otp.request');
    })->name('login')->withoutMiddleware('check.subscription');
    
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->forget(['admin_last_activity']);
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('otp.request')->with('success', 'Anda telah berhasil logout.');
    })->name('logout')->withoutMiddleware('check.subscription');
    
    // Manage Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard')->middleware('session.timeout');
    
    // Manage Settings
    Route::get('/general-setting', [GeneralSettingController::class, 'index'])->name('general.setting')->middleware('auth', 'session.timeout');
    // User profile
    Route::middleware(['auth'])->group(function () {
        Route::get('/profile', [\App\Http\Controllers\UserProfileController::class, 'edit'])->name('profile.edit');
        Route::post('/profile', [\App\Http\Controllers\UserProfileController::class, 'update'])->name('profile.update');
    });
    Route::post('/general-setting', [GeneralSettingController::class, 'update'])->name('general.setting.update')->middleware('auth', 'session.timeout');
    Route::post('/general-setting/gateway', [GeneralSettingController::class, 'updateGateway'])->name('general.setting.gateway')->middleware('auth', 'session.timeout');
    Route::post('/general-setting/rekening', [GeneralSettingController::class, 'updateRekening'])->name('general.setting.rekening')->middleware('auth', 'session.timeout');
    
    // Manage Users
    Route::resource('users', UserController::class)->middleware('auth');
    Route::get('/role-menu', [UserController::class, 'roleMenu'])->name('users.role-menu')->middleware('auth');
    Route::post('/role-menu', [UserController::class, 'saveRoleMenu'])->name('users.role-menu.save')->middleware('auth');
    
    // Manage Online Payments
    Route::get('/online-payments', [OnlinePaymentController::class, 'index'])->name('online-payments')->middleware('auth');
    Route::get('/online-payments/{id}', [OnlinePaymentController::class, 'show'])->name('online-payments.show')->middleware('auth');
    Route::post('/online-payments/{id}/approve', [OnlinePaymentController::class, 'approve'])->name('online-payments.approve')->middleware('auth');
    Route::post('/online-payments/{id}/reject', [OnlinePaymentController::class, 'reject'])->name('online-payments.reject')->middleware('auth');
    
    // Testing route untuk update bulan status (hanya development)
    Route::post('/online-payments/test-update-bulan/{bulanId}', [OnlinePaymentController::class, 'testUpdateBulanStatus'])->name('online-payments.test-update-bulan')->middleware('auth');
    
    // Testing route untuk reprocess successful transfer (hanya development)
    Route::post('/online-payments/reprocess-transfer/{transferId}', [OnlinePaymentController::class, 'reprocessSuccessfulTransfer'])->name('online-payments.reprocess-transfer')->middleware('auth');
    
    // Manage Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index')->middleware('auth');
    Route::get('/notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread')->middleware('auth');
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read')->middleware('auth');
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all')->middleware('auth');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy')->middleware('auth');
    
    // Manage Tabungan
    Route::get('/tabungan/{id}/setoran', [TabunganController::class, 'setoran'])->name('tabungan.setoran')->middleware('auth');
    Route::post('/tabungan/{id}/store-setoran', [TabunganController::class, 'storeSetoran'])->name('tabungan.store-setoran')->middleware('auth');
    Route::get('/tabungan/{id}/penarikan', [TabunganController::class, 'penarikan'])->name('tabungan.penarikan')->middleware('auth');
    Route::post('/tabungan/{id}/store-penarikan', [TabunganController::class, 'storePenarikan'])->name('tabungan.store-penarikan')->middleware('auth');
    Route::get('/tabungan/{id}/riwayat', [TabunganController::class, 'riwayat'])->name('tabungan.riwayat')->middleware('auth');
    Route::get('/tabungan/{id}/export-mutasi', [TabunganController::class, 'exportMutasi'])->name('tabungan.export-mutasi')->middleware('auth');
    Route::get('/tabungan/{id}/cetak-kuitansi', [TabunganController::class, 'cetakKuitansi'])->name('tabungan.cetak-kuitansi')->middleware('auth');
    Route::resource('tabungan', TabunganController::class)->middleware('auth');
    
    // Manage Subscription/Billing
    Route::get('/subscription', [App\Http\Controllers\SubscriptionController::class, 'index'])->name('subscription.index')->middleware('auth');
    Route::get('/subscription/plans', [App\Http\Controllers\SubscriptionController::class, 'showPlans'])->name('subscription.plans')->middleware('auth');
    Route::post('/subscription/create', [App\Http\Controllers\SubscriptionController::class, 'createSubscription'])->name('subscription.create')->middleware('auth');
    Route::get('/subscription/payment/{subscription_id}', [App\Http\Controllers\SubscriptionController::class, 'payment'])->name('subscription.payment')->middleware('auth');
    Route::post('/subscription/cancel', [App\Http\Controllers\SubscriptionController::class, 'cancelSubscription'])->name('subscription.cancel')->middleware('auth');
    Route::get('/subscription/check-status', [App\Http\Controllers\SubscriptionController::class, 'checkSubscriptionStatus'])->name('subscription.check-status')->middleware('auth');
    Route::post('/subscription/check-payment', [App\Http\Controllers\SubscriptionController::class, 'checkPaymentStatus'])->name('subscription.check-payment')->middleware('auth');
    Route::get('/subscription/notifications', [App\Http\Controllers\SubscriptionController::class, 'getSubscriptionNotifications'])->name('subscription.notifications')->middleware('auth');
    Route::get('/subscription/premium-features', [App\Http\Controllers\SubscriptionController::class, 'premiumFeatures'])->name('subscription.premium-features')->middleware('auth');
    Route::get('/subscription/invoice/{invoice_id}/download', [App\Http\Controllers\SubscriptionController::class, 'downloadInvoice'])->name('subscription.download-invoice')->middleware('auth');

    // Add-ons Routes
    Route::get('/addons', [App\Http\Controllers\AddonController::class, 'index'])->name('addons.index')->middleware('auth');
    Route::get('/addons/{slug}', [App\Http\Controllers\AddonController::class, 'show'])->name('addons.show')->middleware('auth');
    Route::post('/addons/{slug}/purchase', [App\Http\Controllers\AddonController::class, 'purchase'])->name('addons.purchase')->middleware('auth');
    Route::match(['get', 'post'], '/addons/callback', [App\Http\Controllers\AddonController::class, 'callback'])->name('addons.callback');
    Route::post('/addons/check-payment', [App\Http\Controllers\AddonController::class, 'checkPaymentStatus'])->name('addons.check-payment')->middleware('auth');
    Route::get('/addons/invoice/{userAddonId}/download', [App\Http\Controllers\AddonController::class, 'downloadInvoice'])->name('addons.download-invoice')->middleware('auth');
    Route::get('/addons/{slug}/check', [App\Http\Controllers\AddonController::class, 'checkUserAddon'])->name('addons.check')->middleware('auth');
    Route::post('/addons/refresh-status', [App\Http\Controllers\AddonController::class, 'refreshAddonStatus'])->name('addons.refresh-status')->middleware('auth');
    
    // Manage Periods
    Route::resource('periods', PeriodController::class)->middleware('auth');
    
    // Manage Classes
    Route::resource('classes', ClassController::class)->middleware('auth');
    
    // Manage Students
    Route::resource('students', StudentController::class)->middleware('auth');
    Route::get('/students/{id}/bills', [StudentController::class, 'bills'])->name('students.bills')->middleware('auth');
    Route::get('/students/{id}/payments', [StudentController::class, 'payments'])->name('students.payments')->middleware('auth');
    Route::get('/students/{id}/receipts', [StudentController::class, 'receipts'])->name('students.receipts')->middleware('auth');
    Route::get('/students/{id}/tabungan', [StudentController::class, 'tabungan'])->name('students.tabungan')->middleware('auth');
    
    // Manage Payment Positions
    Route::resource('pos', PosController::class)->middleware('auth');
    
    // Manage Payments
    Route::get('/payments/{id}/bulanan', [PaymentController::class, 'bulanan'])->name('payments.bulanan')->middleware('auth');
    Route::get('/payments/{id}/bebas', [PaymentController::class, 'bebas'])->name('payments.bebas')->middleware('auth');
    
    // Manage Laporan Perpos
    Route::get('/laporan-perpos', [PaymentController::class, 'laporanPerpos'])->name('laporan-perpos')->middleware('auth');
    Route::post('/export-laporan-perpos', [PaymentController::class, 'exportLaporanPerpos'])->name('export-laporan-perpos')->middleware('auth');
    Route::post('/export-laporan-perpos-excel', [PaymentController::class, 'exportLaporanPerposExcel'])->name('export-laporan-perpos-excel')->middleware('auth');
    // Alternate GET endpoint to support downloads without POST redirects
    Route::get('/export-laporan-perpos-excel', [PaymentController::class, 'exportLaporanPerposExcel'])->name('export-laporan-perpos-excel-get')->middleware('auth');
    
    // Manage Laporan Perkelas
    Route::get('/laporan-perkelas', [PaymentController::class, 'laporanPerkelas'])->name('laporan-perkelas')->middleware('auth');
    Route::post('/export-laporan-perkelas', [PaymentController::class, 'exportLaporanPerkelas'])->name('export-laporan-perkelas')->middleware('auth');
    
    // Manage Laporan Rekapitulasi
    Route::get('/laporan-rekapitulasi', [PaymentController::class, 'laporanRekapitulasi'])->name('laporan-rekapitulasi')->middleware('auth');
    Route::post('/export-laporan-rekapitulasi', [PaymentController::class, 'exportLaporanRekapitulasi'])->name('export-laporan-rekapitulasi')->middleware('auth');
    
    // Manage Laporan Realisasi Pos
    Route::get('/laporan/realisasi-pos', [RealisasiPosController::class, 'index'])->name('laporan.realisasi-pos')->middleware('auth');
    Route::get('/laporan/realisasi-pos/export-excel', [RealisasiPosController::class, 'exportExcel'])->name('laporan.realisasi-pos.export-excel')->middleware('auth');
    Route::get('/laporan/realisasi-pos/export-pdf', [RealisasiPosController::class, 'exportPdf'])->name('laporan.realisasi-pos.export-pdf')->middleware('auth');
    
    // Manage Laporan Tunggakan Siswa
    Route::get('/laporan/tunggakan-siswa', [LaporanTunggakanSiswaController::class, 'index'])->name('laporan.tunggakan-siswa')->middleware('auth');
    Route::post('/laporan/tunggakan-siswa/export-pdf', [LaporanTunggakanSiswaController::class, 'exportPdf'])->name('laporan.tunggakan-siswa.export-pdf')->middleware('auth');
    // Export PDF tunggakan per siswa
    Route::post('/laporan/tunggakan-siswa/export-pdf-student', [LaporanTunggakanSiswaController::class, 'exportPdfStudent'])->name('laporan.tunggakan-siswa.export-pdf-student')->middleware('auth');
    

    
    // Manage Receipts
    Route::resource('receipts', ReceiptController::class)->middleware('auth');
    Route::get('/receipts/{id}/print', [ReceiptController::class, 'print'])->name('receipts.print')->middleware('auth');
    
    // Manage Account Codes
    Route::resource('account-codes', AccountCodeController::class)->middleware('auth');
    Route::patch('/account-codes/{accountCode}/toggle-status', [AccountCodeController::class, 'toggleStatus'])->name('account-codes.toggle-status')->middleware('auth');
    Route::get('/api/account-codes', [AccountCodeController::class, 'getAccountCodes'])->name('account-codes.api')->middleware('auth');
    
    // Manage Arus Kas
    Route::get('/arus-kas', [ArusKasController::class, 'index'])->name('arus-kas.index')->middleware('auth');
    Route::get('/arus-kas/export', [ArusKasController::class, 'export'])->name('arus-kas.export')->middleware('auth');
    
    // Manage Realisasi Pos
    Route::get('/realisasi-pos', [RealisasiPosController::class, 'index'])->name('realisasi-pos.index')->middleware('auth');
    Route::get('/realisasi-pos/export', [RealisasiPosController::class, 'export'])->name('realisasi-pos.export')->middleware('auth');
    
    // Manage Rekapitulasi Tabungan
    Route::get('/rekapitulasi-tabungan', [\App\Http\Controllers\RekapitulasiTabunganController::class, 'index'])->name('rekapitulasi-tabungan.index')->middleware('auth');
    Route::get('/rekapitulasi-tabungan/export', [\App\Http\Controllers\RekapitulasiTabunganController::class, 'export'])->name('rekapitulasi-tabungan.export')->middleware('auth');
    
    // Manage Laporan Tunggakan Siswa
    Route::get('/laporan-tunggakan-siswa', [LaporanTunggakanSiswaController::class, 'index'])->name('laporan-tunggakan-siswa.index')->middleware('auth');
    Route::get('/laporan-tunggakan-siswa/export', [LaporanTunggakanSiswaController::class, 'export'])->name('laporan-tunggakan-siswa.export')->middleware('auth');
    
    // Manage Bulk WhatsApp
    Route::get('/bulk-whatsapp', [BulkWhatsAppController::class, 'index'])->name('bulk-whatsapp.index')->middleware('auth');
    Route::post('/bulk-whatsapp/bills', [BulkWhatsAppController::class, 'getBills'])->name('bulk-whatsapp.bills')->middleware('auth');
    Route::post('/bulk-whatsapp/send', [BulkWhatsAppController::class, 'sendBulkBills'])->name('bulk-whatsapp.send')->middleware('auth');
    Route::post('/bulk-whatsapp/send-mass-message', [BulkWhatsAppController::class, 'sendMassMessage'])->name('bulk-whatsapp.send-mass-message')->middleware('auth');
    Route::post('/bulk-whatsapp/send-consolidated', [BulkWhatsAppController::class, 'sendConsolidatedBills'])->name('bulk-whatsapp.send-consolidated')->middleware('auth');
    
    // Midtrans removed - using iPaymu now
    
    // Routes untuk Akuntansi Baru
    Route::prefix('accounting')->name('accounting.')->middleware('auth')->group(function () {
        // Daftar Kas
        Route::get('/kas', [\App\Http\Controllers\Accounting\KasController::class, 'index'])->name('kas.index');
        Route::get('/kas/create', [\App\Http\Controllers\Accounting\KasController::class, 'create'])->name('kas.create');
        Route::post('/kas', [\App\Http\Controllers\Accounting\KasController::class, 'store'])->name('kas.store');
        Route::get('/kas/{kas}/edit', [\App\Http\Controllers\Accounting\KasController::class, 'edit'])->name('kas.edit');
        Route::put('/kas/{kas}', [\App\Http\Controllers\Accounting\KasController::class, 'update'])->name('kas.update');
        Route::delete('/kas/{kas}', [\App\Http\Controllers\Accounting\KasController::class, 'destroy'])->name('kas.destroy');

        // Metode Pembayaran
        Route::get('/payment-methods', [\App\Http\Controllers\Accounting\PaymentMethodController::class, 'index'])->name('payment-methods.index');
        Route::get('/payment-methods/create', [\App\Http\Controllers\Accounting\PaymentMethodController::class, 'create'])->name('payment-methods.create');
        Route::post('/payment-methods', [\App\Http\Controllers\Accounting\PaymentMethodController::class, 'store'])->name('payment-methods.store');
        Route::get('/payment-methods/{paymentMethod}/edit', [\App\Http\Controllers\Accounting\PaymentMethodController::class, 'edit'])->name('payment-methods.edit');
        Route::put('/payment-methods/{paymentMethod}', [\App\Http\Controllers\Accounting\PaymentMethodController::class, 'update'])->name('payment-methods.update');
        Route::delete('/payment-methods/{paymentMethod}', [\App\Http\Controllers\Accounting\PaymentMethodController::class, 'destroy'])->name('payment-methods.destroy');

        // Pos Penerimaan (Default dari NAMA POS Pembayaran)
        Route::resource('receipt-pos', \App\Http\Controllers\Accounting\ReceiptPosController::class);
        Route::post('/receipt-pos/transaksi', [\App\Http\Controllers\Accounting\ReceiptPosController::class, 'storeTransaksi'])->name('receipt-pos.store-transaksi');
        Route::post('/receipt-pos/transaksi/{id}/update', [\App\Http\Controllers\Accounting\ReceiptPosController::class, 'updateTransaksi'])->name('receipt-pos.update-transaksi');
        Route::delete('/receipt-pos/transaksi/{id}', [\App\Http\Controllers\Accounting\ReceiptPosController::class, 'destroyTransaksi'])->name('receipt-pos.destroy-transaksi');
        
        // Pos View routes (untuk lihat semua pos dengan pendapatan)
        Route::get('/pos-view', [\App\Http\Controllers\Accounting\ReceiptPosController::class, 'posView'])->name('pos-view');
        
        // Test route untuk debugging receipt-pos
        Route::get('/receipt-pos-test', function() {
            return response()->json([
                'message' => 'Receipt-pos test route working',
                'csrf_token' => csrf_token(),
                'session_id' => session()->getId(),
                'auth_check' => auth()->check(),
                'user' => auth()->user() ? auth()->user()->name : 'Not authenticated'
            ]);
        })->name('receipt-pos.test');

        // Pos Pengeluaran - Gunakan controller baru
        Route::get('/expense-pos', [\App\Http\Controllers\Accounting\ExpenseTransactionController::class, 'index'])->name('expense-pos.index');
        Route::get('/expense-pos/create', [\App\Http\Controllers\Accounting\ExpensePosController::class, 'create'])->name('expense-pos.create');
        Route::post('/expense-pos', [\App\Http\Controllers\Accounting\ExpensePosController::class, 'store'])->name('expense-pos.store');
        Route::get('/expense-pos/{expensePos}/edit', [\App\Http\Controllers\Accounting\ExpensePosController::class, 'edit'])->name('expense-pos.edit');
        Route::put('/expense-pos/{expensePos}', [\App\Http\Controllers\Accounting\ExpensePosController::class, 'update'])->name('expense-pos.update');
        Route::delete('/expense-pos/{expensePos}', [\App\Http\Controllers\Accounting\ExpensePosController::class, 'destroy'])->name('expense-pos.destroy');

        // Transaksi Pengeluaran
        Route::get('/expense-transactions', [\App\Http\Controllers\Accounting\ExpenseTransactionController::class, 'index'])->name('expense-transactions.index');
        Route::post('/expense-transactions', [\App\Http\Controllers\Accounting\ExpenseTransactionController::class, 'store'])->name('expense-transactions.store');
        Route::get('/expense-transactions/create', [\App\Http\Controllers\Accounting\ExpenseTransactionController::class, 'create'])->name('expense-transactions.create');

        Route::get('/expense-transactions/{id}/edit', [\App\Http\Controllers\Accounting\ExpenseTransactionController::class, 'edit'])->name('expense-transactions.edit');
        Route::get('/expense-transactions/{id}/details', [\App\Http\Controllers\Accounting\ExpenseTransactionController::class, 'getTransactionDetails'])->name('expense-transactions.details');
        Route::get('/expense-transactions/{id}/print', [\App\Http\Controllers\Accounting\ExpenseTransactionController::class, 'print'])->name('expense-transactions.print');
        Route::put('/expense-transactions/{id}', [\App\Http\Controllers\Accounting\ExpenseTransactionController::class, 'update'])->name('expense-transactions.update');
        Route::delete('/expense-transactions/{id}', [\App\Http\Controllers\Accounting\ExpenseTransactionController::class, 'destroy'])->name('expense-transactions.destroy');
        Route::get('/expense-transactions/get-next-number', [\App\Http\Controllers\Accounting\ExpenseTransactionController::class, 'getNextNumber'])->name('expense-transactions.get-next-number');
        Route::post('/expense-transactions/store-expense-pos', [\App\Http\Controllers\Accounting\ExpenseTransactionController::class, 'storeExpensePos'])->name('expense-transactions.store-expense-pos');
        Route::get('/expense-transactions/get-expense-pos', [\App\Http\Controllers\Accounting\ExpenseTransactionController::class, 'getExpensePos'])->name('expense-transactions.get-expense-pos');
        Route::get('/expense-transactions/get-expense-pos-detail/{id}', [\App\Http\Controllers\Accounting\ExpenseTransactionController::class, 'getExpensePosDetail'])->name('expense-transactions.get-expense-pos-detail');
        Route::put('/expense-transactions/update-expense-pos/{id}', [\App\Http\Controllers\Accounting\ExpenseTransactionController::class, 'updateExpensePos'])->name('expense-transactions.update-expense-pos');
        Route::delete('/expense-transactions/delete-expense-pos/{id}', [\App\Http\Controllers\Accounting\ExpenseTransactionController::class, 'deleteExpensePos'])->name('expense-transactions.delete-expense-pos');
        Route::get('/expense-transactions/{id}', [\App\Http\Controllers\Accounting\ExpenseTransactionController::class, 'show'])->name('expense-transactions.show');

        // Pindah Buku Kas
        Route::get('/cash-transfer', [\App\Http\Controllers\Accounting\CashTransferController::class, 'index'])->name('cash-transfer.index');
        Route::get('/cash-transfer/create', [\App\Http\Controllers\Accounting\CashTransferController::class, 'create'])->name('cash-transfer.create');
        Route::post('/cash-transfer', [\App\Http\Controllers\Accounting\CashTransferController::class, 'store'])->name('cash-transfer.store');
        Route::get('/cash-transfer/{cashTransfer}/edit', [\App\Http\Controllers\Accounting\CashTransferController::class, 'edit'])->name('cash-transfer.edit');
        Route::put('/cash-transfer/{cashTransfer}', [\App\Http\Controllers\Accounting\CashTransferController::class, 'update'])->name('cash-transfer.update');
        Route::delete('/cash-transfer/{cashTransfer}', [\App\Http\Controllers\Accounting\CashTransferController::class, 'destroy'])->name('cash-transfer.destroy');

        // Arus Kas
        Route::get('/cashflow', [\App\Http\Controllers\Accounting\CashflowController::class, 'index'])->name('cashflow.index');
        Route::post('/cashflow/laporan', [\App\Http\Controllers\Accounting\CashflowController::class, 'laporan'])->name('cashflow.laporan');
        Route::get('/cashflow/export', [\App\Http\Controllers\Accounting\CashflowController::class, 'export'])->name('cashflow.export');
        Route::get('/cashflow/export-excel', [\App\Http\Controllers\Accounting\CashflowController::class, 'exportExcel'])->name('cashflow.export-excel');
    });
    
});

// OTP Routes
Route::get('/otp/login', [App\Http\Controllers\OtpController::class, 'showRequestForm'])->name('otp.request');
Route::post('/otp/login', [App\Http\Controllers\OtpController::class, 'requestOtp'])->name('otp.send');
Route::get('/otp/verify', [App\Http\Controllers\OtpController::class, 'showVerifyForm'])->name('otp.verify');
Route::post('/otp/verify', [App\Http\Controllers\OtpController::class, 'verifyOtp'])->name('otp.verify.submit');
Route::get('/otp/resend', [App\Http\Controllers\OtpController::class, 'resendOtp'])->name('otp.resend');

// Student Routes - dengan middleware yang benar
Route::prefix('student')->name('student.')->group(function () {
    // Redirect otomatis dari /student ke /student/login
    Route::get('/', function () {
        return redirect()->route('student.login');
    });
    
    // Auth routes
    Route::get('/login', [StudentAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [StudentAuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [StudentAuthController::class, 'logout'])->name('logout');
    Route::get('/logout', [StudentAuthController::class, 'logoutGet'])->name('logout.get');
    
    // Protected routes
    Route::middleware('student.auth')->group(function () {
        Route::get('/dashboard', [StudentAuthController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [StudentAuthController::class, 'profile'])->name('profile');
        Route::get('/identity.json', [StudentAuthController::class, 'identityJson'])->name('identity.json');
        Route::post('/update-password', [StudentAuthController::class, 'updatePassword'])->name('update-password');
        Route::post('/clear-cache', [StudentAuthController::class, 'clearCache'])->name('clear-cache');
        Route::post('/refresh-csrf', [StudentAuthController::class, 'refreshCsrfToken'])->name('refresh-csrf');
        
        // Bills
        Route::get('/bills', [StudentAuthController::class, 'bills'])->name('bills');
        Route::get('/cart', [StudentAuthController::class, 'cart'])->name('cart');
        
        // Tabungan
        Route::get('/tabungan', [StudentAuthController::class, 'tabungan'])->name('tabungan');
        
        // E-Perpustakaan
        Route::get('/library', [StudentAuthController::class, 'library'])->name('library');
        
        // Online Payment
        Route::get('/online-payment', [StudentAuthController::class, 'onlinePayment'])->name('online-payment');
        Route::get('/payment-form/{studentId}/{billType}/{billId}', [StudentAuthController::class, 'paymentForm'])->name('payment-form');
        Route::post('/payment/process', [StudentAuthController::class, 'processPayment'])->name('payment.process');
        Route::get('/payment/history', [StudentAuthController::class, 'paymentHistory'])->name('payment.history');
        Route::get('/payment/detail/{id}', [StudentAuthController::class, 'paymentDetail'])->name('payment.detail');
        Route::get('/payment/receipt/{id}', [StudentAuthController::class, 'downloadReceipt'])->name('payment.receipt');
        Route::get('/receipt/detail/{id}/{type}', [StudentAuthController::class, 'receiptDetail'])->name('receipt.detail');

        // E-Jurnal Harian 7KAIH
        Route::prefix('jurnal')->name('jurnal.')->group(function () {
            Route::get('/', [StudentAuthController::class, 'jurnalIndex'])->name('index');
            Route::get('/create', [StudentAuthController::class, 'jurnalCreate'])->name('create');
            Route::post('/store', [StudentAuthController::class, 'jurnalStore'])->name('store');
            Route::get('/show/{id}', [StudentAuthController::class, 'jurnalShow'])->name('show');
            Route::get('/edit/{id}', [StudentAuthController::class, 'jurnalEdit'])->name('edit');
            Route::post('/update/{id}', [StudentAuthController::class, 'jurnalUpdate'])->name('update');
            Route::delete('/delete/{id}', [StudentAuthController::class, 'jurnalDelete'])->name('delete');
            Route::get('/rekap', [StudentAuthController::class, 'jurnalRekap'])->name('rekap');
            Route::get('/rekap/{month}/{year}', [StudentAuthController::class, 'jurnalRekapBulanan'])->name('rekap.bulanan');
        });

        // Bimbingan Konseling (BK)
        Route::prefix('bk')->name('bk.')->group(function () {
            Route::get('/', [StudentAuthController::class, 'bkIndex'])->name('index');
            Route::get('/pelanggaran/{id}', [StudentAuthController::class, 'bkShowPelanggaran'])->name('show-pelanggaran');
            Route::get('/bimbingan/create', [StudentAuthController::class, 'bkCreateBimbingan'])->name('create-bimbingan');
            Route::post('/bimbingan/store', [StudentAuthController::class, 'bkStoreBimbingan'])->name('store-bimbingan');
        });
        
        // Bank Transfer
        Route::post('/bank-transfer/prepare', [StudentAuthController::class, 'prepareBankTransfer'])->name('bank-transfer.prepare');
        Route::get('/bank-transfer', [StudentAuthController::class, 'bankTransfer'])->name('bank-transfer');
        Route::post('/bank-transfer/process', [StudentAuthController::class, 'processBankTransfer'])->name('bank-transfer.process');
        
        // Tabungan Payment
        Route::post('/tabungan/process', [StudentAuthController::class, 'processTabunganPayment'])->name('tabungan.process');
        

    });
});

// Callback Routes - Tripay & Midtrans REMOVED (using iPaymu now)
Route::prefix('callback')->name('callback.')->group(function () {
    // Route::post('/tripay', [CallbackController::class, 'tripayCallback'])->name('tripay');
    // Route::post('/midtrans', [CallbackController::class, 'midtransCallback'])->name('midtrans');
    Route::post('/payment', [CallbackController::class, 'paymentCallback'])->name('payment');
});

// Main Routes
Route::get('/', function () {
    return redirect()->route('student.login');
})->name('home');

Route::get('/welcome', function () {
    return view('welcome');
})->name('welcome');

// Period (Tahun Pelajaran) Routes
Route::resource('periods', PeriodController::class)->middleware('auth');
Route::post('periods/{period}/set-active', [PeriodController::class, 'setActive'])->name('periods.set-active')->middleware('auth');

// Class (Kelas) Routes
Route::resource('classes', ClassController::class)->middleware('auth');

// Student (Peserta Didik) Routes
Route::delete('/bulk-delete-students', [StudentController::class, 'bulkDelete'])->name('students.bulk-delete');
Route::post('/bulk-delete-students', [StudentController::class, 'bulkDelete'])->name('students.bulk-delete-post');
Route::get('/test-bulk', function() { return 'Bulk route works!'; })->name('test.bulk');
Route::resource('students', StudentController::class)->middleware('auth');

// Import routes for students
Route::get('/students-import', function () {
    return view('students.import');
})->name('students.import-form')->middleware('auth');

Route::post('/students-import', [StudentController::class, 'import'])->name('students.import')->middleware('auth');
Route::get('/students-download-template', [StudentController::class, 'downloadTemplate'])->name('students.download-template')->middleware('auth');
Route::get('/students-export', [StudentController::class, 'export'])->name('students.export')->middleware('auth');
Route::get('/students-move-class', [StudentController::class, 'moveClass'])->name('students.move-class')->middleware('auth');
Route::post('/students-get-by-class', [StudentController::class, 'getStudentsByClass'])->name('students.get-by-class')->middleware('auth');
Route::post('/students-process-move-class', [StudentController::class, 'processMoveClass'])->name('students.process-move-class')->middleware('auth');
Route::get('/students-graduate', [StudentController::class, 'graduate'])->name('students.graduate')->middleware('auth');
Route::post('/students-process-graduate', [StudentController::class, 'processGraduate'])->name('students.process-graduate')->middleware('auth');

// Reset password routes
Route::post('/students/{id}/reset-password', [StudentController::class, 'resetPassword'])->name('students.reset-password')->middleware('auth');
Route::post('/students/reset-password-massal', [StudentController::class, 'resetPasswordMassal'])->name('students.reset-password-massal')->middleware('auth');

// Debug routes
Route::get('/cek-db', function () {
    return DB::connection()->getDatabaseName();
});

Route::get('/debug/student/{id}', function($id) {
    $student = \App\Models\Student::find($id);
    return response()->json($student);
});

Route::get('/debug/users', function() {
    $users = \App\Models\User::all();
    return response()->json($users);
});

Route::get('/debug/payment-structure', function() {
    $payments = \App\Models\Payment::with('pos')->get();
    return response()->json($payments);
});

// Payment Gateway Test Routes
// Test Tripay Route - REMOVED
/*
Route::get('/test-tripay', function() {
    try {
        $tripayService = new \App\Services\TripayService();
        $channels = $tripayService->getPaymentChannels();
        
        return response()->json([
            'success' => true,
            'channels' => $channels,
            'message' => 'TripayService berfungsi dengan baik'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'message' => 'TripayService error'
        ]);
    }
})->name('test.tripay');
*/

Route::get('/tripay-return', function(Request $request) {
    $reference = $request->get('reference');
    $status = $request->get('status');
    
    if ($reference) {
        // Cek status transaksi di database
        $transfer = DB::table('transfer')->where('reference', $reference)->first();
        
        if ($transfer) {
            if ($status === 'PAID') {
                return redirect()->route('student.payment.history')
                    ->with('success', 'Pembayaran berhasil! Status akan diperbarui dalam beberapa menit.');
            } else {
                return redirect()->route('student.payment.history')
                    ->with('warning', 'Pembayaran belum selesai. Anda dapat melanjutkan pembayaran dari halaman riwayat.');
            }
        }
    }
    
    return redirect()->route('student.payment.history')
        ->with('info', 'Kembali dari halaman pembayaran.');
});

// Midtrans Payment Routes - REMOVED (using iPaymu now)

Route::get('/test-payment-gateway', function() {
    return view('test-payment-gateway');
})->name('test.payment.gateway');

// Test route untuk payment channels tanpa auth
Route::get('/test-payment-channels', [App\Http\Controllers\OnlinePaymentController::class, 'getPaymentChannels']);

// Test route untuk payment channels cart
Route::get('/test-payment-channels-cart', function() {
    try {
        $tripayService = new App\Services\TripayService();
        $channels = $tripayService->getPaymentChannels();
        return response()->json($channels);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
});

// Test route untuk process payment tanpa auth
Route::post('/test-process-payment', [App\Http\Controllers\OnlinePaymentController::class, 'processPayment']);

// Test Tripay Transaction Route - REMOVED
/*
Route::post('/test-tripay-transaction', function() {
    try {
        $tripayService = new TripayService();
        $testData = [
            'method' => 'BRIVA',
            'merchant_ref' => 'PG-TEST-' . time(),
            'amount' => 100000,
            'customer_name' => 'Test User',
            'customer_email' => 'test@sppqu.com',
            'customer_phone' => '08123456789',
            'order_items' => [
                [
                    'name' => 'Test Tagihan',
                    'price' => 100000,
                    'quantity' => 1
                ]
            ],
            'return_url' => route('tripay.return'),
            'callback_url' => route('online-payment.callback')
        ];
        $result = $tripayService->createTransaction($testData);
        return response()->json($result);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
});
*/

// Test route untuk StudentAuthController processPayment
Route::post('/test-student-payment', function() {
    try {
        // Simulate request data
        $requestData = [
            'student_id' => 1,
            'bill_type' => 'bulanan',
            'bill_id' => 1,
            'amount' => 100000,
            'payment_type' => 'realtime',
            'payment_method' => 'BRIVA',
            'description' => 'Test payment'
        ];
        $request = new \Illuminate\Http\Request();
        $request->merge($requestData);
        $controller = new \App\Http\Controllers\StudentAuthController();
        $response = $controller->processPayment($request);
        
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            return $response;
        } elseif ($response instanceof \Illuminate\Http\RedirectResponse) {
            return response()->json([
                'success' => false,
                'redirect' => $response->getTargetUrl(),
                'message' => 'Controller returned a redirect response'
            ]);
        } else {
        return response()->json([
                'success' => false,
                'message' => 'Controller returned an unexpected response type'
        ]);
        }
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'message' => 'StudentAuthController processPayment test failed'
        ]);
    }
})->name('test.student.payment');

// Route untuk Tripay Cart - REMOVED
/*
Route::post('/test-tripay-transaction-from-cart', function(Request $request) {
    try {
        // Check if student is logged in
        if (!session('is_student')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $studentId = session('student_id');
        $cartItems = json_decode($request->input('cart_items'), true);
        $totalAmount = $request->input('total_amount');
        $paymentMethod = $request->input('payment_method', 'BRIVA');
        
        if (!$cartItems || !$totalAmount) {
            return response()->json([
                'success' => false,
                'message' => 'Cart items atau total amount tidak valid'
            ]);
        }

        // Generate merchant reference
        $merchantRef = 'PG-CART-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $orderItems = [];
        foreach ($cartItems as $item) {
            $orderItems[] = [
                'name' => $item['name'] ?? 'Tagihan',
                'price' => isset($item['amount']) ? (int)preg_replace('/[^0-9]/', '', $item['amount']) : (int)$totalAmount,
                'quantity' => $item['quantity'] ?? 1
            ];
        }

        $tripayService = new App\Services\TripayService();
        $result = $tripayService->createTransaction([
            'method' => $paymentMethod,
            'merchant_ref' => $merchantRef,
            'amount' => (int)$totalAmount,
            'customer_name' => 'Test User Cart',
            'customer_email' => 'testcart@sppqu.com',
            'customer_phone' => '08123456789',
            'order_items' => $orderItems,
            'return_url' => route('tripay.return'),
            'callback_url' => route('online-payment.callback')
        ]);

        if (!$result || !isset($result['success']) || !$result['success']) {
            throw new \Exception('Gagal membuat transaksi Tripay: ' . ($result['message'] ?? 'Unknown error'));
        }

        DB::beginTransaction();

        // Insert to transfer table
        $transferId = DB::table('transfer')->insertGetId([
            'student_id' => $studentId,
            'detail' => 'Pembayaran Cart via Payment Gateway',
            'status' => 0, // Pending
            'confirm_pay' => (int)$totalAmount,
            'reference' => $result['data']['reference'] ?? $merchantRef,
            'merchantRef' => $merchantRef,
            'checkout_url' => $result['data']['checkout_url'] ?? null,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Insert to transfer_detail table for each cart item
        foreach ($cartItems as $item) {
            $amount = (int)str_replace(['Rp ', '.', ','], '', $item['amount']);
            
            DB::table('transfer_detail')->insert([
                'transfer_id' => $transferId,
                'payment_type' => $item['type'] === 'bulanan' ? 1 : 2,
                'bulan_id' => $item['type'] === 'bulanan' ? $item['id'] : null,
                'bebas_id' => $item['type'] === 'bebas' ? $item['id'] : null,
                'desc' => $item['name'],
                'subtotal' => $amount,
                'is_tabungan' => 0 // Default value for cart items
            ]);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil diproses',
            'checkout_url' => $result['data']['checkout_url'] ?? null,
            'data' => $result['data'] ?? [],
            'reference' => $result['data']['reference'] ?? $merchantRef
        ]);

    } catch (\Exception $e) {
        DB::rollback();
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
});
*/

// Webhook Test Routes
Route::get('/webhook-test', function() {
    return response()->json([
        'message' => 'Webhook test route working',
        'timestamp' => now()
    ]);
});

Route::get('/callback-test', function() {
    return response()->json([
        'success' => true,
        'message' => 'Callback endpoint is accessible',
        'timestamp' => now(),
        'url' => request()->url()
    ]);
});

Route::post('/callback-test', function(Request $request) {
    Log::info('Simple callback test received', $request->all());
    return response()->json(['success' => true, 'message' => 'Callback received']);
})->name('callback.test');

// WhatsApp Test Routes
Route::get('/test-whatsapp-notification', function() {
    return response()->json([
        'message' => 'WhatsApp notification test route working',
        'timestamp' => now()
    ]);
});

// Pindah Kelas Routes
Route::get('/pindah-kelas', function () {
    return view('students.pindah-kelas');
})->name('pindah-kelas')->middleware('auth');

// Redirect Routes untuk Menu Utama
Route::get('/tahun-pelajaran', function () {
    return redirect()->route('periods.index');
})->name('tahun-pelajaran');

Route::get('/kelas', function () {
    return redirect()->route('classes.index');
})->name('kelas');

Route::get('/peserta-didik', function () {
    return redirect()->route('students.index');
})->name('peserta-didik');

// API Routes
Route::get('/api/account-codes', [AccountCodeController::class, 'getAccountCodes'])->name('account-codes.api')->middleware('auth');
Route::get('/api/students/search', [PaymentController::class, 'searchStudent'])->name('api.students.search')->middleware('auth');
Route::get('/api/students/{id}/detail', [PaymentController::class, 'studentDetail'])->name('api.students.detail')->middleware('auth');
Route::get('/api/students/{id}/tagihan', [PaymentController::class, 'studentTagihan'])->name('api.students.tagihan')->middleware('auth');
Route::get('/api/students/{id}/tabungan', [PaymentController::class, 'studentTabungan'])->name('api.students.tabungan')->middleware('auth');
Route::get('/api/students/{studentId}/transactions', [PaymentController::class, 'getTransactionHistory'])->name('api.students.transactions')->middleware('auth');
Route::get('/api/check-saldo', function (Request $request) {
    $posId = $request->get('pos_id');
    $amount = $request->get('amount');
    
    if (!$posId || !$amount) {
        return response()->json([
            'valid' => false,
            'message' => 'Parameter tidak lengkap'
        ]);
    }
    
    // Hitung saldo pos pembayaran - debit/kredit tables removed
    $totalPenerimaan = 0; // debit table removed
    $totalPengeluaran = 0; // kredit table removed
    $saldoPos = 0;
    
    if ($saldoPos < $amount) {
        return response()->json([
            'valid' => false,
            'message' => 'Saldo tidak mencukupi',
            'saldo' => $saldoPos,
            'required' => $amount
        ]);
    }
    
    return response()->json([
        'valid' => true,
        'message' => 'Saldo mencukupi',
        'saldo' => $saldoPos,
        'required' => $amount
    ]);
});

// Laporan Routes
Route::get('/laporan-rekapitulasi', [PaymentController::class, 'laporanRekapitulasi'])->name('laporan-rekapitulasi')->middleware('auth');
Route::post('/export-laporan-rekapitulasi', [PaymentController::class, 'exportLaporanRekapitulasi'])->name('export-laporan-rekapitulasi')->middleware('auth');
Route::get('/rekapitulasi-tabungan', [App\Http\Controllers\RekapitulasiTabunganController::class, 'index'])->name('rekapitulasi-tabungan.index');
Route::get('/rekapitulasi-tabungan/export-pdf', [App\Http\Controllers\RekapitulasiTabunganController::class, 'exportPdf'])->name('rekapitulasi-tabungan.export-pdf');
Route::get('/rekapitulasi-tabungan/export-excel', [App\Http\Controllers\RekapitulasiTabunganController::class, 'exportExcel'])->name('rekapitulasi-tabungan.export-excel');
Route::get('/rekapitulasi-tabungan/detail/{studentId}', [App\Http\Controllers\RekapitulasiTabunganController::class, 'getDetailTransaksiApi'])->name('rekapitulasi-tabungan.detail');

// Rekapitulasi Tabungan Routes (Admin)
Route::middleware(['auth', 'session.timeout'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/rekapitulasi-tabungan', [App\Http\Controllers\RekapitulasiTabunganController::class, 'index'])->name('rekapitulasi-tabungan.index');
    Route::get('/rekapitulasi-tabungan/export-pdf', [App\Http\Controllers\RekapitulasiTabunganController::class, 'exportPdf'])->name('rekapitulasi-tabungan.export-pdf');
    Route::get('/rekapitulasi-tabungan/export-excel', [App\Http\Controllers\RekapitulasiTabunganController::class, 'exportExcel'])->name('rekapitulasi-tabungan.export-excel');
    Route::get('/rekapitulasi-tabungan/detail/{studentId}', [App\Http\Controllers\RekapitulasiTabunganController::class, 'getDetailTransaksiApi'])->name('rekapitulasi-tabungan.detail');
});

// Subscription Callback Route (tanpa middleware CSRF)
Route::match(['GET', 'POST'], '/subscription/callback', [App\Http\Controllers\SubscriptionController::class, 'callback'])->name('subscription.callback');

// AJAX Test Routes
Route::get('/test-ajax-page', function() {
    return view('students.test-ajax');
})->name('test.ajax-page');

Route::get('/test-data', function() {
    $classes = App\Models\ClassModel::all();
    $students = App\Models\Student::where('student_status', 1)->get();
    
    $result = [
        'classes' => $classes->map(function($c) {
            return ['id' => $c->id, 'name' => $c->class_name];
        }),
        'students' => $students->map(function($s) {
            return [
                'id' => $s->id,
                'nis' => $s->student_nis,
                'name' => $s->student_full_name,
                'class_id' => $s->class_class_id,
                'status' => $s->student_status
            ];
        })
    ];
    
    return response()->json([
        'message' => 'Test data route working',
        'data' => $result
    ]);
});

// Test route for debugging
Route::post('/test-ajax', function() {
    return response()->json(['message' => 'AJAX works!', 'data' => request()->all()]);
})->name('test.ajax');

Route::get('/test-ajax-page', function() {
    return view('students.test-ajax');
})->name('test.ajax.page');

// Filter History Test Routes
Route::get('/test-filter-history', function(Request $request) {
    return response()->json([
        'success' => true,
        'message' => 'Filter test route accessible',
        'filters' => $request->only(['status', 'payment_type', 'date_from', 'date_to', 'search', 'per_page']),
        'url' => $request->url(),
        'query' => $request->query()
    ]);
})->name('test.filter.history');

Route::get('/test-outside', function() {
    return 'Test route di luar group manage berhasil!';
});



// Payment Form Routes
Route::get('/payment-form/{studentId}/{billType}/{billId}', [OnlinePaymentController::class, 'paymentForm'])->name('form');

// Bulanan Routes (API-style under web middleware)
Route::get('api/bulanan/{student_id}/{payment_id}', [PaymentController::class, 'getTarifBulananSiswa'])->middleware('auth');
Route::put('api/bulanan/{student_id}/{payment_id}', [PaymentController::class, 'updateTarifBulananSiswa'])->middleware('auth');
Route::delete('api/bulanan/{student_id}/{payment_id}', [PaymentController::class, 'deleteTarifBulananSiswa'])->middleware('auth');
Route::post('api/bulanan/siswa/{payment_id}', [PaymentController::class, 'storeTarifBulananSiswa'])->middleware('auth');
Route::post('api/bulanan/bulk-delete', [PaymentController::class, 'bulkDeleteTarifBulanan'])->middleware('auth');

// Payment Routes Group
Route::prefix('payment')->name('payment.')->middleware('auth')->group(function () {
    Route::get('/pembayaran-tunai', [PaymentController::class, 'cashPayment'])->name('cash');
    Route::get('/', [PaymentController::class, 'index'])->name('index');
    Route::get('/create', [PaymentController::class, 'create'])->name('create');
    Route::post('/', [PaymentController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [PaymentController::class, 'edit'])->name('edit');
    Route::put('/{id}', [PaymentController::class, 'update'])->name('update');
    Route::get('/{id}/setting', [PaymentController::class, 'setting'])->name('setting');
    Route::delete('/{id}', [PaymentController::class, 'destroy'])->name('destroy');
    
    // Payment Tarif Routes
    Route::post('/{id}/store-tarif-bulanan', [PaymentController::class, 'storeTarifBulanan'])->name('store-tarif-bulanan');
    Route::post('/{id}/store-tarif-bebas', [PaymentController::class, 'storeTarifBebas'])->name('store-tarif-bebas');
    Route::post('/{id}/store-tarif-bebas-siswa', [PaymentController::class, 'storeTarifBebasSiswa'])->name('store-tarif-bebas-siswa');
    Route::post('/{id}/store-tarif-bulanan-siswa', [PaymentController::class, 'storeTarifBulananSiswa'])->name('store-tarif-bulanan-siswa');
    Route::put('/{id}/update-tarif-bulanan', [PaymentController::class, 'updateTarifBulananMasal'])->name('update-tarif-bulanan');

    // Multi cash payment (bulanan + bebas in one transaction)
    Route::post('/multi-cash', [PaymentController::class, 'processMultiCashPayment'])->name('multi-cash');
    Route::put('/{id}/update-tarif-bebas', [PaymentController::class, 'updateTarifBebas'])->name('update-tarif-bebas');
    Route::put('/{id}/bulk-update-tarif-bebas', [PaymentController::class, 'bulkUpdateTarifBebas'])->name('bulk-update-tarif-bebas');
    Route::put('/{id}/update-tarif-bulanan-siswa', [PaymentController::class, 'updateTarifBulananSiswa'])->name('update-tarif-bulanan-siswa');
    Route::put('/{id}/update-tarif-bebas-siswa', [PaymentController::class, 'updateTarifBebasSiswa'])->name('update-tarif-bebas-siswa');
    Route::delete('/{id}/delete-tarif-bulanan', [PaymentController::class, 'bulkDeleteTarifBulanan'])->name('delete-tarif-bulanan');
    Route::delete('/{id}/delete-tarif-bebas', [PaymentController::class, 'bulkDeleteTarifBebas'])->name('delete-tarif-bebas');
    Route::delete('/{id}/delete-tarif-bulanan-siswa', [PaymentController::class, 'deleteTarifBulananSiswa'])->name('delete-tarif-bulanan-siswa');
    Route::delete('/{id}/delete-tarif-bebas-siswa', [PaymentController::class, 'deleteTarifBebas'])->name('delete-tarif-bebas-siswa');
});

// API Payment Routes
Route::prefix('api/payment')->name('api.payment.')->middleware('auth')->group(function () {
    Route::post('/process', [PaymentController::class, 'processPayment'])->name('process');
    Route::post('/bebas/process', [PaymentController::class, 'processBebasPayment'])->name('bebas.process');
    Route::post('/bebas/process-flexible', [PaymentController::class, 'processBebasPaymentFlexible'])->name('bebas.process-flexible');
    Route::post('/delete-transaction', [PaymentController::class, 'deleteTransaction'])->name('delete-transaction');
});

// Laporan Payment Routes - Sudah dipindah ke route group manage
// Route::get('/laporan-perpos', [PaymentController::class, 'laporanPerpos'])->name('laporan-perpos')->middleware('auth');
// Route::post('/export-laporan-perpos', [PaymentController::class, 'exportLaporanPerpos'])->name('export-laporan-perpos')->middleware('auth');
// Route::get('/laporan-perkelas', [PaymentController::class, 'laporanPerkelas'])->name('laporan-perkelas')->middleware('auth');
// Route::post('/export-laporan-perkelas', [PaymentController::class, 'exportLaporanPerkelas'])->name('export-laporan-perkelas')->middleware('auth');
// Route::get('/laporan-rekapitulasi', [PaymentController::class, 'laporanRekapitulasi'])->name('laporan-rekapitulasi')->middleware('auth');
// Route::post('/export-laporan-rekapitulasi', [PaymentController::class, 'exportLaporanRekapitulasi'])->name('export-laporan-rekapitulasi')->middleware('auth');

// Laporan Tunggakan Siswa Routes
Route::get('/laporan/tunggakan-siswa', [LaporanTunggakanSiswaController::class, 'index'])->name('laporan.tunggakan-siswa')->middleware('auth');
Route::post('/laporan/tunggakan-siswa/export-pdf', [LaporanTunggakanSiswaController::class, 'exportPdf'])->name('laporan.tunggakan-siswa.export-pdf')->middleware('auth');
Route::post('/laporan/tunggakan-siswa/export-excel', [LaporanTunggakanSiswaController::class, 'exportExcel'])->name('laporan.tunggakan-siswa.export-excel')->middleware('auth');

// Bulk WhatsApp Routes
Route::get('/bulk-whatsapp', [BulkWhatsAppController::class, 'index'])->name('bulk-whatsapp.index')->middleware('auth');
Route::post('/bulk-whatsapp/bills', [BulkWhatsAppController::class, 'getBills'])->name('bulk-whatsapp.bills')->middleware('auth');
Route::post('/bulk-whatsapp/send', [BulkWhatsAppController::class, 'sendBulkBills'])->name('bulk-whatsapp.send')->middleware('auth');
Route::post('/bulk-whatsapp/send-mass-message', [BulkWhatsAppController::class, 'sendMassMessage'])->name('bulk-whatsapp.send-mass-message')->middleware('auth');
Route::post('/bulk-whatsapp/send-consolidated', [BulkWhatsAppController::class, 'sendConsolidatedBills'])->name('bulk-whatsapp.send-consolidated')->middleware('auth');

// Test Tunggakan Data Routes
Route::get('/test-tunggakan-data', function() {
    $bulananCount = DB::table('bulan')
        ->where('bulan_bill', '>', 0)
        ->where('bulan_pay', '<', 'bulan_bill')
        ->count();
    
    $bebasCount = DB::table('bebas_pay')
        ->where('bebas_pay_bill', '>', 0)
        ->where('bebas_pay_pay', '<', 'bebas_pay_bill')
        ->count();
    
    $sampleBulanan = DB::table('bulan as b')
        ->join('students as s', 'b.student_student_id', '=', 's.student_id')
        ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
        ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
        ->where('b.bulan_bill', '>', 0)
        ->where('b.bulan_pay', '<', 'b.bulan_bill')
        ->select('s.student_full_name', 'pos.pos_name', 'b.bulan_bill', 'b.bulan_pay', DB::raw('(b.bulan_bill - b.bulan_pay) as tunggakan'))
        ->limit(5)
        ->get();
    
    $sampleBebas = DB::table('bebas_pay as bp')
        ->join('students as s', 'bp.student_student_id', '=', 's.student_id')
        ->join('bebas as be', 'bp.bebas_bebas_id', '=', 'be.bebas_id')
        ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
        ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
        ->where('bp.bebas_pay_bill', '>', 0)
        ->where('bp.bebas_pay_pay', '<', 'bp.bebas_pay_bill')
        ->select('s.student_full_name', 'pos.pos_name', 'bp.bebas_pay_bill', 'bp.bebas_pay_pay', DB::raw('(bp.bebas_pay_bill - bp.bebas_pay_pay) as tunggakan'))
        ->limit(5)
        ->get();
    
    return response()->json([
        'bulanan_count' => $bulananCount,
        'bebas_count' => $bebasCount,
        'sample_bulanan' => $sampleBulanan,
        'sample_bebas' => $sampleBebas
    ]);
})->name('test.tunggakan-data');

// Addons Routes
Route::get('/addons', [App\Http\Controllers\AddonController::class, 'index'])->name('addons.index')->middleware('auth');
Route::get('/addons/{slug}', [App\Http\Controllers\AddonController::class, 'show'])->name('addons.show')->middleware('auth');
Route::post('/addons/{slug}/purchase', [App\Http\Controllers\AddonController::class, 'purchase'])->name('addons.purchase')->middleware('auth');
Route::match(['get', 'post'], '/addons/callback', [App\Http\Controllers\AddonController::class, 'callback'])->name('addons.callback');
Route::get('/addons/{slug}/check', [App\Http\Controllers\AddonController::class, 'checkUserAddon'])->name('addons.check')->middleware('auth');
Route::post('/addons/refresh-status', [App\Http\Controllers\AddonController::class, 'refreshAddonStatus'])->name('addons.refresh-status')->middleware('auth');

// Subscription Routes
Route::get('/subscription', [App\Http\Controllers\SubscriptionController::class, 'index'])->name('subscription.index')->middleware('auth');
Route::get('/subscription/plans', [App\Http\Controllers\SubscriptionController::class, 'showPlans'])->name('subscription.plans')->middleware('auth');
Route::post('/subscription/create', [App\Http\Controllers\SubscriptionController::class, 'createSubscription'])->name('subscription.create')->middleware('auth');
Route::get('/subscription/payment/{subscription_id}', [App\Http\Controllers\SubscriptionController::class, 'payment'])->name('subscription.payment')->middleware('auth');
Route::post('/subscription/cancel', [App\Http\Controllers\SubscriptionController::class, 'cancelSubscription'])->name('subscription.cancel')->middleware('auth');
Route::get('/subscription/check-status', [App\Http\Controllers\SubscriptionController::class, 'checkSubscriptionStatus'])->name('subscription.check-status')->middleware('auth');
Route::get('/subscription/notifications', [App\Http\Controllers\SubscriptionController::class, 'getSubscriptionNotifications'])->name('subscription.notifications')->middleware('auth');
Route::get('/subscription/premium-features', [App\Http\Controllers\SubscriptionController::class, 'premiumFeatures'])->name('subscription.premium-features')->middleware('auth');
Route::get('/subscription/invoice/{invoice_id}/download', [App\Http\Controllers\SubscriptionController::class, 'downloadInvoice'])->name('subscription.download-invoice')->middleware('auth');

// Tabungan Routes
Route::get('/tabungan/{id}/setoran', [TabunganController::class, 'setoran'])->name('tabungan.setoran')->middleware('auth');
Route::post('/tabungan/{id}/store-setoran', [TabunganController::class, 'storeSetoran'])->name('tabungan.store-setoran')->middleware('auth');
Route::get('/tabungan/{id}/penarikan', [TabunganController::class, 'penarikan'])->name('tabungan.penarikan')->middleware('auth');
Route::post('/tabungan/{id}/store-penarikan', [TabunganController::class, 'storePenarikan'])->name('tabungan.store-penarikan')->middleware('auth');
Route::get('/tabungan/{id}/riwayat', [TabunganController::class, 'riwayat'])->name('tabungan.riwayat')->middleware('auth');
Route::get('/tabungan/{id}/export-mutasi', [TabunganController::class, 'exportMutasi'])->name('tabungan.export-mutasi')->middleware('auth');
Route::resource('tabungan', TabunganController::class)->middleware('auth');

// Notifications Routes
Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index')->middleware('auth');
Route::get('/notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread')->middleware('auth');
Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read')->middleware('auth');
Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all')->middleware('auth');
Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy')->middleware('auth');

// Online Payments Routes
Route::get('/online-payments', [OnlinePaymentController::class, 'index'])->name('online-payments')->middleware('auth');
Route::get('/online-payments/{id}', [OnlinePaymentController::class, 'show'])->name('online-payments.show')->middleware('auth');
Route::post('/online-payments/{id}/approve', [OnlinePaymentController::class, 'approve'])->name('online-payments.approve')->middleware('auth');
Route::post('/online-payments/{id}/reject', [OnlinePaymentController::class, 'reject'])->name('online-payments.reject')->middleware('auth');

// Users Routes
Route::resource('users', UserController::class)->middleware('auth');
Route::get('/role-menu', [UserController::class, 'roleMenu'])->name('users.role-menu')->middleware('auth');
Route::post('/role-menu', [UserController::class, 'saveRoleMenu'])->name('users.role-menu.save')->middleware('auth');

// General Setting Routes
Route::get('/general-setting', [GeneralSettingController::class, 'index'])->name('general-setting')->middleware('auth', 'session.timeout');
Route::post('/general-setting', [GeneralSettingController::class, 'update'])->name('general-setting.update')->middleware('auth', 'session.timeout');

// Dashboard Routes
Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard')->middleware('auth', 'session.timeout');

// Logout Routes
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->forget(['admin_last_activity']);
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('otp.request')->with('success', 'Anda telah berhasil logout.');
})->name('logout');

// Pos Routes
Route::resource('pos', PosController::class)->middleware('auth');

// Realisasi Pos Routes
Route::get('/laporan/realisasi-pos', [RealisasiPosController::class, 'index'])->name('laporan.realisasi-pos')->middleware('auth');
Route::get('/laporan/realisasi-pos/export-excel', [RealisasiPosController::class, 'exportExcel'])->name('laporan.realisasi-pos.export-excel')->middleware('auth');
Route::get('/laporan/realisasi-pos/export-pdf', [RealisasiPosController::class, 'exportPdf'])->name('laporan.realisasi-pos.export-pdf')->middleware('auth');

// Test Realisasi Pos Routes
Route::get('/test-realisasi-pos', [RealisasiPosController::class, 'test'])->name('test.realisasi-pos');
Route::get('/test-target-calculation', [RealisasiPosController::class, 'testTargetCalculation'])->name('test.target-calculation');
Route::get('/test-month-data', [RealisasiPosController::class, 'testMonthData'])->name('test.month-data');
Route::get('/test-terbayar-data', [RealisasiPosController::class, 'testTerbayarData'])->name('test.terbayar-data');
Route::get('/test-bulan-data', [RealisasiPosController::class, 'testBulanData'])->name('test.bulan-data');
Route::get('/test-realisasi-calculation', [RealisasiPosController::class, 'testRealisasiCalculation'])->name('test.realisasi-calculation');
Route::get('/test-terbayar-comparison', [RealisasiPosController::class, 'testTerbayarComparison'])->name('test.terbayar-comparison');
Route::get('/test-comparison-with-rekapitulasi', [RealisasiPosController::class, 'testComparisonWithRekapitulasi'])->name('test.comparison-with-rekapitulasi');
Route::get('/test-same-data-comparison', [RealisasiPosController::class, 'testSameDataComparison'])->name('test.same-data-comparison');
Route::get('/test-realisasi-by-class', [RealisasiPosController::class, 'testRealisasiByClass'])->name('test.realisasi-by-class');
Route::get('/test-realisasi-pos-with-class', [RealisasiPosController::class, 'testRealisasiPosWithClass'])->name('test.realisasi-pos-with-class');

// Pembayaran Pos Routes
Route::get('/pembayaran-pos', function() {
    return 'Pembayaran Pos - Test Route Berhasil!';
})->name('test.pembayaran-pos');

// Test Pembayaran Per Pos Routes
Route::get('/test-pembayaran-per-pos', function() {
    return 'Test route berhasil!';
});

Route::get('/test-simple', function() {
    return 'Test simple berhasil!';
});

Route::get('/hello', function() {
    return 'Hello World!';
});

// Test Account Codes Toast Routes
Route::get('/test-account-codes-toast', function() {
    // Test success toast
    if (request('type') === 'success') {
        return redirect()->route('manage.account-codes.index')
            ->with('success', 'Test: Kode akun berhasil ditambahkan');
    }
    
    // Test error toast  
    if (request('type') === 'error') {
        return redirect()->route('manage.account-codes.index')
            ->with('error', 'Test: Kode akun sudah digunakan. Silakan gunakan kode yang berbeda.');
    }
    
    // Test warning toast
    if (request('type') === 'warning') {
        return redirect()->route('manage.account-codes.index')
            ->with('warning', 'Test: Peringatan sistem account codes');
    }
    
    return response()->json([
        'message' => 'Toast test tersedia',
        'options' => [
            'success' => url('/test-account-codes-toast?type=success'),
            'error' => url('/test-account-codes-toast?type=error'), 
            'warning' => url('/test-account-codes-toast?type=warning')
        ]
    ]);
})->name('test.account-codes-toast');

// Receipt Routes
Route::get('/receipt/{paymentNumber}', [ReceiptController::class, 'show'])->name('receipt.show');
Route::get('/generate-receipt', [ReceiptController::class, 'generateReceipt'])->name('receipt.generate')->middleware('auth');

// Online Payment Routes
Route::prefix('online-payment')->name('online-payment.')->middleware('auth')->group(function () {
    Route::get('/', [OnlinePaymentController::class, 'index'])->name('index');
    Route::get('/search', [OnlinePaymentController::class, 'searchStudent'])->name('search');
    Route::post('/find-student', [OnlinePaymentController::class, 'findStudent'])->name('find-student');
    Route::get('/student/{studentId}/bills', [OnlinePaymentController::class, 'studentBills'])->name('student-bills');
    Route::get('/payment-form/{studentId}/{billType}/{billId}', [OnlinePaymentController::class, 'paymentForm'])->name('form');
    Route::post('/process', [OnlinePaymentController::class, 'processPayment'])->name('process');
    Route::get('/history', [OnlinePaymentController::class, 'paymentHistory'])->name('history');
    Route::get('/detail/{id}', [OnlinePaymentController::class, 'paymentDetail'])->name('detail');
    Route::get('/receipt/{id}', [OnlinePaymentController::class, 'downloadReceipt'])->name('receipt');
    Route::post('/verify/{id}', [OnlinePaymentController::class, 'verifyPayment'])->name('verify');
    Route::get('/payment-channels', [OnlinePaymentController::class, 'getPaymentChannels'])->name('payment-channels');
    Route::get('/return', [OnlinePaymentController::class, 'paymentReturn'])->name('return');
});

// Test route untuk detail tanpa auth (temporary)
Route::get('/test-detail/{id}', [OnlinePaymentController::class, 'paymentDetail'])->name('test.detail');

// Callback routes tanpa middleware apapun - Tripay REMOVED
/*
Route::group(['middleware' => []], function () {
    Route::post('/callback', [CallbackController::class, 'tripayCallback']);
    Route::post('/webhook', [CallbackController::class, 'tripayCallback']);
    Route::post('/tripay-webhook', [CallbackController::class, 'tripayCallback']);
    Route::post('/api/callback', [CallbackController::class, 'tripayCallback']);
    Route::post('/tripay/callback', [CallbackController::class, 'tripayCallback']);
    
    // Simple test route
    Route::post('/test-callback', function(Request $request) {
        Log::info('Test callback received', $request->all());
        return response()->json(['success' => true, 'message' => 'Test callback received']);
    });
    
    // Super simple test route
    Route::post('/simple-callback', function() {
        Log::info('Simple callback hit');
        return response()->json(['success' => true, 'message' => 'Simple callback works']);
    });
    
    // Ultra simple test route - no parameters
    Route::post('/ultra-callback', function() {
        return 'OK';
    });
    
    // Test route untuk Tripay callback
    Route::post('/tripay-test', function() {
        return response()->json(['success' => true, 'message' => 'Tripay test callback works']);
    });
    
    // Super ultra simple callback - no middleware at all
    Route::post('/no-csrf-callback', function() {
        return 'NO-CSRF-OK';
    });
    
    // Payment callback routes (DEPRECATED - Use api.php for Tripay callback)
    // Route::post('/api/tripay/callback', [OnlinePaymentController::class, 'paymentCallback'])->name('api.tripay.callback'); // MOVED TO api.php
    Route::post('/online-payment/callback', [OnlinePaymentController::class, 'paymentCallback'])->name('online-payment.callback');
    Route::post('/midtrans/webhook', [OnlinePaymentController::class, 'paymentCallback'])->name('midtrans.webhook');
});
*/

// Logout Routes
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/manage');
})->name('logout');

// Login Routes
Route::get('/login', function () {
    return redirect()->route('otp.request');
})->name('login');

// Test routes - moved outside manage group for direct access
    Route::get('/test-pembayaran-per-pos', function() {
        return 'Test route berhasil!';
    });
    
    Route::get('/test-simple', function() {
        return 'Test simple berhasil!';
    });
    
    Route::get('/pembayaran-pos', function() {
        return 'Pembayaran Pos - Test Route Berhasil!';
    })->name('test.pembayaran-pos');
    
    Route::get('/hello', function() {
        return 'Hello World!';
    });

    Route::get('/test-account-codes-toast', function() {
        // Test success toast
        if (request('type') === 'success') {
            return redirect()->route('manage.account-codes.index')
                ->with('success', 'Test: Kode akun berhasil ditambahkan');
        }
        
        // Test error toast  
        if (request('type') === 'error') {
            return redirect()->route('manage.account-codes.index')
                ->with('error', 'Test: Kode akun sudah digunakan. Silakan gunakan kode yang berbeda.');
        }
        
        // Test warning toast
        if (request('type') === 'warning') {
            return redirect()->route('manage.account-codes.index')
                ->with('warning', 'Test: Peringatan sistem account codes');
        }
        
        return response()->json([
            'message' => 'Toast test tersedia',
            'options' => [
                'success' => url('/test-account-codes-toast?type=success'),
                'error' => url('/test-account-codes-toast?type=error'), 
                'warning' => url('/test-account-codes-toast?type=warning')
            ]
        ]);
    })->name('test.account-codes-toast');

    Route::get('/test-auto-accounting', function() {
        return response()->json([
            'message' => 'Auto accounting test route',
            'status' => 'working'
        ]);
    })->name('test.auto-accounting');

    Route::get('/test-tunggakan-data', function() {
        try {
            $bulananCount = DB::table('bulanan_pay as bp')
                ->join('students as s', 'bp.student_student_id', '=', 's.student_id')
                ->join('bulanan as b', 'bp.bulanan_bulanan_id', '=', 'b.bulanan_id')
                ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                ->where('b.bulan_bill', '>', 0)
                ->where('b.bulan_pay', '<', 'b.bulan_bill')
                ->count();

            $bebasCount = DB::table('bebas_pay as bp')
                ->join('students as s', 'bp.student_student_id', '=', 's.student_id')
                ->join('bebas as be', 'bp.bebas_bebas_id', '=', 'be.bebas_id')
                ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                ->where('bp.bebas_pay_bill', '>', 0)
                ->where('bp.bebas_pay_pay', '<', 'bp.bebas_pay_bill')
                ->count();

            $sampleBulanan = DB::table('bulanan_pay as bp')
                ->join('students as s', 'bp.student_student_id', '=', 's.student_id')
                ->join('bulanan as b', 'bp.bulanan_bulanan_id', '=', 'b.bulanan_id')
                ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                ->where('b.bulan_bill', '>', 0)
                ->where('b.bulan_pay', '<', 'b.bulan_bill')
                ->select('s.student_full_name', 'pos.pos_name', 'b.bulan_bill', 'b.bulan_pay', DB::raw('(b.bulan_bill - b.bulan_pay) as tunggakan'))
                ->limit(5)
                ->get();
            
            $sampleBebas = DB::table('bebas_pay as bp')
                ->join('students as s', 'bp.student_student_id', '=', 's.student_id')
                ->join('bebas as be', 'bp.bebas_bebas_id', '=', 'be.bebas_id')
                ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                ->where('bp.bebas_pay_bill', '>', 0)
                ->where('bp.bebas_pay_pay', '<', 'bp.bebas_pay_bill')
                ->select('s.student_full_name', 'pos.pos_name', 'bp.bebas_pay_bill', 'bp.bebas_pay_pay', DB::raw('(bp.bebas_pay_bill - bp.bebas_pay_pay) as tunggakan'))
                ->limit(5)
                ->get();
            
            return response()->json([
                'bulanan_count' => $bulananCount,
                'bebas_count' => $bebasCount,
                'sample_bulanan' => $sampleBulanan,
                'sample_bebas' => $sampleBebas
            ]);
    } catch (Exception $e) {
        return response()->json([
            'error' => 'Terjadi kesalahan saat mengambil data tunggakan',
            'message' => $e->getMessage()
        ], 500);
    }
        })->name('test.tunggakan-data');

    // Arus Kas Routes
    Route::get('/arus-kas', [ArusKasController::class, 'index'])->name('arus-kas.index')->middleware('auth');
    Route::get('/arus-kas/export-excel', [ArusKasController::class, 'exportExcel'])->name('arus-kas.excel')->middleware('auth');
    Route::get('/arus-kas/export-pdf', [ArusKasController::class, 'exportPDF'])->name('arus-kas.pdf')->middleware('auth');

    // Pos Routes
    Route::resource('pos', PosController::class)->middleware('auth');

    // Account Codes Routes
    Route::resource('account-codes', AccountCodeController::class)->middleware('auth');
    Route::patch('/account-codes/{accountCode}/toggle-status', [AccountCodeController::class, 'toggleStatus'])->name('account-codes.toggle-status')->middleware('auth');
    Route::get('/api/account-codes', [AccountCodeController::class, 'getAccountCodes'])->name('account-codes.api')->middleware('auth');

// OTP Routes - REMOVED DUPLICATE (already defined above at line 316)
/*
Route::get('/otp/login', [App\Http\Controllers\OtpController::class, 'showRequestForm'])->name('otp.request');
Route::post('/otp/login', [App\Http\Controllers\OtpController::class, 'requestOtp'])->name('otp.request');
Route::get('/otp/verify', [App\Http\Controllers\OtpController::class, 'showVerifyForm'])->name('otp.verify');
Route::post('/otp/verify', [App\Http\Controllers\OtpController::class, 'verifyOtp'])->name('otp.verify');
Route::get('/otp/resend', [App\Http\Controllers\OtpController::class, 'resendOtp'])->name('otp.resend');
*/

// Student Routes - dengan middleware yang benar
Route::prefix('student')->name('student.')->group(function () {
    // Redirect otomatis dari /student ke /student/login
    Route::get('/', function () {
        return redirect()->route('student.login');
    });
    
    // Auth routes
    Route::get('/login', [StudentAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [StudentAuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [StudentAuthController::class, 'logout'])->name('logout');
    Route::get('/logout', [StudentAuthController::class, 'logoutGet'])->name('logout.get');
    
    // Protected routes
    Route::middleware('student.auth')->group(function () {
        Route::get('/dashboard', [StudentAuthController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [StudentAuthController::class, 'profile'])->name('profile');
        Route::post('/update-password', [StudentAuthController::class, 'updatePassword'])->name('update-password');
        Route::post('/clear-cache', [StudentAuthController::class, 'clearCache'])->name('clear-cache');
        Route::post('/refresh-csrf', [StudentAuthController::class, 'refreshCsrfToken'])->name('refresh-csrf');
        
        // Bills
        Route::get('/bills', [StudentAuthController::class, 'bills'])->name('bills');
        Route::get('/cart', [StudentAuthController::class, 'cart'])->name('cart');
        
        // Tabungan
        Route::get('/tabungan', [StudentAuthController::class, 'tabungan'])->name('tabungan');
        
        // E-Perpustakaan
        Route::get('/library', [StudentAuthController::class, 'library'])->name('library');
        
        // Online Payment
        Route::get('/online-payment', [StudentAuthController::class, 'onlinePayment'])->name('online-payment');
        Route::get('/payment-form/{studentId}/{billType}/{billId}', [StudentAuthController::class, 'paymentForm'])->name('payment-form');
        Route::post('/payment/process', [StudentAuthController::class, 'processPayment'])->name('payment.process');
        Route::get('/payment/history', [StudentAuthController::class, 'paymentHistory'])->name('payment.history');
        Route::get('/payment/detail/{id}', [StudentAuthController::class, 'paymentDetail'])->name('payment.detail');
        Route::get('/payment/receipt/{id}', [StudentAuthController::class, 'downloadReceipt'])->name('payment.receipt');
        Route::get('/receipt/detail/{id}/{type}', [StudentAuthController::class, 'receiptDetail'])->name('receipt.detail');

        
        // Bank Transfer
        Route::post('/bank-transfer/prepare', [StudentAuthController::class, 'prepareBankTransfer'])->name('bank-transfer.prepare');
        Route::get('/bank-transfer', [StudentAuthController::class, 'bankTransfer'])->name('bank-transfer');
        Route::post('/bank-transfer/process', [StudentAuthController::class, 'processBankTransfer'])->name('bank-transfer.process');
        
        // Tabungan Payment
        Route::post('/tabungan/process', [StudentAuthController::class, 'processTabunganPayment'])->name('tabungan.process');
        
   
        
        // Cart Payment iPaymu
        Route::post('/cart/payment/ipaymu', [StudentAuthController::class, 'processCartPaymentIpaymu'])
            ->name('cart.payment.ipaymu');
        
        // Test route for debugging
        Route::get('/test-cart-route', function() {
            return response()->json(['message' => 'Cart route is working']);
        })->name('test.cart.route');
        

    });
});

// Cart Payment without auth for testing - REMOVED
/*
Route::post('/student/test-midtrans-transaction-from-cart-no-auth', [StudentAuthController::class, 'processCartPayment'])
    ->name('cart.payment.noauth')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class, \App\Http\Middleware\StudentAuth::class])
    ->middleware('web');
*/



// Callback Routes - Tripay & Midtrans REMOVED (using iPaymu now)
Route::prefix('callback')->name('callback.')->group(function () {
    // Route::post('/tripay', [CallbackController::class, 'tripayCallback'])->name('tripay');
    // Route::post('/midtrans', [CallbackController::class, 'midtransCallback'])->name('midtrans');
    Route::post('/payment', [CallbackController::class, 'paymentCallback'])->name('payment');
});

// Main Routes
Route::get('/', function () {
    return redirect()->route('student.login');
})->name('home');

Route::get('/welcome', function () {
    return view('welcome');
})->name('welcome');

// Period (Tahun Pelajaran) Routes
Route::resource('periods', PeriodController::class)->middleware('auth');
Route::post('periods/{period}/set-active', [PeriodController::class, 'setActive'])->name('periods.set-active')->middleware('auth');

// Class (Kelas) Routes
Route::resource('classes', ClassController::class)->middleware('auth');

// Student (Peserta Didik) Routes
Route::delete('/bulk-delete-students', [StudentController::class, 'bulkDelete'])->name('students.bulk-delete');
Route::post('/bulk-delete-students', [StudentController::class, 'bulkDelete'])->name('students.bulk-delete-post');
Route::get('/test-bulk', function() { return 'Bulk route works!'; })->name('test.bulk');
Route::resource('students', StudentController::class)->middleware('auth');

// Import routes for students
Route::get('/students-import', function () {
    return view('students.import');
})->name('students.import-form')->middleware('auth');

Route::post('/students-import', [StudentController::class, 'import'])->name('students.import')->middleware('auth');
Route::get('/students-download-template', [StudentController::class, 'downloadTemplate'])->name('students.download-template')->middleware('auth');
Route::get('/students-export', [StudentController::class, 'export'])->name('students.export')->middleware('auth');
Route::get('/students-move-class', [StudentController::class, 'moveClass'])->name('students.move-class')->middleware('auth');
Route::post('/students-get-by-class', [StudentController::class, 'getStudentsByClass'])->name('students.get-by-class')->middleware('auth');
Route::post('/students-process-move-class', [StudentController::class, 'processMoveClass'])->name('students.process-move-class')->middleware('auth');
Route::get('/students-graduate', [StudentController::class, 'graduate'])->name('students.graduate')->middleware('auth');
Route::post('/students-process-graduate', [StudentController::class, 'processGraduate'])->name('students.process-graduate')->middleware('auth');

// Reset password routes
Route::post('/students/{id}/reset-password', [StudentController::class, 'resetPassword'])->name('students.reset-password')->middleware('auth');
Route::post('/students/reset-password-massal', [StudentController::class, 'resetPasswordMassal'])->name('students.reset-password-massal')->middleware('auth');

// Debug routes
Route::get('/cek-db', function () {
    return DB::connection()->getDatabaseName();
});

Route::get('/debug/student/{id}', function($id) {
    $student = \App\Models\Student::find($id);
    return response()->json($student);
});

Route::get('/debug/users', function() {
    $users = \App\Models\User::all();
    return response()->json($users);
});

Route::get('/debug/payment-structure', function() {
    $payments = \App\Models\Payment::with('pos')->get();
    return response()->json($payments);
});

// Payment Gateway Test Routes
// Test Tripay Route - REMOVED
/*
Route::get('/test-tripay', function() {
    try {
        $tripayService = new \App\Services\TripayService();
        $channels = $tripayService->getPaymentChannels();
        
        return response()->json([
            'success' => true,
            'channels' => $channels,
            'message' => 'TripayService berfungsi dengan baik'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'message' => 'TripayService error'
        ]);
    }
})->name('test.tripay');
*/

Route::get('/tripay-return', function(Request $request) {
    $reference = $request->get('reference');
    $status = $request->get('status');
    
    if ($reference) {
        // Cek status transaksi di database
        $transfer = DB::table('transfer')->where('reference', $reference)->first();
        
        if ($transfer) {
            if ($status === 'PAID') {
                return redirect()->route('student.payment.history')
                    ->with('success', 'Pembayaran berhasil! Status akan diperbarui dalam beberapa menit.');
            } else {
                return redirect()->route('student.payment.history')
                    ->with('warning', 'Pembayaran belum selesai. Anda dapat melanjutkan pembayaran dari halaman riwayat.');
            }
        }
    }
    
    return redirect()->route('student.payment.history')
        ->with('info', 'Kembali dari halaman pembayaran.');
});

// Midtrans Payment Routes - REMOVED (using iPaymu now)

Route::get('/test-payment-gateway', function() {
    return view('test-payment-gateway');
})->name('test.payment.gateway');

// Test route untuk payment channels tanpa auth
Route::get('/test-payment-channels', [App\Http\Controllers\OnlinePaymentController::class, 'getPaymentChannels']);

// Test route untuk payment channels cart
Route::get('/test-payment-channels-cart', function() {
    try {
        $tripayService = new App\Services\TripayService();
        $channels = $tripayService->getPaymentChannels();
        return response()->json($channels);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
});

// Test route untuk process payment tanpa auth
Route::post('/test-process-payment', [App\Http\Controllers\OnlinePaymentController::class, 'processPayment']);

// Test Tripay Transaction Route - REMOVED
/*
Route::post('/test-tripay-transaction', function() {
    try {
        $tripayService = new TripayService();
        $testData = [
            'method' => 'BRIVA',
            'merchant_ref' => 'PG-TEST-' . time(),
            'amount' => 100000,
            'customer_name' => 'Test User',
            'customer_email' => 'test@sppqu.com',
            'customer_phone' => '08123456789',
            'order_items' => [
                [
                    'name' => 'Test Tagihan',
                    'price' => 100000,
                    'quantity' => 1
                ]
            ],
            'return_url' => route('tripay.return'),
            'callback_url' => route('online-payment.callback')
        ];
        $result = $tripayService->createTransaction($testData);
        return response()->json($result);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
});
*/

// Test route untuk StudentAuthController processPayment
Route::post('/test-student-payment', function() {
    try {
        // Simulate request data
        $requestData = [
            'student_id' => 1,
            'bill_type' => 'bulanan',
            'bill_id' => 1,
            'amount' => 100000,
            'payment_type' => 'realtime',
            'payment_method' => 'BRIVA',
            'description' => 'Test payment'
        ];
        $request = new \Illuminate\Http\Request();
        $request->merge($requestData);
        $controller = new \App\Http\Controllers\StudentAuthController();
        $response = $controller->processPayment($request);
        
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            return $response;
        } elseif ($response instanceof \Illuminate\Http\RedirectResponse) {
            return response()->json([
                'success' => false,
                'redirect' => $response->getTargetUrl(),
                'message' => 'Controller returned a redirect response'
            ]);
        } else {
        return response()->json([
                'success' => false,
                'message' => 'Controller returned an unexpected response type'
        ]);
        }
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'message' => 'StudentAuthController processPayment test failed'
        ]);
    }
})->name('test.student.payment');


// Webhook Test Routes
Route::get('/webhook-test', function() {
    return response()->json([
        'message' => 'Webhook test route working',
        'timestamp' => now()
    ]);
});

Route::get('/callback-test', function() {
    return response()->json([
        'success' => true,
        'message' => 'Callback endpoint is accessible',
        'timestamp' => now(),
        'url' => request()->url()
    ]);
});

Route::post('/callback-test', function(Request $request) {
    Log::info('Simple callback test received', $request->all());
    return response()->json(['success' => true, 'message' => 'Callback received']);
})->name('callback.test');

// WhatsApp Test Routes
Route::get('/test-whatsapp-notification', function() {
    return response()->json([
        'message' => 'WhatsApp notification test route working',
        'timestamp' => now()
    ]);
});

// Pindah Kelas Routes
Route::get('/pindah-kelas', function () {
    return view('students.pindah-kelas');
})->name('pindah-kelas')->middleware('auth');

// Redirect Routes untuk Menu Utama
Route::get('/tahun-pelajaran', function () {
    return redirect()->route('periods.index');
})->name('tahun-pelajaran');

Route::get('/kelas', function () {
    return redirect()->route('classes.index');
})->name('kelas');

Route::get('/peserta-didik', function () {
    return redirect()->route('students.index');
})->name('peserta-didik');

// API Routes
Route::get('/api/account-codes', [AccountCodeController::class, 'getAccountCodes'])->name('account-codes.api')->middleware('auth');
Route::get('/api/students/search', [PaymentController::class, 'searchStudent'])->name('api.students.search')->middleware('auth');
Route::get('/api/students/{id}/detail', [PaymentController::class, 'studentDetail'])->name('api.students.detail')->middleware('auth');
Route::get('/api/students/{id}/tagihan', [PaymentController::class, 'studentTagihan'])->name('api.students.tagihan')->middleware('auth');
Route::get('/api/students/{id}/tabungan', [PaymentController::class, 'studentTabungan'])->name('api.students.tabungan')->middleware('auth');
Route::get('/api/students/{studentId}/transactions', [PaymentController::class, 'getTransactionHistory'])->name('api.students.transactions')->middleware('auth');
Route::get('/api/check-saldo', function (Request $request) {
    $posId = $request->get('pos_id');
    $amount = $request->get('amount');
    
    if (!$posId || !$amount) {
        return response()->json([
            'valid' => false,
            'message' => 'Parameter tidak lengkap'
        ]);
    }
    
    // Hitung saldo pos pembayaran - debit/kredit tables removed
    $totalPenerimaan = 0; // debit table removed
    $totalPengeluaran = 0; // kredit table removed
    $saldoPos = 0;
    
    if ($saldoPos < $amount) {
        return response()->json([
            'valid' => false,
            'message' => 'Saldo tidak mencukupi',
            'saldo' => $saldoPos,
            'required' => $amount
        ]);
    }
    
    return response()->json([
        'valid' => true,
        'message' => 'Saldo mencukupi',
        'saldo' => $saldoPos,
        'required' => $amount
    ]);
});

// Laporan Routes
Route::get('/laporan-rekapitulasi', [PaymentController::class, 'laporanRekapitulasi'])->name('laporan-rekapitulasi')->middleware('auth');
Route::get('/rekapitulasi-tabungan', [App\Http\Controllers\RekapitulasiTabunganController::class, 'index'])->name('rekapitulasi-tabungan.index');
Route::get('/rekapitulasi-tabungan/export-pdf', [App\Http\Controllers\RekapitulasiTabunganController::class, 'exportPdf'])->name('rekapitulasi-tabungan.export-pdf');
Route::get('/rekapitulasi-tabungan/export-excel', [App\Http\Controllers\RekapitulasiTabunganController::class, 'exportExcel'])->name('rekapitulasi-tabungan.export-excel');
Route::get('/rekapitulasi-tabungan/detail/{studentId}', [App\Http\Controllers\RekapitulasiTabunganController::class, 'getDetailTransaksiApi'])->name('rekapitulasi-tabungan.detail');

// Rekapitulasi Tabungan Routes (Admin)
Route::middleware(['auth', 'session.timeout'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/rekapitulasi-tabungan', [App\Http\Controllers\RekapitulasiTabunganController::class, 'index'])->name('rekapitulasi-tabungan.index');
    Route::get('/rekapitulasi-tabungan/export-pdf', [App\Http\Controllers\RekapitulasiTabunganController::class, 'exportPdf'])->name('rekapitulasi-tabungan.export-pdf');
    Route::get('/rekapitulasi-tabungan/export-excel', [App\Http\Controllers\RekapitulasiTabunganController::class, 'exportExcel'])->name('rekapitulasi-tabungan.export-excel');
    Route::get('/rekapitulasi-tabungan/detail/{studentId}', [App\Http\Controllers\RekapitulasiTabunganController::class, 'getDetailTransaksiApi'])->name('rekapitulasi-tabungan.detail');
});

// Subscription Callback Route (tanpa middleware CSRF)
Route::match(['GET', 'POST'], '/subscription/callback', [App\Http\Controllers\SubscriptionController::class, 'callback'])->name('subscription.callback');

// AJAX Test Routes
Route::get('/test-ajax-page', function() {
    return view('students.test-ajax');
})->name('test.ajax-page');

Route::get('/test-data', function() {
    $classes = App\Models\ClassModel::all();
    $students = App\Models\Student::where('student_status', 1)->get();
    
    $result = [
        'classes' => $classes->map(function($c) {
            return ['id' => $c->id, 'name' => $c->class_name];
        }),
        'students' => $students->map(function($s) {
            return [
                'id' => $s->id,
                'nis' => $s->student_nis,
                'name' => $s->student_full_name,
                'class_id' => $s->class_class_id,
                'status' => $s->student_status
            ];
        })
    ];
    
    return response()->json([
        'message' => 'Test data route working',
        'data' => $result
    ]);
});

// Test route for debugging
Route::post('/test-ajax', function() {
    return response()->json(['message' => 'AJAX works!', 'data' => request()->all()]);
})->name('test.ajax');

Route::get('/test-ajax-page', function() {
    return view('students.test-ajax');
})->name('test.ajax.page');

// Filter History Test Routes
Route::get('/test-filter-history', function(Request $request) {
    return response()->json([
        'success' => true,
        'message' => 'Filter test route accessible',
        'filters' => $request->only(['status', 'payment_type', 'date_from', 'date_to', 'search', 'per_page']),
        'url' => $request->url(),
        'query' => $request->query()
    ]);
})->name('test.filter.history');

Route::get('/test-outside', function() {
    return 'Test route di luar group manage berhasil!';
});

// Form Test Routes - Midtrans REMOVED
/*
Route::get('/form', function() {
    $students = \App\Models\Student::all();
    $periods = \App\Models\Period::all();
    return view('online-payment.midtrans-form', compact('students', 'periods'));
})->name('midtrans.form');
*/

// Payment Form Routes
Route::get('/payment-form/{studentId}/{billType}/{billId}', [OnlinePaymentController::class, 'paymentForm'])->name('form');

// Bulanan Routes (duplicate - removed)

// Payment Routes (duplicate - removed)

// Laporan Payment Routes
// Route ini sudah dipindah ke route group manage
// Route::get('/laporan-perpos', [PaymentController::class, 'laporanPerpos'])->name('laporan-perpos')->middleware('auth');
// Route::post('/export-laporan-perpos', [PaymentController::class, 'exportLaporanPerpos'])->name('export-laporan-perpos')->middleware('auth');
// Route::get('/laporan-perkelas', [PaymentController::class, 'laporanPerkelas'])->name('laporan-perkelas')->middleware('auth');
// Route::post('/export-laporan-perkelas', [PaymentController::class, 'exportLaporanPerkelas'])->name('export-laporan-perkelas')->middleware('auth');
// Route::get('/laporan-rekapitulasi', [PaymentController::class, 'laporanRekapitulasi'])->name('laporan-rekapitulasi')->middleware('auth');
// Route::post('/export-laporan-rekapitulasi', [PaymentController::class, 'exportLaporanRekapitulasi'])->name('export-laporan-rekapitulasi')->middleware('auth');

// Laporan Tunggakan Siswa Routes
Route::get('/laporan/tunggakan-siswa', [LaporanTunggakanSiswaController::class, 'index'])->name('laporan.tunggakan-siswa')->middleware('auth');
Route::post('/laporan/tunggakan-siswa/export-pdf', [LaporanTunggakanSiswaController::class, 'exportPdf'])->name('laporan.tunggakan-siswa.export-pdf')->middleware('auth');
Route::post('/laporan/tunggakan-siswa/export-excel', [LaporanTunggakanSiswaController::class, 'exportExcel'])->name('laporan.tunggakan-siswa.export-excel')->middleware('auth');

// Bulk WhatsApp Routes
Route::get('/bulk-whatsapp', [BulkWhatsAppController::class, 'index'])->name('bulk-whatsapp.index')->middleware('auth');
Route::post('/bulk-whatsapp/bills', [BulkWhatsAppController::class, 'getBills'])->name('bulk-whatsapp.bills')->middleware('auth');
Route::post('/bulk-whatsapp/send', [BulkWhatsAppController::class, 'sendBulkBills'])->name('bulk-whatsapp.send')->middleware('auth');
Route::post('/bulk-whatsapp/send-mass-message', [BulkWhatsAppController::class, 'sendMassMessage'])->name('bulk-whatsapp.send-mass-message')->middleware('auth');
Route::post('/bulk-whatsapp/send-consolidated', [BulkWhatsAppController::class, 'sendConsolidatedBills'])->name('bulk-whatsapp.send-consolidated')->middleware('auth');

// Test Tunggakan Data Routes
Route::get('/test-tunggakan-data', function() {
    $bulananCount = DB::table('bulan')
        ->where('bulan_bill', '>', 0)
        ->where('bulan_pay', '<', 'bulan_bill')
        ->count();
    
    $bebasCount = DB::table('bebas_pay')
        ->where('bebas_pay_bill', '>', 0)
        ->where('bebas_pay_pay', '<', 'bebas_pay_bill')
        ->count();
    
    $sampleBulanan = DB::table('bulan as b')
        ->join('students as s', 'b.student_student_id', '=', 's.student_id')
        ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
        ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
        ->where('b.bulan_bill', '>', 0)
        ->where('b.bulan_pay', '<', 'b.bulan_bill')
        ->select('s.student_full_name', 'pos.pos_name', 'b.bulan_bill', 'b.bulan_pay', DB::raw('(b.bulan_bill - b.bulan_pay) as tunggakan'))
        ->limit(5)
        ->get();
    
    $sampleBebas = DB::table('bebas_pay as bp')
        ->join('students as s', 'bp.student_student_id', '=', 's.student_id')
        ->join('bebas as be', 'bp.bebas_bebas_id', '=', 'be.bebas_id')
        ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
        ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
        ->where('bp.bebas_pay_bill', '>', 0)
        ->where('bp.bebas_pay_pay', '<', 'bp.bebas_pay_bill')
        ->select('s.student_full_name', 'pos.pos_name', 'bp.bebas_pay_bill', 'bp.bebas_pay_pay', DB::raw('(bp.bebas_pay_bill - bp.bebas_pay_pay) as tunggakan'))
        ->limit(5)
        ->get();
    
    return response()->json([
        'bulanan_count' => $bulananCount,
        'bebas_count' => $bebasCount,
        'sample_bulanan' => $sampleBulanan,
        'sample_bebas' => $sampleBebas
    ]);
})->name('test.tunggakan-data');

// Addons Routes
Route::get('/addons', [App\Http\Controllers\AddonController::class, 'index'])->name('addons.index')->middleware('auth');
Route::get('/addons/{slug}', [App\Http\Controllers\AddonController::class, 'show'])->name('addons.show')->middleware('auth');
Route::post('/addons/{slug}/purchase', [App\Http\Controllers\AddonController::class, 'purchase'])->name('addons.purchase')->middleware('auth');
Route::match(['get', 'post'], '/addons/callback', [App\Http\Controllers\AddonController::class, 'callback'])->name('addons.callback');
Route::get('/addons/{slug}/check', [App\Http\Controllers\AddonController::class, 'checkUserAddon'])->name('addons.check')->middleware('auth');
Route::post('/addons/refresh-status', [App\Http\Controllers\AddonController::class, 'refreshAddonStatus'])->name('addons.refresh-status')->middleware('auth');

// Subscription Routes
Route::get('/subscription', [App\Http\Controllers\SubscriptionController::class, 'index'])->name('subscription.index')->middleware('auth');
Route::get('/subscription/plans', [App\Http\Controllers\SubscriptionController::class, 'showPlans'])->name('subscription.plans')->middleware('auth');
Route::post('/subscription/create', [App\Http\Controllers\SubscriptionController::class, 'createSubscription'])->name('subscription.create')->middleware('auth');
Route::get('/subscription/payment/{subscription_id}', [App\Http\Controllers\SubscriptionController::class, 'payment'])->name('subscription.payment')->middleware('auth');
Route::post('/subscription/cancel', [App\Http\Controllers\SubscriptionController::class, 'cancelSubscription'])->name('subscription.cancel')->middleware('auth');
Route::get('/subscription/check-status', [App\Http\Controllers\SubscriptionController::class, 'checkSubscriptionStatus'])->name('subscription.check-status')->middleware('auth');
Route::get('/subscription/notifications', [App\Http\Controllers\SubscriptionController::class, 'getSubscriptionNotifications'])->name('subscription.notifications')->middleware('auth');
Route::get('/subscription/premium-features', [App\Http\Controllers\SubscriptionController::class, 'premiumFeatures'])->name('subscription.premium-features')->middleware('auth');
Route::get('/subscription/invoice/{invoice_id}/download', [App\Http\Controllers\SubscriptionController::class, 'downloadInvoice'])->name('subscription.download-invoice')->middleware('auth');

// Tabungan Routes
Route::get('/tabungan/{id}/setoran', [TabunganController::class, 'setoran'])->name('tabungan.setoran')->middleware('auth');
Route::post('/tabungan/{id}/store-setoran', [TabunganController::class, 'storeSetoran'])->name('tabungan.store-setoran')->middleware('auth');
Route::get('/tabungan/{id}/penarikan', [TabunganController::class, 'penarikan'])->name('tabungan.penarikan')->middleware('auth');
Route::post('/tabungan/{id}/store-penarikan', [TabunganController::class, 'storePenarikan'])->name('tabungan.store-penarikan')->middleware('auth');
Route::get('/tabungan/{id}/riwayat', [TabunganController::class, 'riwayat'])->name('tabungan.riwayat')->middleware('auth');
Route::get('/tabungan/{id}/export-mutasi', [TabunganController::class, 'exportMutasi'])->name('tabungan.export-mutasi')->middleware('auth');
Route::resource('tabungan', TabunganController::class)->middleware('auth');

// Notifications Routes
Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index')->middleware('auth');
Route::get('/notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread')->middleware('auth');
Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read')->middleware('auth');
Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all')->middleware('auth');
Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy')->middleware('auth');

// Online Payments Routes
Route::get('/online-payments', [OnlinePaymentController::class, 'index'])->name('online-payments')->middleware('auth');
Route::get('/online-payments/{id}', [OnlinePaymentController::class, 'show'])->name('online-payments.show')->middleware('auth');
Route::post('/online-payments/{id}/approve', [OnlinePaymentController::class, 'approve'])->name('online-payments.approve')->middleware('auth');
Route::post('/online-payments/{id}/reject', [OnlinePaymentController::class, 'reject'])->name('online-payments.reject')->middleware('auth');

// Users Routes
Route::resource('users', UserController::class)->middleware('auth');
Route::get('/role-menu', [UserController::class, 'roleMenu'])->name('users.role-menu')->middleware('auth');
Route::post('/role-menu', [UserController::class, 'saveRoleMenu'])->name('users.role-menu.save')->middleware('auth');

// General Setting Routes
Route::get('/general-setting', [GeneralSettingController::class, 'index'])->name('general-setting')->middleware('auth', 'session.timeout');
Route::post('/general-setting', [GeneralSettingController::class, 'update'])->name('general-setting.update')->middleware('auth', 'session.timeout');

// Dashboard Routes
Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard')->middleware('auth', 'session.timeout');

// Logout Routes
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->forget(['admin_last_activity']);
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('otp.request')->with('success', 'Anda telah berhasil logout.');
})->name('logout');

// Pos Routes
Route::resource('pos', PosController::class)->middleware('auth');

// Realisasi Pos Routes
Route::get('/laporan/realisasi-pos', [RealisasiPosController::class, 'index'])->name('laporan.realisasi-pos')->middleware('auth');
Route::get('/laporan/realisasi-pos/export-excel', [RealisasiPosController::class, 'exportExcel'])->name('laporan.realisasi-pos.export-excel')->middleware('auth');
Route::get('/laporan/realisasi-pos/export-pdf', [RealisasiPosController::class, 'exportPdf'])->name('laporan.realisasi-pos.export-pdf')->middleware('auth');

// Test Realisasi Pos Routes
Route::get('/test-realisasi-pos', [RealisasiPosController::class, 'test'])->name('test.realisasi-pos');
Route::get('/test-target-calculation', [RealisasiPosController::class, 'testTargetCalculation'])->name('test.target-calculation');
Route::get('/test-month-data', [RealisasiPosController::class, 'testMonthData'])->name('test.month-data');
Route::get('/test-terbayar-data', [RealisasiPosController::class, 'testTerbayarData'])->name('test.terbayar-data');
Route::get('/test-bulan-data', [RealisasiPosController::class, 'testBulanData'])->name('test.bulan-data');
Route::get('/test-realisasi-calculation', [RealisasiPosController::class, 'testRealisasiCalculation'])->name('test.realisasi-calculation');

// Pembayaran Pos Routes
Route::get('/pembayaran-pos', function() {
    return 'Pembayaran Pos - Test Route Berhasil!';
})->name('test.pembayaran-pos');

// Test Pembayaran Per Pos Routes
Route::get('/test-pembayaran-per-pos', function() {
    return 'Test route berhasil!';
});

Route::get('/test-simple', function() {
    return 'Test simple berhasil!';
});

Route::get('/hello', function() {
    return 'Hello World!';
});

// Test Account Codes Toast Routes
Route::get('/test-account-codes-toast', function() {
    // Test success toast
    if (request('type') === 'success') {
        return redirect()->route('manage.account-codes.index')
            ->with('success', 'Test: Kode akun berhasil ditambahkan');
    }
    
    // Test error toast  
    if (request('type') === 'error') {
        return redirect()->route('manage.account-codes.index')
            ->with('error', 'Test: Kode akun sudah digunakan. Silakan gunakan kode yang berbeda.');
    }
    
    // Test warning toast
    if (request('type') === 'warning') {
        return redirect()->route('manage.account-codes.index')
            ->with('warning', 'Test: Peringatan sistem account codes');
    }
    
    return response()->json([
        'message' => 'Toast test tersedia',
        'options' => [
            'success' => url('/test-account-codes-toast?type=success'),
            'error' => url('/test-account-codes-toast?type=error'), 
            'warning' => url('/test-account-codes-toast?type=warning')
        ]
    ]);
})->name('test.account-codes-toast');

// Receipt Routes
Route::get('/receipt/{paymentNumber}', [ReceiptController::class, 'show'])->name('receipt.show');
Route::get('/generate-receipt', [ReceiptController::class, 'generateReceipt'])->name('receipt.generate')->middleware('auth');

// Online Payment Routes
Route::prefix('online-payment')->name('online-payment.')->middleware('auth')->group(function () {
    Route::get('/', [OnlinePaymentController::class, 'index'])->name('index');
    Route::get('/search', [OnlinePaymentController::class, 'searchStudent'])->name('search');
    Route::post('/find-student', [OnlinePaymentController::class, 'findStudent'])->name('find-student');
    Route::get('/student/{studentId}/bills', [OnlinePaymentController::class, 'studentBills'])->name('student-bills');
    Route::get('/payment-form/{studentId}/{billType}/{billId}', [OnlinePaymentController::class, 'paymentForm'])->name('form');
    Route::post('/process', [OnlinePaymentController::class, 'processPayment'])->name('process');
    Route::get('/history', [OnlinePaymentController::class, 'paymentHistory'])->name('history');
    Route::get('/detail/{id}', [OnlinePaymentController::class, 'paymentDetail'])->name('detail');
    Route::get('/receipt/{id}', [OnlinePaymentController::class, 'downloadReceipt'])->name('receipt');
    Route::post('/verify/{id}', [OnlinePaymentController::class, 'verifyPayment'])->name('verify');
    Route::get('/payment-channels', [OnlinePaymentController::class, 'getPaymentChannels'])->name('payment-channels');
    Route::get('/return', [OnlinePaymentController::class, 'paymentReturn'])->name('return');
});

// Test route untuk detail tanpa auth (temporary)
Route::get('/test-detail/{id}', [OnlinePaymentController::class, 'paymentDetail'])->name('test.detail');

// Callback routes tanpa middleware apapun - Tripay REMOVED
/*
Route::group(['middleware' => []], function () {
    Route::post('/callback', [CallbackController::class, 'tripayCallback']);
    Route::post('/webhook', [CallbackController::class, 'tripayCallback']);
    Route::post('/tripay-webhook', [CallbackController::class, 'tripayCallback']);
    Route::post('/api/callback', [CallbackController::class, 'tripayCallback']);
    Route::post('/tripay/callback', [CallbackController::class, 'tripayCallback']);
    
    // Simple test route
    Route::post('/test-callback', function(Request $request) {
        Log::info('Test callback received', $request->all());
        return response()->json(['success' => true, 'message' => 'Test callback received']);
    });
    
    // Super simple test route
    Route::post('/simple-callback', function() {
        Log::info('Simple callback hit');
        return response()->json(['success' => true, 'message' => 'Simple callback works']);
    });
    
    // Ultra simple test route - no parameters
    Route::post('/ultra-callback', function() {
        return 'OK';
    });
    
    // Test route untuk Tripay callback
    Route::post('/tripay-test', function() {
        return response()->json(['success' => true, 'message' => 'Tripay test callback works']);
    });
    
    // Super ultra simple callback - no middleware at all
    Route::post('/no-csrf-callback', function() {
        return 'NO-CSRF-OK';
    });
    
    // Payment callback routes (DEPRECATED - Use api.php for Tripay callback)
    // Route::post('/api/tripay/callback', [OnlinePaymentController::class, 'paymentCallback'])->name('api.tripay.callback'); // MOVED TO api.php
    Route::post('/online-payment/callback', [OnlinePaymentController::class, 'paymentCallback'])->name('online-payment.callback');
    Route::post('/midtrans/webhook', [OnlinePaymentController::class, 'paymentCallback'])->name('midtrans.webhook');
});
*/

// Logout Routes
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/manage');
})->name('logout');

// Login Routes
Route::get('/login', function () {
    return redirect()->route('otp.request');
})->name('login');

// Test routes - moved outside manage group for direct access
    Route::get('/test-pembayaran-per-pos', function() {
        return 'Test route berhasil!';
    });
    
    Route::get('/test-simple', function() {
        return 'Test simple berhasil!';
    });
    
    Route::get('/pembayaran-pos', function() {
        return 'Pembayaran Pos - Test Route Berhasil!';
    })->name('test.pembayaran-pos');
    
    Route::get('/hello', function() {
        return 'Hello World!';
    });

    Route::get('/test-account-codes-toast', function() {
        // Test success toast
        if (request('type') === 'success') {
            return redirect()->route('manage.account-codes.index')
                ->with('success', 'Test: Kode akun berhasil ditambahkan');
        }
        
        // Test error toast  
        if (request('type') === 'error') {
            return redirect()->route('manage.account-codes.index')
                ->with('error', 'Test: Kode akun sudah digunakan. Silakan gunakan kode yang berbeda.');
        }
        
        // Test warning toast
        if (request('type') === 'warning') {
            return redirect()->route('manage.account-codes.index')
                ->with('warning', 'Test: Peringatan sistem account codes');
        }
        
        return response()->json([
            'message' => 'Toast test tersedia',
            'options' => [
                'success' => url('/test-account-codes-toast?type=success'),
                'error' => url('/test-account-codes-toast?type=error'), 
                'warning' => url('/test-account-codes-toast?type=warning')
            ]
        ]);
    })->name('test.account-codes-toast');

    Route::get('/test-auto-accounting', function() {
        return response()->json([
            'message' => 'Auto accounting test route',
            'status' => 'working'
        ]);
    })->name('test.auto-accounting');

    Route::get('/test-tunggakan-data', function() {
        try {
            $bulananCount = DB::table('bulanan_pay as bp')
                ->join('students as s', 'bp.student_student_id', '=', 's.student_id')
                ->join('bulanan as b', 'bp.bulanan_bulanan_id', '=', 'b.bulanan_id')
                ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                ->where('b.bulan_bill', '>', 0)
                ->where('b.bulan_pay', '<', 'b.bulan_bill')
                ->count();

            $bebasCount = DB::table('bebas_pay as bp')
                ->join('students as s', 'bp.student_student_id', '=', 's.student_id')
                ->join('bebas as be', 'bp.bebas_bebas_id', '=', 'be.bebas_id')
                ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                ->where('bp.bebas_pay_bill', '>', 0)
                ->where('bp.bebas_pay_pay', '<', 'bp.bebas_pay_bill')
                ->count();

            $sampleBulanan = DB::table('bulanan_pay as bp')
                ->join('students as s', 'bp.student_student_id', '=', 's.student_id')
                ->join('bulanan as b', 'bp.bulanan_bulanan_id', '=', 'b.bulanan_id')
                ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                ->where('b.bulan_bill', '>', 0)
                ->where('b.bulan_pay', '<', 'b.bulan_bill')
                ->select('s.student_full_name', 'pos.pos_name', 'b.bulan_bill', 'b.bulan_pay', DB::raw('(b.bulan_bill - b.bulan_pay) as tunggakan'))
                ->limit(5)
                ->get();
            
            $sampleBebas = DB::table('bebas_pay as bp')
                ->join('students as s', 'bp.student_student_id', '=', 's.student_id')
                ->join('bebas as be', 'bp.bebas_bebas_id', '=', 'be.bebas_id')
                ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                ->where('bp.bebas_pay_bill', '>', 0)
                ->where('bp.bebas_pay_pay', '<', 'bp.bebas_pay_bill')
                ->select('s.student_full_name', 'pos.pos_name', 'bp.bebas_pay_bill', 'bp.bebas_pay_pay', DB::raw('(bp.bebas_pay_bill - bp.bebas_pay_pay) as tunggakan'))
                ->limit(5)
                ->get();
            
            return response()->json([
                'bulanan_count' => $bulananCount,
                'bebas_count' => $bebasCount,
                'sample_bulanan' => $sampleBulanan,
                'sample_bebas' => $sampleBebas
            ]);
    } catch (Exception $e) {
        return response()->json([
            'error' => 'Terjadi kesalahan saat mengambil data tunggakan',
            'message' => $e->getMessage()
        ], 500);
    }
        })->name('test.tunggakan-data');

    // Arus Kas Routes
    Route::get('/arus-kas', [ArusKasController::class, 'index'])->name('arus-kas.index')->middleware('auth');
    Route::get('/arus-kas/export-excel', [ArusKasController::class, 'exportExcel'])->name('arus-kas.excel')->middleware('auth');
    Route::get('/arus-kas/export-pdf', [ArusKasController::class, 'exportPDF'])->name('arus-kas.pdf')->middleware('auth');

    // Pos Routes
    Route::resource('pos', PosController::class)->middleware('auth');

