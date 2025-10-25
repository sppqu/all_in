<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SPMBWaveAdditionalFee extends Model
{
    use HasFactory;

    protected $table = 'spmb_wave_additional_fees';

    protected $fillable = [
        'wave_id',
        'additional_fee_id',
        'amount',
        'is_active'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function wave()
    {
        return $this->belongsTo(SPMBWave::class, 'wave_id');
    }

    public function additionalFee()
    {
        return $this->belongsTo(SPMBAdditionalFee::class, 'additional_fee_id');
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }
}