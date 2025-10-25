<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'judul',
        'pengarang',
        'penerbit',
        'tahun_terbit',
        'isbn',
        'deskripsi',
        'cover_image',
        'file_path',
        'jumlah_halaman',
        'bahasa',
        'status',
        'total_views',
        'total_downloads',
        'total_loans',
        'is_featured',
        'is_active',
        'uploaded_by'
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'tahun_terbit' => 'integer',
    ];

    /**
     * Get the category that owns the book
     */
    public function category()
    {
        return $this->belongsTo(BookCategory::class, 'category_id');
    }

    /**
     * Get the user who uploaded the book
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get loans for this book
     */
    public function loans()
    {
        return $this->hasMany(BookLoan::class, 'book_id');
    }

    /**
     * Get reading history for this book
     */
    public function readingHistory()
    {
        return $this->hasMany(ReadingHistory::class, 'book_id');
    }

    /**
     * Scope: Only active books
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Only available books
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'tersedia');
    }

    /**
     * Scope: Featured books
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope: Search books
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('judul', 'like', "%{$search}%")
                ->orWhere('pengarang', 'like', "%{$search}%")
                ->orWhere('penerbit', 'like', "%{$search}%")
                ->orWhere('isbn', 'like', "%{$search}%")
                ->orWhere('deskripsi', 'like', "%{$search}%");
        });
    }

    /**
     * Increment view count
     */
    public function incrementViews()
    {
        $this->increment('total_views');
    }

    /**
     * Increment download count
     */
    public function incrementDownloads()
    {
        $this->increment('total_downloads');
    }

    /**
     * Increment loan count
     */
    public function incrementLoans()
    {
        $this->increment('total_loans');
    }
}
