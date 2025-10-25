<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookLoan;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LoanController extends Controller
{
    /**
     * Display a listing of loans
     */
    public function index(Request $request)
    {
        $query = BookLoan::with(['book', 'user', 'student']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by overdue
        if ($request->filled('overdue') && $request->overdue == '1') {
            $query->overdue();
        }

        // Filter by search (peminjam atau buku)
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('user', function($userQuery) use ($request) {
                    $userQuery->where('name', 'like', '%' . $request->search . '%');
                })
                ->orWhereHas('book', function($bookQuery) use ($request) {
                    $bookQuery->where('judul', 'like', '%' . $request->search . '%');
                });
            });
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('tanggal_pinjam', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('tanggal_pinjam', '<=', $request->end_date);
        }

        $loans = $query->latest()->paginate(20);
        
        // Statistics
        $stats = [
            'active' => BookLoan::where('status', 'dipinjam')->count(),
            'returned' => BookLoan::where('status', 'dikembalikan')->count(),
            'overdue' => BookLoan::overdue()->count(),
            'returned_today' => BookLoan::where('status', 'dikembalikan')
                ->whereDate('tanggal_kembali_aktual', today())
                ->count(),
            'total_fines' => BookLoan::where('status', 'dikembalikan')
                ->whereMonth('tanggal_kembali_aktual', now()->month)
                ->whereYear('tanggal_kembali_aktual', now()->year)
                ->sum('denda'),
        ];

        return view('library.loans.index', compact('loans', 'stats'));
    }

    /**
     * Show the form for creating a new loan
     */
    public function create()
    {
        $books = Book::active()->available()->get();
        
        return view('library.loans.create', compact('books'));
    }

    /**
     * Search students for Select2 AJAX (autocomplete)
     */
    public function searchStudents(Request $request)
    {
        $search = $request->get('q');
        
        $students = Student::with('class')
            ->where('student_status', 1)
            ->where(function($query) use ($search) {
                $query->where('student_nis', 'like', '%' . $search . '%')
                      ->orWhere('student_full_name', 'like', '%' . $search . '%');
            })
            ->orderBy('student_full_name')
            ->limit(20)
            ->get()
            ->map(function($student) {
                return [
                    'student_id' => $student->student_id,
                    'student_nis' => $student->student_nis,
                    'student_full_name' => $student->student_full_name,
                    'class_name' => $student->class->class_name ?? '-'
                ];
            });
        
        return response()->json($students);
    }

    /**
     * Check student active loans count (AJAX)
     */
    public function checkStudentLoans($student_id)
    {
        $student = Student::where('student_id', $student_id)->first();
        
        if (!$student) {
            return response()->json(['active_loans' => 0]);
        }
        
        // Find user by student email
        $user = User::where('email', $student->student_email)->first();
        
        if (!$user) {
            return response()->json(['active_loans' => 0]);
        }
        
        // Count active loans
        $activeLoans = BookLoan::where('user_id', $user->id)
            ->where('status', 'dipinjam')
            ->count();
        
        return response()->json([
            'active_loans' => $activeLoans,
            'student_name' => $student->student_full_name,
            'can_borrow' => $activeLoans < 3
        ]);
    }

    /**
     * Store a newly created loan
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'student_id' => 'required',
            'tanggal_pinjam' => 'required|date',
            'durasi_hari' => 'required|integer|min:1|max:14', // Max 14 hari
            'catatan' => 'nullable|string',
        ]);

        // Check book availability
        $book = Book::findOrFail($validated['book_id']);
        if ($book->status !== 'tersedia') {
            return back()->with('error', 'Buku tidak tersedia untuk dipinjam!');
        }

        // Find or create user for student
        $student = Student::where('student_id', $validated['student_id'])->firstOrFail();
        $userEmail = $student->student_email ?? 'student' . $student->student_id . '@temp.com';
        
        // Use firstOrCreate to avoid duplicate errors
        $user = User::firstOrCreate(
            ['email' => $userEmail],
            [
                'name' => $student->student_full_name,
                'password' => bcrypt('password'),
                'role' => 'student',
            ]
        );

        // Create loan
        $loan = BookLoan::create([
            'book_id' => $validated['book_id'],
            'user_id' => $user->id,
            'student_id' => $student->student_id,
            'tanggal_pinjam' => $validated['tanggal_pinjam'],
            'tanggal_kembali_rencana' => Carbon::parse($validated['tanggal_pinjam'])->addDays($validated['durasi_hari']),
            'status' => 'dipinjam',
            'catatan' => $validated['catatan'],
            'processed_by' => auth()->id(),
        ]);

        // Increment book loan count
        $book->incrementLoans();

        return redirect()->route('manage.library.loans.index')
            ->with('success', 'Peminjaman berhasil dicatat!');
    }

    /**
     * Return a loan
     */
    public function return(Request $request, $id)
    {
        $loan = BookLoan::findOrFail($id);

        if ($loan->status !== 'dipinjam') {
            return back()->with('error', 'Peminjaman ini sudah dikembalikan!');
        }

        $loan->update([
            'tanggal_kembali_aktual' => now(),
            'status' => 'dikembalikan',
            'denda' => $loan->calculateFine(),
        ]);

        return redirect()->route('manage.library.loans.index')
            ->with('success', 'Buku berhasil dikembalikan!');
    }

    /**
     * Show loan details
     */
    public function show($id)
    {
        $loan = BookLoan::with(['book', 'user', 'student', 'processor'])->findOrFail($id);
        return view('library.loans.show', compact('loan'));
    }

    /**
     * Get fine calculation for a loan (AJAX)
     */
    public function getFine($id)
    {
        $loan = BookLoan::findOrFail($id);
        
        return response()->json([
            'fine' => $loan->calculateFine(),
            'days_overdue' => $loan->daysOverdue(),
            'is_overdue' => $loan->isOverdue(),
        ]);
    }

    /**
     * Edit loan
     */
    public function edit($id)
    {
        $loan = BookLoan::with(['book', 'user'])->findOrFail($id);
        $books = Book::active()->get();
        
        return view('library.loans.edit', compact('loan', 'books'));
    }

    /**
     * Update loan
     */
    public function update(Request $request, $id)
    {
        $loan = BookLoan::findOrFail($id);
        
        $validated = $request->validate([
            'tanggal_kembali_rencana' => 'required|date',
            'catatan' => 'nullable|string',
        ]);

        $loan->update($validated);

        return redirect()->route('manage.library.loans.index')
            ->with('success', 'Peminjaman berhasil diupdate!');
    }
}

