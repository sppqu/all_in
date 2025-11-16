@extends('layouts.adminty')

@section('title', 'Riwayat Pembayaran Online')

<style>
/* Action Button Icon Colors - Ensure white icons */
.btn-outline-primary .fas,
.btn-outline-primary .fa {
    color: inherit !important;
}

.btn-outline-primary:hover .fas,
.btn-outline-primary:hover .fa {
    color: white !important;
}

.btn-outline-success .fas,
.btn-outline-success .fa {
    color: inherit !important;
}

.btn-outline-success:hover .fas,
.btn-outline-success:hover .fa {
    color: white !important;
}

.btn-success .fas,
.btn-success .fa {
    color: white !important;
}

.btn-danger .fas,
.btn-danger .fa {
    color: white !important;
}
</style>

@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="{{ asset('css/simple-toast.css') }}">
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Riwayat Pembayaran Online</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('online-payment.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-filter"></i> Filter Data</h6>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('online-payment.history') }}" id="filterForm" class="row g-3">
                                <div class="col-md-3">
                                    <label for="search" class="form-label">Cari NIS/Nama</label>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           value="{{ request('search') }}" placeholder="Masukkan NIS atau nama...">
                                </div>
                                <div class="col-md-2">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-control select-primary" id="status" name="status">
                                        <option value="">Semua Status</option>
                                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Berhasil</option>
                                        <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Ditolak</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="per_page" class="form-label">Data per Halaman</label>
                                    <select class="form-control select-primary" id="per_page" name="per_page">
                                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                        <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                                        <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100</option>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search me-2"></i>Cari
                                        </button>
                                        <a href="{{ route('online-payment.history') }}" class="btn btn-secondary">
                                            <i class="fas fa-undo me-2"></i>Reset
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Results Info -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="text-muted">
                            Menampilkan {{ $transfers->firstItem() ?? 0 }} sampai {{ $transfers->lastItem() ?? 0 }} dari {{ $transfers->total() }} data
                        </div>
                        <div class="text-muted">
                            {{ $transfers->count() }} data per halaman
                        </div>
                    </div>
                    
                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th width="3%" class="text-left">No</th>
                                    <th width="12%" class="text-left">NIS</th>
                                    <th width="20%" class="text-left">Nama Lengkap</th>
                                    <th width="15%" class="text-left">Kelas</th>
                                    <th width="15%" class="text-left">Reference</th>
                                    <th width="10%" class="text-center">Status</th>
                                    <th width="15%" class="text-left">Tanggal</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transfers as $index => $transfer)
                                    <tr>
                                        <td>{{ $transfers->firstItem() + $loop->index }}</td>
                                        <td>
                                            {{ $transfer->student_nis }}
                                        </td>
                                        <td>
                                            {{ $transfer->student_full_name }}
                                        </td>
                                        <td>{{ $transfer->class_name ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ $transfer->reference }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($transfer->status == 1)
                                                <span class="badge bg-success">Berhasil</span>
                                            @elseif($transfer->status == 0)
                                                <span class="badge bg-warning">Menunggu</span>
                                            @else
                                                <span class="badge bg-danger">Ditolak</span>
                                            @endif
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($transfer->created_at)->format('d/m/Y H:i') }}</td>
                                        <td class="text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                @if($transfer->status == 1)
                                                    <!-- Cetak Receipt - untuk pembayaran yang sudah disetujui -->
                                                    <button type="button" class="btn btn-sm btn-outline-success print-receipt-btn" 
                                                            data-payment-id="{{ $transfer->transfer_id }}"
                                                            title="Cetak Receipt">
                                                        <i class="fas fa-print"></i>
                                                    </button>
                                                @else
                                                    <!-- Lihat Detail - untuk pembayaran yang belum disetujui -->
                                                    <button type="button" class="btn btn-sm btn-outline-primary view-detail-btn" 
                                                            data-payment-id="{{ $transfer->transfer_id }}"
                                                            title="Lihat Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Tidak ada riwayat pembayaran</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        @if($transfers->hasPages())
                                <nav aria-label="Page navigation">
                                    <ul class="pagination pagination-sm mb-0">
                                        {{-- Previous Page Link --}}
                                        @if ($transfers->onFirstPage())
                                            <li class="page-item disabled">
                                                <span class="page-link">‹</span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $transfers->previousPageUrl() }}" rel="prev">‹</a>
                                            </li>
                                        @endif

                                        {{-- Pagination Elements --}}
                                    @php
                                        $currentPage = $transfers->currentPage();
                                        $lastPage = $transfers->lastPage();
                                        
                                        // Hitung range halaman yang akan ditampilkan (maksimal 10 nomor)
                                        $startPage = max(1, $currentPage - 4);
                                        $endPage = min($lastPage, $startPage + 9);
                                        
                                        // Jika endPage terlalu dekat dengan lastPage, sesuaikan startPage
                                        if ($endPage - $startPage < 9 && $startPage > 1) {
                                            $startPage = max(1, $endPage - 9);
                                        }
                                    @endphp
                                    
                                    {{-- Tampilkan halaman pertama jika tidak termasuk dalam range --}}
                                    @if($startPage > 1)
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $transfers->url(1) }}">1</a>
                                        </li>
                                        @if($startPage > 2)
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        @endif
                                    @endif
                                    
                                    {{-- Tampilkan range halaman utama --}}
                                    @for($page = $startPage; $page <= $endPage; $page++)
                                        @if ($page == $currentPage)
                                                <li class="page-item active">
                                                    <span class="page-link">{{ $page }}</span>
                                                </li>
                                            @else
                                                <li class="page-item">
                                                <a class="page-link" href="{{ $transfers->url($page) }}">{{ $page }}</a>
                                                </li>
                                            @endif
                                    @endfor
                                    
                                    {{-- Tampilkan halaman terakhir jika tidak termasuk dalam range --}}
                                    @if($endPage < $lastPage)
                                        @if($endPage < $lastPage - 1)
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        @endif
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $transfers->url($lastPage) }}">{{ $lastPage }}</a>
                                        </li>
                                    @endif

                                        {{-- Next Page Link --}}
                                        @if ($transfers->hasMorePages())
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $transfers->nextPageUrl() }}" rel="next">›</a>
                                            </li>
                                        @else
                                            <li class="page-item disabled">
                                                <span class="page-link">›</span>
                                            </li>
                                        @endif
                                    </ul>
                                </nav>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Detail Pembayaran</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detailContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Proof Image Modal -->
