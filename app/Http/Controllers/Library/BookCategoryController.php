<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\BookCategory;
use Illuminate\Http\Request;

class BookCategoryController extends Controller
{
    /**
     * Display a listing of categories
     */
    public function index()
    {
        $categories = BookCategory::withCount('books')
            ->ordered()
            ->paginate(15);
        
        return view('library.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category
     */
    public function create()
    {
        return view('library.categories.create');
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:100',
            'kode' => 'required|string|max:20|unique:book_categories,kode',
            'deskripsi' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'warna' => 'nullable|string|max:7',
            'urutan' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['icon'] = $validated['icon'] ?? 'fas fa-folder';
        $validated['warna'] = $validated['warna'] ?? '#3498db';
        $validated['is_active'] = $request->has('is_active');

        BookCategory::create($validated);

        return redirect()->route('library.categories.index')
            ->with('success', 'Kategori berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the specified category
     */
    public function edit($id)
    {
        $category = BookCategory::findOrFail($id);
        return view('library.categories.edit', compact('category'));
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, $id)
    {
        $category = BookCategory::findOrFail($id);

        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:100',
            'kode' => 'required|string|max:20|unique:book_categories,kode,' . $id,
            'deskripsi' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'warna' => 'nullable|string|max:7',
            'urutan' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $category->update($validated);

        return redirect()->route('library.categories.index')
            ->with('success', 'Kategori berhasil diupdate!');
    }

    /**
     * Remove the specified category
     */
    public function destroy($id)
    {
        $category = BookCategory::findOrFail($id);

        // Check if category has books
        if ($category->books()->count() > 0) {
            return redirect()->route('library.categories.index')
                ->with('error', 'Kategori tidak bisa dihapus karena masih memiliki buku!');
        }

        $category->delete();

        return redirect()->route('library.categories.index')
            ->with('success', 'Kategori berhasil dihapus!');
    }
}

