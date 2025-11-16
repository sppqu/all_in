@extends('layouts.adminty')

@section('title', 'Peminjaman Saya - E-Perpustakaan')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">📖 Peminjaman Saya</h4>
            <p class="text-muted mb-0">Riwayat peminjaman buku digital</p>
        </div>
        <div>
            <a href="{{ route('library.card') }}" class="btn btn-outline-primary me-2">
                <i class="fas fa-id-card me-2"></i>Kartu Perpustakaan
            </a>
            <a href="{{ route('library.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-book fa-3x opacity-50"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h2 class="mb-0">{{ $stats['active'] }}</h2>
                            <p class="mb-0">Sedang Dipinjam</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle fa-3x opacity-50"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h2 class="mb-0">{{ $stats['returned'] }}</h2>
                            <p class="mb-0">Sudah Dikembalikan</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle fa-3x opacity-50"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h2 class="mb-0">{{ $stats['overdue'] }}</h2>
                            <p class="mb-0">Terlambat</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loans List -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if($loans->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Buku</th>
                            <th>Tanggal Pinjam</th>
                            <th>Harus Kembali</th>
                            <th>Tgl Kembali</th>
                            <th>Status</th>
                            <th>Denda</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($loans as $index => $loan)
                        <tr>
                            <td>{{ $loans->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($loan->book->cover_image)
                                    <img src="{{ asset('storage/' . $loan->book->cover_image) }}" 
                                         class="rounded me-3" 
                                         style="width: 40px; height: 55px; object-fit: cover;">
                                    @else
                                    <div class="bg-secondary rounded me-3 d-flex align-items-center justify-content-center text-white"
                                         style="width: 40px; height: 55px;">
                                        <i class="fas fa-book"></i>
                                    </div>
                                    @endif
                                    <div>
                                        <strong>{{ $loan->book->judul }}</strong><br>
                                        <small class="text-muted">{{ $loan->book->pengarang }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($loan->tanggal_pinjam)->format('d M Y') }}</td>
                            <td>
                                {{ \Carbon\Carbon::parse($loan->tanggal_kembali_rencana)->format('d M Y') }}
                                @if($loan->isOverdue() && $loan->status == 'dipinjam')
                                <br><span class="badge bg-danger">{{ $loan->daysOverdue() }} hari terlambat</span>
                                @endif
                            </td>
                            <td>
                                {{ $loan->tanggal_kembali_aktual ? \Carbon\Carbon::parse($loan->tanggal_kembali_aktual)->format('d M Y') : '-' }}
                            </td>
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
                                <span class="badge bg-dark">Hilang</span>
                                @else
                                <span class="badge bg-secondary">{{ ucfirst($loan->status) }}</span>
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
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($loans->hasPages())
            <div class="mt-3">
                {{ $loans->links() }}
            </div>
            @endif
            @else
            <div class="text-center py-5">
                <i class="fas fa-book-reader fa-4x text-muted mb-3"></i>
                <h5>Belum Ada Peminjaman</h5>
                <p class="text-muted">Anda belum pernah meminjam buku</p>
                <a href="{{ route('library.search') }}" class="btn btn-primary mt-3">
                    <i class="fas fa-search me-2"></i>Cari Buku
                </a>
            </div>
            @endif
        </div>
    </div>

    @if($stats['overdue'] > 0)
    <div class="alert alert-warning mt-4">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
            <div>
                <strong>Perhatian!</strong><br>
                Anda memiliki {{ $stats['overdue'] }} peminjaman yang terlambat. Segera kembalikan untuk menghindari denda tambahan (Rp 1.000/hari).
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

