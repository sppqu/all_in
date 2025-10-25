<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BookController extends Controller
{
    /**
     * Display a listing of books
     */
    public function index()
    {
        $books = Book::with(['category', 'uploader'])
            ->latest()
            ->paginate(15);
        
        return view('library.books.index', compact('books'));
    }

    /**
     * Show the form for creating a new book
     */
    public function create()
    {
        $categories = BookCategory::active()->ordered()->get();
        return view('library.books.create', compact('categories'));
    }

    /**
     * Store a newly created book
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:book_categories,id',
            'judul' => 'required|string|max:255',
            'pengarang' => 'required|string|max:255',
            'penerbit' => 'nullable|string|max:255',
            'tahun_terbit' => 'nullable|integer|min:1900|max:' . date('Y'),
            'isbn' => 'nullable|string|max:50|unique:books,isbn',
            'deskripsi' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'file_pdf' => 'nullable|mimes:pdf|max:50000', // Max 50MB
            'jumlah_halaman' => 'nullable|integer|min:1',
            'bahasa' => 'nullable|string|max:50',
            'is_featured' => 'nullable|boolean',
            'status' => 'required|in:tersedia,tidak_tersedia',
        ]);

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')->store('library/covers', 'public');
            $validated['cover_image'] = $coverPath;
        }

        // Handle PDF upload
        if ($request->hasFile('file_pdf')) {
            $pdfPath = $request->file('file_pdf')->store('library/books', 'public');
            $validated['file_path'] = $pdfPath;
        }

        $validated['uploaded_by'] = auth()->id();
        $validated['is_featured'] = $request->has('is_featured');

        Book::create($validated);

        return redirect()->route('library.books.index')
            ->with('success', 'Buku berhasil ditambahkan!');
    }

    /**
     * Display the specified book
     */
    public function show($id)
    {
        $book = Book::with(['category', 'uploader', 'loans', 'readingHistory'])->findOrFail($id);
        return view('library.books.show', compact('book'));
    }

    /**
     * Show the form for editing the specified book
     */
    public function edit($id)
    {
        $book = Book::findOrFail($id);
        $categories = BookCategory::active()->ordered()->get();
        return view('library.books.edit', compact('book', 'categories'));
    }

    /**
     * Update the specified book
     */
    public function update(Request $request, $id)
    {
        $book = Book::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'required|exists:book_categories,id',
            'judul' => 'required|string|max:255',
            'pengarang' => 'required|string|max:255',
            'penerbit' => 'nullable|string|max:255',
            'tahun_terbit' => 'nullable|integer|min:1900|max:' . date('Y'),
            'isbn' => 'nullable|string|max:50|unique:books,isbn,' . $id,
            'deskripsi' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'file_pdf' => 'nullable|mimes:pdf|max:50000',
            'jumlah_halaman' => 'nullable|integer|min:1',
            'bahasa' => 'nullable|string|max:50',
            'is_featured' => 'nullable|boolean',
            'status' => 'required|in:tersedia,tidak_tersedia',
        ]);

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            // Delete old cover
            if ($book->cover_image) {
                Storage::disk('public')->delete($book->cover_image);
            }
            $coverPath = $request->file('cover_image')->store('library/covers', 'public');
            $validated['cover_image'] = $coverPath;
        }

        // Handle PDF upload
        if ($request->hasFile('file_pdf')) {
            // Delete old PDF
            if ($book->file_path) {
                Storage::disk('public')->delete($book->file_path);
            }
            $pdfPath = $request->file('file_pdf')->store('library/books', 'public');
            $validated['file_path'] = $pdfPath;
        }

        $validated['is_featured'] = $request->has('is_featured');

        $book->update($validated);

        return redirect()->route('library.books.index')
            ->with('success', 'Buku berhasil diupdate!');
    }

    /**
     * Remove the specified book
     */
    public function destroy($id)
    {
        $book = Book::findOrFail($id);

        // Delete files
        if ($book->cover_image) {
            Storage::disk('public')->delete($book->cover_image);
        }
        if ($book->file_path) {
            Storage::disk('public')->delete($book->file_path);
        }

        $book->delete();

        return redirect()->route('library.books.index')
            ->with('success', 'Buku berhasil dihapus!');
    }
}

