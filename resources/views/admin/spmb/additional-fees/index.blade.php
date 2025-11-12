@extends('layouts.coreui')

@push('styles')
<style>
    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    .btn-primary {
        background: linear-gradient(135deg, #008060 0%, #00a86b 100%);
        border: none;
        border-radius: 10px;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #006b4f 0%, #008060 100%);
        transform: translateY(-1px);
    }
    .table thead th {
        background: white;
        color: #333;
        border: none;
        font-weight: 600;
        border-bottom: 2px solid #008060;
        padding: 1rem;
    }
    .table tbody td {
        padding: 1rem;
        vertical-align: middle;
        border-top: 1px solid #dee2e6;
    }
    .table tbody tr:hover {
        background-color: #f8f9fa;
    }
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #6c757d;
    }
    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Action Buttons -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-1" style="font-size: 1.5rem;">
                        <i class="fas fa-plus-circle me-2"></i>Biaya Tambahan SPMB
                    </h4>
                    <p class="text-muted mb-0" style="font-size: 0.9rem;">Kelola biaya tambahan seperti seragam, buku, dan lainnya</p>
                </div>
                <div class="btn-group">
                    <a href="{{ route('manage.spmb.settings') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>Kembali ke Pengaturan
                    </a>
                    <a href="{{ route('manage.spmb.additional-fees.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Tambah Biaya
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Data Table -->
    <div class="card">
        <div class="card-body">
            @if($additionalFees->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Nama Biaya</th>
                                <th>Kode</th>
                                <th>Kategori</th>
                                <th>Jenis</th>
                                <th>Jumlah</th>
                                <th>Status</th>
                                <th>Urutan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($additionalFees as $index => $fee)
                                <tr>
                                    <td>{{ $additionalFees->firstItem() + $index }}</td>
                                    <td>
                                        <div>
                                            <strong>{{ $fee->name }}</strong>
                                            @if($fee->description)
                                                <br><small class="text-muted">{{ Str::limit($fee->description, 50) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td><code>{{ $fee->code }}</code></td>
                                    <td>{!! $fee->category_badge !!}</td>
                                    <td>{!! $fee->type_badge !!}</td>
                                    <td><strong>{{ $fee->formatted_amount }}</strong></td>
                                    <td>
                                        @if($fee->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>{{ $fee->sort_order }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('manage.spmb.additional-fees.show', $fee->id) }}" 
                                               class="btn btn-outline-info" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('manage.spmb.additional-fees.edit', $fee->id) }}" 
                                               class="btn btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('manage.spmb.additional-fees.toggle-status', $fee->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-{{ $fee->is_active ? 'secondary' : 'success' }}" 
                                                        title="{{ $fee->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                    <i class="fas fa-{{ $fee->is_active ? 'pause' : 'play' }}"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('manage.spmb.additional-fees.destroy', $fee->id) }}" 
                                                  class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus biaya tambahan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $additionalFees->links() }}
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-plus-circle"></i>
                    <h4>Belum Ada Biaya Tambahan</h4>
                    <p>Mulai dengan menambahkan biaya tambahan pertama Anda.</p>
                    <a href="{{ route('manage.spmb.additional-fees.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Tambah Biaya Pertama
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
