<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transfer extends Model
{
    use SoftDeletes;

    protected $table = 'transfer';
    protected $primaryKey = 'transfer_id';

    protected $fillable = [
        'student_id',
        'detail',
        'status',
        'confirm_name',
        'confirm_bank',
        'confirm_accnum',
        'confirm_photo',
        'confirm_pay',
        'confirm_date',
        'verif_user',
        'verif_date',
        'is_out',
        'checkout_url',
        'merchantRef',
        'reference',
        'payment_method',
        'gateway_transaction_id',
        'payment_details',
        'bill_type',
        'bill_id',
        'payment_number',
        'paid_at',
        'jenis_pembayaran',
        'metode_pembayaran_tabungan',
        'referensi_tabungan',
        'midtrans_snap_token',
        'accounting_processed',
        'accounting_processed_at'
    ];

    protected $casts = [
        'payment_details' => 'array',
        'paid_at' => 'datetime',
        'accounting_processed_at' => 'datetime',
        'confirm_date' => 'datetime',
        'verif_date' => 'datetime'
    ];

    /**
     * Get the student that owns the transfer
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    /**
     * Get the transfer details for this transfer
     */
    public function transferDetails()
    {
        return $this->hasMany(TransferDetail::class, 'transfer_id', 'transfer_id');
    }

    /**
     * Get the verifier user
     */
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verif_user', 'id');
    }

    /**
     * Scope for pending transfers
     */
    public function scopePending($query)
    {
        return $query->where('status', 0);
    }

    /**
     * Scope for successful transfers
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope for rejected transfers
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 2);
    }

    /**
     * Scope for online payments
     */
    public function scopeOnline($query)
    {
        return $query->whereNotNull('checkout_url');
    }

    /**
     * Scope for manual transfers
     */
    public function scopeManual($query)
    {
        return $query->whereNull('checkout_url');
    }

    /**
     * Get status text
     */
    public function getStatusTextAttribute()
    {
        switch ($this->status) {
            case 0:
                return 'Menunggu Verifikasi';
            case 1:
                return 'Berhasil';
            case 2:
                return 'Ditolak';
            case 3:
                return 'Dibatalkan';
            case 4:
                return 'Expired';
            default:
                return 'Tidak Diketahui';
        }
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute()
    {
        switch ($this->status) {
            case 0:
                return 'bg-warning';
            case 1:
                return 'bg-success';
            case 2:
                return 'bg-danger';
            case 3:
                return 'bg-secondary';
            case 4:
                return 'bg-dark';
            default:
                return 'bg-secondary';
        }
    }
}
