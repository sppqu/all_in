<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payment';
    protected $primaryKey = 'payment_id';
    public $timestamps = false;

    protected $fillable = [
        'payment_type',
        'is_for_spmb',
        'period_period_id',
        'pos_pos_id',
        'school_id',
        'payment_input_date',
        'payment_last_update',
    ];

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id', 'id');
    }

    public function period()
    {
        return $this->belongsTo(\App\Models\Period::class, 'period_period_id', 'period_id');
    }
    public function pos()
    {
        return $this->belongsTo(\App\Models\Pos::class, 'pos_pos_id', 'pos_id');
    }

    protected $casts = [
        'is_for_spmb' => 'boolean',
    ];
} 