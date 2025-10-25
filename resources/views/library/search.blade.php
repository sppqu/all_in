@extends('layouts.coreui')

@section('title', 'Pencarian Buku - E-Perpustakaan')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="mb-4">
        <h4 class="mb-3 fw-bold">🔍 Pencarian Buku</h4>
        
        <!-- Search Form -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('library.search') }}" method="GET">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <input type="text" name="q" class="form-control form-control-lg" 
                                   placeholder="Cari judul, pengarang, penerbit..." 
                                   value="{{ request('q') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="category" class="form-select form-select-lg">
                                <option value="">Semua Kategori</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->nama_kategori }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="sort" class="form-select form-select-lg">
                                <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Terbaru</option>
                                <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Terpopuler</option>
                                <option value="title" {{ request('sort') == 'title' ? 'selected' : '' }}>Judul A-Z</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-search me-2"></i>Cari
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Results -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">
            @if(request('q'))
                Hasil pencarian "{{ request('q') }}" 
            @else
                Semua Buku
            @endif
            <span class="badge bg-primary">{{ $books->total() }}</span>
        </h5>
    </div>

    <div class="row g-3">
        @forelse($books as $book)
        <div class="col-md-2">
            @include('library.partials.book-card', ['book' => $book])
        </div>
        @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-search fa-4x text-muted mb-3"></i>
                    <h5>Buku tidak ditemukan</h5>
                    <p class="text-muted">Coba gunakan kata kunci lain atau ubah filter pencarian</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    @if($books->hasPages())
    <div class="mt-4">
        {{ $books->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection

