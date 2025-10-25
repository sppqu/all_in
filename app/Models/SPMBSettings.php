<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SPMBSettings extends Model
{
    protected $table = 's_p_m_b_settings';
    
    protected $fillable = [
        'tahun_pelajaran',
        'pendaftaran_dibuka',
        'tanggal_buka',
        'tanggal_tutup',
        'biaya_pendaftaran',
        'biaya_spmb',
        'deskripsi',
        'pengaturan_tambahan'
    ];

    protected $casts = [
        'pendaftaran_dibuka' => 'boolean',
        'tanggal_buka' => 'date',
        'tanggal_tutup' => 'date',
        'biaya_pendaftaran' => 'decimal:2',
        'biaya_spmb' => 'decimal:2',
        'pengaturan_tambahan' => 'array'
    ];

    /**
     * Get current active settings
     */
    public static function getCurrentSettings()
    {
        return self::where('pendaftaran_dibuka', true)->first();
    }

    /**
     * Check if registration is open
     */
    public function isRegistrationOpen()
    {
        if (!$this->pendaftaran_dibuka) {
            return false;
        }

        $now = now();
        
        if ($this->tanggal_buka && $now->lt($this->tanggal_buka)) {
            return false;
        }

        if ($this->tanggal_tutup && $now->gt($this->tanggal_tutup)) {
            return false;
        }

        return true;
    }
}
