@extends('layouts.adminty')

@section('title', 'Detail Peminjaman - E-Perpustakaan')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="mb-4">
        <a href="{{ route('manage.library.loans.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <!-- Loan Header -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-book me-2"></i>Detail Peminjaman</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <!-- Book Info -->
                        <div class="col-md-3 text-center mb-3 mb-md-0">
                            @if($loan->book->cover_image)
                            <img src="{{ asset('storage/' . $loan->book->cover_image) }}" 
                                 class="img-fluid rounded shadow"
                                 style="max-height: 200px;">
                            @else
                            <div class="bg-secondary rounded d-flex align-items-center justify-content-center text-white"
                                 style="height: 200px;">
                                <i class="fas fa-book fa-4x"></i>
                            </div>
                            @endif
                        </div>
                        
                        <div class="col-md-9">
                            <h4 class="mb-3">{{ $loan->book->judul }}</h4>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p class="mb-2">
                                        <strong><i class="fas fa-user me-2 text-muted"></i>Pengarang:</strong><br>
                                        {{ $loan->book->pengarang }}
                                    </p>
                                    <p class="mb-2">
                                        <strong><i class="fas fa-layer-group me-2 text-muted"></i>Kategori:</strong><br>
                                        <span class="badge" style="background-color: {{ $loan->book->category->warna }};">
                                            {{ $loan->book->category->nama_kategori }}
                                        </span>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2">
                                        <strong><i class="fas fa-building me-2 text-muted"></i>Penerbit:</strong><br>
                                        {{ $loan->book->penerbit ?? '-' }}
                                    </p>
                                    <p class="mb-2">
                                        <strong><i class="fas fa-barcode me-2 text-muted"></i>ISBN:</strong><br>
                                        {{ $loan->book->isbn ?? '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Borrower Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>Informasi Peminjam</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong><i class="fas fa-user me-2 text-muted"></i>Nama:</strong><br>
                                {{ $loan->user->name }}
                            </p>
                            <p class="mb-2">
                                <strong><i class="fas fa-envelope me-2 text-muted"></i>Email:</strong><br>
                                {{ $loan->user->email }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            @if($loan->student)
                            <p class="mb-2">
                                <strong><i class="fas fa-id-card me-2 text-muted"></i>NIS:</strong><br>
                                {{ $loan->student->student_nis }}
                            </p>
                            <p class="mb-2">
                                <strong><i class="fas fa-school me-2 text-muted"></i>Kelas:</strong><br>
                                {{ $loan->student->class->class_name ?? '-' }}
                            </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loan Details -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Detail Peminjaman</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block mb-1">Tanggal Pinjam</small>
                                <h6 class="mb-0">{{ $loan->tanggal_pinjam->format('d M Y') }}</h6>
                                <small class="text-muted">{{ $loan->tanggal_pinjam->diffForHumans() }}</small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block mb-1">Harus Kembali</small>
                                <h6 class="mb-0">{{ $loan->tanggal_kembali_rencana->format('d M Y') }}</h6>
                                @if($loan->status == 'dipinjam')
                                    @if($loan->isOverdue())
                                    <small class="text-danger">Terlambat {{ $loan->daysOverdue() }} hari</small>
                                    @else
                                    <small class="text-success">{{ $loan->tanggal_kembali_rencana->diffForHumans() }}</small>
                                    @endif
                                @endif
                            </div>
                        </div>
                        @if($loan->tanggal_kembali_aktual)
                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block mb-1">Tanggal Kembali Aktual</small>
                                <h6 class="mb-0">{{ \Carbon\Carbon::parse($loan->tanggal_kembali_aktual)->format('d M Y') }}</h6>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($loan->tanggal_kembali_aktual)->diffForHumans() }}</small>
                            </div>
                        </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block mb-1">Status</small>
                                @if($loan->status == 'dipinjam')
                                    @if($loan->isOverdue())
                                    <h6 class="mb-0"><span class="badge bg-danger">Terlambat</span></h6>
                                    @else
                                    <h6 class="mb-0"><span class="badge bg-primary">Dipinjam</span></h6>
                                    @endif
                                @elseif($loan->status == 'dikembalikan')
                                <h6 class="mb-0"><span class="badge bg-success">Dikembalikan</span></h6>
                                @elseif($loan->status == 'hilang')
                                <h6 class="mb-0"><span class="badge bg-dark">Hilang/Rusak</span></h6>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($loan->denda > 0 || ($loan->isOverdue() && $loan->status == 'dipinjam'))
                    <div class="alert alert-warning mt-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                            <div>
                                <h6 class="mb-1">Denda Keterlambatan</h6>
                                @if($loan->denda > 0)
                                <p class="mb-0">Denda yang harus dibayar: <strong class="text-danger">Rp {{ number_format($loan->denda) }}</strong></p>
                                @else
                                <p class="mb-0">Estimasi denda jika dikembalikan sekarang: <strong class="text-warning">Rp {{ number_format($loan->calculateFine()) }}</strong></p>
                                <small class="text-muted">Denda: Rp 1.000/hari ({{ $loan->daysOverdue() }} hari terlambat)</small>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($loan->catatan)
                    <div class="mt-3">
                        <strong><i class="fas fa-sticky-note me-2 text-muted"></i>Catatan:</strong>
                        <p class="mb-0 mt-2 p-3 bg-light rounded">{{ $loan->catatan }}</p>
                    </div>
                    @endif

                    @if($loan->processor)
                    <div class="mt-3 text-muted small">
                        <i class="fas fa-user-check me-1"></i>Diproses oleh: {{ $loan->processor->name }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            @if($loan->status == 'dipinjam')
            <div class="d-grid gap-2">
                <button type="button" class="btn btn-success btn-lg" 
                        onclick="returnBook({{ $loan->id }})">
                    <i class="fas fa-undo me-2"></i>Kembalikan Buku
                </button>
                <a href="{{ route('manage.library.loans.edit', $loan->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-2"></i>Edit Peminjaman
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Return Modal -->
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="returnForm" method="POST" action="{{ route('manage.library.loans.return', $loan->id) }}">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Kembalikan Buku</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Konfirmasi pengembalian buku:</p>
                    <div class="alert alert-light border">
                        <strong>{{ $loan->book->judul }}</strong><br>
                        <small class="text-muted">oleh {{ $loan->user->name }}</small>
                    </div>
                    
                    @if($loan->isOverdue())
                    <div class="alert alert-warning">
                        <strong>Denda Keterlambatan:</strong><br>
                        Rp {{ number_format($loan->calculateFine()) }}
                        <small class="d-block text-muted mt-1">{{ $loan->daysOverdue() }} hari × Rp 1.000</small>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Kondisi Buku</label>
                        <select name="kondisi" class="form-control select-primary">
                            <option value="baik">Baik</option>
                            <option value="rusak_ringan">Rusak Ringan</option>
                            <option value="rusak_berat">Rusak Berat</option>
                            <option value="hilang">Hilang</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="catatan" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Kembalikan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function returnBook(loanId) {
    const modal = new bootstrap.Modal(document.getElementById('returnModal'));
    modal.show();
}
</script>
@endsection

