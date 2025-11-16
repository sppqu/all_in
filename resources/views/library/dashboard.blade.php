@extends('layouts.adminty')

@section('title', 'E-Perpustakaan')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">📚 E-Perpustakaan Digital</h4>
            <p class="text-muted mb-0">Koleksi buku digital lengkap untuk pembelajaran</p>
        </div>
        <div>
            @if(auth()->user()->role == 'superadmin' || auth()->user()->role == 'admin')
            <a href="{{ route('manage.library.cards.index') }}" class="btn btn-outline-success me-2">
                <i class="fas fa-id-card me-2"></i>Cetak Kartu
            </a>
            <a href="{{ route('manage.library.loans.index') }}" class="btn btn-outline-info me-2">
                <i class="fas fa-list-alt me-2"></i>Kelola Peminjaman
            </a>
            <a href="{{ route('manage.library.books.index') }}" class="btn btn-primary">
                <i class="fas fa-cog me-2"></i>Kelola Buku
            </a>
            @else
            <a href="{{ route('library.card') }}" class="btn btn-outline-primary me-2">
                <i class="fas fa-id-card me-2"></i>Kartu Perpustakaan
            </a>
            <a href="{{ route('library.my-loans') }}" class="btn btn-primary">
                <i class="fas fa-book me-2"></i>Peminjaman Saya
            </a>
            @endif
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white" style="position: relative; padding-right: 90px !important;">
                    <div>
                        <h3 class="mb-0">{{ number_format($totalBooks) }}</h3>
                        <p class="mb-0">Total Buku</p>
                    </div>
                    <div style="position: absolute; top: 15px; right: 10px; width: 64px; height: 64px; display: flex; align-items: center; justify-content: center; border-radius: 8px; background-color: rgba(255, 255, 255, 0.2); z-index: 10;">
                        <i class="fas fa-book" style="font-size: 2rem; color: #ffffff !important;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white" style="position: relative; padding-right: 90px !important;">
                    <div>
                        <h3 class="mb-0">{{ number_format($totalCategories) }}</h3>
                        <p class="mb-0">Kategori</p>
                    </div>
                    <div style="position: absolute; top: 15px; right: 10px; width: 64px; height: 64px; display: flex; align-items: center; justify-content: center; border-radius: 8px; background-color: rgba(255, 255, 255, 0.2); z-index: 10;">
                        <i class="fas fa-layer-group" style="font-size: 2rem; color: #ffffff !important;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body text-white" style="position: relative; padding-right: 90px !important;">
                    <div>
                        <h3 class="mb-0">{{ number_format($activeLoans) }}</h3>
                        <p class="mb-0">Dipinjam</p>
                    </div>
                    <div style="position: absolute; top: 15px; right: 10px; width: 64px; height: 64px; display: flex; align-items: center; justify-content: center; border-radius: 8px; background-color: rgba(255, 255, 255, 0.2); z-index: 10;">
                        <i class="fas fa-book-reader" style="font-size: 2rem; color: #ffffff !important;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="card-body text-white" style="position: relative; padding-right: 90px !important;">
                    <div>
                        <h3 class="mb-0">{{ number_format($totalReads) }}</h3>
                        <p class="mb-0">Pembacaan</p>
                    </div>
                    <div style="position: absolute; top: 15px; right: 10px; width: 64px; height: 64px; display: flex; align-items: center; justify-content: center; border-radius: 8px; background-color: rgba(255, 255, 255, 0.2); z-index: 10;">
                        <i class="fas fa-eye" style="font-size: 2rem; color: #ffffff !important;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header" style="background: #01a9ac; color: #ffffff !important;">
                    <h5 class="mb-0" style="color: #ffffff !important;">
                        <i class="fas fa-book-reader me-2" style="color: #ffffff !important;"></i>Grafik Peminjaman Buku (30 Hari Terakhir)
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="loansChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header" style="background: #28a745; color: #ffffff !important;">
                    <h5 class="mb-0" style="color: #ffffff !important;">
                        <i class="fas fa-eye me-2" style="color: #ffffff !important;"></i>Grafik Pembacaan Ebook (30 Hari Terakhir)
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="readsChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>


    @if($featuredBooks->count() > 0)
    <!-- Featured Books -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 fw-bold">⭐ Buku Unggulan</h5>
            <a href="{{ route('library.search') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
        </div>
        <div class="row g-3">
            @foreach($featuredBooks as $book)
            <div class="col-md-3">
                @include('library.partials.book-card', ['book' => $book])
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- My Loans Section (User) -->
    @if(auth()->user()->role !== 'superadmin' && auth()->user()->role !== 'admin')
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 fw-bold">📖 Peminjaman Saya</h5>
            <a href="{{ route('library.my-loans') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
        </div>
        @php
            $myLoans = \App\Models\BookLoan::with('book')
                ->where('user_id', auth()->id())
                ->where('status', 'dipinjam')
                ->latest()
                ->take(3)
                ->get();
        @endphp
        
        @if($myLoans->count() > 0)
        <div class="row g-3">
            @foreach($myLoans as $loan)
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex">
                            @if($loan->book->cover_image)
                            <img src="{{ asset('storage/' . $loan->book->cover_image) }}" 
                                 style="width: 60px; height: 80px; object-fit: cover;" 
                                 class="rounded me-3">
                            @else
                            <div class="bg-secondary rounded me-3 d-flex align-items-center justify-content-center text-white"
                                 style="width: 60px; height: 80px;">
                                <i class="fas fa-book"></i>
                            </div>
                            @endif
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ Str::limit($loan->book->judul, 40) }}</h6>
                                <small class="text-muted d-block mb-2">{{ $loan->book->pengarang }}</small>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        Kembali: {{ \Carbon\Carbon::parse($loan->tanggal_kembali_rencana)->format('d M Y') }}
                                    </small>
                                    @if($loan->isOverdue())
                                    <span class="badge bg-danger">
                                        Terlambat {{ $loan->daysOverdue() }} hari
                                    </span>
                                    @else
                                    <span class="badge bg-success">Aktif</span>
                                    @endif
                                </div>
                                @if($loan->isOverdue())
                                <div class="mt-2 alert alert-danger py-1 px-2 mb-0">
                                    <small><strong>Denda:</strong> Rp {{ number_format($loan->calculateFine()) }}</small>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-4">
                <i class="fas fa-book-reader fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-0">Anda belum memiliki peminjaman aktif</p>
            </div>
        </div>
        @endif
    </div>
    @endif

</div>

<style>
.hover-shadow {
    transition: all 0.3s ease;
}
.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
}
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
$(document).ready(function() {
    // Loans Chart
    const loansCtx = document.getElementById('loansChart');
    if (loansCtx) {
        new Chart(loansCtx, {
            type: 'line',
            data: {
                labels: @json($dailyLoanLabels ?? []),
                datasets: [{
                    label: 'Peminjaman Buku',
                    data: @json($dailyLoans ?? []),
                    borderColor: '#01a9ac',
                    backgroundColor: 'rgba(1, 169, 172, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointBackgroundColor: '#01a9ac',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    tooltip: {
                        enabled: true,
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 13
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    },
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            }
        });
    }
    
    // Reads Chart
    const readsCtx = document.getElementById('readsChart');
    if (readsCtx) {
        new Chart(readsCtx, {
            type: 'line',
            data: {
                labels: @json($dailyReadLabels ?? []),
                datasets: [{
                    label: 'Pembacaan Ebook',
                    data: @json($dailyReads ?? []),
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointBackgroundColor: '#28a745',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    tooltip: {
                        enabled: true,
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 13
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    },
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endsection
