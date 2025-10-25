@extends('layouts.coreui')

@section('title', 'Tambah Kas Baru')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fa fa-plus me-2"></i>Tambah Kas Baru
                    </h4>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fa fa-exclamation-circle me-2"></i>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('manage.accounting.kas.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nama_kas" class="form-label">
                                        Nama Kas <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('nama_kas') is-invalid @enderror" 
                                           id="nama_kas" name="nama_kas" value="{{ old('nama_kas') }}" 
                                           placeholder="Contoh: KAS TUNAI, KAS BANK" required>
                                    @error('nama_kas')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jenis" class="form-label">
                                        Jenis Kas <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('jenis') is-invalid @enderror" 
                                            id="jenis" name="jenis" required>
                                        <option value="">Pilih Jenis Kas</option>
                                        <option value="cash" {{ old('jenis') == 'cash' ? 'selected' : '' }}>
                                            Tunai
                                        </option>
                                        <option value="bank" {{ old('jenis') == 'bank' ? 'selected' : '' }}>
                                            Bank
                                        </option>
                                        <option value="e_wallet" {{ old('jenis') == 'e_wallet' ? 'selected' : '' }}>
                                            E-Wallet
                                        </option>
                                    </select>
                                    @error('jenis')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="saldo" class="form-label">
                                        Saldo Awal <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control @error('saldo') is-invalid @enderror" 
                                               id="saldo" name="saldo" value="{{ old('saldo', 0) }}" 
                                               placeholder="0" min="0" step="1000" required>
                                    </div>
                                    @error('saldo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Masukkan saldo awal kas ini
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="is_active" class="form-label">Status</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" 
                                               name="is_active" value="1" {{ old('is_active') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Aktif
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Kas yang aktif dapat digunakan dalam transaksi
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                      id="deskripsi" name="deskripsi" rows="3" 
                                      placeholder="Jelaskan detail kas ini...">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Contoh: KAS TUNAI DI TANGAN BENDAHARA SEKOLAH
                            </small>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('manage.accounting.kas.index') }}" class="btn btn-secondary">
                                <i class="fa fa-arrow-left me-2"></i>Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save me-2"></i>Simpan Kas
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
