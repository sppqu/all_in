<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'jenjang',
        'nama_sekolah',
        'alamat',
        'no_telp',
        'logo_sekolah',
    ];
}
