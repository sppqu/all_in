@extends('layouts.adminty')

@section('title', 'Edit Buku - E-Perpustakaan')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="mb-4">
        <a href="{{ route('manage.library.books.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Buku</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('manage.library.books.update', $book->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Judul Buku <span class="text-danger">*</span></label>
                                <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror" 
                                       value="{{ old('judul', $book->judul) }}" required>
                                @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Pengarang <span class="text-danger">*</span></label>
                                <input type="text" name="pengarang" class="form-control @error('pengarang') is-invalid @enderror" 
                                       value="{{ old('pengarang', $book->pengarang) }}" required>
                                @error('pengarang')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kategori <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-control select-primary @error('category_id') is-invalid @enderror" required>
                                    <option value="">Pilih Kategori</option>
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id', $book->category_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->nama_kategori }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Penerbit</label>
                                <input type="text" name="penerbit" class="form-control @error('penerbit') is-invalid @enderror" 
                                       value="{{ old('penerbit', $book->penerbit) }}">
                                @error('penerbit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tahun Terbit</label>
                                <input type="number" name="tahun_terbit" class="form-control @error('tahun_terbit') is-invalid @enderror" 
                                       value="{{ old('tahun_terbit', $book->tahun_terbit) }}" min="1900" max="{{ date('Y') }}">
                                @error('tahun_terbit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">ISBN</label>
                                <input type="text" name="isbn" class="form-control @error('isbn') is-invalid @enderror" 
                                       value="{{ old('isbn', $book->isbn) }}">
                                @error('isbn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Jumlah Halaman</label>
                                <input type="number" name="jumlah_halaman" class="form-control @error('jumlah_halaman') is-invalid @enderror" 
                                       value="{{ old('jumlah_halaman', $book->jumlah_halaman) }}" min="1">
                                @error('jumlah_halaman')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" rows="4" class="form-control @error('deskripsi') is-invalid @enderror">{{ old('deskripsi', $book->deskripsi) }}</textarea>
                            @error('deskripsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cover Buku</label>
                                @if($book->cover_image)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $book->cover_image) }}" 
                                         alt="Cover" 
                                         style="max-width: 150px; max-height: 200px; object-fit: cover; border-radius: 4px;">
                                    <p class="text-muted small mb-1">Cover saat ini</p>
                                </div>
                                @endif
                                <input type="file" name="cover_image" class="form-control @error('cover_image') is-invalid @enderror" 
                                       accept="image/*">
                                <small class="text-muted">Format: JPG, PNG. Max: 2MB. Kosongkan jika tidak ingin mengubah.</small>
                                @error('cover_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">File PDF</label>
                                @if($book->file_path)
                                <div class="mb-2">
                                    <a href="{{ route('library.serve-pdf', $book->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-file-pdf me-1"></i>Lihat PDF Saat Ini
                                    </a>
                                    <p class="text-muted small mb-1 mt-1">File PDF saat ini</p>
                                </div>
                                @endif
                                <input type="file" name="file_pdf" class="form-control @error('file_pdf') is-invalid @enderror" 
                                       accept=".pdf">
                                <small class="text-muted">Format: PDF. Max: 50MB. Kosongkan jika tidak ingin mengubah.</small>
                                @error('file_pdf')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control select-primary @error('status') is-invalid @enderror" required>
                                    <option value="tersedia" {{ old('status', $book->status) == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                                    <option value="tidak_tersedia" {{ old('status', $book->status) == 'tidak_tersedia' ? 'selected' : '' }}>Tidak Tersedia</option>
                                </select>
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label d-block">Buku Unggulan?</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_featured" value="1" 
                                           {{ old('is_featured', $book->is_featured) ? 'checked' : '' }}>
                                    <label class="form-check-label">Ya, jadikan buku unggulan</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4" style="gap: 8px;">
                            <a href="{{ route('manage.library.books.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Buku
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