<div class="modal fade" id="proofImageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Bukti Transfer</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="proofImage" src="" alt="Bukti Transfer" class="img-fluid" style="max-height: 500px;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Simple and clean payment detail function
window.viewPaymentDetail = function(paymentId) {
    console.log('viewPaymentDetail called with:', paymentId);
    
    try {
        // Show loading in modal
        document.getElementById('detailContent').innerHTML = `
            <div class="text-center">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p class="mt-2">Memuat detail...</p>
            </div>
        `;
        
        // Show modal using Bootstrap 5
        if (typeof bootstrap !== 'undefined' && typeof bootstrap.Modal !== 'undefined') {
            const modal = new bootstrap.Modal(document.getElementById('detailModal'));
            modal.show();
        } else {
            console.error('Bootstrap Modal not available');
            alert('Modal tidak tersedia. Silakan refresh halaman.');
            return;
        }
        
        // Load payment detail via AJAX
        fetch(`{{ url('test-detail') }}/${paymentId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(html => {
                document.getElementById('detailContent').innerHTML = html;
                // Setup verification buttons after content is loaded
                if (typeof setupVerificationButtons === 'function') {
                    setupVerificationButtons();
                }
            })
            .catch(error => {
                console.error('Error loading payment detail:', error);
                document.getElementById('detailContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Gagal memuat detail pembayaran. Silakan coba lagi.
                    </div>
                `;
            });
    } catch (error) {
        console.error('Error in viewPaymentDetail:', error);
        alert('Terjadi kesalahan saat membuka detail pembayaran.');
    }
};

// Function to view proof image
window.viewProofImage = function(imageUrl, transferId) {
    console.log('viewProofImage called with:', imageUrl, transferId);
    
    // Set image source
    document.getElementById('proofImage').src = imageUrl;
    
    // Show proof image modal
    const modal = new bootstrap.Modal(document.getElementById('proofImageModal'));
    modal.show();
};

// Function to download receipt
window.downloadReceipt = function(paymentId) {
    console.log('downloadReceipt called with:', paymentId);
    
    // Open receipt in new tab
    const receiptUrl = `{{ url('online-payment/receipt') }}/${paymentId}`;
    window.open(receiptUrl, '_blank');
};

// Function to print receipt directly
window.printReceipt = function(paymentId) {
    console.log('printReceipt called with:', paymentId);
    
    // Open receipt in new tab and trigger print
    const receiptUrl = `{{ url('online-payment/receipt') }}/${paymentId}`;
    const printWindow = window.open(receiptUrl, '_blank');
    
    // Wait for the page to load, then trigger print
    if (printWindow) {
        printWindow.onload = function() {
            setTimeout(function() {
                printWindow.print();
            }, 500);
        };
    }
};

