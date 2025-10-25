<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SPMBPayment extends Model
{
    use HasFactory;

    protected $table = 'spmb_payments';
    
    protected $fillable = [
        'registration_id',
        'type',
        'amount',
        'payment_method',
        'payment_reference',
        'tripay_reference',
        'status',
        'payment_url',
        'qr_code',
        'expired_at',
        'paid_at',
        'notes',
        'proof_of_payment'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expired_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the registration that owns this payment
     */
    public function registration()
    {
        return $this->belongsTo(SPMBRegistration::class, 'registration_id');
    }

    /**
     * Get payment type name
     */
    public function getTypeName()
    {
        $types = [
            'registration_fee' => 'Biaya Pendaftaran',
            'spmb_fee' => 'Biaya SPMB'
        ];

        return $types[$this->type] ?? $this->type;
    }

    /**
     * Get payment method name
     */
    public function getPaymentMethodName()
    {
        $methods = [
            'QRIS' => 'QRIS',
            'BRIVA' => 'BRI Virtual Account',
            'MANDIRI' => 'Mandiri Virtual Account',
            'BNI' => 'BNI Virtual Account',
            'BCA' => 'BCA Virtual Account',
            'OVO' => 'OVO',
            'DANA' => 'DANA',
            'SHOPEEPAY' => 'ShopeePay',
            'GOPAY' => 'GoPay',
            'LINKAJA' => 'LinkAja'
        ];

        return $methods[$this->payment_method] ?? $this->payment_method;
    }

    /**
     * Get status name
     */
    public function getStatusName()
    {
        $statuses = [
            'pending' => 'Menunggu Pembayaran',
            'paid' => 'Lunas',
            'skipped' => 'Di-skip',
            'expired' => 'Kadaluarsa',
            'failed' => 'Gagal',
            'cancelled' => 'Dibatalkan'
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Check if payment is expired
     */
    public function isExpired()
    {
        return $this->expired_at && $this->expired_at->isPast() && $this->status === 'pending';
    }

    /**
     * Check if payment is successful
     */
    public function isPaid()
    {
        return $this->status === 'paid';
    }

    /**
     * Get amount in Rupiah format
     */
    public function getAmountFormattedAttribute()
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }
}

