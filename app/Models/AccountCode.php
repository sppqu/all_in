<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountCode extends Model
{
    use HasFactory;

    protected $table = 'account_codes';
    
    // Primary key default adalah 'id'
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
        'tipe',
        'kategori',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope untuk akun yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk filter berdasarkan tipe
     */
    public function scopeByType($query, $tipe)
    {
        return $query->where('tipe', $tipe);
    }

    /**
     * Scope untuk filter berdasarkan kategori
     */
    public function scopeByCategory($query, $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    /**
     * Scope untuk pencarian
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('kode', 'like', "%{$search}%")
              ->orWhere('nama', 'like', "%{$search}%")
              ->orWhere('deskripsi', 'like', "%{$search}%");
        });
    }

    /**
     * Get tipe label
     */
    public function getTipeLabelAttribute()
    {
        $labels = [
            'aktiva' => 'Aktiva',
            'pasiva' => 'Pasiva',
            'modal' => 'Modal',
            'pendapatan' => 'Pendapatan',
            'beban' => 'Beban'
        ];

        return $labels[$this->tipe] ?? $this->tipe;
    }

    /**
     * Get kategori label
     */
    public function getKategoriLabelAttribute()
    {
        $labels = [
            'lancar' => 'Lancar',
            'tetap' => 'Tetap',
            'pendapatan' => 'Pendapatan',
            'beban_operasional' => 'Beban Operasional',
            'beban_non_operasional' => 'Beban Non-Operasional'
        ];

        return $labels[$this->kategori] ?? $this->kategori;
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        return $this->is_active ? 'Aktif' : 'Non-Aktif';
    }

    /**
     * Get tipe badge class
     */
    public function getTipeBadgeClassAttribute()
    {
        $classes = [
            'aktiva' => 'badge bg-primary',
            'pasiva' => 'badge bg-warning',
            'modal' => 'badge bg-success',
            'pendapatan' => 'badge bg-info',
            'beban' => 'badge bg-danger'
        ];

        return $classes[$this->tipe] ?? 'badge bg-secondary';
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        return $this->is_active ? 'badge bg-success' : 'badge bg-secondary';
    }
} 