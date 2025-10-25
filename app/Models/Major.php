<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Major extends Model
{
    use HasFactory;

    protected $primaryKey = 'majors_id';
    
    protected $fillable = [
        'majors_name'
    ];

    // Relasi dengan Student
    public function students()
    {
        return $this->hasMany(Student::class, 'majors_majors_id', 'majors_id');
    }
}
