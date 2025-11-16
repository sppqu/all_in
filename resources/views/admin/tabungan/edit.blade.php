@extends('layouts.adminty')

@section('title', 'Edit Tabungan')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Edit Tabungan</h4>
        <a href="{{ route('manage.tabungan.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0">
            <h6 class="mb-0 fw-bold">Form Edit Tabungan</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('manage.tabungan.update', $tabungan->tabungan_id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Nama Siswa</label>
                            <input type="text" class="form-control" value="{{ $tabungan->student_full_name }} ({{ $tabungan->student_nis }})" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Kelas</label>
                            <input type="text" class="form-control" value="{{ $tabungan->class_name }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="saldo" class="form-label">Saldo <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" 
                                       class="form-control @error('saldo') is-invalid @enderror" 
                                       id="saldo" 
                                       name="saldo" 
                                       value="{{ old('saldo', number_format($tabungan->saldo, 0, ',', '.')) }}" 
                                       placeholder="Masukkan saldo" 
                                       required>
                            </div>
                            @error('saldo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Tanggal Terakhir Update</label>
                            <input type="text" class="form-control" value="{{ $tabungan->tabungan_last_update ? \Carbon\Carbon::parse($tabungan->tabungan_last_update)->format('d/m/Y H:i') : 'Belum ada update' }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Tabungan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Format input saldo dengan separator ribuan
    document.getElementById('saldo').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value) {
            value = parseInt(value).toLocaleString('id-ID');
            e.target.value = value;
        }
    });
</script>
@endpush 