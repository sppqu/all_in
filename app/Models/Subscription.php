<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'plan_name',
        'amount',
        'duration_days',
        'status',
        'payment_method',
        'snap_token',
        'activated_at',
        'expires_at',
        'cancelled_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
        'cancelled_at' => 'datetime'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoice()
    {
        return $this->hasOne(SubscriptionInvoice::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('expires_at', '>', now());
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Methods
    public function isActive()
    {
        return $this->status === 'active' && $this->expires_at > now();
    }

    public function isExpired()
    {
        return $this->expires_at <= now();
    }

    public function getFormattedAmountAttribute()
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function getStatusBadgeAttribute()
    {
        switch ($this->status) {
            case 'active':
                return '<span class="badge bg-success">Aktif</span>';
            case 'pending':
                return '<span class="badge bg-warning text-dark">Menunggu</span>';
            case 'cancelled':
                return '<span class="badge bg-danger">Dibatalkan</span>';
            case 'expired':
                return '<span class="badge bg-secondary">Berakhir</span>';
            default:
                return '<span class="badge bg-info">' . ucfirst($this->status) . '</span>';
        }
    }
}
