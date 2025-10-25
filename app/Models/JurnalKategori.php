<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JurnalKategori extends Model
{
    use HasFactory;

    protected $table = 'jurnal_kategori';
    protected $primaryKey = 'kategori_id';

    protected $fillable = [
        'nama_kategori',
        'kode',
        'deskripsi',
        'icon',
        'warna',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relasi ke JurnalEntry
     */
    public function entries()
    {
        return $this->hasMany(JurnalEntry::class, 'kategori_id', 'kategori_id');
    }

    /**
     * Scope untuk kategori aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('urutan');
    }
}

