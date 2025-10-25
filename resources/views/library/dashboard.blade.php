@extends('layouts.coreui')

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
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-books fa-3x opacity-50"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h2 class="mb-0">{{ number_format($totalBooks) }}</h2>
                            <p class="mb-0">Total Buku</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-layer-group fa-3x opacity-50"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h2 class="mb-0">{{ number_format($totalCategories) }}</h2>
                            <p class="mb-0">Kategori</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-book-reader fa-3x opacity-50"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h2 class="mb-0">{{ number_format($activeLoans) }}</h2>
                            <p class="mb-0">Dipinjam</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-eye fa-3x opacity-50"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h2 class="mb-0">{{ number_format($totalReads) }}</h2>
                            <p class="mb-0">Pembacaan</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <form action="{{ route('library.search') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" name="q" class="form-control border-start-0" 
                                   placeholder="Cari judul, pengarang, atau penerbit..." 
                                   value="{{ request('q') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select name="category" class="form-select form-select-lg">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->nama_kategori }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-search me-2"></i>Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Categories -->
    <div class="mb-4">
        <h5 class="mb-3 fw-bold">📑 Kategori Buku</h5>
        <div class="row g-3">
            @foreach($categories as $category)
            <div class="col-md-2 col-6">
                <a href="{{ route('library.search', ['category' => $category->id]) }}" 
                   class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 hover-shadow">
                        <div class="card-body text-center">
                            <div class="mb-2" style="color: {{ $category->warna }};">
                                <i class="{{ $category->icon }} fa-2x"></i>
                            </div>
                            <h6 class="mb-1" style="font-size: 0.85rem;">{{ $category->nama_kategori }}</h6>
                            <small class="text-muted">{{ $category->books_count }} buku</small>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
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

    <!-- Loans Management (Admin) -->
    @if(auth()->user()->role == 'superadmin' || auth()->user()->role == 'admin')
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 fw-bold">📋 Peminjaman Terbaru</h5>
            <a href="{{ route('manage.library.loans.index') }}" class="btn btn-sm btn-outline-primary">Kelola Semua</a>
        </div>
        @php
            $recentLoans = \App\Models\BookLoan::with(['book', 'user'])
                ->latest()
                ->take(5)
                ->get();
        @endphp
        
        @if($recentLoans->count() > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Peminjam</th>
                                <th>Buku</th>
                                <th>Tanggal Pinjam</th>
                                <th>Kembali</th>
                                <th>Status</th>
                                <th>Denda</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentLoans as $loan)
                            <tr>
                                <td>{{ $loan->user->name }}</td>
                                <td>{{ Str::limit($loan->book->judul, 40) }}</td>
                                <td>{{ \Carbon\Carbon::parse($loan->tanggal_pinjam)->format('d M Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($loan->tanggal_kembali_rencana)->format('d M Y') }}</td>
                                <td>
                                    @if($loan->status == 'dipinjam')
                                        @if($loan->isOverdue())
                                        <span class="badge bg-danger">Terlambat</span>
                                        @else
                                        <span class="badge bg-primary">Dipinjam</span>
                                        @endif
                                    @elseif($loan->status == 'dikembalikan')
                                    <span class="badge bg-success">Dikembalikan</span>
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
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Recent Books -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 fw-bold">🆕 Buku Terbaru</h5>
            <a href="{{ route('library.search', ['sort' => 'latest']) }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
        </div>
        <div class="row g-3">
            @foreach($recentBooks as $book)
            <div class="col-md-2">
                @include('library.partials.book-card', ['book' => $book])
            </div>
            @endforeach
        </div>
    </div>
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
@endsection
