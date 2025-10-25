<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PelanggaranSiswa extends Model
{
    use HasFactory;

    protected $table = 'pelanggaran_siswa';

    protected $fillable = [
        'siswa_id',
        'pelanggaran_id',
        'tanggal_pelanggaran',
        'keterangan',
        'pelapor',
        'tempat',
        'status',
        'catatan_admin',
        'created_by',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'tanggal_pelanggaran' => 'date',
        'approved_at' => 'datetime',
    ];

    /**
     * Relationship: PelanggaranSiswa belongs to Student
     */
    public function siswa()
    {
        return $this->belongsTo(Student::class, 'siswa_id', 'student_id');
    }

    /**
     * Relationship: PelanggaranSiswa belongs to Pelanggaran
     */
    public function pelanggaran()
    {
        return $this->belongsTo(Pelanggaran::class, 'pelanggaran_id');
    }

    /**
     * Relationship: Created by User
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship: Approved by User
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope: Filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Filter by siswa
     */
    public function scopeBySiswa($query, $siswaId)
    {
        return $query->where('siswa_id', $siswaId);
    }

    /**
     * Scope: Filter by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_pelanggaran', [$startDate, $endDate]);
    }

    /**
     * Get total point untuk siswa tertentu
     */
    public static function getTotalPointSiswa($siswaId, $status = 'approved')
    {
        return self::where('siswa_id', $siswaId)
            ->where('status', $status)
            ->with('pelanggaran')
            ->get()
            ->sum(function ($item) {
                return $item->pelanggaran->point ?? 0;
            });
    }
}
