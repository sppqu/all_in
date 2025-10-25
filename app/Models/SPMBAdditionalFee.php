<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SPMBAdditionalFee extends Model
{
    use HasFactory;

    protected $table = 'spmb_additional_fees';

    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'category',
        'amount',
        'conditions',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'conditions' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    // Relationships
    public function waves()
    {
        return $this->belongsToMany(SPMBWave::class, 'spmb_wave_additional_fees')
                    ->withPivot(['amount', 'is_active'])
                    ->withTimestamps();
    }

    public function registrations()
    {
        return $this->belongsToMany(SPMBRegistration::class, 'spmb_registration_additional_fees')
                    ->withPivot(['amount', 'is_paid', 'paid_at', 'metadata'])
                    ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Accessors & Mutators
    public function getFormattedAmountAttribute()
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function getTypeBadgeAttribute()
    {
        $badges = [
            'mandatory' => '<span class="badge bg-danger">Wajib</span>',
            'optional' => '<span class="badge bg-info">Opsional</span>',
            'conditional' => '<span class="badge bg-warning">Kondisional</span>'
        ];

        return $badges[$this->type] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    public function getCategoryBadgeAttribute()
    {
        $badges = [
            'seragam' => '<span class="badge bg-primary">Seragam</span>',
            'buku' => '<span class="badge bg-success">Buku</span>',
            'alat_tulis' => '<span class="badge bg-info">Alat Tulis</span>',
            'kegiatan' => '<span class="badge bg-warning">Kegiatan</span>',
            'lainnya' => '<span class="badge bg-secondary">Lainnya</span>'
        ];

        return $badges[$this->category] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    // Helper methods
    public function isMandatory()
    {
        return $this->type === 'mandatory';
    }

    public function isOptional()
    {
        return $this->type === 'optional';
    }

    public function isConditional()
    {
        return $this->type === 'conditional';
    }

    public function getAmountForWave($waveId)
    {
        $waveFee = $this->waves()->where('wave_id', $waveId)->first();
        return $waveFee ? $waveFee->pivot->amount : $this->amount;
    }

    public function isActiveForWave($waveId)
    {
        $waveFee = $this->waves()->where('wave_id', $waveId)->first();
        if (!$waveFee) {
            return $this->is_active;
        }
        return $waveFee->pivot->is_active;
    }

    public function checkConditions($registration = null)
    {
        if (!$this->conditions || !$registration) {
            return true;
        }

        $conditions = $this->conditions;
        
        // Check gender condition
        if (isset($conditions['gender']) && $registration->jenis_kelamin !== $conditions['gender']) {
            return false;
        }

        // Check class condition
        if (isset($conditions['class']) && $registration->kelas !== $conditions['class']) {
            return false;
        }

        // Add more conditions as needed
        return true;
    }
}