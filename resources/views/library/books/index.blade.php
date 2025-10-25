@extends('layouts.coreui')

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

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

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
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('manage.library.books.edit', $book->id) }}" 
                                       class="btn btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('manage.library.books.destroy', $book->id) }}" 
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Yakin hapus buku ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" title="Hapus">
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
            <div class="mt-3">
                {{ $books->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

