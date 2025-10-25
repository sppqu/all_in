@extends('layouts.coreui')

@section('title', 'Kode Akun')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-chart-line me-2"></i>
                            Kode Akun (Chart of Accounts)
                        </h4>
                        <a href="{{ route('manage.account-codes.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            Tambah Kode Akun
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

                    @if(session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ session('warning') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Statistik -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Total Akun</h6>
                                            <h3>{{ $accountCodes->total() }}</h3>
                                        </div>
                                        <div>
                                            <i class="fas fa-chart-line fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Aktif</h6>
                                            <h3>{{ $accountCodes->where('is_active', true)->count() }}</h3>
                                        </div>
                                        <div>
                                            <i class="fas fa-check-circle fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Non-Aktif</h6>
                                            <h3>{{ $accountCodes->where('is_active', false)->count() }}</h3>
                                        </div>
                                        <div>
                                            <i class="fas fa-times-circle fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Tipe Aktiva</h6>
                                            <h3>{{ $accountCodes->where('tipe', 'aktiva')->count() }}</h3>
                                        </div>
                                        <div>
                                            <i class="fas fa-building fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter dan Pencarian -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <form method="GET" action="{{ route('manage.account-codes.index') }}" class="row g-3">
                                <div class="col-md-2">
                                    <label for="search" class="form-label">Pencarian</label>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           value="{{ request('search') }}" placeholder="Kode, nama, deskripsi...">
                                </div>
                                <div class="col-md-2">
                                    <label for="tipe" class="form-label">Tipe</label>
                                    <select class="form-select" id="tipe" name="tipe">
                                        <option value="">Semua Tipe</option>
                                        @foreach($tipeOptions as $value => $label)
                                            <option value="{{ $value }}" {{ request('tipe') == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="kategori" class="form-label">Kategori</label>
                                    <select class="form-select" id="kategori" name="kategori">
                                        <option value="">Semua Kategori</option>
                                        @foreach($kategoriOptions as $value => $label)
                                            <option value="{{ $value }}" {{ request('kategori') == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="">Semua Status</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search me-1"></i>
                                            Cari
                                        </button>
                                        <a href="{{ route('manage.account-codes.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-undo me-1"></i>
                                            Reset
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    </div>

                    <!-- Tabel Kode Akun -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="15%">Kode</th>
                                    <th width="25%">Nama Akun</th>
                                    <th width="15%">Tipe</th>
                                    <th width="15%">Kategori</th>
                                    <th width="10%">Status</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($accountCodes as $index => $accountCode)
                                <tr>
                                    <td>{{ $index + 1 + ($accountCodes->currentPage() - 1) * $accountCodes->perPage() }}</td>
                                    <td>
                                        <strong>{{ $accountCode->kode }}</strong>
                                    </td>
                                    <td>
                                        <div>{{ $accountCode->nama }}</div>
                                        @if($accountCode->deskripsi)
                                            <small class="text-muted">{{ Str::limit($accountCode->deskripsi, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="{{ $accountCode->tipe_badge_class }}">
                                            {{ $accountCode->tipe_label }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($accountCode->kategori)
                                            <span class="badge bg-info">{{ $accountCode->kategori_label }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="{{ $accountCode->status_badge_class }}">
                                            {{ $accountCode->status_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('manage.account-codes.show', $accountCode) }}" 
                                               class="btn btn-sm btn-info" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('manage.account-codes.edit', $accountCode) }}" 
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('manage.account-codes.toggle-status', $accountCode) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-secondary" title="Toggle Status"
                                                        onclick="return confirm('Yakin ingin mengubah status kode akun ini?')">
                                                    <i class="fas fa-toggle-on"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('manage.account-codes.destroy', $accountCode) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus"
                                                        onclick="return confirm('Yakin ingin menghapus kode akun ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3"></i>
                                            <p>Tidak ada data kode akun</p>
                                            <a href="{{ route('manage.account-codes.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus me-1"></i>
                                                Tambah Kode Akun Pertama
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        @if($accountCodes->hasPages())
                            <div class="d-flex align-items-center">
                                <span class="text-muted me-3">
                                    Showing {{ $accountCodes->firstItem() ?? 0 }} to {{ $accountCodes->lastItem() ?? 0 }} of {{ $accountCodes->total() }} results
                                </span>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination pagination-sm mb-0">
                                        {{-- Previous Page Link --}}
                                        @if ($accountCodes->onFirstPage())
                                            <li class="page-item disabled">
                                                <span class="page-link">‹</span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $accountCodes->previousPageUrl() }}" rel="prev">‹</a>
                                            </li>
                                        @endif

                                        {{-- Pagination Elements --}}
                                        @foreach ($accountCodes->getUrlRange(1, $accountCodes->lastPage()) as $page => $url)
                                            @if ($page == $accountCodes->currentPage())
                                                <li class="page-item active">
                                                    <span class="page-link">{{ $page }}</span>
                                                </li>
                                            @else
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                                </li>
                                            @endif
                                        @endforeach

                                        {{-- Next Page Link --}}
                                        @if ($accountCodes->hasMorePages())
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $accountCodes->nextPageUrl() }}" rel="next">›</a>
                                            </li>
                                        @else
                                            <li class="page-item disabled">
                                                <span class="page-link">›</span>
                                            </li>
                                        @endif
                                    </ul>
                                </nav>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Custom pagination styling - minimal and clean */
    .pagination {
        margin-bottom: 0;
    }
    .pagination .page-link {
        color: #6c757d;
        background-color: #fff;
        border: 1px solid #dee2e6;
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        min-width: 32px;
        text-align: center;
    }
    .pagination .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: #fff;
    }
    .pagination .page-item.disabled .page-link {
        color: #adb5bd;
        background-color: #fff;
        border-color: #dee2e6;
    }
    .pagination .page-link:hover {
        background-color: #e9ecef;
        border-color: #dee2e6;
        color: #0d6efd;
    }
    .pagination-sm .page-link {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Auto submit form when filter changes
    $('#tipe, #kategori, #status').change(function() {
        $(this).closest('form').submit();
    });
});
</script>
@endpush 