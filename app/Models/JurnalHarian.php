<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JurnalHarian extends Model
{
    use HasFactory;

    protected $table = 'jurnal_harian';
    protected $primaryKey = 'jurnal_id';

    protected $fillable = [
        'siswa_id',
        'tanggal',
        'status',
        'catatan_umum',
        'refleksi',
        'verified_by',
        'verified_at',
        'catatan_guru',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'verified_at' => 'datetime',
    ];

    /**
     * Relasi ke Student
     */
    public function siswa()
    {
        return $this->belongsTo(Student::class, 'siswa_id', 'student_id');
    }

    /**
     * Relasi ke User (Guru yang verifikasi)
     */
    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Relasi ke JurnalEntry
     */
    public function entries()
    {
        return $this->hasMany(JurnalEntry::class, 'jurnal_id', 'jurnal_id');
    }

    /**
     * Scope untuk status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk tanggal range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal', [$startDate, $endDate]);
    }

    /**
     * Get total nilai/score
     */
    public function getTotalNilaiAttribute()
    {
        return $this->entries->sum('nilai');
    }

    /**
     * Get rata-rata nilai
     */
    public function getRataRataNilaiAttribute()
    {
        $count = $this->entries->count();
        return $count > 0 ? round($this->entries->sum('nilai') / $count, 2) : 0;
    }

    /**
     * Get badge class by status
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'draft' => 'secondary',
            'submitted' => 'info',
            'verified' => 'success',
            'revised' => 'warning',
            default => 'secondary',
        };
    }
}

