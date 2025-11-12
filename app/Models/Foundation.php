<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Foundation extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_yayasan',
        'alamat_yayasan',
        'no_telp_yayasan',
        'logo_yayasan',
    ];

    public function schools()
    {
        return $this->hasMany(School::class, 'foundation_id');
    }

    public function activeSchools()
    {
        return $this->schools()->where('status', 'active');
    }
}





