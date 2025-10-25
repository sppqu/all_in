<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BookLoan extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'user_id',
        'student_id',
        'tanggal_pinjam',
        'tanggal_kembali_rencana',
        'tanggal_kembali_aktual',
        'status',
        'denda',
        'catatan',
        'processed_by'
    ];

    protected $casts = [
        'tanggal_pinjam' => 'date',
        'tanggal_kembali_rencana' => 'date',
        'tanggal_kembali_aktual' => 'date',
    ];

    /**
     * Get the book being loaned
     */
    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    /**
     * Get the user who borrowed the book
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the student (if user is student)
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    /**
     * Get the user who processed the loan
     */
    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Scope: Active loans
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'dipinjam');
    }

    /**
     * Scope: Overdue loans
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'dipinjam')
            ->where('tanggal_kembali_rencana', '<', Carbon::today());
    }

    /**
     * Check if loan is overdue
     */
    public function isOverdue()
    {
        if ($this->status !== 'dipinjam') {
            return false;
        }
        
        return Carbon::parse($this->tanggal_kembali_rencana)->isPast();
    }

    /**
     * Calculate days overdue
     */
    public function daysOverdue()
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        
        return Carbon::now()->diffInDays(Carbon::parse($this->tanggal_kembali_rencana));
    }

    /**
     * Calculate fine amount (Rp 1000 per day)
     */
    public function calculateFine()
    {
        return $this->daysOverdue() * 1000;
    }
}
