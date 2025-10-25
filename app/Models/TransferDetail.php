<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferDetail extends Model
{
    protected $table = 'transfer_detail';
    protected $primaryKey = 'id';

    protected $fillable = [
        'transfer_id',
        'payment_type',
        'bulan_id',
        'bebas_id',
        'desc',
        'subtotal',
        'is_tabungan'
    ];

    protected $casts = [
        'subtotal' => 'decimal:0'
    ];

    /**
     * Get the transfer that owns this detail
     */
    public function transfer()
    {
        return $this->belongsTo(Transfer::class, 'transfer_id', 'transfer_id');
    }

    /**
     * Get the bulan bill if this is a monthly payment
     */
    public function bulan()
    {
        return $this->belongsTo(Bulan::class, 'bulan_id', 'bulan_id');
    }

    /**
     * Get the bebas bill if this is a free payment
     */
    public function bebas()
    {
        return $this->belongsTo(Bebas::class, 'bebas_id', 'bebas_id');
    }

    /**
     * Get payment type text
     */
    public function getPaymentTypeTextAttribute()
    {
        switch ($this->payment_type) {
            case 1:
                return 'Bulanan';
            case 2:
                return 'Bebas';
            case 3:
                return 'Tabungan';
            default:
                return 'Tidak Diketahui';
        }
    }

    /**
     * Check if this is a tabungan payment
     */
    public function isTabungan()
    {
        return $this->payment_type == 3 || $this->is_tabungan == 1;
    }
}
