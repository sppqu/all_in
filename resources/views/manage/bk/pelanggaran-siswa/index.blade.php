@extends('layouts.adminty')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-clipboard-list me-2"></i>Pencatatan Pelanggaran Siswa
                        </h5>
                        <div>
                            <a href="{{ route('manage.bk.pelanggaran-siswa.report') }}" class="btn btn-light btn-sm me-2">
                                <i class="fas fa-chart-bar"></i> Laporan Rekap
                            </a>
                            <a href="{{ route('manage.bk.pelanggaran-siswa.create') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-plus"></i> Catat Pelanggaran
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Filter -->
                    <form method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="Cari siswa (NIS/Nama)..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-control select-primary">
                                    <option value="">Semua Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="start_date" class="form-control" placeholder="Dari Tanggal" value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="end_date" class="form-control" placeholder="Sampai Tanggal" value="{{ request('end_date') }}">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('manage.bk.pelanggaran-siswa.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="10%">Tanggal</th>
                                    <th width="20%">Siswa</th>
                                    <th width="25%">Pelanggaran</th>
                                    <th width="8%" class="text-center">Point</th>
                                    <th width="10%">Pelapor</th>
                                    <th width="10%" class="text-center">Status</th>
                                    <th width="12%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pelanggaranSiswa as $item)
                                <tr>
                                    <td>{{ $pelanggaranSiswa->firstItem() + $loop->index }}</td>
                                    <td>{{ $item->tanggal_pelanggaran->format('d/m/Y') }}</td>
                                    <td>
                                        <strong>{{ $item->siswa->student_full_name }}</strong><br>
                                        <small class="text-muted">NIS: {{ $item->siswa->student_nis }}</small>
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: {{ $item->pelanggaran->kategori->warna }}">
                                            {{ $item->pelanggaran->kategori->nama }}
                                        </span><br>
                                        {{ $item->pelanggaran->nama }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-danger fs-6">{{ $item->pelanggaran->point }}</span>
                                    </td>
                                    <td>{{ $item->pelapor }}</td>
                                    <td class="text-center">
                                        @if($item->status == 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($item->status == 'approved')
                                            <span class="badge bg-success">Disetujui</span>
                                        @else
                                            <span class="badge bg-danger">Ditolak</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('manage.bk.pelanggaran-siswa.show', $item->id) }}" class="btn btn-outline-info" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('manage.bk.pelanggaran-siswa.cetak-surat', $item->id) }}" 
                                               class="btn btn-outline-success" title="Cetak Surat" target="_blank">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            <a href="{{ route('manage.bk.pelanggaran-siswa.edit', $item->id) }}" class="btn btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('manage.bk.pelanggaran-siswa.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus data ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Belum ada data pelanggaran siswa</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($pelanggaranSiswa->hasPages())
                    <div class="mt-3">
                        {{ $pelanggaranSiswa->onEachSide(1)->links('pagination::bootstrap-4') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Pagination - Smaller & Cleaner */
.pagination {
    margin-bottom: 0;
}

.page-link {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    line-height: 1.5;
}

.page-item:first-child .page-link,
.page-item:last-child .page-link {
    border-radius: 0.25rem;
}
</style>
@endsection

