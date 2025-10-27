@extends('layouts.student')

@section('title', 'Tabungan')

@section('content')
<div class="container-fluid">
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-3">
                    <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 35px; height: 35px;">
                        <i class="fas fa-arrow-up text-success"></i>
                    </div>
                                         <h6 class="mb-1 fw-bold small">Rp {{ number_format($totalSetoran, 0, ',', '.') }}</h6>
                     <small class="text-muted small">Total Setoran</small>
                     @if($totalSetoran == 0)
                         <small class="text-muted d-block mt-1">Belum ada setoran</small>
                     @endif
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-3">
                    <div class="bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 35px; height: 35px;">
                        <i class="fas fa-arrow-down text-danger"></i>
                    </div>
                                         <h6 class="mb-1 fw-bold small">Rp {{ number_format($totalPenarikan, 0, ',', '.') }}</h6>
                     <small class="text-muted small">Total Penarikan</small>
                     @if($totalPenarikan == 0)
                         <small class="text-muted d-block mt-1">Belum ada penarikan</small>
                     @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Setor Tabungan Button -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <h6 class="mb-2 fw-bold small">Setor Tabungan</h6>
                    <p class="text-muted mb-2 small">Tambah saldo tabungan Anda untuk keperluan mendatang</p>
                    <button class="btn btn-success btn-sm px-3" data-bs-toggle="modal" data-bs-target="#setorTabunganModal">
                        <i class="fas fa-plus me-1"></i>Setor Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold small">Riwayat Transaksi</h6>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-wallet text-primary me-2 small"></i>
                        <span class="text-muted small">Saldo: Rp {{ number_format($totalTabungan, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="card-body p-0">

                    
                    @if($logTabungan->count() > 0)
                        @foreach($logTabungan as $transaksi)
                        <div class="d-flex justify-content-between align-items-center p-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="d-flex align-items-center">
                                <div class="bg-{{ $transaksi->kredit > 0 ? 'success' : 'danger' }} bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px;">
                                    <i class="fas fa-{{ $transaksi->kredit > 0 ? 'arrow-up' : 'arrow-down' }} text-{{ $transaksi->kredit > 0 ? 'success' : 'danger' }} small"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold small">{{ $transaksi->kredit > 0 ? 'Setoran' : 'Penarikan' }}</h6>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($transaksi->log_tabungan_input_date)->format('d/m/Y H:i') }}</small>
                                    @if($transaksi->keterangan)
                                        <br><small class="text-muted">{{ $transaksi->keterangan }}</small>
                                    @endif
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-{{ $transaksi->kredit > 0 ? 'success' : 'danger' }} small">
                                    {{ $transaksi->kredit > 0 ? '+' : '-' }} Rp {{ number_format($transaksi->kredit > 0 ? $transaksi->kredit : $transaksi->debit, 0, ',', '.') }}
                                </div>
                                <small class="badge bg-{{ $transaksi->kredit > 0 ? 'success' : 'danger' }} bg-opacity-10 text-{{ $transaksi->kredit > 0 ? 'success' : 'danger' }}">
                                    {{ $transaksi->kredit > 0 ? 'Setoran' : 'Penarikan' }}
                                </small>
                            </div>
                        </div>
                        @endforeach
                    @else
                                                 <div class="text-center py-4">
                             <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 50px; height: 50px;">
                                 <i class="fas fa-university text-muted"></i>
                             </div>
                             <h6 class="text-muted small">Belum ada transaksi tabungan</h6>
                             <p class="text-muted mb-0 small">Mulai dengan melakukan setoran tabungan</p>
                         </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Setor Tabungan Modal -->
<div class="modal fade" id="setorTabunganModal" tabindex="-1" aria-labelledby="setorTabunganModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
            <!-- Header dengan gradient hijau -->
            <div class="modal-header border-0 text-center position-relative" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border-radius: 20px 20px 0 0;">
                <div class="w-100 text-center">
                    <div class="bg-white bg-opacity-20 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-piggy-bank text-white" style="font-size: 24px;"></i>
                    </div>
                    <h5 class="modal-title text-white fw-bold mb-0" id="setorTabunganModalLabel">
                        💰 Setor Tabungan
                    </h5>
                    <p class="text-white-50 mb-0 small">Tambah saldo untuk masa depan yang lebih baik</p>
                </div>
                <button type="button" class="btn-close btn-close-white position-absolute" data-bs-dismiss="modal" aria-label="Close" style="top: 15px; right: 15px;"></button>
            </div>
            
            <div class="modal-body p-4">
                <!-- Quick Amount Buttons -->
                <div class="mb-4">
                    <label class="form-label fw-bold text-dark mb-3">
                        <i class="fas fa-coins me-2 text-success"></i>Pilih Jumlah Setoran
                    </label>
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <button type="button" class="btn btn-outline-success w-100 quick-amount" data-amount="50000">
                                <small class="fw-bold">Rp 50K</small>
                            </button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-outline-success w-100 quick-amount" data-amount="100000">
                                <small class="fw-bold">Rp 100K</small>
                            </button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-outline-success w-100 quick-amount" data-amount="200000">
                                <small class="fw-bold">Rp 200K</small>
                            </button>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-4">
                            <button type="button" class="btn btn-outline-success w-100 quick-amount" data-amount="500000">
                                <small class="fw-bold">Rp 500K</small>
                            </button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-outline-success w-100 quick-amount" data-amount="1000000">
                                <small class="fw-bold">Rp 1M</small>
                            </button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-outline-secondary w-100" onclick="clearAmount()">
                                <small class="fw-bold">Custom</small>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Custom Amount Input -->
                <div class="mb-4">
                    <label for="setorAmount" class="form-label fw-bold text-dark">
                        <i class="fas fa-edit me-2 text-success"></i>Jumlah Setoran (Rp)
                    </label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-money-bill-wave text-success"></i>
                        </span>
                        <input type="number" class="form-control border-start-0" id="setorAmount" 
                               placeholder="Masukkan jumlah setoran" min="10000" step="1" required
                               style="font-size: 18px; font-weight: 600;">
                        <span class="input-group-text bg-light border-start-0">IDR</span>
                    </div>
                    <div class="form-text">
                        <i class="fas fa-info-circle me-1"></i>Minimal setoran Rp 10.000
                    </div>
                </div>

                <!-- Info Card -->
                <div class="alert alert-success border-0" style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-lightbulb text-success me-3" style="font-size: 20px;"></i>
                        <div>
                            <h6 class="mb-1 fw-bold text-success">💡 Tips Setoran</h6>
                            <small class="text-muted mb-0">Setor secara rutin untuk membangun kebiasaan menabung yang baik</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer border-0 p-4">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Batal
                </button>
                <button type="button" class="btn btn-success px-4 fw-bold" onclick="addSetoranToCart()" id="addToCartBtn">
                    <i class="fas fa-cart-plus me-2"></i>Tambah ke Keranjang
                </button>
            </div>
        </div>
    </div>
</div>

<style>
@media (max-width: 768px) {
    .container-fluid {
        padding: 15px;
    }
    
    .card {
        border-radius: 12px;
    }
    
    .badge {
        font-size: 0.75rem;
    }
    
    .card-body {
        padding: 1rem !important;
    }
    
    .modal-sm {
        max-width: 95%;
        margin: 1rem auto;
    }
}

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.bg-opacity-10 {
    background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
}

.bg-success.bg-opacity-10 {
    background-color: rgba(40, 167, 69, 0.1) !important;
}

.bg-danger.bg-opacity-10 {
    background-color: rgba(220, 53, 69, 0.1) !important;
}

/* Enhanced Modal Styling */
.modal-content {
    overflow: hidden;
}

.modal-header {
    padding: 2rem 2rem 1rem 2rem;
}

.modal-body {
    padding: 2rem;
}

.modal-footer {
    padding: 1rem 2rem 2rem 2rem;
}

/* Quick Amount Buttons */
.quick-amount {
    transition: all 0.3s ease;
    border-radius: 12px;
    font-weight: 600;
    padding: 0.75rem 0.5rem;
}

.quick-amount:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.quick-amount.btn-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
    color: white;
}

/* Input Styling */
.input-group-lg .form-control {
    border-radius: 12px;
    font-weight: 600;
}

.input-group-lg .input-group-text {
    border-radius: 12px;
    font-weight: 600;
}

/* Button Styling */
.btn {
    border-radius: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.btn-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
}

.btn-success:hover {
    background: linear-gradient(135deg, #218838 0%, #1ea085 100%);
}

.btn-outline-secondary {
    border: 2px solid #6c757d;
    color: #6c757d;
}

.btn-outline-secondary:hover {
    background: #6c757d;
    border-color: #6c757d;
    color: white;
}

/* Alert Styling */
.alert {
    border-radius: 12px;
    border: none;
}

/* Textarea Styling */
.form-control {
    border-radius: 12px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

/* Animation for modal */
.modal.fade .modal-dialog {
    transform: scale(0.8);
    transition: transform 0.3s ease;
}

.modal.show .modal-dialog {
    transform: scale(1);
}

/* Responsive adjustments */
@media (max-width: 576px) {
    .modal-dialog {
        margin: 1rem;
    }
    
    .modal-header {
        padding: 1.5rem 1.5rem 1rem 1.5rem;
    }
    
    .modal-body {
        padding: 1.5rem;
    }
    
    .modal-footer {
        padding: 1rem 1.5rem 1.5rem 1.5rem;
    }
    
    .quick-amount {
        padding: 0.5rem 0.25rem;
        font-size: 0.875rem;
    }
}

</style>

@push('scripts')
<script>
// Quick amount buttons functionality
document.addEventListener('DOMContentLoaded', function() {
    // Add click event to quick amount buttons
    document.querySelectorAll('.quick-amount').forEach(button => {
        button.addEventListener('click', function() {
            const amount = this.getAttribute('data-amount');
            document.getElementById('setorAmount').value = amount;
            
            // Update button states
            document.querySelectorAll('.quick-amount').forEach(btn => {
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-success');
            });
            this.classList.remove('btn-outline-success');
            this.classList.add('btn-success');
            
            // Update add to cart button
            updateAddToCartButton();
        });
    });
    
    // Add input event to custom amount
    document.getElementById('setorAmount').addEventListener('input', function() {
        updateAddToCartButton();
        
        // Clear quick amount selection if custom amount is entered
        if (this.value) {
            document.querySelectorAll('.quick-amount').forEach(btn => {
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-success');
            });
        }
    });
});

function clearAmount() {
    document.getElementById('setorAmount').value = '';
    document.querySelectorAll('.quick-amount').forEach(btn => {
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-success');
    });
    updateAddToCartButton();
}

function updateAddToCartButton() {
    const amount = parseInt(document.getElementById('setorAmount').value);
    const addToCartBtn = document.getElementById('addToCartBtn');
    
    if (amount && amount >= 10000) {
        addToCartBtn.disabled = false;
        addToCartBtn.innerHTML = `<i class="fas fa-cart-plus me-2"></i>Tambah ke Keranjang (Rp ${amount.toLocaleString('id-ID')})`;
        addToCartBtn.classList.remove('btn-secondary');
        addToCartBtn.classList.add('btn-success');
    } else {
        addToCartBtn.disabled = true;
        addToCartBtn.innerHTML = `<i class="fas fa-cart-plus me-2"></i>Tambah ke Keranjang`;
        addToCartBtn.classList.remove('btn-success');
        addToCartBtn.classList.add('btn-secondary');
    }
}

function addSetoranToCart() {
    const amount = parseInt(document.getElementById('setorAmount').value);
    
    if (!amount || amount < 10000) {
        showAlert('Minimal setoran Rp 10.000!', 'warning');
        return;
    }
    
    // Create cart item object
    const cartItem = {
        type: 'tabungan',
        id: 'setor_' + Date.now(),
        name: 'Setor Tabungan',
        amount: `Rp ${amount.toLocaleString('id-ID')}`,
        month: 'Tabungan',
        keterangan: 'Setor Tabungan',
        setorAmount: amount
    };
    
    // Get existing cart from localStorage
    let cart = JSON.parse(localStorage.getItem('studentCart') || '[]');
    
    // Check if item already exists in cart
    const existingItem = cart.find(item => item.type === 'tabungan');
    if (existingItem) {
        showAlert('Setoran tabungan sudah ada di keranjang!', 'warning');
        return;
    }
    
    // Add item to cart
    cart.push(cartItem);
    localStorage.setItem('studentCart', JSON.stringify(cart));
    
    // Update cart badge
    updateCartBadge();
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('setorTabunganModal'));
    modal.hide();
    
    // Show success message
    showAlert('Setoran tabungan berhasil ditambahkan ke keranjang!', 'success');
    
    // Reset form
    document.getElementById('setorAmount').value = '';
    clearAmount();
}

function showAlert(message, type) {
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); border-radius: 8px;';
    alertDiv.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
            <span>${message}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 3000);
}
</script>
@endpush
@endsection 