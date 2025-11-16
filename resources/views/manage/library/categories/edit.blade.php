@extends('layouts.adminty')

@section('title', 'Edit Kategori - E-Perpustakaan')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="mb-4">
        <a href="{{ route('manage.library.categories.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Kategori</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('manage.library.categories.update', $category->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                                <input type="text" name="nama_kategori" class="form-control @error('nama_kategori') is-invalid @enderror" 
                                       value="{{ old('nama_kategori', $category->nama_kategori) }}" required>
                                @error('nama_kategori')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kode <span class="text-danger">*</span></label>
                                <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror" 
                                       value="{{ old('kode', $category->kode) }}" required>
                                @error('kode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <small class="text-muted">Kode unik untuk kategori (contoh: FIK, SEJ, DLL)</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" 
                                      rows="3">{{ old('deskripsi', $category->deskripsi) }}</textarea>
                            @error('deskripsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Icon</label>
                                <input type="text" name="icon" class="form-control @error('icon') is-invalid @enderror" 
                                       value="{{ old('icon', $category->icon) }}" placeholder="fas fa-folder">
                                @error('icon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <small class="text-muted">Font Awesome icon class (contoh: fas fa-book, fas fa-folder)</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Warna</label>
                                <input type="color" name="warna" class="form-control form-control-color @error('warna') is-invalid @enderror" 
                                       value="{{ old('warna', $category->warna) }}">
                                @error('warna')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Urutan</label>
                                <input type="number" name="urutan" class="form-control @error('urutan') is-invalid @enderror" 
                                       value="{{ old('urutan', $category->urutan) }}" min="0">
                                @error('urutan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <small class="text-muted">Urutan tampil (0 = pertama)</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                                           {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Aktif
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('manage.library.categories.index') }}" class="btn btn-secondary me-2">
                                <i class="fas fa-times me-2"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



