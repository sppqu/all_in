@extends('layouts.adminty')

@section('title', 'Kategori Buku - E-Perpustakaan')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">📑 Kategori Buku</h4>
            <p class="text-muted mb-0">Manajemen kategori koleksi buku</p>
        </div>
        <div>
            <a href="{{ route('library.index') }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
            <a href="{{ route('manage.library.categories.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Tambah Kategori
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="10%">Icon</th>
                            <th>Nama Kategori</th>
                            <th>Kode</th>
                            <th width="10%">Warna</th>
                            <th width="10%">Jumlah Buku</th>
                            <th width="10%">Status</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $index => $category)
                        <tr>
                            <td>{{ $categories->firstItem() + $index }}</td>
                            <td>
                                <div class="text-center" style="color: {{ $category->warna ?? '#3498db' }};">
                                    <i class="{{ $category->icon ?? 'fas fa-folder' }} fa-2x"></i>
                                </div>
                            </td>
                            <td>
                                <strong>{{ $category->nama_kategori }}</strong>
                                @if($category->deskripsi)
                                <br><small class="text-muted">{{ Str::limit($category->deskripsi, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $category->kode }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div style="width: 30px; height: 30px; background-color: {{ $category->warna ?? '#3498db' }}; border-radius: 4px; border: 1px solid #ddd;"></div>
                                    <small class="ms-2 text-muted">{{ $category->warna ?? '#3498db' }}</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ number_format($category->books_count ?? 0) }}</span>
                            </td>
                            <td>
                                @if($category->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('manage.library.categories.edit', $category->id) }}" 
                                       class="btn btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('manage.library.categories.destroy', $category->id) }}" 
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Yakin hapus kategori ini?')">
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
                                <i class="fas fa-folder fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Belum ada kategori</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($categories->hasPages())
            <div class="mt-3">
                {{ $categories->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

