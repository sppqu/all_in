<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SPMBKejuruan extends Model
{
    protected $table = 's_p_m_b_kejuruans';
    
    protected $fillable = [
        'nama_kejuruan',
        'kode_kejuruan',
        'deskripsi',
        'aktif',
        'kuota'
    ];

    protected $casts = [
        'aktif' => 'boolean'
    ];

    /**
     * Get active kejuruan
     */
    public static function getActive()
    {
        return self::where('aktif', true)->orderBy('nama_kejuruan')->get();
    }

    /**
     * Get registrations for this kejuruan
     */
    public function registrations()
    {
        return $this->hasMany(SPMBRegistration::class, 'kejuruan_id');
    }

    /**
     * Get remaining quota
     */
    public function getRemainingQuotaAttribute()
    {
        if (!$this->kuota) {
            return null;
        }

        $used = $this->registrations()->where('status_pendaftaran', 'diterima')->count();
        return max(0, $this->kuota - $used);
    }
}
