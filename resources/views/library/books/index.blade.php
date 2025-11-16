@extends('layouts.adminty')

@section('title', 'Kelola Buku - E-Perpustakaan')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">📚 Kelola Buku</h4>
            <p class="text-muted mb-0">Manajemen koleksi buku digital</p>
        </div>
        <div>
            <a href="{{ route('library.index') }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
            <a href="{{ route('manage.library.books.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Tambah Buku
            </a>
        </div>
    </div>


    <!-- Filter Section -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('manage.library.books.index') }}" method="GET" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Cari Buku</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Judul, pengarang, penerbit, ISBN..." 
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Kategori</label>
                        <select name="category_id" class="form-control select-primary">
                            <option value="">Semua Kategori</option>
                            @foreach($categories ?? [] as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->nama_kategori }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control select-primary">
                            <option value="">Semua Status</option>
                            <option value="tersedia" {{ request('status') == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                            <option value="tidak_tersedia" {{ request('status') == 'tidak_tersedia' ? 'selected' : '' }}>Tidak Tersedia</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Urutkan</label>
                        <select name="sort" class="form-control select-primary">
                            <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Terbaru</option>
                            <option value="title" {{ request('sort') == 'title' ? 'selected' : '' }}>Judul A-Z</option>
                            <option value="pengarang" {{ request('sort') == 'pengarang' ? 'selected' : '' }}>Pengarang</option>
                            <option value="views" {{ request('sort') == 'views' ? 'selected' : '' }}>Paling Banyak Dilihat</option>
                            <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Terlama</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex" style="gap: 8px;">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter"></i>
                            </button>
                            @if(request()->hasAny(['search', 'category_id', 'status', 'sort']))
                            <a href="{{ route('manage.library.books.index') }}" class="btn btn-secondary" title="Reset">
                                <i class="fas fa-times"></i>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="10%">Cover</th>
                            <th>Judul</th>
                            <th>Pengarang</th>
                            <th>Kategori</th>
                            <th width="10%">Status</th>
                            <th width="10%">Views</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($books as $index => $book)
                        <tr>
                            <td>{{ $books->firstItem() + $index }}</td>
                            <td>
                                @if($book->cover_image)
                                    <img src="{{ asset('storage/' . $book->cover_image) }}" 
                                         class="img-thumbnail" 
                                         style="width: 60px; height: 80px; object-fit: cover;">
                                @else
                                    <div class="bg-secondary text-white d-flex align-items-center justify-content-center"
                                         style="width: 60px; height: 80px;">
                                        <i class="fas fa-book"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $book->judul }}</strong><br>
                                @if($book->isbn)
                                <small class="text-muted">ISBN: {{ $book->isbn }}</small>
                                @endif
                            </td>
                            <td>{{ $book->pengarang }}</td>
                            <td>
                                <span class="badge" style="background-color: {{ $book->category->warna ?? '#3498db' }};">
                                    {{ $book->category->nama_kategori ?? '-' }}
                                </span>
                            </td>
                            <td>
                                @if($book->status == 'tersedia')
                                    <span class="badge bg-success">Tersedia</span>
                                @else
                                    <span class="badge bg-secondary">Tidak Tersedia</span>
                                @endif
                            </td>
                            <td>
                                <i class="fas fa-eye me-1"></i>{{ number_format($book->total_views) }}
                            </td>
                            <td>
                                <div class="d-flex" style="gap: 4px;">
                                    <a href="{{ route('manage.library.books.edit', $book->id) }}" 
                                       class="btn btn-warning btn-sm action-btn" 
                                       title="Edit"
                                       style="width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('manage.library.books.destroy', $book->id) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirm('Yakin hapus buku ini?')"
                                          style="margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-danger btn-sm action-btn" 
                                                title="Hapus"
                                                style="width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-book fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Belum ada buku</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($books->hasPages())
            <div class="mt-3 d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">
                        Menampilkan {{ $books->firstItem() }} - {{ $books->lastItem() }} dari {{ $books->total() }} buku
                    </small>
                </div>
                <div>
                    {{ $books->links() }}
                </div>
            </div>
            @else
            <div class="mt-3">
                <small class="text-muted">
                    Menampilkan {{ $books->count() }} buku
                </small>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
.action-btn {
    transition: all 0.2s ease;
    border: none !important;
}

.action-btn.btn-warning {
    background-color: #ff6b35 !important;
    border-color: #ff6b35 !important;
    color: #fff !important;
}

.action-btn.btn-warning:hover {
    background-color: #ff5722 !important;
    border-color: #ff5722 !important;
    color: #fff !important;
}

.action-btn.btn-danger {
    background-color: #dc3545 !important;
    border-color: #dc3545 !important;
    color: #fff !important;
}

.action-btn.btn-danger:hover {
    background-color: #c82333 !important;
    border-color: #c82333 !important;
    color: #fff !important;
}

.action-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.15);
}

.table td {
    vertical-align: middle;
}
</style>
@endsection

