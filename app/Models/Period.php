<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    use HasFactory;

    protected $primaryKey = 'period_id';
    
    protected $fillable = [
        'period_start',
        'period_end',
        'period_status'
    ];

    protected $casts = [
        'period_start' => 'integer',
        'period_end' => 'integer',
        'period_status' => 'boolean'
    ];

    // Accessor untuk mendapatkan nama periode
    public function getPeriodNameAttribute()
    {
        return $this->period_start . '/' . $this->period_end;
    }

    // Accessor untuk status text
    public function getStatusTextAttribute()
    {
        return $this->period_status ? 'Aktif' : 'Non-Aktif';
    }

    // Scope untuk periode aktif
    public function scopeActive($query)
    {
        return $query->where('period_status', 1);
    }
}
