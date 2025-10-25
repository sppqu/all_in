<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SPMBDocument extends Model
{
    use HasFactory;

    protected $table = 'spmb_documents';
    
    protected $fillable = [
        'registration_id',
        'document_type',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'status',
        'notes'
    ];

    /**
     * Get the registration that owns this document
     */
    public function registration()
    {
        return $this->belongsTo(SPMBRegistration::class, 'registration_id');
    }

    /**
     * Get document type name
     */
    public function getDocumentTypeName()
    {
        $types = [
            'kk' => 'Kartu Keluarga',
            'akte_lahir' => 'Akte Kelahiran',
            'ijazah' => 'Ijazah',
            'skl' => 'Surat Keterangan Lulus',
            'foto' => 'Foto',
            'raport' => 'Raport',
            'sertifikat' => 'Sertifikat Prestasi'
        ];

        return $types[$this->document_type] ?? $this->document_type;
    }

    /**
     * Get file size in human readable format
     */
    public function getFileSizeHumanAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}

