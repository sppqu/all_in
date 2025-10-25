<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Library\LibraryController;
use App\Http\Controllers\Library\BookController;
use App\Http\Controllers\Library\BookCategoryController;
use App\Http\Controllers\Library\LoanController;
use App\Http\Controllers\Library\ReaderController;

/*
|--------------------------------------------------------------------------
| Library Routes
|--------------------------------------------------------------------------
|
| E-Perpustakaan Digital Routes
|
*/

// Public routes (semua user bisa akses)
Route::middleware(['auth'])->group(function () {
    
    // Library Dashboard & Search
    Route::get('/library', [LibraryController::class, 'index'])->name('library.index');
    Route::get('/library/search', [LibraryController::class, 'search'])->name('library.search');
    Route::get('/library/book/{id}', [LibraryController::class, 'show'])->name('library.book.show');
    
    // Reader (Baca & Download)
    Route::get('/library/read/{id}', [ReaderController::class, 'read'])->name('library.read');
    Route::get('/library/download/{id}', [ReaderController::class, 'download'])->name('library.download');
    Route::post('/library/read/{id}/progress', [ReaderController::class, 'updateProgress'])->name('library.update-progress');
    
    // My Loans & Library Card
    Route::get('/library/my-loans', [LibraryController::class, 'myLoans'])->name('library.my-loans');
    Route::get('/library/card', [LibraryController::class, 'libraryCard'])->name('library.card');
});

// Admin routes (hanya superadmin dan role tertentu)
Route::middleware(['auth'])->prefix('manage/library')->name('manage.library.')->group(function () {
    
    // Dashboard Admin
    Route::get('/dashboard', [LibraryController::class, 'index'])->name('dashboard');
    
    // Book Management
    Route::resource('books', BookController::class);
    
    // Category Management
    Route::resource('categories', BookCategoryController::class)->except(['show']);
    
    // Loan Management
    Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
    Route::get('/loans/create', [LoanController::class, 'create'])->name('loans.create');
    Route::post('/loans', [LoanController::class, 'store'])->name('loans.store');
    Route::get('/loans/{id}', [LoanController::class, 'show'])->name('loans.show');
    Route::get('/loans/{id}/edit', [LoanController::class, 'edit'])->name('loans.edit');
    Route::put('/loans/{id}', [LoanController::class, 'update'])->name('loans.update');
    Route::post('/loans/{id}/return', [LoanController::class, 'return'])->name('loans.return');
    Route::get('/loans/{id}/fine', [LoanController::class, 'getFine'])->name('loans.fine');
    
    // AJAX Search Students
    Route::get('/students/search', [LoanController::class, 'searchStudents'])->name('students.search');
    Route::get('/loans/check-student/{student_id}', [LoanController::class, 'checkStudentLoans'])->name('loans.check-student');
    
    // Library Cards Management
    Route::get('/library-cards', [LibraryController::class, 'cardsManagement'])->name('cards.index');
    Route::get('/library-cards/print-class/{class_id}', [LibraryController::class, 'printClassCards'])->name('cards.print-class');
});

