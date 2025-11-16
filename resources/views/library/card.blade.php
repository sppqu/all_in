@extends('layouts.adminty')

@section('title', 'Kartu Perpustakaan Digital - E-Perpustakaan')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="mb-4">
        <a href="{{ route('library.my-loans') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <!-- Digital Library Card -->
            <div class="card border-0 shadow-lg overflow-hidden mb-4" id="library-card">
                <div class="position-relative" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px;">
                    <!-- Card Background Pattern -->
                    <div class="position-absolute top-0 start-0 w-100 h-100" style="opacity: 0.1; background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGcgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj48ZyBmaWxsPSIjZmZmIj48cGF0aCBkPSJNMzYgMzRjMCAxLjEwNS0uODk1IDItMiAycy0yLS44OTUtMi0yIC44OTUtMiAyLTIgMiAuODk1IDIgMm0wLTEwYzAtMS4xMDUtLjg5NS0yLTItMnMtMiAuODk1LTIgMiAuODk1IDIgMiAyIDItLjg5NSAyLTIiLz48L2c+PC9nPjwvc3ZnPg==');"></div>
                    
                    <div class="row align-items-center position-relative">
                        <div class="col-md-3 text-center text-md-start mb-3 mb-md-0">
                            @if($student && $student->student_photo)
                            <img src="{{ asset('storage/' . $student->student_photo) }}" 
                                 class="rounded-circle border border-4 border-white shadow"
                                 style="width: 120px; height: 120px; object-fit: cover;">
                            @else
                            <div class="rounded-circle border border-4 border-white shadow d-inline-flex align-items-center justify-content-center bg-white"
                                 style="width: 120px; height: 120px;">
                                <i class="fas fa-user fa-3x" style="color: #667eea;"></i>
                            </div>
                            @endif
                        </div>
                        
                        <div class="col-md-9">
                            <div class="text-white">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-book-reader fa-2x me-3"></i>
                                    <div>
                                        <h5 class="mb-0 fw-bold">KARTU PERPUSTAKAAN DIGITAL</h5>
                                        <small class="opacity-75">E-Perpustakaan SPPQU</small>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <h3 class="mb-1 fw-bold">{{ $user->name }}</h3>
                                    @if($student)
                                    <p class="mb-1"><strong>NIS:</strong> {{ $student->student_nis }}</p>
                                    <p class="mb-1"><strong>Kelas:</strong> {{ $student->class->class_name ?? '-' }}</p>
                                    @endif
                                    <p class="mb-0"><strong>Email:</strong> {{ $user->email }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- QR Code Section -->
                    <div class="position-absolute bottom-0 end-0 p-3 bg-white m-3 rounded shadow" style="width: 100px; height: 100px;">
                        <div id="qrcode" class="w-100 h-100"></div>
                    </div>
                    
                    <!-- Member Since -->
                    <div class="position-absolute bottom-0 start-0 p-3 text-white">
                        <small class="opacity-75">Member sejak</small><br>
                        <strong>{{ \Carbon\Carbon::parse($user->created_at)->format('M Y') }}</strong>
                    </div>
                </div>

                <!-- Card Stats Bar -->
                <div class="card-footer bg-white border-0">
                    <div class="row text-center py-2">
                        <div class="col-4">
                            <h4 class="mb-0 text-primary">{{ $totalBorrowed }}</h4>
                            <small class="text-muted">Total Pinjam</small>
                        </div>
                        <div class="col-4 border-start border-end">
                            <h4 class="mb-0 text-success">{{ $activeBorrowed }}</h4>
                            <small class="text-muted">Aktif</small>
                        </div>
                        <div class="col-4">
                            <h4 class="mb-0 text-info">{{ $booksRead }}</h4>
                            <small class="text-muted">Buku Dibaca</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-grid gap-2 mb-4">
                <button onclick="downloadCard()" class="btn btn-primary btn-lg">
                    <i class="fas fa-download me-2"></i>Download Kartu
                </button>
                <button onclick="shareCard()" class="btn btn-outline-secondary">
                    <i class="fas fa-share-alt me-2"></i>Bagikan Kartu
                </button>
            </div>

            <!-- Benefits Info -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3"><i class="fas fa-star text-warning me-2"></i>Keuntungan Member</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check-circle text-success fa-lg me-2"></i>
                                </div>
                                <div>
                                    <strong>Akses Unlimited</strong>
                                    <p class="text-muted small mb-0">Baca ribuan buku digital kapan saja</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check-circle text-success fa-lg me-2"></i>
                                </div>
                                <div>
                                    <strong>Pinjam Gratis</strong>
                                    <p class="text-muted small mb-0">Maksimal 3 buku sekaligus</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check-circle text-success fa-lg me-2"></i>
                                </div>
                                <div>
                                    <strong>Download Offline</strong>
                                    <p class="text-muted small mb-0">Download untuk baca tanpa internet</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check-circle text-success fa-lg me-2"></i>
                                </div>
                                <div>
                                    <strong>Riwayat Lengkap</strong>
                                    <p class="text-muted small mb-0">Tracking semua aktivitas membaca</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR Code Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
// Generate QR Code
document.addEventListener('DOMContentLoaded', function() {
    new QRCode(document.getElementById("qrcode"), {
        text: "LIBRARY_MEMBER_{{ $user->id }}_{{ $user->email }}",
        width: 80,
        height: 80,
        colorDark : "#667eea",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.H
    });
});

// Download Card as Image
function downloadCard() {
    // Using html2canvas library
    if (typeof html2canvas === 'undefined') {
        // Load html2canvas dynamically
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
        script.onload = function() {
            captureCard();
        };
        document.head.appendChild(script);
    } else {
        captureCard();
    }
}

function captureCard() {
    const card = document.getElementById('library-card');
    html2canvas(card, {
        scale: 2,
        backgroundColor: '#ffffff'
    }).then(canvas => {
        // Convert to image and download
        const link = document.createElement('a');
        link.download = 'Kartu-Perpustakaan-{{ str_replace(" ", "-", $user->name) }}.png';
        link.href = canvas.toDataURL();
        link.click();
    });
}

// Share Card
function shareCard() {
    if (navigator.share) {
        navigator.share({
            title: 'Kartu Perpustakaan Digital',
            text: 'Lihat kartu perpustakaan digital saya!',
            url: window.location.href
        }).catch(err => console.log('Error sharing:', err));
    } else {
        // Fallback: copy link
        navigator.clipboard.writeText(window.location.href);
        alert('Link kartu berhasil disalin!');
    }
}
</script>

<style>
#library-card {
    transition: transform 0.3s ease;
}

#library-card:hover {
    transform: scale(1.02);
}
</style>
@endsection

