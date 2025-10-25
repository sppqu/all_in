<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BimbinganKonseling extends Model
{
    use HasFactory;

    protected $table = 'bimbingan_konseling';
    protected $primaryKey = 'bimbingan_id';

    protected $fillable = [
        'siswa_id',
        'jenis_bimbingan',
        'kategori',
        'permasalahan',
        'analisis',
        'tindakan',
        'hasil',
        'tanggal_bimbingan',
        'sesi_ke',
        'status',
        'catatan',
        'guru_bk_id',
    ];

    protected $casts = [
        'tanggal_bimbingan' => 'date',
    ];

    /**
     * Relasi ke Student
     */
    public function siswa()
    {
        return $this->belongsTo(Student::class, 'siswa_id', 'student_id');
    }

    /**
     * Relasi ke User (Guru BK)
     */
    public function guruBk()
    {
        return $this->belongsTo(User::class, 'guru_bk_id');
    }

    /**
     * Scope untuk filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk filter by jenis
     */
    public function scopeByJenis($query, $jenis)
    {
        return $query->where('jenis_bimbingan', $jenis);
    }

    /**
     * Get badge class by status
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'dijadwalkan' => 'primary',
            'berlangsung' => 'warning',
            'selesai' => 'success',
            'ditunda' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Get kategori badge class
     */
    public function getKategoriBadgeAttribute()
    {
        return match($this->kategori) {
            'ringan' => 'info',
            'sedang' => 'warning',
            'berat' => 'danger',
            default => 'secondary',
        };
    }
}

