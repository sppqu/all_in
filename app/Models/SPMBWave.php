<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class SPMBWave extends Model
{
    protected $table = 'spmb_waves';
    
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'registration_fee',
        'spmb_fee',
        'is_active',
        'quota'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'registration_fee' => 'decimal:2',
        'spmb_fee' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    /**
     * Get registrations for this wave
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(SPMBRegistration::class, 'wave_id');
    }

    /**
     * Get additional fees for this wave
     */
    public function additionalFees()
    {
        return $this->belongsToMany(SPMBAdditionalFee::class, 'spmb_wave_additional_fees')
                    ->withPivot(['amount', 'is_active'])
                    ->withTimestamps();
    }

    /**
     * Check if wave is currently active
     */
    public function isCurrentlyActive(): bool
    {
        $now = Carbon::now();
        return $this->is_active && 
               $now->gte($this->start_date) && 
               $now->lte($this->end_date);
    }

    /**
     * Check if wave is expired
     */
    public function isExpired(): bool
    {
        return Carbon::now()->gt($this->end_date);
    }

    /**
     * Check if wave hasn't started yet
     */
    public function isNotStarted(): bool
    {
        return Carbon::now()->lt($this->start_date);
    }

    /**
     * Get formatted registration fee
     */
    public function getFormattedRegistrationFeeAttribute(): string
    {
        return 'Rp ' . number_format($this->registration_fee, 0, ',', '.');
    }

    /**
     * Get formatted SPMB fee
     */
    public function getFormattedSpmbFeeAttribute(): string
    {
        return 'Rp ' . number_format($this->spmb_fee, 0, ',', '.');
    }

    /**
     * Get status badge for wave
     */
    public function getStatusBadgeAttribute(): string
    {
        if (!$this->is_active) {
            return '<span class="badge bg-secondary">Nonaktif</span>';
        }

        if ($this->isNotStarted()) {
            return '<span class="badge bg-info">Belum Dimulai</span>';
        }

        if ($this->isExpired()) {
            return '<span class="badge bg-danger">Berakhir</span>';
        }

        if ($this->isCurrentlyActive()) {
            return '<span class="badge bg-success">Aktif</span>';
        }

        return '<span class="badge bg-warning">Tidak Diketahui</span>';
    }

    /**
     * Get current registrations count
     */
    public function getCurrentRegistrationsCountAttribute(): int
    {
        return $this->registrations()->count();
    }

    /**
     * Check if quota is full
     */
    public function isQuotaFull(): bool
    {
        if (!$this->quota) {
            return false;
        }

        return $this->current_registrations_count >= $this->quota;
    }
}
