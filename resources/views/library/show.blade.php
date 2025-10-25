@extends('layouts.coreui')

@section('title', $book->judul . ' - E-Perpustakaan')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="mb-4">
        <a href="{{ route('library.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                @if($book->cover_image)
                    <img src="{{ asset('storage/' . $book->cover_image) }}" 
                         class="card-img-top" 
                         alt="{{ $book->judul }}">
                @else
                    <div class="card-img-top bg-gradient d-flex align-items-center justify-content-center"
                         style="height: 400px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-book fa-5x text-white opacity-50"></i>
                    </div>
                @endif
                <div class="card-body">
                    @if($book->file_path)
                        <div class="d-grid gap-2">
                            <a href="{{ route('library.read', $book->id) }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-book-reader me-2"></i>Baca Online
                            </a>
                            <a href="{{ route('library.download', $book->id) }}" class="btn btn-outline-success">
                                <i class="fas fa-download me-2"></i>Download PDF
                            </a>
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>File PDF tidak tersedia
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="mb-3">{{ $book->judul }}</h2>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong><i class="fas fa-user me-2"></i>Pengarang:</strong><br>
                                {{ $book->pengarang }}
                            </p>
                            <p class="mb-2">
                                <strong><i class="fas fa-building me-2"></i>Penerbit:</strong><br>
                                {{ $book->penerbit ?? '-' }}
                            </p>
                            <p class="mb-2">
                                <strong><i class="fas fa-calendar me-2"></i>Tahun Terbit:</strong><br>
                                {{ $book->tahun_terbit ?? '-' }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong><i class="fas fa-barcode me-2"></i>ISBN:</strong><br>
                                {{ $book->isbn ?? '-' }}
                            </p>
                            <p class="mb-2">
                                <strong><i class="fas fa-layer-group me-2"></i>Kategori:</strong><br>
                                <span class="badge" style="background-color: {{ $book->category->warna }};">
                                    {{ $book->category->nama_kategori }}
                                </span>
                            </p>
                            <p class="mb-2">
                                <strong><i class="fas fa-file-pdf me-2"></i>Jumlah Halaman:</strong><br>
                                {{ $book->jumlah_halaman ?? '-' }} halaman
                            </p>
                        </div>
                    </div>

                    @if($book->deskripsi)
                    <div class="mb-3">
                        <strong><i class="fas fa-align-left me-2"></i>Deskripsi:</strong>
                        <p class="mt-2">{{ $book->deskripsi }}</p>
                    </div>
                    @endif

                    <hr>

                    <div class="row text-center">
                        <div class="col-4">
                            <div class="p-3">
                                <i class="fas fa-eye fa-2x text-primary mb-2"></i>
                                <h4>{{ number_format($book->total_views) }}</h4>
                                <small class="text-muted">Dilihat</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3">
                                <i class="fas fa-download fa-2x text-success mb-2"></i>
                                <h4>{{ number_format($book->total_downloads) }}</h4>
                                <small class="text-muted">Download</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3">
                                <i class="fas fa-book fa-2x text-info mb-2"></i>
                                <h4>{{ number_format($book->total_loans) }}</h4>
                                <small class="text-muted">Peminjaman</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($relatedBooks->count() > 0)
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3"><i class="fas fa-books me-2"></i>Buku Terkait</h5>
                    <div class="row g-3">
                        @foreach($relatedBooks as $relatedBook)
                        <div class="col-md-3">
                            @include('library.partials.book-card', ['book' => $relatedBook])
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

