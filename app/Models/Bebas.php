<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bebas extends Model
{
    protected $primaryKey = 'bebas_id';
    public $timestamps = false;
    
    protected $fillable = [
        'student_student_id',
        'payment_payment_id',
        'bebas_bill',
        'bebas_total_pay',
        'bebas_desc',
        'bebas_input_date',
        'bebas_last_update'
    ];

    protected $casts = [
        'bebas_bill' => 'decimal:0',
        'bebas_total_pay' => 'decimal:0',
        'bebas_input_date' => 'datetime',
        'bebas_last_update' => 'datetime'
    ];

    // Relasi ke Student
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_student_id', 'student_id');
    }

    // Relasi ke Payment
    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_payment_id', 'payment_id');
    }
}
