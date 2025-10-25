<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PelanggaranKategori extends Model
{
    use HasFactory;

    protected $table = 'pelanggaran_kategori';

    protected $fillable = [
        'nama',
        'kode',
        'keterangan',
        'warna',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relationship: Kategori has many Pelanggaran
     */
    public function pelanggaran()
    {
        return $this->hasMany(Pelanggaran::class, 'kategori_id');
    }

    /**
     * Scope: Only active categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
