<?php $__env->startSection('title', 'Riwayat Pembayaran Online'); ?>

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

<?php $__env->startSection('head'); ?>
<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="<?php echo e(asset('css/simple-toast.css')); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Riwayat Pembayaran Online</h4>
                    <div class="d-flex gap-2">
                        <a href="<?php echo e(route('online-payment.index')); ?>" class="btn btn-secondary">
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
                            <form method="GET" action="<?php echo e(route('online-payment.history')); ?>" id="filterForm" class="row g-3">
                                <div class="col-md-3">
                                    <label for="search" class="form-label">Cari NIS/Nama</label>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           value="<?php echo e(request('search')); ?>" placeholder="Masukkan NIS atau nama...">
                                </div>
                                <div class="col-md-2">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="">Semua Status</option>
                                        <option value="0" <?php echo e(request('status') == '0' ? 'selected' : ''); ?>>Menunggu Verifikasi</option>
                                        <option value="1" <?php echo e(request('status') == '1' ? 'selected' : ''); ?>>Berhasil</option>
                                        <option value="2" <?php echo e(request('status') == '2' ? 'selected' : ''); ?>>Ditolak</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="per_page" class="form-label">Data per Halaman</label>
                                    <select class="form-select" id="per_page" name="per_page">
                                        <option value="10" <?php echo e(request('per_page', 10) == 10 ? 'selected' : ''); ?>>10</option>
                                        <option value="25" <?php echo e(request('per_page', 10) == 25 ? 'selected' : ''); ?>>25</option>
                                        <option value="50" <?php echo e(request('per_page', 10) == 50 ? 'selected' : ''); ?>>50</option>
                                        <option value="100" <?php echo e(request('per_page', 10) == 100 ? 'selected' : ''); ?>>100</option>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search me-2"></i>Cari
                                        </button>
                                        <a href="<?php echo e(route('online-payment.history')); ?>" class="btn btn-secondary">
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
                            Menampilkan <?php echo e($transfers->firstItem() ?? 0); ?> sampai <?php echo e($transfers->lastItem() ?? 0); ?> dari <?php echo e($transfers->total()); ?> data
                        </div>
                        <div class="text-muted">
                            <?php echo e($transfers->count()); ?> data per halaman
                        </div>
                    </div>
                    
                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th width="3%">No</th>
                                    <th width="12%">NIS</th>
                                    <th width="20%">Nama Lengkap</th>
                                    <th width="15%">Kelas</th>
                                    <th width="15%">Reference</th>
                                    <th width="10%">Status</th>
                                    <th width="15%">Tanggal</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $transfers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $transfer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($transfers->firstItem() + $loop->index); ?></td>
                                        <td>
                                            <?php echo e($transfer->student_nis); ?>

                                        </td>
                                        <td>
                                            <?php echo e($transfer->student_full_name); ?>

                                        </td>
                                        <td><?php echo e($transfer->class_name ?? '-'); ?></td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo e($transfer->reference); ?>

                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php if($transfer->status == 1): ?>
                                                <span class="badge bg-success">Berhasil</span>
                                            <?php elseif($transfer->status == 0): ?>
                                                <span class="badge bg-warning">Menunggu</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Ditolak</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e(\Carbon\Carbon::parse($transfer->created_at)->format('d/m/Y H:i')); ?></td>
                                        <td class="text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                <?php if($transfer->status == 1): ?>
                                                    <!-- Cetak Receipt - untuk pembayaran yang sudah disetujui -->
                                                    <button type="button" class="btn btn-sm btn-outline-success print-receipt-btn" 
                                                            data-payment-id="<?php echo e($transfer->transfer_id); ?>"
                                                            title="Cetak Receipt">
                                                        <i class="fas fa-print"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <!-- Lihat Detail - untuk pembayaran yang belum disetujui -->
                                                    <button type="button" class="btn btn-sm btn-outline-primary view-detail-btn" 
                                                            data-payment-id="<?php echo e($transfer->transfer_id); ?>"
                                                            title="Lihat Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Tidak ada riwayat pembayaran</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        <?php if($transfers->hasPages()): ?>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination pagination-sm mb-0">
                                        
                                        <?php if($transfers->onFirstPage()): ?>
                                            <li class="page-item disabled">
                                                <span class="page-link">‹</span>
                                            </li>
                                        <?php else: ?>
                                            <li class="page-item">
                                                <a class="page-link" href="<?php echo e($transfers->previousPageUrl()); ?>" rel="prev">‹</a>
                                            </li>
                                        <?php endif; ?>

                                        
                                    <?php
                                        $currentPage = $transfers->currentPage();
                                        $lastPage = $transfers->lastPage();
                                        
                                        // Hitung range halaman yang akan ditampilkan (maksimal 10 nomor)
                                        $startPage = max(1, $currentPage - 4);
                                        $endPage = min($lastPage, $startPage + 9);
                                        
                                        // Jika endPage terlalu dekat dengan lastPage, sesuaikan startPage
                                        if ($endPage - $startPage < 9 && $startPage > 1) {
                                            $startPage = max(1, $endPage - 9);
                                        }
                                    ?>
                                    
                                    
                                    <?php if($startPage > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?php echo e($transfers->url(1)); ?>">1</a>
                                        </li>
                                        <?php if($startPage > 2): ?>
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    
                                    <?php for($page = $startPage; $page <= $endPage; $page++): ?>
                                        <?php if($page == $currentPage): ?>
                                                <li class="page-item active">
                                                    <span class="page-link"><?php echo e($page); ?></span>
                                                </li>
                                            <?php else: ?>
                                                <li class="page-item">
                                                <a class="page-link" href="<?php echo e($transfers->url($page)); ?>"><?php echo e($page); ?></a>
                                                </li>
                                            <?php endif; ?>
                                    <?php endfor; ?>
                                    
                                    
                                    <?php if($endPage < $lastPage): ?>
                                        <?php if($endPage < $lastPage - 1): ?>
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        <?php endif; ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?php echo e($transfers->url($lastPage)); ?>"><?php echo e($lastPage); ?></a>
                                        </li>
                                    <?php endif; ?>

                                        
                                        <?php if($transfers->hasMorePages()): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="<?php echo e($transfers->nextPageUrl()); ?>" rel="next">›</a>
                                            </li>
                                        <?php else: ?>
                                            <li class="page-item disabled">
                                                <span class="page-link">›</span>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                        <?php endif; ?>
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
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
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
        fetch(`<?php echo e(url('test-detail')); ?>/${paymentId}`)
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
    const receiptUrl = `<?php echo e(url('online-payment/receipt')); ?>/${paymentId}`;
    window.open(receiptUrl, '_blank');
};

// Function to print receipt directly
window.printReceipt = function(paymentId) {
    console.log('printReceipt called with:', paymentId);
    
    // Open receipt in new tab and trigger print
    const receiptUrl = `<?php echo e(url('online-payment/receipt')); ?>/${paymentId}`;
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
    fetch(`<?php echo e(url('online-payment/verify')); ?>/${paymentId}`, {
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
                const receiptUrl = `<?php echo e(url('online-payment/receipt')); ?>/${paymentId}`;
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
function showToast(type, title, message) {
    alert(`${title}: ${message}`);
}

// Global toast functions
window.showVerificationToast = showToast;
window.fallbackToast = showToast;

// Debug: Log all available functions
console.log('Available functions on window:');
console.log('- viewPaymentDetail:', typeof window.viewPaymentDetail);
console.log('- Bootstrap Modal:', typeof bootstrap);
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.coreui', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sppqu\sppqu_addon\resources\views/online-payment/history.blade.php ENDPATH**/ ?>