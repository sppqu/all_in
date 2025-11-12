<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'foundation_id',
        'jenjang',
        'nama_sekolah',
        'kepala_sekolah',
        'npsn',
        'email',
        'alamat',
        'alamat_baris_1',
        'alamat_baris_2',
        'no_telp',
        'logo_sekolah',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function foundation()
    {
        return $this->belongsTo(Foundation::class, 'foundation_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'school_id', 'id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_schools', 'school_id', 'user_id')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    public function classes()
    {
        return $this->hasMany(ClassModel::class, 'school_id', 'id');
    }

    public function periods()
    {
        return $this->hasMany(Period::class, 'school_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getTotalStudentsAttribute()
    {
        return $this->students()->where('student_status', 1)->count();
    }

    public function getTotalClassesAttribute()
    {
        return $this->classes()->count();
    }
}

