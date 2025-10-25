<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnlinePayment extends Model
{
    protected $fillable = [
        'payment_number',
        'student_id',
        'bill_type',
        'bill_id',
        'amount',
        'payment_method',
        'status',
        'payment_details',
        'gateway_transaction_id',
        'paid_at',
        // Manual payment fields
        'manual_proof_file',
        'manual_notes',
        'manual_bank_name',
        'manual_account_number',
        'manual_account_name',
        'manual_transfer_amount',
        // Payment gateway fields
        'gateway_name',
        'gateway_order_id',
        'gateway_response',
        'gateway_expired_at',
        // Verification fields
        'verification_status',
        'verification_notes',
        'verified_by',
        'verified_at',
        // Midtrans fields
        'reference',
        'payment_type',
        'period',
        'description',
        'payment_data',
        'snap_token'
    ];

    protected $casts = [
        'payment_details' => 'array',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
        'manual_transfer_amount' => 'decimal:2',
        'gateway_expired_at' => 'datetime',
        'verified_at' => 'datetime'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'badge-warning',
            'success' => 'badge-success',
            'failed' => 'badge-danger',
            'expired' => 'badge-secondary'
        ];

        return $badges[$this->status] ?? 'badge-secondary';
    }

    public function getStatusTextAttribute()
    {
        $texts = [
            'pending' => 'Menunggu Pembayaran',
            'success' => 'Berhasil',
            'failed' => 'Gagal',
            'expired' => 'Kadaluarsa'
        ];

        return $texts[$this->status] ?? 'Tidak Diketahui';
    }

    public function getVerificationStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'badge-warning',
            'verified' => 'badge-success',
            'rejected' => 'badge-danger'
        ];

        return $badges[$this->verification_status] ?? 'badge-secondary';
    }

    public function getVerificationStatusTextAttribute()
    {
        $texts = [
            'pending' => 'Menunggu Verifikasi',
            'verified' => 'Terverifikasi',
            'rejected' => 'Ditolak'
        ];

        return $texts[$this->verification_status] ?? 'Tidak Diketahui';
    }

    public function getPaymentMethodTextAttribute()
    {
        $texts = [
            'bank_transfer' => 'Transfer Bank',
            'credit_card' => 'Kartu Kredit',
            'e_wallet' => 'E-Wallet',
            'manual' => 'Manual Transfer'
        ];

        return $texts[$this->payment_method] ?? 'Tidak Diketahui';
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
