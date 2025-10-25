<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\ReadingHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        $pdfUrl = Storage::url($book->file_path);

        return view('library.reader.read', compact('book', 'pdfUrl'));
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
        
        // Get student ID if user is student
        if (auth()->check() && auth()->user()->role === 'student') {
            $student = \App\Models\Student::where('student_email', auth()->user()->email)->first();
            $studentId = $student ? $student->student_id : null;
        }

        ReadingHistory::create([
            'book_id' => $book->id,
            'user_id' => auth()->id() ?? null,
            'student_id' => $studentId,
            'activity_type' => $activityType,
            'started_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
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

        $history = ReadingHistory::where('book_id', $id)
            ->where('user_id', auth()->id())
            ->where('activity_type', 'read')
            ->latest()
            ->first();

        if ($history) {
            $history->update([
                'last_page' => $validated['last_page'],
                'reading_duration' => $validated['reading_duration'] ?? 0,
            ]);
        }

        return response()->json(['success' => true]);
    }
}

