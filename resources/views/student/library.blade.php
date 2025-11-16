@extends('layouts.student')

@section('title', 'E-Perpustakaan')

@section('content')
<div class="container-fluid px-3 py-4">
    <!-- Header Section -->
    <div class="welcome-banner mb-4">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1 fw-bold text-white">
                    <i class="fas fa-book-reader me-2"></i>E-Perpustakaan
                </h4>
                <p class="mb-0 text-white-50">Kelola peminjaman dan baca buku digitalmu</p>
            </div>
            <div class="d-none d-md-block">
                <a href="#kartu-digital" class="btn btn-light btn-sm">
                    <i class="fas fa-id-card me-1"></i>Kartu Digital
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                    <i class="fas fa-book-open"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Sedang Dipinjam</div>
                    <div class="stat-value">{{ $activeLoans }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                    <i class="fas fa-history"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Total Pinjaman</div>
                    <div class="stat-value">{{ $totalBorrowed }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                    <i class="fas fa-book-reader"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Buku Dibaca</div>
                    <div class="stat-value">{{ $booksRead }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <ul class="nav nav-pills mb-4 library-tabs" id="libraryTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="loans-tab" data-bs-toggle="tab" data-bs-target="#loans" type="button">
                <i class="fas fa-book-open me-2"></i>Riwayat Pinjaman
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="catalog-tab" data-bs-toggle="tab" data-bs-target="#catalog" type="button">
                <i class="fas fa-books me-2"></i>Katalog Buku
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="card-tab" data-bs-toggle="tab" data-bs-target="#card" type="button">
                <i class="fas fa-id-card me-2"></i>Kartu Digital
            </button>
        </li>
    </ul>

    <!-- Tabs Content -->
    <div class="tab-content" id="libraryTabsContent">
        
        <!-- Riwayat Pinjaman Tab -->
        <div class="tab-pane fade show active" id="loans" role="tabpanel">
            @if($loanHistory->count() > 0)
                <div class="row g-3">
                    @foreach($loanHistory as $loan)
                    <div class="col-12">
                        <div class="loan-card">
                            <div class="row align-items-center">
                                <div class="col-3 col-md-2">
                                    <div class="book-cover-small">
                                        @if($loan->cover_image)
                                            <img src="{{ asset('storage/' . $loan->cover_image) }}" alt="{{ $loan->judul }}">
                                        @else
                                            <div class="book-placeholder">
                                                <i class="fas fa-book"></i>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-9 col-md-10">
                                    <div class="loan-info">
                                        <h6 class="loan-title mb-1">{{ $loan->judul }}</h6>
                                        <p class="loan-author mb-2">
                                            <i class="fas fa-user me-1"></i>{{ $loan->pengarang }}
                                        </p>
                                        <div class="row g-2">
                                            <div class="col-6 col-md-3">
                                                <small class="text-muted d-block">Dipinjam</small>
                                                <small class="fw-bold">{{ \Carbon\Carbon::parse($loan->tanggal_pinjam)->format('d/m/Y') }}</small>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <small class="text-muted d-block">Kembali</small>
                                                <small class="fw-bold">{{ $loan->tanggal_kembali ? \Carbon\Carbon::parse($loan->tanggal_kembali)->format('d/m/Y') : '-' }}</small>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <small class="text-muted d-block">Status</small>
                                                @if($loan->status == 'dipinjam')
                                                    @php
                                                        $isOverdue = $loan->tanggal_kembali ? \Carbon\Carbon::parse($loan->tanggal_kembali)->isPast() : false;
                                                    @endphp
                                                    @if($isOverdue)
                                                        <span class="badge bg-danger">Terlambat</span>
                                                    @else
                                                        <span class="badge bg-warning">Dipinjam</span>
                                                    @endif
                                                @elseif($loan->status == 'dikembalikan')
                                                    <span class="badge bg-success">Dikembalikan</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($loan->status) }}</span>
                                                @endif
                                            </div>
                                            @if($loan->status == 'dipinjam' && $loan->tanggal_kembali && \Carbon\Carbon::parse($loan->tanggal_kembali)->isPast())
                                            <div class="col-6 col-md-3">
                                                <small class="text-muted d-block">Denda</small>
                                                @php
                                                    $days = \Carbon\Carbon::parse($loan->tanggal_kembali)->diffInDays(now());
                                                    $fine = $days * 1000;
                                                @endphp
                                                <small class="fw-bold text-danger">Rp {{ number_format($fine, 0, ',', '.') }}</small>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-book-open"></i>
                    <h5>Belum Ada Riwayat Pinjaman</h5>
                    <p>Mulai pinjam buku dari katalog perpustakaan</p>
                </div>
            @endif
        </div>

        <!-- Katalog Buku Tab -->
        <div class="tab-pane fade" id="catalog" role="tabpanel">
            <!-- Search Bar -->
            <div class="search-box mb-4">
                <input type="text" class="form-control" id="searchBook" placeholder="Cari buku berdasarkan judul atau pengarang...">
                <i class="fas fa-search"></i>
            </div>

            <!-- Category Filter -->
            <div class="category-filter mb-4">
                <button class="category-btn active" data-category="all">
                    <i class="fas fa-book"></i> Semua
                </button>
                @foreach($categories as $category)
                <button class="category-btn" data-category="{{ $category->id }}">
                    <i class="fas fa-bookmark"></i> {{ $category->nama_kategori }}
                </button>
                @endforeach
            </div>

            <!-- Books Grid -->
            @if($recentBooks->count() > 0)
                <div class="row g-3" id="booksGrid">
                    @foreach($recentBooks as $book)
                    <div class="col-6 col-md-4 col-lg-3 book-item" data-category="{{ $book->category_id }}" data-title="{{ strtolower($book->judul) }}" data-author="{{ strtolower($book->pengarang) }}">
                        <div class="book-card" @if($book->file_path) onclick="window.location.href='{{ route('student.library.read', $book->id) }}'" style="cursor: pointer;" @endif>
                            <div class="book-cover">
                                @if($book->cover_image)
                                    <img src="{{ asset('storage/' . $book->cover_image) }}" alt="{{ $book->judul }}">
                                @else
                                    <div class="book-placeholder-large">
                                        <i class="fas fa-book"></i>
                                    </div>
                                @endif
                                <div class="book-overlay">
                                    @if($book->status == 'tersedia')
                                        <span class="badge bg-success">Tersedia</span>
                                    @else
                                        <span class="badge bg-secondary">Dipinjam</span>
                                    @endif
                                </div>
                            </div>
                            <div class="book-info">
                                <div class="book-category mb-1" style="color: {{ $book->warna }}">
                                    <i class="fas fa-bookmark me-1"></i>{{ $book->nama_kategori }}
                                </div>
                                <h6 class="book-title">{{ Str::limit($book->judul, 40) }}</h6>
                                <p class="book-author">{{ Str::limit($book->pengarang, 30) }}</p>
                                @if($book->file_path)
                                    <a href="{{ route('student.library.read', $book->id) }}" class="btn btn-sm btn-primary w-100" onclick="event.stopPropagation();">
                                        <i class="fas fa-book-reader me-1"></i>Baca Online
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-books"></i>
                    <h5>Belum Ada Buku</h5>
                    <p>Katalog buku sedang dalam proses pengisian</p>
                </div>
            @endif
        </div>

        <!-- Kartu Digital Tab -->
        <div class="tab-pane fade" id="card" role="tabpanel">
            <div class="library-card-container" id="kartu-digital">
                <div class="digital-library-card">
                    <div class="card-header-lib">
                        <div class="card-logo">
                            <i class="fas fa-book-reader"></i>
                        </div>
                        <div class="card-title">
                            <h5 class="mb-0">KARTU PERPUSTAKAAN</h5>
                            <small>{{ $schoolProfile->nama_sekolah ?? 'Sekolah' }}</small>
                        </div>
                    </div>
                    
                    <div class="card-body-lib">
                        <div class="student-info-lib">
                            <table class="info-table">
                                <tr>
                                    <td class="label">Nama</td>
                                    <td class="colon">:</td>
                                    <td class="value">{{ $student->student_full_name }}</td>
                                </tr>
                                <tr>
                                    <td class="label">NIS</td>
                                    <td class="colon">:</td>
                                    <td class="value">{{ $student->student_nis }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Kelas</td>
                                    <td class="colon">:</td>
                                    <td class="value">{{ $student->class->class_name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="label">ID Perpustakaan</td>
                                    <td class="colon">:</td>
                                    <td class="value">LIB-{{ str_pad($student->student_id, 6, '0', STR_PAD_LEFT) }}</td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="card-stats">
                            <div class="stat-item-card">
                                <div class="stat-number">{{ $activeLoans }}</div>
                                <div class="stat-text">Aktif</div>
                            </div>
                            <div class="stat-item-card">
                                <div class="stat-number">{{ $totalBorrowed }}</div>
                                <div class="stat-text">Total</div>
                            </div>
                            <div class="stat-item-card">
                                <div class="stat-number">{{ $booksRead }}</div>
                                <div class="stat-text">Dibaca</div>
                            </div>
                        </div>
                        
                        <!-- Barcode Section -->
                        <div class="barcode-section">
                            <svg id="libraryBarcode"></svg>
                            <div class="barcode-text">LIB-{{ str_pad($student->student_id, 6, '0', STR_PAD_LEFT) }}</div>
                        </div>
                    </div>
                    
                    <div class="card-footer-lib">
                        <div class="validity">
                            <small>Berlaku hingga: {{ \Carbon\Carbon::now()->addYear()->format('d/m/Y') }}</small>
                        </div>
                    </div>
                </div>

                <!-- Download Button -->
                <div class="text-center mt-4">
                    <button class="btn btn-primary" onclick="downloadCard()">
                        <i class="fas fa-download me-2"></i>Download Kartu Digital
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
/* Welcome Banner */
.welcome-banner {
    background: linear-gradient(135deg, rgb(22, 54, 197), rgb(56, 161, 231));
    padding: 1.5rem;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(22, 54, 197, 0.2);
}

/* Stat Cards */
.stat-card {
    background: white;
    border-radius: 15px;
    padding: 1rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.stat-info {
    flex: 1;
}

.stat-label {
    font-size: 0.75rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: bold;
    color: #2c3e50;
}

/* Tabs */
.library-tabs {
    background: white;
    padding: 0.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.library-tabs .nav-link {
    border-radius: 8px;
    padding: 0.6rem 1rem;
    color: #6c757d;
    font-weight: 500;
    border: none;
    transition: all 0.3s ease;
}

.library-tabs .nav-link.active {
    background: linear-gradient(135deg, rgb(22, 54, 197), rgb(56, 161, 231));
    color: white;
}

@media (max-width: 576px) {
    .library-tabs .nav-link {
        font-size: 0.85rem;
        padding: 0.5rem 0.75rem;
    }
}

/* Loan Card */
.loan-card {
    background: white;
    border-radius: 12px;
    padding: 1rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    transition: transform 0.3s ease;
}

.loan-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.book-cover-small {
    width: 100%;
    aspect-ratio: 3/4;
    border-radius: 8px;
    overflow: hidden;
    background: #f8f9fa;
}

.book-cover-small img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.book-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    font-size: 2rem;
}

.loan-title {
    font-size: 1rem;
    font-weight: 600;
    color: #2c3e50;
}

.loan-author {
    font-size: 0.85rem;
    color: #6c757d;
}

/* Search Box */
.search-box {
    position: relative;
}

.search-box input {
    padding-left: 2.5rem;
    border-radius: 12px;
    border: 2px solid #e9ecef;
}

.search-box input:focus {
    border-color: rgb(56, 161, 231);
    box-shadow: 0 0 0 0.2rem rgba(56, 161, 231, 0.25);
}

.search-box i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
}

/* Category Filter */
.category-filter {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.category-btn {
    padding: 0.5rem 1rem;
    border: 2px solid #e9ecef;
    background: white;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.85rem;
}

.category-btn:hover, .category-btn.active {
    background: linear-gradient(135deg, rgb(22, 54, 197), rgb(56, 161, 231));
    color: white;
    border-color: transparent;
}

/* Book Card */
.book-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    transition: transform 0.3s ease;
}

.book-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.book-cover {
    position: relative;
    width: 100%;
    aspect-ratio: 3/4;
    overflow: hidden;
    background: #f8f9fa;
}

.book-cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.book-placeholder-large {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    font-size: 3rem;
}

.book-overlay {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
}

.book-info {
    padding: 1rem;
}

.book-category {
    font-size: 0.75rem;
    font-weight: 600;
}

.book-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.25rem;
    min-height: 2.5rem;
}

.book-author {
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 0.75rem;
    min-height: 1.5rem;
}

/* Digital Library Card */
.library-card-container {
    max-width: 500px;
    margin: 0 auto;
}

.digital-library-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 2rem;
    color: white;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}

.card-header-lib {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid rgba(255,255,255,0.2);
}

.card-logo {
    width: 50px;
    height: 50px;
    background: rgba(255,255,255,0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.card-body-lib {
    margin-bottom: 1.5rem;
}

.info-table {
    width: 100%;
    margin-bottom: 1.5rem;
}

.info-table td {
    padding: 0.4rem 0;
    font-size: 0.95rem;
}

.info-table .label {
    width: 40%;
    font-weight: 500;
}

.info-table .colon {
    width: 5%;
}

.info-table .value {
    font-weight: 600;
}

.card-stats {
    display: flex;
    gap: 1rem;
    justify-content: space-around;
    background: rgba(255,255,255,0.1);
    padding: 1rem;
    border-radius: 12px;
}

.stat-item-card {
    text-align: center;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: bold;
}

.stat-text {
    font-size: 0.75rem;
    opacity: 0.9;
}

.barcode-section {
    margin-top: 1.5rem;
    text-align: center;
    background: white;
    padding: 1rem;
    border-radius: 12px;
}

.barcode-section svg {
    max-width: 100%;
    height: auto;
}

.barcode-text {
    margin-top: 0.5rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: #2c3e50;
    letter-spacing: 1px;
}

.card-footer-lib {
    text-align: center;
    padding-top: 1rem;
    border-top: 2px solid rgba(255,255,255,0.2);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #6c757d;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.3;
}

.empty-state h5 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

@media (max-width: 576px) {
    .digital-library-card {
        padding: 1.5rem;
    }
    
    .info-table td {
        font-size: 0.85rem;
    }
    
    .stat-card {
        padding: 0.75rem;
    }
    
    .stat-icon {
        width: 40px;
        height: 40px;
        font-size: 1.2rem;
    }
}
</style>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
// Generate Barcode
document.addEventListener('DOMContentLoaded', function() {
    JsBarcode("#libraryBarcode", "LIB{{ str_pad($student->student_id, 6, '0', STR_PAD_LEFT) }}", {
        format: "CODE128",
        width: 2,
        height: 50,
        displayValue: false,
        background: "#ffffff",
        lineColor: "#000000",
        margin: 10
    });
});

// Search functionality
document.getElementById('searchBook').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const books = document.querySelectorAll('.book-item');
    
    books.forEach(book => {
        const title = book.dataset.title;
        const author = book.dataset.author;
        
        if (title.includes(searchTerm) || author.includes(searchTerm)) {
            book.style.display = 'block';
        } else {
            book.style.display = 'none';
        }
    });
});

// Category filter
document.querySelectorAll('.category-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        // Remove active class from all buttons
        document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
        
        // Add active class to clicked button
        this.classList.add('active');
        
        const category = this.dataset.category;
        const books = document.querySelectorAll('.book-item');
        
        books.forEach(book => {
            if (category === 'all' || book.dataset.category === category) {
                book.style.display = 'block';
            } else {
                book.style.display = 'none';
            }
        });
    });
});

// Download card function
function downloadCard() {
    const card = document.querySelector('.digital-library-card');
    
    html2canvas(card, {
        scale: 2,
        backgroundColor: null,
        logging: false,
        useCORS: true
    }).then(canvas => {
        const link = document.createElement('a');
        link.download = 'kartu-perpustakaan-{{ $student->student_nis }}.png';
        link.href = canvas.toDataURL();
        link.click();
    });
}
</script>
@endpush

@endsection

