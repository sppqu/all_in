<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggaran extends Model
{
    use HasFactory;

    protected $table = 'pelanggaran';

    protected $fillable = [
        'kategori_id',
        'kode',
        'nama',
        'point',
        'keterangan',
        'is_active'
    ];

    protected $casts = [
        'point' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Relationship: Pelanggaran belongs to Kategori
     */
    public function kategori()
    {
        return $this->belongsTo(PelanggaranKategori::class, 'kategori_id');
    }

    /**
     * Relationship: Pelanggaran has many PelanggaranSiswa
     */
    public function pelanggaranSiswa()
    {
        return $this->hasMany(PelanggaranSiswa::class, 'pelanggaran_id');
    }

    /**
     * Scope: Only active pelanggaran
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: By category
     */
    public function scopeByKategori($query, $kategoriId)
    {
        return $query->where('kategori_id', $kategoriId);
    }
}
