@extends('layouts.adminty')

@section('title', 'Tambah Kode Akun')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-plus me-2"></i>
                            Tambah Kode Akun Baru
                        </h4>
                        <a href="{{ route('manage.account-codes.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Toast Notifications -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <strong>Terdapat kesalahan pada input:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('manage.account-codes.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="kode" class="form-label">
                                        Kode Akun <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('kode') is-invalid @enderror" 
                                           id="kode" name="kode" value="{{ old('kode') }}" 
                                           placeholder="Contoh: 1101, 2101, 4101" maxlength="32" required>
                                    <div class="form-text">
                                        Masukkan kode akun (maksimal 32 karakter). Contoh: 1101 untuk Kas, 2101 untuk Hutang Dagang
                                    </div>
                                    @error('kode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nama" class="form-label">
                                        Nama Akun <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('nama') is-invalid @enderror" 
                                           id="nama" name="nama" value="{{ old('nama') }}" 
                                           placeholder="Contoh: Kas, Piutang Dagang, Pendapatan SPP" maxlength="128" required>
                                    <div class="form-text">
                                        Masukkan nama akun yang jelas dan mudah dipahami
                                    </div>
                                    @error('nama')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tipe" class="form-label">
                                        Tipe Akun <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('tipe') is-invalid @enderror" id="tipe" name="tipe" required>
                                        <option value="">Pilih Tipe Akun</option>
                                        @foreach($tipeOptions as $value => $label)
                                            <option value="{{ $value }}" {{ old('tipe') == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">
                                        Pilih tipe akun sesuai dengan klasifikasi akuntansi
                                    </div>
                                    @error('tipe')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="kategori" class="form-label">Kategori</label>
                                    <select class="form-select @error('kategori') is-invalid @enderror" id="kategori" name="kategori">
                                        <option value="">Pilih Kategori (Opsional)</option>
                                        @foreach($kategoriOptions as $value => $label)
                                            <option value="{{ $value }}" {{ old('kategori') == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">
                                        Pilih kategori untuk pengelompokan lebih detail (opsional)
                                    </div>
                                    @error('kategori')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                      id="deskripsi" name="deskripsi" rows="3" 
                                      placeholder="Masukkan deskripsi atau penjelasan tambahan tentang akun ini...">{{ old('deskripsi') }}</textarea>
                            <div class="form-text">
                                Deskripsi opsional untuk memberikan penjelasan lebih detail tentang akun
                            </div>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Akun Aktif
                                </label>
                                <div class="form-text">
                                    Centang jika akun ini akan digunakan dalam transaksi
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('manage.account-codes.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>
                                Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                Simpan Kode Akun
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Panduan Kode Akun -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Panduan Kode Akun
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Klasifikasi Tipe Akun:</h6>
                            <ul class="list-unstyled">
                                <li><strong>1xxx - Aktiva:</strong> Kas, Piutang, Persediaan, Aktiva Tetap</li>
                                <li><strong>2xxx - Pasiva:</strong> Hutang Dagang, Hutang Bank, Hutang Pajak</li>
                                <li><strong>3xxx - Modal:</strong> Modal Pemilik, Laba Ditahan</li>
                                <li><strong>4xxx - Pendapatan:</strong> Pendapatan SPP, Pendapatan Lain-lain</li>
                                <li><strong>5xxx - Beban:</strong> Beban Operasional, Beban Administrasi</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Contoh Kode Akun:</h6>
                            <ul class="list-unstyled">
                                <li><strong>1101:</strong> Kas</li>
                                <li><strong>1201:</strong> Piutang SPP</li>
                                <li><strong>2101:</strong> Hutang Dagang</li>
                                <li><strong>3101:</strong> Modal Pemilik</li>
                                <li><strong>4101:</strong> Pendapatan SPP</li>
                                <li><strong>5101:</strong> Beban Operasional</li>
                            </ul>
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
$(document).ready(function() {
    console.log('Create form loaded');
    
    // Auto uppercase kode
    $('#kode').on('input', function() {
        $(this).val($(this).val().toUpperCase());
    });

    // Simple form logging
    $('form').on('submit', function(e) {
        console.log('Form submitted!');
        console.log('Form action:', $(this).attr('action'));
        console.log('Form method:', $(this).attr('method'));
        console.log('Form data:', $(this).serialize());
        
        // Let form submit normally
        return true;
    });
});
</script>
@endpush 