// Function to verify payment
window.verifyPayment = function(paymentId, status) {
    console.log('verifyPayment called with:', paymentId, status);
    
    const action = status === 'verified' ? 'memverifikasi' : 'menolak';
    const button = event.target.closest('button');
    
    if (!button) {
        console.error('Button not found');
        return;
    }
    
    // Show loading state
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
    
    // Submit verification via AJAX
    fetch(`{{ url('online-payment/verify') }}/${paymentId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            verification_status: status,
            verification_notes: ''
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Close the detail modal
            const detailModal = bootstrap.Modal.getInstance(document.getElementById('detailModal'));
            if (detailModal) {
                detailModal.hide();
            }
            
            // Jika verified dan ada redirect URL, redirect ke halaman cetak kuitansi
            if (status === 'verified' && data.redirect) {
                // Redirect ke halaman cetak kuitansi
                window.location.href = data.redirect;
            } else {
                // Show success message
                alert(`Pembayaran berhasil ${status === 'verified' ? 'diverifikasi' : 'ditolak'}`);
                
                // Refresh the page to show updated status
                setTimeout(() => {
                    location.reload();
                }, 1500);
            }
        } else {
            alert('Gagal memproses verifikasi: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat memproses verifikasi');
    })
    .finally(() => {
        // Reset button state
        button.disabled = false;
        button.innerHTML = originalText;
    });
};



// Simple page initialization
document.addEventListener('DOMContentLoaded', function() {
    console.log('History page loaded');
    
    // Auto-submit form when per_page changes
    const perPageSelect = document.getElementById('per_page');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    }
    
    // Auto-submit form when Enter is pressed in search field
    const searchInput = document.getElementById('search');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('filterForm').submit();
            }
        });
    }
    
    // Setup view detail buttons
    setupViewDetailButtons();
    
    // Setup print receipt buttons
    setupPrintReceiptButtons();
});

// Function to setup view detail buttons
function setupViewDetailButtons() {
    console.log('Setting up view detail buttons');
    
    // Add click event to all view detail buttons
    const detailButtons = document.querySelectorAll('.view-detail-btn');
    detailButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const paymentId = this.getAttribute('data-payment-id');
            console.log('View detail button clicked for payment:', paymentId);
            
            // Call viewPaymentDetail function
            if (typeof window.viewPaymentDetail === 'function') {
                window.viewPaymentDetail(paymentId);
            } else {
                console.error('viewPaymentDetail function not available');
                alert('Fungsi detail pembayaran tidak tersedia. Silakan refresh halaman.');
            }
        });
    });
}

// Function to setup print receipt buttons
function setupPrintReceiptButtons() {
    console.log('Setting up print receipt buttons');
    
    // Add click event to all print receipt buttons
    const printButtons = document.querySelectorAll('.print-receipt-btn');
    printButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const paymentId = this.getAttribute('data-payment-id');
            console.log('Print receipt button clicked for payment:', paymentId);
            
            // Call printReceipt function
            if (typeof window.printReceipt === 'function') {
                window.printReceipt(paymentId);
            } else {
                console.error('printReceipt function not available');
                // Fallback: open receipt in new tab
                const receiptUrl = `{{ url('online-payment/receipt') }}/${paymentId}`;
                window.open(receiptUrl, '_blank');
            }
        });
    });
}

// Function to setup verification buttons
function setupVerificationButtons() {
    console.log('Setting up verification buttons');
    
    // Add click event to all verification buttons
    const approveButtons = document.querySelectorAll('[data-action="approve"]');
    const rejectButtons = document.querySelectorAll('[data-action="reject"]');
    
    approveButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const paymentId = this.getAttribute('data-payment-id');
            console.log('Approve button clicked for payment:', paymentId);
            
            if (confirm('Apakah Anda yakin ingin memverifikasi pembayaran ini?')) {
                verifyPayment(paymentId, 'verified');
            }
        });
    });
    
    rejectButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const paymentId = this.getAttribute('data-payment-id');
            console.log('Reject button clicked for payment:', paymentId);
            
            if (confirm('Apakah Anda yakin ingin menolak pembayaran ini?')) {
                verifyPayment(paymentId, 'rejected');
            }
        });
    });
}
</script>

<!-- Simple Toast System -->
<script>
// Simple toast notification system
// showToast function is now global from adminty.blade.php layout
// Global toast functions
window.showVerificationToast = window.showToast;
window.fallbackToast = window.showToast;

// Debug: Log all available functions
console.log('Available functions on window:');
console.log('- viewPaymentDetail:', typeof window.viewPaymentDetail);
console.log('- Bootstrap Modal:', typeof bootstrap);
</script>
@endsection
