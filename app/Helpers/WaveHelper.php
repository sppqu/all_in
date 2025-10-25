<?php

namespace App\Helpers;

use App\Models\SPMBWave;
use App\Models\SPMBSettings;
use App\Models\SPMBAdditionalFee;

class WaveHelper
{
    /**
     * Get the appropriate registration fee for a registration
     * Priority: Wave fee > Settings fee > Config fee
     */
    public static function getRegistrationFee($registration = null)
    {
        // If registration has a wave, use wave fee
        if ($registration && $registration->wave_id) {
            $wave = SPMBWave::find($registration->wave_id);
            if ($wave && $wave->is_active && $wave->registration_fee > 0) {
                return $wave->registration_fee;
            }
        }

        // Get from active SPMB settings
        $settings = SPMBSettings::where('pendaftaran_dibuka', true)->first();
        if ($settings && $settings->biaya_pendaftaran > 0) {
            return $settings->biaya_pendaftaran;
        }

        // Fallback to config
        return config('tripay.spmb.registration_fee', 50000);
    }

    /**
     * Get the appropriate SPMB fee for a registration
     * Priority: Wave fee > Settings fee > Config fee
     */
    public static function getSpmbFee($registration = null)
    {
        // If registration has a wave, use wave fee
        if ($registration && $registration->wave_id) {
            $wave = SPMBWave::find($registration->wave_id);
            if ($wave && $wave->is_active && $wave->spmb_fee > 0) {
                return $wave->spmb_fee;
            }
        }

        // Get from active SPMB settings
        $settings = SPMBSettings::where('pendaftaran_dibuka', true)->first();
        if ($settings && $settings->biaya_spmb > 0) {
            return $settings->biaya_spmb;
        }

        // Fallback to config
        return config('tripay.spmb.spmb_fee', 200000);
    }

    /**
     * Get available waves for selection
     */
    public static function getActiveWaves()
    {
        return SPMBWave::where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->orderBy('start_date')
            ->get();
    }

    /**
     * Get wave info for display
     */
    public static function getWaveInfo($waveId)
    {
        $wave = SPMBWave::find($waveId);
        if (!$wave) {
            return null;
        }

        return [
            'id' => $wave->id,
            'name' => $wave->name,
            'description' => $wave->description,
            'registration_fee' => $wave->registration_fee,
            'spmb_fee' => $wave->spmb_fee,
            'formatted_registration_fee' => $wave->formatted_registration_fee,
            'formatted_spmb_fee' => $wave->formatted_spmb_fee,
            'start_date' => $wave->start_date,
            'end_date' => $wave->end_date,
            'quota' => $wave->quota,
            'current_registrations' => $wave->current_registrations_count,
            'is_quota_full' => $wave->isQuotaFull(),
            'status' => $wave->status_badge
        ];
    }

    /**
     * Check if registration can select a specific wave
     */
    public static function canSelectWave($waveId, $registration = null)
    {
        $wave = SPMBWave::find($waveId);
        if (!$wave || !$wave->is_active) {
            return false;
        }

        // Check if wave is currently active (within date range)
        if (!$wave->isCurrentlyActive()) {
            return false;
        }

        // Check quota if set
        if ($wave->quota && $wave->isQuotaFull()) {
            return false;
        }

        return true;
    }

    /**
     * Get additional fees for a specific wave
     */
    public static function getWaveAdditionalFees($waveId)
    {
        $wave = SPMBWave::find($waveId);
        if (!$wave) {
            return collect();
        }

        return $wave->additionalFees()
                    ->wherePivot('is_active', true)
                    ->orderByPivot('sort_order')
                    ->get()
                    ->map(function ($fee) use ($waveId) {
                        return [
                            'id' => $fee->id,
                            'name' => $fee->name,
                            'code' => $fee->code,
                            'description' => $fee->description,
                            'type' => $fee->type,
                            'category' => $fee->category,
                            'amount' => $fee->pivot->amount,
                            'formatted_amount' => 'Rp ' . number_format($fee->pivot->amount, 0, ',', '.'),
                            'conditions' => $fee->conditions,
                            'type_badge' => $fee->type_badge,
                            'category_badge' => $fee->category_badge
                        ];
                    });
    }

    /**
     * Get available additional fees for registration selection
     */
    public static function getAvailableAdditionalFees($registration = null)
    {
        $query = SPMBAdditionalFee::active()->ordered();

        // If registration has a wave, filter by wave+active fees
        if ($registration && $registration->wave_id) {
            $wave = SPMBWave::find($registration->wave_id);
            if ($wave) {
                return $wave->additionalFees()
                            ->wherePivot('is_active', true)
                            ->orderBy('sort_order')
                            ->get();
            }
        }

        // Fallback to all active fees
        return $query->get();
    }

    /**
     * Calculate total additional fees for a registration
     */
    public static function calculateTotalAdditionalFees($registration)
    {
        if (!$registration || !$registration->additionalFees) {
            return 0;
        }

        return $registration->additionalFees()
                           ->wherePivot('is_paid', false)
                           ->sum('spmb_registration_additional_fees.amount');
    }

    /**
     * Get additional fees breakdown for a registration
     */
    public static function getRegistrationAdditionalFeesBreakdown($registration)
    {
        if (!$registration) {
            return [];
        }

        return $registration->additionalFees()
                           ->get()
                           ->map(function ($fee) {
                               return [
                                   'id' => $fee->id,
                                   'name' => $fee->name,
                                   'code' => $fee->code,
                                   'amount' => $fee->pivot->amount,
                                   'formatted_amount' => 'Rp ' . number_format($fee->pivot->amount, 0, ',', '.'),
                                   'is_paid' => $fee->pivot->is_paid,
                                   'paid_at' => $fee->pivot->paid_at,
                                   'status_badge' => $fee->pivot->is_paid ? 
                                       '<span class="badge bg-success">Lunas</span>' : 
                                       '<span class="badge bg-warning">Belum Bayar</span>',
                                   'metadata' => $fee->pivot->metadata
                               ];
                           });
    }

    /**
     * Check if registration can add additional fee
     */
    public static function canAddAdditionalFee($registration, $additionalFeeId)
    {
        if (!$registration) {
            return false;
        }

        $additionalFee = SPMBAdditionalFee::find($additionalFeeId);
        if (!$additionalFee || !$additionalFee->is_active) {
            return false;
        }

        // Check conditions
        if (!$additionalFee->checkConditions($registration)) {
            return false;
        }

        // Check if already exists
        $exists = $registration->additionalFees()
                              ->where('additional_fee_id', $additionalFeeId)
                              ->exists();

        return !$exists;
    }

    /**
     * Get all fee categories
     */
    public static function getFeeCategories()
    {
        return [
            'seragam' => 'Seragam',
            'buku' => 'Buku & Modul',
            'alat_tulis' => 'Alat Tulis',
            'kegiatan' => 'Kegiatan',
            'lainnya' => 'Lainnya'
        ];
    }

    /**
     * Get all fee types
     */
    public static function getFeeTypes()
    {
        return [
            'mandatory' => 'Wajib',
            'optional' => 'Opsional',
            'conditional' => 'Kondisional'
        ];
    }
}
