@extends('layouts.adminty')

@push('styles')
<style>
    .form-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 25px;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
    }

    .section-header {
        background: #01a9ac;
        color: white;
        padding: 20px 25px;
        border-radius: 15px;
        margin-bottom: 25px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .field-item {
        border: 2px solid rgba(0, 128, 96, 0.1);
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 15px;
        background: rgba(255, 255, 255, 0.95);
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .field-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        border-color: rgba(0, 128, 96, 0.3);
    }

    .field-item.inactive {
        opacity: 0.6;
        background: rgba(248, 249, 250, 0.8);
        border-color: rgba(0, 128, 96, 0.05);
    }

    .field-type-badge {
        font-size: 0.75rem;
        padding: 4px 12px;
        border-radius: 10px;
        font-weight: 600;
    }

    .sortable-handle {
        cursor: move;
        color: #008060;
        font-size: 18px;
        transition: all 0.3s ease;
    }

    .sortable-handle:hover {
        color: #006d52;
        transform: scale(1.1);
    }

    .action-btn {
        transition: all 0.2s ease;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    .action-btn-view:hover {
        background-color: #01a9ac !important;
        color: white !important;
    }

    .action-btn-edit:hover {
        background-color: #ff9800 !important;
        color: white !important;
    }

    .action-btn-hide:hover {
        background-color: #6c757d !important;
        color: white !important;
    }

    .action-btn-show:hover {
        background-color: #28a745 !important;
        color: white !important;
    }

    .action-btn-delete:hover {
        background-color: #dc3545 !important;
        color: white !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="font-size: 1.5rem;">
                <i class="fas fa-cogs me-2"></i>Pengaturan Form SPMB
            </h4>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">Kelola field form pendaftaran SPMB</p>
        </div>
        <div>
            <a href="{{ route('manage.spmb.index') }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i>Kembali
            </a>
            <a href="{{ route('manage.spmb.form-settings.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Tambah Field
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="form-card">
                <div class="p-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($fieldsBySection->count() > 0)
                        @foreach($fieldsBySection as $section => $fields)
                            <div class="section-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-folder me-2"></i>{{ ucfirst($section) }}
                                    <span class="badge bg-light text-dark ms-2">{{ $fields->count() }} field</span>
                                </h5>
                            </div>

                            <div class="sortable-fields" data-section="{{ $section }}">
                                @foreach($fields as $field)
                                    <div class="field-item {{ !$field->is_active ? 'inactive' : '' }}" data-id="{{ $field->id }}">
                                        <div class="row align-items-center">
                                            <div class="col-md-1">
                                                <i class="fas fa-grip-vertical sortable-handle"></i>
                                            </div>
                                            <div class="col-md-2">
                                                <strong>{{ $field->field_label }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $field->field_name }}</small>
                                            </div>
                                            <div class="col-md-2">
                                                <span class="badge bg-info field-type-badge">{{ $field->field_type }}</span>
                                                @if($field->is_required)
                                                    <span class="badge bg-warning field-type-badge">Required</span>
                                                @endif
                                            </div>
                                            <div class="col-md-2">
                                                @if($field->field_placeholder)
                                                    <small class="text-muted">Placeholder: {{ $field->field_placeholder }}</small>
                                                @endif
                                            </div>
                                            <div class="col-md-2">
                                                <span class="badge {{ $field->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $field->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                                </span>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="d-flex" style="gap: 8px;">
                                                    <a href="{{ route('manage.spmb.form-settings.show', $field->id) }}" 
                                                       class="btn btn-sm action-btn action-btn-view" 
                                                       title="Lihat Detail"
                                                       style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center; border: 2px solid #01a9ac; border-radius: 4px; background: white; color: #01a9ac;">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('manage.spmb.form-settings.edit', $field->id) }}" 
                                                       class="btn btn-sm action-btn action-btn-edit" 
                                                       title="Edit"
                                                       style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center; border: 2px solid #ff9800; border-radius: 4px; background: white; color: #ff9800;">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="POST" action="{{ route('manage.spmb.form-settings.toggle-status', $field->id) }}" class="d-inline" style="margin: 0;">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="btn btn-sm action-btn {{ $field->is_active ? 'action-btn-hide' : 'action-btn-show' }}" 
                                                                title="{{ $field->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                                                style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center; border: 2px solid {{ $field->is_active ? '#6c757d' : '#28a745' }}; border-radius: 4px; background: white; color: {{ $field->is_active ? '#6c757d' : '#28a745' }};">
                                                            <i class="fas {{ $field->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('manage.spmb.form-settings.destroy', $field->id) }}" 
                                                          class="d-inline" 
                                                          style="margin: 0;"
                                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus field ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="btn btn-sm action-btn action-btn-delete" 
                                                                title="Hapus"
                                                                style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center; border: 2px solid #dc3545; border-radius: 4px; background: white; color: #dc3545;">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Belum ada field form</h5>
                            <p class="text-muted">Klik tombol "Tambah Field" untuk menambahkan field form baru.</p>
                            <a href="{{ route('manage.spmb.form-settings.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Tambah Field Pertama
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    // Initialize sortable for each section
    document.querySelectorAll('.sortable-fields').forEach(function(container) {
        new Sortable(container, {
            handle: '.sortable-handle',
            animation: 150,
            onEnd: function(evt) {
                const fields = Array.from(container.children).map((item, index) => ({
                    id: item.dataset.id,
                    order: index
                }));
                
                fetch('{{ route("manage.spmb.form-settings.update-order") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ fields: fields })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        const alert = document.createElement('div');
                        alert.className = 'alert alert-success alert-dismissible fade show';
                        alert.innerHTML = '<i class="fas fa-check-circle me-2"></i>Urutan field berhasil diperbarui.<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                        document.querySelector('.container-fluid').insertBefore(alert, document.querySelector('.form-card'));
                    }
                });
            }
        });
    });
</script>
@endpush
