@extends('layouts.adminty')

@push('styles')
<style>
    .form-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .section-header {
        background: #01a9ac;
        color: white;
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
    }
    .info-item {
        border-bottom: 1px solid #e9ecef;
        padding: 10px 0;
    }
    .info-item:last-child {
        border-bottom: none;
    }
    .info-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 5px;
    }
    .info-value {
        color: #6c757d;
    }
    .badge-custom {
        font-size: 0.875rem;
        padding: 6px 12px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1" style="font-size: 1.5rem;">
                        <i class="fas fa-eye me-2"></i>Detail Field Form SPMB
                    </h4>
                    <p class="text-muted mb-0" style="font-size: 0.9rem;">Detail informasi field form</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('manage.spmb.form-settings.edit', $field->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                    <a href="{{ route('manage.spmb.form-settings.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Kembali
                    </a>
                </div>
            </div>

            <div class="form-card">
                <div class="p-4">
                    <!-- Basic Information -->
                    <div class="section-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>Informasi Dasar
                        </h5>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Nama Field</div>
                        <div class="info-value">
                            <code>{{ $field->field_name }}</code>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Label Field</div>
                        <div class="info-value">{{ $field->field_label }}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Tipe Field</div>
                        <div class="info-value">
                            <span class="badge bg-info badge-custom">{{ $field->field_type }}</span>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Section</div>
                        <div class="info-value">
                            <span class="badge bg-primary badge-custom">{{ $field->getSectionLabel() }}</span>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Urutan</div>
                        <div class="info-value">{{ $field->field_order }}</div>
                    </div>

                    <!-- Field Options -->
                    <div class="section-header">
                        <h5 class="mb-0">
                            <i class="fas fa-cog me-2"></i>Opsi Field
                        </h5>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Placeholder</div>
                        <div class="info-value">
                            @if($field->field_placeholder)
                                <code>{{ $field->field_placeholder }}</code>
                            @else
                                <span class="text-muted">Tidak ada</span>
                            @endif
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Help Text</div>
                        <div class="info-value">
                            @if($field->field_help_text)
                                {{ $field->field_help_text }}
                            @else
                                <span class="text-muted">Tidak ada</span>
                            @endif
                        </div>
                    </div>

                    @if($field->field_type === 'select' && $field->field_options)
                    <div class="info-item">
                        <div class="info-label">Opsi Select</div>
                        <div class="info-value">
                            <div class="row">
                                @foreach($field->field_options as $option)
                                    <div class="col-md-6 mb-2">
                                        <span class="badge bg-light text-dark">
                                            {{ $option['value'] ?? $option }}: {{ $option['label'] ?? $option }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Status -->
                    <div class="section-header">
                        <h5 class="mb-0">
                            <i class="fas fa-toggle-on me-2"></i>Status
                        </h5>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Field Wajib</div>
                        <div class="info-value">
                            @if($field->is_required)
                                <span class="badge bg-warning badge-custom">
                                    <i class="fas fa-asterisk me-1"></i>Wajib
                                </span>
                            @else
                                <span class="badge bg-secondary badge-custom">
                                    <i class="fas fa-minus me-1"></i>Opsional
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Status Aktif</div>
                        <div class="info-value">
                            @if($field->is_active)
                                <span class="badge bg-success badge-custom">
                                    <i class="fas fa-eye me-1"></i>Aktif
                                </span>
                            @else
                                <span class="badge bg-danger badge-custom">
                                    <i class="fas fa-eye-slash me-1"></i>Tidak Aktif
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Timestamps -->
                    <div class="section-header">
                        <h5 class="mb-0">
                            <i class="fas fa-clock me-2"></i>Informasi Sistem
                        </h5>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Dibuat</div>
                        <div class="info-value">{{ $field->created_at->format('d F Y H:i:s') }}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Diperbarui</div>
                        <div class="info-value">{{ $field->updated_at->format('d F Y H:i:s') }}</div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between mt-4">
                        <div class="d-flex gap-2">
                            <a href="{{ route('manage.spmb.form-settings.edit', $field->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit me-1"></i>Edit Field
                            </a>
                            <form method="POST" action="{{ route('manage.spmb.form-settings.toggle-status', $field->id) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn {{ $field->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}">
                                    <i class="fas {{ $field->is_active ? 'fa-eye-slash' : 'fa-eye' }} me-1"></i>
                                    {{ $field->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('manage.spmb.form-settings.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Kembali
                            </a>
                            <form method="POST" action="{{ route('manage.spmb.form-settings.destroy', $field->id) }}" 
                                  class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus field ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash me-1"></i>Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
