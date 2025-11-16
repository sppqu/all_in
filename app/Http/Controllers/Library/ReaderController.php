<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\ReadingHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ReaderController extends Controller
{
    /**
     * Read book online (PDF viewer)
     */
    public function read($id)
    {
        $book = Book::findOrFail($id);

        if (!$book->file_path) {
            return back()->with('error', 'File PDF tidak tersedia!');
        }

        // Log reading activity
        $this->logActivity($book, 'read');

        // Use route for PDF viewer instead of Storage::url()
        // Both student and admin use the same route name (library.serve-pdf)
        $pdfUrl = route('library.serve-pdf', $book->id);

        return view('library.reader.read', compact('book', 'pdfUrl'));
    }

    /**
     * Serve PDF file for viewing
     * Supports both student (via session) and admin (via auth)
     */
    public function servePdf($id)
    {
        // Check authentication (student session or regular auth)
        if (!session('is_student') && !auth()->check()) {
            abort(401, 'Unauthorized');
        }

        $book = Book::findOrFail($id);

        if (!$book->file_path) {
            abort(404, 'File PDF tidak tersedia!');
        }

        // Check if file exists
        if (!Storage::disk('public')->exists($book->file_path)) {
            abort(404, 'File PDF tidak ditemukan!');
        }

        $filePath = Storage::disk('public')->path($book->file_path);

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $book->judul . '.pdf"',
        ]);
    }

    /**
     * Download book PDF
     */
    public function download($id)
    {
        $book = Book::findOrFail($id);

        if (!$book->file_path) {
            return back()->with('error', 'File PDF tidak tersedia!');
        }

        // Log download activity
        $this->logActivity($book, 'download');

        // Increment download count
        $book->incrementDownloads();

        return Storage::download($book->file_path, $book->judul . '.pdf');
    }

    /**
     * Log reading activity
     */
    private function logActivity(Book $book, $activityType)
    {
        $studentId = null;
        $userId = null;
        
        // Check if user is student (from session)
        if (session('is_student')) {
            $studentId = session('student_id');
            
            // Get or create user for student (same logic as StudentAuthController@library)
            if ($studentId) {
                $student = \App\Models\Student::find($studentId);
                if ($student) {
                    $userEmail = $student->student_email ?? 'student' . $student->student_id . '@temp.com';
                    
                    // Check if user exists by email
                    $user = DB::table('users')->where('email', $userEmail)->first();
                    
                    if (!$user) {
                        // Create user if not exists
                        try {
                            $userId = DB::table('users')->insertGetId([
                                'name' => $student->student_full_name,
                                'email' => $userEmail,
                                'password' => bcrypt('password'),
                                'role' => 'student',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            $user = DB::table('users')->where('id', $userId)->first();
                        } catch (\Exception $e) {
                            // If insert fails (duplicate), try to get existing user
                            $user = DB::table('users')->where('email', $userEmail)->first();
                        }
                    }
                    
                    if ($user) {
                        $userId = $user->id;
                    }
                }
            }
        } else if (auth()->check()) {
            // Regular user (admin, etc.)
            $userId = auth()->id();
            
            // Check if authenticated user is student
            if (auth()->user()->role === 'student') {
                $student = \App\Models\Student::where('student_email', auth()->user()->email)->first();
                $studentId = $student ? $student->student_id : null;
            }
        }

        // Only create reading history if user_id is available (required field)
        if ($userId) {
            ReadingHistory::create([
                'book_id' => $book->id,
                'user_id' => $userId,
                'student_id' => $studentId,
                'activity_type' => $activityType,
                'started_at' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }

    /**
     * Update reading progress
     */
    public function updateProgress(Request $request, $id)
    {
        $validated = $request->validate([
            'last_page' => 'required|integer|min:1',
            'reading_duration' => 'nullable|integer',
        ]);

        $studentId = null;
        $userId = null;
        
        // Check if user is student (from session)
        if (session('is_student')) {
            $studentId = session('student_id');
            
            // Get or create user for student (same logic as logActivity)
            if ($studentId) {
                $student = \App\Models\Student::find($studentId);
                if ($student) {
                    $userEmail = $student->student_email ?? 'student' . $student->student_id . '@temp.com';
                    
                    // Check if user exists by email
                    $user = DB::table('users')->where('email', $userEmail)->first();
                    
                    if (!$user) {
                        // Create user if not exists
                        try {
                            $userId = DB::table('users')->insertGetId([
                                'name' => $student->student_full_name,
                                'email' => $userEmail,
                                'password' => bcrypt('password'),
                                'role' => 'student',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            $user = DB::table('users')->where('id', $userId)->first();
                        } catch (\Exception $e) {
                            // If insert fails (duplicate), try to get existing user
                            $user = DB::table('users')->where('email', $userEmail)->first();
                        }
                    }
                    
                    if ($user) {
                        $userId = $user->id;
                    }
                }
            }
        } else if (auth()->check()) {
            $userId = auth()->id();
        }

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $query = ReadingHistory::where('book_id', $id)
            ->where('activity_type', 'read');
            
        if ($studentId) {
            $query->where('student_id', $studentId);
        } else {
            $query->where('user_id', $userId);
        }
        
        $history = $query->latest()->first();

        if ($history) {
            $history->update([
                'last_page' => $validated['last_page'],
                'reading_duration' => $validated['reading_duration'] ?? 0,
            ]);
        }

        return response()->json(['success' => true]);
    }
}

