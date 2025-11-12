@extends('layouts.coreui')

@push('styles')
<style>
    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }
    .btn-primary {
        background: linear-gradient(135deg, #008060 0%, #00a86b 100%);
        border: none;
        border-radius: 10px;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #006b4f 0%, #008060 100%);
        transform: translateY(-1px);
    }
    .form-control, .form-select {
        border-radius: 10px;
        border: 2px solid #e9ecef;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }
    .form-control:focus, .form-select:focus {
        border-color: #008060;
        box-shadow: 0 0 0 0.2rem rgba(0, 128, 96, 0.25);
    }
    .form-label {
        font-weight: 600;
        color: #333;
        margin-bottom: 0.5rem;
    }
    .form-check-input:checked {
        background-color: #008060;
        border-color: #008060;
    }
    .form-check-input:focus {
        box-shadow: 0 0 0 0.2rem rgba(0, 128, 96, 0.25);
    }
    .help-text {
        font-size: 0.875rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }
    .required {
        color: #dc3545;
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
                        <i class="fas fa-edit me-2"></i>Edit Biaya Tambahan SPMB
                    </h4>
                    <p class="text-muted mb-0" style="font-size: 0.9rem;">Edit data biaya tambahan</p>
                </div>
                <a href="{{ route('manage.spmb.additional-fees.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
            </div>

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Terjadi kesalahan:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-edit me-2"></i>Form Edit Biaya Tambahan</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('manage.spmb.additional-fees.update', $additionalFee->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <!-- Nama Biaya -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">
                                    Nama Biaya <span class="required">*</span>
                                </label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $additionalFee->name) }}" 
                                       placeholder="Contoh: Seragam Putra" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="help-text">Nama lengkap biaya yang akan ditampilkan</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="code" class="form-label">
                                    Kode Biaya <span class="required">*</span>
                                </label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                       id="code" name="code" value="{{ old('code', $additionalFee->code) }}" 
                                       placeholder="Contoh: SERAGAM_PUTRA" required>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="help-text">Kode unik untuk identifikasi biaya</div>
                            </div>
                        </div>

                        <!-- Kategori dan Jenis -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="category" class="form-label">
                                    Kategori <span class="required">*</span>
                                </label>
                                <select class="form-select @error('category') is-invalid @enderror" 
                                        id="category" name="category" required>
                                    <option value="">Pilih Kategori</option>
                                    @foreach($categories as $key => $label)
                                        <option value="{{ $key }}" {{ old('category', $additionalFee->category) == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="type" class="form-label">
                                    Jenis Biaya <span class="required">*</span>
                                </label>
                                <select class="form-select @error('type') is-invalid @enderror" 
                                        id="type" name="type" required>
                                    <option value="">Pilih Jenis</option>
                                    @foreach($types as $key => $label)
                                        <option value="{{ $key }}" {{ old('type', $additionalFee->type) == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Jumlah dan Urutan -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="amount" class="form-label">
                                    Jumlah Biaya <span class="required">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                           id="amount" name="amount" value="{{ old('amount', $additionalFee->amount) }}" 
                                           min="0" step="1" placeholder="350000" required>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="help-text">Masukkan jumlah dalam rupiah (tanpa titik atau koma)</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="sort_order" class="form-label">Urutan Tampilan</label>
                                <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                       id="sort_order" name="sort_order" value="{{ old('sort_order', $additionalFee->sort_order) }}" 
                                       min="0" placeholder="0">
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="help-text">Urutan tampilan (0 = paling atas)</div>
                            </div>
                        </div>

                        <!-- Deskripsi -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" 
                                      placeholder="Deskripsi lengkap tentang biaya ini...">{{ old('description', $additionalFee->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="help-text">Deskripsi opsional untuk menjelaskan biaya ini</div>
                        </div>

                        <!-- Status Aktif -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                       value="1" {{ old('is_active', $additionalFee->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <strong>Aktifkan biaya ini</strong>
                                </label>
                            </div>
                            <div class="help-text">Biaya yang tidak aktif tidak akan muncul dalam pilihan</div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('manage.spmb.additional-fees.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update Biaya
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Info Card -->
            <div class="card mt-4">
                <div class="card-body">
                    <h6 class="card-title text-primary">
                        <i class="fas fa-info-circle me-2"></i>Informasi Biaya
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Status Saat Ini:</h6>
                            <ul class="list-unstyled">
                                <li><strong>Kategori:</strong> {!! $additionalFee->category_badge !!}</li>
                                <li><strong>Jenis:</strong> {!! $additionalFee->type_badge !!}</li>
                                <li><strong>Status:</strong> 
                                    @if($additionalFee->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Digunakan di Gelombang:</h6>
                            @if($additionalFee->waves->count() > 0)
                                <ul class="list-unstyled">
                                    @foreach($additionalFee->waves as $wave)
                                        <li><i class="fas fa-wave-square text-primary me-2"></i>{{ $wave->name }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted">Belum digunakan di gelombang manapun</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto format amount input
    document.getElementById('amount').addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^\d]/g, '');
        e.target.value = value;
    });
</script>
@endpush
