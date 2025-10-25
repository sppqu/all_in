@extends('layouts.coreui')

@section('title', 'Kelola Peminjaman - E-Perpustakaan')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">📋 Kelola Peminjaman Buku</h4>
            <p class="text-muted mb-0">Manajemen peminjaman dan pengembalian</p>
        </div>
        <div>
            <a href="{{ route('library.index') }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
            <a href="{{ route('manage.library.loans.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Tambah Peminjaman
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-book-open fa-2x opacity-50"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ number_format($stats['active']) }}</h3>
                            <p class="mb-0">Sedang Dipinjam</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle fa-2x opacity-50"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ number_format($stats['overdue']) }}</h3>
                            <p class="mb-0">Terlambat</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle fa-2x opacity-50"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ number_format($stats['returned_today']) }}</h3>
                            <p class="mb-0">Dikembalikan Hari Ini</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-coins fa-2x opacity-50"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">Rp {{ number_format($stats['total_fines']) }}</h3>
                            <p class="mb-0">Total Denda Bulan Ini</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('manage.library.loans.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="dipinjam" {{ request('status') == 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                            <option value="dikembalikan" {{ request('status') == 'dikembalikan' ? 'selected' : '' }}>Dikembalikan</option>
                            <option value="hilang" {{ request('status') == 'hilang' ? 'selected' : '' }}>Hilang/Rusak</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="overdue" class="form-select">
                            <option value="">Semua</option>
                            <option value="1" {{ request('overdue') == '1' ? 'selected' : '' }}>Hanya Terlambat</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Cari nama peminjam atau judul buku..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Loans Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Peminjam</th>
                            <th>Buku</th>
                            <th>Tgl Pinjam</th>
                            <th>Harus Kembali</th>
                            <th>Tgl Kembali</th>
                            <th>Status</th>
                            <th>Denda</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loans as $index => $loan)
                        <tr>
                            <td>{{ $loans->firstItem() + $index }}</td>
                            <td>
                                <strong>{{ $loan->user->name }}</strong><br>
                                <small class="text-muted">{{ $loan->user->email }}</small>
                            </td>
                            <td>
                                {{ Str::limit($loan->book->judul, 40) }}<br>
                                <small class="text-muted">{{ $loan->book->pengarang }}</small>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($loan->tanggal_pinjam)->format('d M Y') }}</td>
                            <td>
                                {{ \Carbon\Carbon::parse($loan->tanggal_kembali_rencana)->format('d M Y') }}
                                @if($loan->isOverdue() && $loan->status == 'dipinjam')
                                <br><span class="badge bg-danger">{{ $loan->daysOverdue() }} hari</span>
                                @endif
                            </td>
                            <td>{{ $loan->tanggal_kembali_aktual ? \Carbon\Carbon::parse($loan->tanggal_kembali_aktual)->format('d M Y') : '-' }}</td>
                            <td>
                                @if($loan->status == 'dipinjam')
                                    @if($loan->isOverdue())
                                    <span class="badge bg-danger">Terlambat</span>
                                    @else
                                    <span class="badge bg-primary">Dipinjam</span>
                                    @endif
                                @elseif($loan->status == 'dikembalikan')
                                <span class="badge bg-success">Dikembalikan</span>
                                @elseif($loan->status == 'hilang')
                                <span class="badge bg-dark">Hilang/Rusak</span>
                                @endif
                            </td>
                            <td>
                                @if($loan->denda > 0)
                                <span class="text-danger fw-bold">Rp {{ number_format($loan->denda) }}</span>
                                @elseif($loan->isOverdue() && $loan->status == 'dipinjam')
                                <span class="text-warning">Rp {{ number_format($loan->calculateFine()) }}</span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($loan->status == 'dipinjam')
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-success" 
                                            onclick="returnBook({{ $loan->id }})" title="Kembalikan">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                    <a href="{{ route('manage.library.loans.edit', $loan->id) }}" 
                                       class="btn btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                                @else
                                <a href="{{ route('manage.library.loans.show', $loan->id) }}" 
                                   class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-book fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Belum ada data peminjaman</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($loans->hasPages())
            <div class="mt-3">
                {{ $loans->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Return Modal -->
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="returnForm" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Kembalikan Buku</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah buku sudah dikembalikan?</p>
                    <div class="mb-3">
                        <label class="form-label">Kondisi Buku</label>
                        <select name="kondisi" class="form-select" required>
                            <option value="baik">Baik</option>
                            <option value="rusak_ringan">Rusak Ringan</option>
                            <option value="rusak_berat">Rusak Berat</option>
                            <option value="hilang">Hilang</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="catatan" class="form-control" rows="3"></textarea>
                    </div>
                    <div id="fineInfo" class="alert alert-warning" style="display:none;">
                        <strong>Denda keterlambatan:</strong> <span id="fineAmount"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Kembalikan Buku</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function returnBook(loanId) {
    const form = document.getElementById('returnForm');
    form.action = `/manage/library/loans/${loanId}/return`;
    
    // Fetch loan details to show fine if any
    fetch(`/manage/library/loans/${loanId}/fine`)
        .then(res => res.json())
        .then(data => {
            if (data.fine > 0) {
                document.getElementById('fineInfo').style.display = 'block';
                document.getElementById('fineAmount').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.fine);
            } else {
                document.getElementById('fineInfo').style.display = 'none';
            }
        });
    
    const modal = new bootstrap.Modal(document.getElementById('returnModal'));
    modal.show();
}
</script>
@endsection

