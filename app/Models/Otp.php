<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    use HasFactory;

    protected $table = 'otp';
    protected $primaryKey = 'id';
    
    // Nonaktifkan timestamps otomatis karena tabel tidak memiliki created_at dan updated_at
    public $timestamps = false;

    protected $fillable = [
        'nomor',
        'otp',
        'waktu'
    ];

    protected $casts = [
        'waktu' => 'integer',
        'otp' => 'string' // Pastikan OTP selalu string untuk mempertahankan leading zeros
    ];

    // Scope untuk OTP yang masih berlaku (5 menit)
    public function scopeValid($query)
    {
        $fiveMinutesAgo = time() - (5 * 60);
        return $query->where('waktu', '>', $fiveMinutesAgo);
    }

    // Cek apakah OTP masih berlaku
    public function isValid()
    {
        $fiveMinutesAgo = time() - (5 * 60);
        return $this->waktu > $fiveMinutesAgo;
    }
}
