@extends('layouts.coreui')

@section('title', 'Tambah Tabungan')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
        padding-left: 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Tambah Tabungan</h4>
        <a href="{{ route('manage.tabungan.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0">
            <h6 class="mb-0 fw-bold">Form Tambah Tabungan</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('manage.tabungan.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="student_id" class="form-label">Pilih Siswa <span class="text-danger">*</span></label>
                            <select name="student_id" id="student_id" class="form-select @error('student_id') is-invalid @enderror" required>
                                <option value="">Pilih Siswa</option>
                            </select>
                            @error('student_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="saldo" class="form-label">Saldo Awal <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" 
                                       class="form-control @error('saldo') is-invalid @enderror" 
                                       id="saldo" 
                                       name="saldo" 
                                       value="{{ old('saldo') }}" 
                                       placeholder="Masukkan saldo awal" 
                                       required>
                            </div>
                            @error('saldo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Simpan Tabungan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2 with AJAX search for student
        $('#student_id').select2({
            placeholder: 'Ketik NIS atau nama siswa untuk mencari...',
            allowClear: true,
            width: '100%',
            ajax: {
                url: '/api/students/search',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        term: params.term || '',
                        status: 1 // Only active students
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.results || []
                    };
                },
                cache: true
            },
            language: {
                noResults: function() {
                    return "Tidak ada hasil ditemukan";
                },
                searching: function() {
                    return "Mencari...";
                },
                inputTooShort: function() {
                    return "Ketik minimal 1 karakter untuk mencari";
                }
            },
            minimumInputLength: 1
        });

        // Set old value if exists (for form validation errors)
        @if(old('student_id'))
            $.ajax({
                url: '/api/students/search',
                data: {
                    term: '{{ old("student_id") }}',
                    status: 1
                },
                dataType: 'json'
            }).then(function(data) {
                if (data.results && data.results.length > 0) {
                    var option = new Option(data.results[0].text, data.results[0].id, true, true);
                    $('#student_id').append(option).trigger('change');
                }
            });
        @endif
    });

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