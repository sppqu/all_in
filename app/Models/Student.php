<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $primaryKey = 'student_id';
    
    // Disable default timestamps karena menggunakan kolom kustom
    public $timestamps = false;
    
    // Set custom timestamp column names
    const CREATED_AT = 'student_input_date';
    const UPDATED_AT = 'student_last_update';
    
    protected $fillable = [
        'student_nis',
        'student_nisn',
        'student_password',
        'student_full_name',
        'student_gender',
        'student_born_place',
        'student_born_date',
        'student_img',
        'student_phone',
        'student_hobby',
        'student_address',
        'student_name_of_mother',
        'student_name_of_father',
        'student_parent_phone',
        'class_class_id',
        'majors_majors_id',
        'student_status',
        'student_last_update',
        'school_id'
    ];

    protected $casts = [
        'student_born_date' => 'date',
        'student_status' => 'boolean',
        'student_input_date' => 'datetime',
        'student_last_update' => 'datetime'
    ];

    protected $hidden = [
        'student_password'
    ];

    // Relasi dengan ClassModel
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_class_id', 'class_id');
    }

    // Relasi dengan Major
    public function major()
    {
        return $this->belongsTo(Major::class, 'majors_majors_id', 'majors_id');
    }

    // Relasi dengan School
    public function school()
    {
        return $this->belongsTo(\App\Models\School::class, 'school_id', 'id');
    }

    // Accessor untuk gender text
    public function getGenderTextAttribute()
    {
        return $this->student_gender === 'L' ? 'Laki-laki' : 'Perempuan';
    }

    // Accessor untuk status text
    public function getStatusTextAttribute()
    {
        return $this->student_status ? 'Aktif' : 'Non-Aktif';
    }

    // Accessor untuk umur
    public function getAgeAttribute()
    {
        if ($this->student_born_date) {
            return $this->student_born_date->age;
        }
        return null;
    }

    // Scope untuk filter berdasarkan kelas
    public function scopeByClass($query, $classId)
    {
        return $query->where('class_class_id', $classId);
    }

    // Scope untuk filter berdasarkan jurusan
    public function scopeByMajor($query, $majorId)
    {
        return $query->where('majors_majors_id', $majorId);
    }

    // Scope untuk siswa aktif
    public function scopeActive($query)
    {
        return $query->where('student_status', 1);
    }

    // Relasi dengan Transfer
    public function transfers()
    {
        return $this->hasMany(\App\Models\Transfer::class, 'student_id', 'student_id');
    }

    // Relasi dengan Pelanggaran Siswa
    public function pelanggaranSiswa()
    {
        return $this->hasMany(\App\Models\PelanggaranSiswa::class, 'siswa_id', 'student_id');
    }

    // Get total point pelanggaran
    public function getTotalPointPelanggaranAttribute()
    {
        return \App\Models\PelanggaranSiswa::getTotalPointSiswa($this->student_id);
    }

    // Get accessor untuk NIS (untuk compatibility)
    public function getNisAttribute()
    {
        return $this->student_nis;
    }

    // Get accessor untuk Nama (untuk compatibility)
    public function getNamaAttribute()
    {
        return $this->student_full_name;
    }
}
