<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookCategory;
use App\Models\BookLoan;
use App\Models\ReadingHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LibraryController extends Controller
{
    /**
     * Display library dashboard
     */
    public function index()
    {
        // Statistics
        $totalBooks = Book::active()->count();
        $totalCategories = BookCategory::active()->count();
        $activeLoans = BookLoan::active()->count();
        $totalReads = ReadingHistory::count();
        
        // Recent books
        $recentBooks = Book::with('category')
            ->active()
            ->latest()
            ->take(6)
            ->get();
        
        // Featured books
        $featuredBooks = Book::with('category')
            ->active()
            ->featured()
            ->take(4)
            ->get();
        
        // Popular books (most viewed)
        $popularBooks = Book::with('category')
            ->active()
            ->orderBy('total_views', 'desc')
            ->take(5)
            ->get();
        
        // Categories with book count
        $categories = BookCategory::active()
            ->withCount('books')
            ->ordered()
            ->get();
        
        // Recent reading activity
        $recentActivity = ReadingHistory::with(['book', 'user'])
            ->latest()
            ->take(10)
            ->get();
        
        // Monthly statistics
        $monthlyStats = DB::table('book_reading_history')
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();
        
        return view('library.dashboard', compact(
            'totalBooks',
            'totalCategories',
            'activeLoans',
            'totalReads',
            'recentBooks',
            'featuredBooks',
            'popularBooks',
            'categories',
            'recentActivity',
            'monthlyStats'
        ));
    }

    /**
     * Search books
     */
    public function search(Request $request)
    {
        $query = Book::with('category')->active();
        
        // Search by keyword
        if ($request->filled('q')) {
            $query->search($request->q);
        }
        
        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        
        // Filter by year
        if ($request->filled('year')) {
            $query->where('tahun_terbit', $request->year);
        }
        
        // Sort
        $sortBy = $request->get('sort', 'latest');
        switch ($sortBy) {
            case 'popular':
                $query->orderBy('total_views', 'desc');
                break;
            case 'title':
                $query->orderBy('judul', 'asc');
                break;
            case 'oldest':
                $query->oldest();
                break;
            default:
                $query->latest();
        }
        
        $books = $query->paginate(12);
        $categories = BookCategory::active()->ordered()->get();
        
        return view('library.search', compact('books', 'categories'));
    }

    /**
     * Show book details
     */
    public function show($id)
    {
        $book = Book::with(['category', 'uploader'])->findOrFail($id);
        
        // Increment view count
        $book->incrementViews();
        
        // Related books (same category)
        $relatedBooks = Book::where('category_id', $book->category_id)
            ->where('id', '!=', $book->id)
            ->active()
            ->take(4)
            ->get();
        
        // Check if user has active loan
        $hasActiveLoan = false;
        if (auth()->check()) {
            $hasActiveLoan = BookLoan::where('book_id', $book->id)
                ->where('user_id', auth()->id())
                ->where('status', 'dipinjam')
                ->exists();
        }
        
        return view('library.show', compact('book', 'relatedBooks', 'hasActiveLoan'));
    }

    /**
     * Show user's active loans
     */
    public function myLoans()
    {
        $loans = BookLoan::with(['book', 'processor'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);
        
        $stats = [
            'active' => BookLoan::where('user_id', auth()->id())->where('status', 'dipinjam')->count(),
            'returned' => BookLoan::where('user_id', auth()->id())->where('status', 'dikembalikan')->count(),
            'overdue' => BookLoan::where('user_id', auth()->id())->where('status', 'dipinjam')
                ->where('tanggal_kembali_rencana', '<', now())->count(),
        ];
        
        return view('library.my-loans', compact('loans', 'stats'));
    }

    /**
     * Show digital library card
     */
    public function libraryCard()
    {
        $user = auth()->user();
        
        // Get student info if user is student
        $student = null;
        if ($user->role === 'student' || $user->email) {
            $student = \App\Models\Student::where('student_email', $user->email)->first();
        }
        
        // Statistics
        $totalBorrowed = BookLoan::where('user_id', $user->id)->count();
        $activeBorrowed = BookLoan::where('user_id', $user->id)->where('status', 'dipinjam')->count();
        $booksRead = ReadingHistory::where('user_id', $user->id)
            ->where('activity_type', 'read')
            ->distinct('book_id')
            ->count();
        
        return view('library.card', compact('user', 'student', 'totalBorrowed', 'activeBorrowed', 'booksRead'));
    }

    /**
     * Library cards management (Admin)
     */
    public function cardsManagement()
    {
        $classes = \App\Models\ClassModel::orderBy('class_name')->get();
        
        return view('library.cards.index', compact('classes'));
    }

    /**
     * Print library cards per class (PDF)
     */
    public function printClassCards($class_id)
    {
        $class = \App\Models\ClassModel::findOrFail($class_id);
        
        $students = \App\Models\Student::where('class_class_id', $class_id)
            ->where('student_status', 1)
            ->orderBy('student_full_name')
            ->get();
        
        $schoolProfile = \App\Models\SchoolProfile::first();
        
        $pdf = \PDF::loadView('library.cards.print-class', compact('class', 'students', 'schoolProfile'));
        $pdf->setPaper('A4', 'portrait');
        
        $filename = 'Kartu_Perpustakaan_' . str_replace(' ', '_', $class->class_name) . '_' . date('Y-m-d') . '.pdf';
        
        return $pdf->stream($filename);
    }
}

