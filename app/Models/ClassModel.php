<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    use HasFactory;

    protected $primaryKey = 'class_id';
    
    protected $fillable = [
        'class_name'
    ];

    // Aktifkan timestamps
    public $timestamps = true;

    // Relasi dengan Student (update foreign key)
    public function students()
    {
        return $this->hasMany(Student::class, 'class_class_id', 'class_id');
    }

    // Accessor untuk mendapatkan jumlah siswa
    public function getStudentCountAttribute()
    {
        return $this->students()->count();
    }
}
