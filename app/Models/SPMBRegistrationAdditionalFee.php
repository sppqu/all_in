<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SPMBRegistrationAdditionalFee extends Model
{
    use HasFactory;

    protected $table = 'spmb_registration_additional_fees';

    protected $fillable = [
        'registration_id',
        'additional_fee_id',
        'amount',
        'is_paid',
        'paid_at',
        'metadata'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_paid' => 'boolean',
        'paid_at' => 'datetime',
        'metadata' => 'array'
    ];

    // Relationships
    public function registration()
    {
        return $this->belongsTo(SPMBRegistration::class, 'registration_id');
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

    public function getStatusBadgeAttribute()
    {
        if ($this->is_paid) {
            return '<span class="badge bg-success">Lunas</span>';
        }
        return '<span class="badge bg-warning">Belum Bayar</span>';
    }

    // Helper methods
    public function markAsPaid()
    {
        $this->update([
            'is_paid' => true,
            'paid_at' => now()
        ]);
    }

    public function markAsUnpaid()
    {
        $this->update([
            'is_paid' => false,
            'paid_at' => null
        ]);
    }
}