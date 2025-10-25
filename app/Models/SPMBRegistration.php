<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class SPMBRegistration extends Model
{
    use HasFactory;

    protected $table = 'spmb_registrations';
    
    protected $fillable = [
        'name',
        'phone',
        'password',
        'step',
        'status',
        'registration_fee_paid',
        'spmb_fee_paid',
        'form_data',
        'nomor_pendaftaran',
        'kejuruan_id',
        'status_pendaftaran',
        'catatan_admin',
        'wave_id',
        'transferred_to_students',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'form_data' => 'array',
        'registration_fee_paid' => 'boolean',
        'spmb_fee_paid' => 'boolean',
        'transferred_to_students' => 'boolean',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Set password attribute with hashing
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Check if password matches
     */
    public function checkPassword($password)
    {
        return Hash::check($password, $this->password);
    }

    /**
     * Get the documents for this registration
     */
    public function documents()
    {
        return $this->hasMany(SPMBDocument::class, 'registration_id');
    }

    /**
     * Get the payments for this registration
     */
    public function payments()
    {
        return $this->hasMany(SPMBPayment::class, 'registration_id');
    }

    /**
     * Get registration fee payment
     */
    public function registrationPayment()
    {
        return $this->payments()->where('type', 'registration_fee')->first();
    }

    /**
     * Get SPMB fee payment
     */
    public function spmbPayment()
    {
        return $this->payments()->where('type', 'spmb_fee')->first();
    }

    /**
     * Check if registration is complete
     */
    public function isComplete()
    {
        return $this->step >= 5 && $this->registration_fee_paid && $this->spmb_fee_paid;
    }

    /**
     * Get next step
     */
    public function getNextStep()
    {
        if ($this->step < 5) {
            return $this->step + 1;
        }
        return null;
    }

    /**
     * Get step name
     */
    public function getStepName()
    {
        $steps = [
            1 => 'Pendaftaran',
            2 => 'Pembayaran Biaya Pendaftaran',
            3 => 'Melengkapi Formulir',
            4 => 'Upload Dokumen',
            5 => 'Pembayaran SPMB',
            6 => 'Selesai'
        ];

        return $steps[$this->step] ?? 'Tidak Diketahui';
    }

    /**
     * Get kejuruan relationship
     */
    public function kejuruan()
    {
        return $this->belongsTo(SPMBKejuruan::class, 'kejuruan_id');
    }

    /**
     * Get wave relationship
     */
    public function wave()
    {
        return $this->belongsTo(SPMBWave::class, 'wave_id');
    }

    /**
     * Get additional fees for this registration
     */
    public function additionalFees()
    {
        return $this->belongsToMany(SPMBAdditionalFee::class, 'spmb_registration_additional_fees')
                    ->withPivot(['amount', 'is_paid', 'paid_at', 'metadata'])
                    ->withTimestamps();
    }

    /**
     * Generate nomor pendaftaran
     */
    public function generateNomorPendaftaran()
    {
        if ($this->nomor_pendaftaran) {
            return $this->nomor_pendaftaran;
        }

        $tahun = date('Y');
        
        // Gunakan ID langsung sebagai digit terakhir nomor pendaftaran
        $idDigit = $this->id;
        
        // Format: SPMB-YYYY-NNNN (NNNN = ID dengan padding 4 digit)
        $nomorPendaftaran = 'SPMB-' . $tahun . '-' . str_pad($idDigit, 4, '0', STR_PAD_LEFT);
        
        $this->update(['nomor_pendaftaran' => $nomorPendaftaran]);
        
        return $nomorPendaftaran;
    }

    /**
     * Get status pendaftaran badge
     */
    public function getStatusPendaftaranBadge()
    {
        $badges = [
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'diterima' => '<span class="badge bg-success">Diterima</span>',
            'ditolak' => '<span class="badge bg-danger">Ditolak</span>'
        ];

        return $badges[$this->status_pendaftaran] ?? '<span class="badge bg-secondary">Tidak Diketahui</span>';
    }
}
