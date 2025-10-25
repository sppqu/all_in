<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JurnalEntry extends Model
{
    use HasFactory;

    protected $table = 'jurnal_entries';
    protected $primaryKey = 'entry_id';

    protected $fillable = [
        'jurnal_id',
        'kategori_id',
        'kegiatan',
        'jam',
        'checklist_data',
        'nilai',
        'catatan',
        'keterangan',
        'foto',
        'waktu_mulai',
        'waktu_selesai',
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
    ];

    /**
     * Relasi ke JurnalHarian
     */
    public function jurnal()
    {
        return $this->belongsTo(JurnalHarian::class, 'jurnal_id', 'jurnal_id');
    }

    /**
     * Relasi ke JurnalHarian (Alias untuk konsistensi)
     */
    public function jurnalHarian()
    {
        return $this->belongsTo(JurnalHarian::class, 'jurnal_id', 'jurnal_id');
    }

    /**
     * Relasi ke JurnalKategori
     */
    public function kategori()
    {
        return $this->belongsTo(JurnalKategori::class, 'kategori_id', 'kategori_id');
    }

    /**
     * Get nilai badge color
     */
    public function getNilaiBadgeAttribute()
    {
        if ($this->nilai >= 9) return 'success';
        if ($this->nilai >= 7) return 'info';
        if ($this->nilai >= 5) return 'warning';
        return 'danger';
    }

    /**
     * Get nilai text
     */
    public function getNilaiTextAttribute()
    {
        if ($this->nilai >= 9) return 'Sangat Baik';
        if ($this->nilai >= 7) return 'Baik';
        if ($this->nilai >= 5) return 'Cukup';
        return 'Perlu Perbaikan';
    }
}

