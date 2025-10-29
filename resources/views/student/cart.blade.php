@extends('layouts.student')

@section('title', 'Keranjang Pembayaran')

@section('content')
<style>
    .bill-item {
        background: white;
        border-radius: 10px;
        border: 1px solid #e9ecef;
        margin-bottom: 12px;
        overflow: hidden;
    }
    
    .bill-item .bill-name {
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 3px;
    }
    
    .bill-item .bill-amount {
        font-size: 0.8rem;
        font-weight: 600;
        color: #198754;
        margin-bottom: 3px;
    }
    
    .bill-item label {
        font-size: 0.75rem;
        color: #6c757d;
    }
    
    .bill-item .payment-amount {
        font-size: 0.8rem;
        font-weight: 600;
        color: #198754;
    }
    
    .bill-header {
        background: #f8f9fa;
        padding: 12px 16px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .bill-content {
        padding: 16px;
    }
    
    .amount-input {
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 8px 12px;
        width: 100%;
        max-width: 200px;
    }
    
    .amount-input:focus {
        border-color: #198754;
        box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
        outline: none;
    }
    
    .payment-summary {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: white;
        border-top: 1px solid #e9ecef;
        padding: 10px 20px;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        z-index: 1000;
    }
    
    .payment-summary .total-label {
        font-size: 0.75rem;
        color: #6c757d;
    }
    
    .payment-summary .total-items {
        font-size: 0.7rem;
        color: #adb5bd;
    }
    
    .payment-summary .total-amount {
        font-size: 0.85rem;
        font-weight: 600;
        color: #198754;
    }
    
    .payment-method {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.85rem;
    }
    
    .payment-method:hover {
        border-color: #198754;
        background-color: #f8f9fa;
    }
    
    .payment-method.selected {
        border-color: #198754;
        background-color: #e8f5e8;
    }
    
    .payment-method input[type="radio"] {
        margin-right: 10px;
    }
    
    .empty-cart {
        text-align: center;
        padding: 30px 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin: 15px 0;
    }
    
    .empty-cart i {
        font-size: 2.5rem;
        color: #dee2e6;
        margin-bottom: 10px;
        opacity: 0.6;
    }
    
    .empty-cart h6 {
        color: #6c757d;
        font-weight: 600;
        margin-bottom: 8px;
    }
    
    .empty-cart p {
        color: #adb5bd;
        font-size: 0.8rem;
        margin-bottom: 15px;
    }
    
    .empty-cart .btn {
        border-radius: 25px;
        padding: 8px 20px;
        font-weight: 500;
        font-size: 0.8rem;
        box-shadow: 0 2px 8px rgba(25, 135, 84, 0.2);
    }
    
    /* Add bottom padding to prevent content from being hidden by fixed summary */
    .container-fluid {
        padding-bottom: 15px;
    }
    
    /* Hide bottom navigation on cart page */
    .bottom-nav {
        display: none !important;
    }
    
    .btn-ubah {
        background-color: #e8f5e8;
        border-color: #198754;
        color: #198754;
        border-radius: 8px;
        padding: 5px 10px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .btn-ubah:hover {
        background-color: #198754;
        border-color: #198754;
        color: white;
    }
    
    .btn-hapus {
        background-color: #dc3545;
        border-color: #dc3545;
        color: white;
        border-radius: 6px;
        padding: 3px 8px;
        font-size: 0.7rem;
        font-weight: 500;
        font-weight: 500;
    }
    
    .btn-hapus:hover {
        background-color: #c82333;
        border-color: #c82333;
        color: white;
    }
    
    .total-section {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    
    .total-label {
        color: #6c757d;
        font-size: 0.8rem;
    }
    
    .total-items {
        color: #6f42c1;
        font-weight: 600;
        font-size: 0.8rem;
    }
    
    .total-amount {
        font-weight: 700;
        font-size: 1.1rem;
        color: #212529;
    }
    
    /* Enhanced Modal Styling for Bank Transfer */
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
    }
</style>

<!-- Cart Header -->
<div class="bg-white shadow-sm py-2 sticky-top" style="z-index: 1020; margin: -15px -15px 15px -15px;">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <a href="{{ route('student.dashboard') }}" class="text-decoration-none me-3">
                    <i class="fas fa-arrow-left text-dark" style="font-size: 18px;"></i>
                </a>
                <div>
                    <h6 class="mb-0 fw-bold">Keranjang Anda</h6>
                    <p class="text-muted mb-0" style="font-size: 0.8rem;">Pilih tagihan yang ingin dibayar</p>
                </div>
            </div>
            <div>
                <a href="{{ route('student.bills') }}" class="btn btn-outline-success btn-sm" style="font-size: 0.8rem;">
                    <i class="fas fa-plus me-1"></i>Tambah Tagihan
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">

    <!-- Cart Items -->
    <div id="cartItems">
        <!-- Items will be loaded here via JavaScript -->
    </div>

    <!-- Empty Cart State -->
    <div id="emptyCart" class="empty-cart" style="display: none;">
        <i class="fas fa-shopping-basket"></i>
        <h6>Keranjang Kosong</h6>
        <p style="font-size: 0.9rem;">Belum ada tagihan yang dipilih untuk dibayar</p>
    </div>

    <!-- Payment Method Modal -->
    <div class="modal fade" id="paymentMethodModal" tabindex="-1" aria-labelledby="paymentMethodModalLabel">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 15px; border: none;">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold" id="paymentMethodModalLabel">
                        <i class="fas fa-credit-card me-2"></i>Pilih Metode Pembayaran
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="payment-method" onclick="selectPaymentMethod('ipaymu')">
                        <input type="radio" name="paymentMethod" value="ipaymu" id="ipaymu">
                        <label for="ipaymu" class="mb-0" style="font-size: 0.85rem;">
                            <i class="fas fa-credit-card me-2 text-success"></i>
                            Transfer Otomatis
                        </label>
                    </div>
                    
                    <div class="payment-method selected" onclick="selectPaymentMethod('transfer')">
                        <input type="radio" name="paymentMethod" value="transfer" id="transfer" checked>
                        <label for="transfer" class="mb-0" style="font-size: 0.85rem;">
                            <i class="fas fa-university me-2 text-primary"></i>
                            Transfer Bank Manual
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" style="font-size: 0.8rem;">Batal</button>
                    <button type="button" class="btn btn-success btn-sm" onclick="confirmPayment()" style="font-size: 0.8rem;">
                        <i class="fas fa-check me-2"></i>Konfirmasi Pembayaran
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Amount Modal -->
    <div class="modal fade" id="paymentAmountModal" tabindex="-1" aria-labelledby="paymentAmountModalLabel">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 15px; border: none;">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold" id="paymentAmountModalLabel">
                        <span id="modalBillTitle">K-PENUNJANG KBM TP 2025/2026</span>
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <p class="text-muted mb-2" style="font-size: 0.85rem;">Tagihan: <span id="modalBillAmount" class="fw-bold">Rp 0</span></p>
                        <div id="remainingInfo" style="display: none;">
                            <p class="text-muted mb-1" style="font-size: 0.85rem;">Total: <span id="modalTotalAmount" class="fw-bold">Rp 0</span></p>
                            <p class="text-success mb-2" style="font-size: 0.85rem;">Dibayar: <span id="modalPaidAmount" class="fw-bold">Rp 0</span></p>
                            <p class="text-info mb-2" style="font-size: 0.85rem;">Sisa: <span id="modalRemainingAmount" class="fw-bold">Rp 0</span></p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="paymentAmount" class="form-label fw-bold" style="font-size: 0.85rem;">Ingin Dibayar Rp :</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="paymentAmount" placeholder="Masukkan nominal pembayaran" style="border-color: #198754; font-size: 0.85rem;" oninput="onPaymentAmountInput()">
                            <div class="input-group-append">
                                <button class="btn btn-outline-success btn-sm" type="button" onclick="setFullAmount()" style="font-size: 0.8rem;">Lunas <span id="fullAmountText">Rp 0</span></button>
                            </div>
                        </div>
                        <div class="mt-2">
                            <button class="btn btn-outline-success btn-sm" type="button" onclick="setMinimumAmount()">Minimum Rp 10.000</button>
                        </div>
                        <div id="amountError" class="alert alert-danger mt-2" style="display: none;">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            <span id="amountErrorMessage" style="font-size: 0.85rem;">Jumlah pembayaran tidak boleh melebihi sisa tagihan</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-success btn-sm" data-bs-dismiss="modal" style="font-size: 0.8rem;">BATAL</button>
                    <button type="button" class="btn btn-success btn-sm" onclick="confirmAmount()" style="font-size: 0.8rem;">OKE</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Midtrans Payment Modal -->
    <div class="modal fade" id="midtransPaymentModal" tabindex="-1" aria-labelledby="midtransPaymentModalLabel">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 15px; border: none;">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold" id="midtransPaymentModalLabel">
                        <i class="fas fa-credit-card me-2"></i>Pilih Metode Pembayaran
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="py-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
                        <p class="mb-0" style="font-size: 0.9rem;">Memproses pembayaran Anda...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bank Transfer Modal for Tabungan -->
<div class="modal fade" id="bankTransferModal" tabindex="-1" aria-labelledby="bankTransferModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
            <!-- Header dengan gradient hijau -->
            <div class="modal-header border-0 text-center position-relative" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border-radius: 20px 20px 0 0;">
                <div class="w-100 text-center">
                    <div class="bg-white bg-opacity-20 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-university text-white" style="font-size: 24px;"></i>
                    </div>
                    <h6 class="modal-title text-white fw-bold mb-0" id="bankTransferModalLabel">
                        🏦 Upload Bukti Transfer Tabungan
                    </h6>
                    <p class="text-white-50 mb-0" style="font-size: 0.8rem;">Upload bukti transfer untuk verifikasi setoran</p>
                </div>
                <button type="button" class="btn-close btn-close-white position-absolute" data-bs-dismiss="modal" aria-label="Close" style="top: 15px; right: 15px;"></button>
            </div>
            
            <div class="modal-body p-4">
                <div class="row">
                    <!-- Left Column - Bank Info & Instructions -->
                    <div class="col-lg-8">
                        <!-- Bank Account Information -->
                        <div class="card mb-4" style="background: linear-gradient(135deg, #198754 0%, #20c997 100%); color: white; border-radius: 15px; border: none; box-shadow: 0 4px 20px rgba(25, 135, 84, 0.3);">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="bg-white bg-opacity-20 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                        <i class="fas fa-university fa-lg"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">Rekening Sekolah</h5>
                                        <p class="mb-0 opacity-75">Transfer ke rekening resmi sekolah</p>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div style="background: rgba(255, 255, 255, 0.1); border-radius: 10px; padding: 15px; margin-bottom: 15px;">
                                            <div style="font-size: 0.85rem; opacity: 0.8; margin-bottom: 5px;">Nama Bank</div>
                                            <div style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0;">{{ $schoolBank->nama_bank ?? 'Belum diatur' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div style="background: rgba(255, 255, 255, 0.1); border-radius: 10px; padding: 15px; margin-bottom: 15px;">
                                            <div style="font-size: 0.85rem; opacity: 0.8; margin-bottom: 5px;">Nomor Rekening</div>
                                            <div class="d-flex align-items-center">
                                                <div style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0; margin-right: 10px;">{{ $schoolBank->norek_bank ?? 'Belum diatur' }}</div>
                                                <button class="btn btn-sm" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.3); color: white; border-radius: 8px; padding: 8px 12px; font-size: 0.8rem;" onclick="copyToClipboard('{{ $schoolBank->norek_bank ?? '' }}')">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div style="background: rgba(255, 255, 255, 0.1); border-radius: 10px; padding: 15px;">
                                    <div style="font-size: 0.85rem; opacity: 0.8; margin-bottom: 5px;">Atas Nama</div>
                                    <div style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0;">{{ $schoolBank->nama_rekening ?? 'Belum diatur' }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Instructions -->
                        <div class="card mb-4" style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 10px; padding: 20px;">
                            <h6 class="fw-bold mb-3">
                                <i class="fas fa-info-circle me-2"></i>Instruksi Transfer
                            </h6>
                            
                            <div class="d-flex align-items-start mb-3">
                                <div style="background: #198754; color: white; width: 25px; height: 25px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 600; margin-right: 15px; flex-shrink: 0;">1</div>
                                <div style="flex: 1;">
                                    <div style="font-weight: 600; margin-bottom: 5px; color: #495057;">Transfer ke Rekening Sekolah</div>
                                    <div style="color: #6c757d; font-size: 0.9rem; margin-bottom: 0;">
                                        Transfer sejumlah <strong id="instructionAmount">Rp 0</strong> ke rekening sekolah yang tertera di atas
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-start mb-3">
                                <div style="background: #198754; color: white; width: 25px; height: 25px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 600; margin-right: 15px; flex-shrink: 0;">2</div>
                                <div style="flex: 1;">
                                    <div style="font-weight: 600; margin-bottom: 5px; color: #495057;">Upload Bukti Transfer</div>
                                    <div style="color: #6c757d; font-size: 0.9rem; margin-bottom: 0;">
                                        Upload bukti transfer di form di bawah ini untuk verifikasi admin
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-start">
                                <div style="background: #198754; color: white; width: 25px; height: 25px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 600; margin-right: 15px; flex-shrink: 0;">3</div>
                                <div style="flex: 1;">
                                    <div style="font-weight: 600; margin-bottom: 5px; color: #495057;">Tunggu Persetujuan Admin</div>
                                    <div style="color: #6c757d; font-size: 0.9rem; margin-bottom: 0;">
                                        Admin akan memverifikasi pembayaran dalam 1-2 hari kerja
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Form -->
                        <div class="card" style="background: white; border-radius: 15px; border: 1px solid #e9ecef; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-3">
                                    <i class="fas fa-upload me-2"></i>Upload Bukti Transfer
                                </h6>
                                


                                <!-- File Upload -->
                                <div class="mb-4">
                                    <div style="border: 2px dashed #dee2e6; border-radius: 10px; padding: 30px; text-align: center; transition: all 0.3s ease; cursor: pointer;" id="uploadArea" onclick="document.getElementById('proofFile').click()">
                                        <div id="uploadContent">
                                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                            <h6 class="text-muted">Klik untuk upload bukti transfer</h6>
                                            <p class="text-muted mb-0">Format: JPG, JPEG, PNG, PDF (Maks. 2MB)</p>
                                        </div>
                                        <div id="filePreview" style="display: none;">
                                            <img id="previewImage" style="max-width: 200px; max-height: 200px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 10px;">
                                            <p id="fileName" class="text-success mb-0"></p>
                                        </div>
                                    </div>
                                    <input type="file" id="proofFile" accept="image/*,.pdf" style="display: none;" required>
                                </div>

                                <!-- Notes -->
                                <div class="mb-4">
                                    <label for="transferNotes" class="form-label fw-bold text-dark">
                                        <i class="fas fa-sticky-note me-2 text-warning"></i>Catatan (Opsional)
                                    </label>
                                    <textarea class="form-control" id="transferNotes" rows="3" 
                                              placeholder="Tambahkan catatan atau keterangan tambahan jika diperlukan..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Payment Summary -->
                    <div class="col-lg-4">
                        <div class="card" style="background: white; border-radius: 15px; border: 1px solid #e9ecef; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-3">
                                    <i class="fas fa-receipt me-2"></i>Ringkasan Setoran
                                </h6>
                                
                                <div style="border-bottom: 1px solid #f1f3f4; padding: 15px 0;">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 fw-bold">Setor Tabungan</h6>
                                            <small class="text-muted">Tabungan</small>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-success" id="summaryAmount">Rp 0</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr class="my-3">
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fw-bold">Total Setoran</h6>
                                    <h5 class="mb-0 fw-bold text-success" id="summaryTotal">Rp 0</h5>
                                </div>
                                
                                <div class="mt-3">
                                    <div class="alert alert-info">
                                        <small>
                                            <i class="fas fa-info-circle me-1"></i>
                                            Setoran akan diproses setelah admin memverifikasi bukti transfer
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer border-0 p-4">
                <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal" style="font-size: 0.85rem;">
                    <i class="fas fa-times me-2"></i>Batal
                </button>
                <button type="button" class="btn btn-success btn-sm px-3 fw-bold" onclick="submitBankTransfer()" id="submitTransferBtn" style="font-size: 0.85rem;">
                    <i class="fas fa-upload me-2"></i>Upload & Kirim
                </button>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Payment Summary (Fixed Bottom) -->
<div id="paymentSummary" class="payment-summary" style="display: none;">
    <div class="d-flex justify-content-between align-items-center">
        <div class="total-section">
            <span class="total-label">Total</span>
            <span id="totalItems" class="total-items">0 item</span>
            <span id="totalAmount" class="total-amount">Rp 0</span>
        </div>
        <button class="btn btn-success btn-sm px-3" onclick="openPaymentModal()" style="font-size: 0.85rem;">
            <i class="fas fa-credit-card me-1"></i>Pilih Pembayaran
        </button>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let selectedPaymentMethod = 'transfer'; // Default to Transfer Bank

    function loadCart() {
        const cart = JSON.parse(localStorage.getItem('studentCart') || '[]');
        const cartItemsContainer = document.getElementById('cartItems');
        const emptyCartDiv = document.getElementById('emptyCart');
        const paymentSummaryDiv = document.getElementById('paymentSummary');
        
        console.log('Loading cart with items:', cart); // Debug log
        
        if (cart.length === 0) {
            cartItemsContainer.style.display = 'none';
            emptyCartDiv.style.display = 'block';
            paymentSummaryDiv.style.display = 'none';
            return;
        }
        
        cartItemsContainer.style.display = 'block';
        emptyCartDiv.style.display = 'none';
        paymentSummaryDiv.style.display = 'block';
        
        let totalAmount = 0;
        let cartHTML = '';
        
        cart.forEach((item, index) => {
            // Extract amount from string (e.g., "Rp 20.000" -> 20000)
            const originalAmount = parseInt(item.amount.replace(/[^\d]/g, ''));
            totalAmount += originalAmount;
            
            // Check if item is monthly (bulanan), non-monthly (bebas), or tabungan
            const isMonthly = item.type === 'bulanan' || item.name.includes('SPP') || item.name.includes('-');
            const isTabungan = item.type === 'tabungan';
            
            cartHTML += `
                <div class="bill-item">
                    <div class="bill-header">
                        <div>
                            <h6 class="bill-name">${item.name}</h6>
                            ${item.month ? `<small class="text-muted">${item.month}</small>` : ''}
                            <p class="bill-amount">${isTabungan ? 'Setoran:' : 'Tagihan:'} ${item.amount}</p>
                            ${item.keterangan ? `<small class="text-muted">${item.keterangan}</small>` : ''}
                        </div>
                        <button class="btn btn-hapus" onclick="removeFromCart(${index})">
                            Hapus
                        </button>
                    </div>
                    <div class="bill-content">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <label class="mb-1">${isTabungan ? 'Jumlah setoran:' : 'Ingin dibayar:'}</label>
                                <div class="payment-amount">${item.amount}</div>
                            </div>
                            ${!isMonthly && !isTabungan ? `<button class="btn btn-ubah" onclick="editItem(${index})">Ubah</button>` : ''}
                        </div>
                    </div>
                </div>
            `;
        });
        
        cartItemsContainer.innerHTML = cartHTML;
        updatePaymentSummary(cart.length, totalAmount);
    }
    
    function updateItemAmount(index, newAmount) {
        const cart = JSON.parse(localStorage.getItem('studentCart') || '[]');
        if (cart[index]) {
            // Format the amount properly
            const numericAmount = newAmount.replace(/[^\d]/g, '');
            if (numericAmount) {
                cart[index].amount = `Rp ${parseInt(numericAmount).toLocaleString('id-ID')}`;
                localStorage.setItem('studentCart', JSON.stringify(cart));
                updatePaymentSummary();
            }
        }
    }
    
    function updatePaymentSummary(itemCount = null, totalAmount = null) {
        const cart = JSON.parse(localStorage.getItem('studentCart') || '[]');
        
        // If parameters are provided, use them; otherwise calculate
        const count = itemCount !== null ? itemCount : cart.length;
        let amount = totalAmount !== null ? totalAmount : 0;
        
        if (totalAmount === null) {
            cart.forEach(item => {
                const itemAmount = parseInt(item.amount.replace(/[^\d]/g, ''));
                amount += itemAmount;
            });
        }
        
        const totalItemsElement = document.getElementById('totalItems');
        const totalAmountElement = document.getElementById('totalAmount');
        
        if (totalItemsElement) {
            totalItemsElement.textContent = `${count} item`;
        }
        if (totalAmountElement) {
            totalAmountElement.textContent = `Rp ${amount.toLocaleString('id-ID')}`;
        }
    }
    
    function selectPaymentMethod(method) {
        selectedPaymentMethod = method;
        
        // Update visual selection
        document.querySelectorAll('.payment-method').forEach(el => {
            el.classList.remove('selected');
        });
        event.currentTarget.classList.add('selected');
        
        // Update radio button with null check
        const radioButton = document.getElementById(method);
        if (radioButton) {
            radioButton.checked = true;
        }
        
        console.log('Payment method selected:', method);
    }
    
    function editItem(index) {
        const cart = JSON.parse(localStorage.getItem('studentCart') || '[]');
        if (cart[index]) {
            const item = cart[index];
            
            // Set modal content
            document.getElementById('modalBillTitle').textContent = item.name;
            
            // Check if this is a bebas bill (non-bulanan)
            if (item.type === 'bebas' && item.totalBill && item.paidAmount) {
                // Show remaining info for bebas bills
                document.getElementById('remainingInfo').style.display = 'block';
                document.getElementById('modalTotalAmount').textContent = `Rp ${parseInt(item.totalBill).toLocaleString('id-ID')}`;
                document.getElementById('modalPaidAmount').textContent = `Rp ${parseInt(item.paidAmount).toLocaleString('id-ID')}`;
                document.getElementById('modalRemainingAmount').textContent = `Rp ${parseInt(item.remainingAmount).toLocaleString('id-ID')}`;
                document.getElementById('modalBillAmount').textContent = `Rp ${parseInt(item.remainingAmount).toLocaleString('id-ID')}`;
                document.getElementById('fullAmountText').textContent = `Rp ${parseInt(item.remainingAmount).toLocaleString('id-ID')}`;
                
                // Store remaining amount for validation
                window.currentRemainingAmount = parseInt(item.remainingAmount);
                console.log('Bebas bill - Remaining amount set to:', window.currentRemainingAmount);
            } else {
                // Hide remaining info for bulanan bills
                document.getElementById('remainingInfo').style.display = 'none';
                document.getElementById('modalBillAmount').textContent = item.amount;
                document.getElementById('fullAmountText').textContent = item.amount;
                window.currentRemainingAmount = parseInt(item.amount.replace(/[^\d]/g, ''));
                console.log('Bulanan bill - Remaining amount set to:', window.currentRemainingAmount);
            }
            
            // Set current amount in input
            const currentAmount = parseInt(item.amount.replace(/[^\d]/g, ''));
            document.getElementById('paymentAmount').value = currentAmount;
            
            // Store current editing index
            window.currentEditingIndex = index;
            
            // Open modal
            const modal = new bootstrap.Modal(document.getElementById('paymentAmountModal'));
            modal.show();
        }
    }
    
    function setFullAmount() {
        const maxAmount = window.currentRemainingAmount || 0;
        document.getElementById('paymentAmount').value = maxAmount.toLocaleString('id-ID');
        validatePaymentAmount();
    }
    
    function validatePaymentAmount() {
        const inputAmount = parseInt(document.getElementById('paymentAmount').value.replace(/[^\d]/g, '') || 0);
        const maxAmount = window.currentRemainingAmount || 0;
        const errorDiv = document.getElementById('amountError');
        const errorMessage = document.getElementById('amountErrorMessage');
        
        console.log('Validating payment amount:', { inputAmount, maxAmount, isValid: inputAmount <= maxAmount });
        
        if (inputAmount > maxAmount && maxAmount > 0) {
            errorDiv.style.display = 'block';
            errorMessage.textContent = `Jumlah pembayaran tidak boleh melebihi sisa tagihan (Rp ${maxAmount.toLocaleString('id-ID')})`;
            return false;
        } else {
            errorDiv.style.display = 'none';
            return true;
        }
    }
    
    // Add real-time validation on input
    function onPaymentAmountInput() {
        const input = document.getElementById('paymentAmount');
        const inputAmount = parseInt(input.value.replace(/[^\d]/g, '') || 0);
        const maxAmount = window.currentRemainingAmount || 0;
        
        console.log('Input validation:', { inputAmount, maxAmount, currentRemaining: window.currentRemainingAmount });
        
        // Auto-format input with thousand separators
        if (inputAmount > 0) {
            input.value = inputAmount.toLocaleString('id-ID');
        }
        
        // Validate against max amount
        if (inputAmount > maxAmount && maxAmount > 0) {
            console.log('Input exceeds max amount, correcting...');
            input.value = maxAmount.toLocaleString('id-ID');
            validatePaymentAmount();
        } else {
            validatePaymentAmount();
        }
    }
    
    function setMinimumAmount() {
        const maxAmount = window.currentRemainingAmount || 0;
        const minAmount = Math.min(10000, maxAmount);
        document.getElementById('paymentAmount').value = minAmount.toLocaleString('id-ID');
        validatePaymentAmount();
    }
    
    function confirmAmount() {
        const amount = parseInt(document.getElementById('paymentAmount').value.replace(/[^\d]/g, '') || 0);
        const maxAmount = window.currentRemainingAmount || 0;
        
        console.log('Confirming amount:', { amount, maxAmount, currentRemaining: window.currentRemainingAmount });
        
        if (!amount || amount < 10000) {
            showAlert('Minimal pembayaran Rp 10.000!', 'warning');
            return;
        }
        
        // Strict validation against remaining amount
        if (amount > maxAmount && maxAmount > 0) {
            showAlert(`Jumlah pembayaran tidak boleh melebihi sisa tagihan (Rp ${maxAmount.toLocaleString('id-ID')})!`, 'error');
            return;
        }
        
        // Validate amount against remaining amount
        if (!validatePaymentAmount()) {
            return;
        }
        
        const cart = JSON.parse(localStorage.getItem('studentCart') || '[]');
        if (cart[window.currentEditingIndex]) {
            cart[window.currentEditingIndex].amount = `Rp ${amount.toLocaleString('id-ID')}`;
            localStorage.setItem('studentCart', JSON.stringify(cart));
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('paymentAmountModal'));
            modal.hide();
            
            // Reload cart
            loadCart();
            
            // Show success message
            showAlert('Nominal pembayaran berhasil diubah!', 'success');
        }
    }
    
    function removeFromCart(index) {
        const cart = JSON.parse(localStorage.getItem('studentCart') || '[]');
        
        if (index >= 0 && index < cart.length) {
            cart.splice(index, 1);
            localStorage.setItem('studentCart', JSON.stringify(cart));
            
            console.log('Item removed from cart. New cart:', cart); // Debug log
            
            // Update cart badge
            updateCartBadge();
            
            // Reload cart
            loadCart();
            
            // Show notification
            showAlert('Item berhasil dihapus dari keranjang!', 'success');
        } else {
            console.error('Invalid index for cart removal:', index);
            showAlert('Terjadi kesalahan saat menghapus item!', 'error');
        }
    }
    
    // Use global updateCartBadge function from layout
    
    // Function to check if cart should be preserved
    function shouldPreserveCart() {
        // Don't clear cart if we're on the cart page
        return window.location.pathname.includes('/cart') || window.location.pathname.includes('/keranjang');
    }
    
    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 3000);
    }
    
    function openPaymentModal() {
        const cart = JSON.parse(localStorage.getItem('studentCart') || '[]');
        if (cart.length === 0) {
            showAlert('Keranjang kosong! Silakan pilih tagihan terlebih dahulu.', 'warning');
            return;
        }
        
        // Set payment method to transfer (default)
        selectedPaymentMethod = 'transfer';
        
        // Update radio button and visual selection
        const transferRadio = document.getElementById('transfer');
        if (transferRadio) {
            transferRadio.checked = true;
        }
        
        // Update visual selection
        document.querySelectorAll('.payment-method').forEach(el => {
            el.classList.remove('selected');
        });
        // Select the transfer payment method
        const transferPaymentMethod = document.querySelector('.payment-method[onclick*="transfer"]');
        if (transferPaymentMethod) {
            transferPaymentMethod.classList.add('selected');
        }
        
        // Open modal
        const modal = new bootstrap.Modal(document.getElementById('paymentMethodModal'));
        modal.show();
        
        // Focus management for accessibility
        modal._element.addEventListener('shown.bs.modal', function () {
            // Remove aria-hidden when modal is shown
            modal._element.removeAttribute('aria-hidden');
            const firstButton = modal._element.querySelector('.btn-success');
            if (firstButton) {
                firstButton.focus();
            }
        });
        
        // Add aria-hidden when modal is hidden
        modal._element.addEventListener('hidden.bs.modal', function () {
            modal._element.setAttribute('aria-hidden', 'true');
        });
    }
    
    function confirmPayment() {
        const cart = JSON.parse(localStorage.getItem('studentCart') || '[]');
        if (cart.length === 0) {
            showAlert('Keranjang kosong! Silakan pilih tagihan terlebih dahulu.', 'warning');
            return;
        }
        
        // Calculate total amount
        let totalAmount = 0;
        cart.forEach(item => {
            const amount = parseInt(item.amount.replace(/[^\d]/g, ''));
            totalAmount += amount;
        });
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('paymentMethodModal'));
        modal.hide();
        
        // Check if Midtrans is enabled (currently disabled)
        const midtransEnabled = false; // Set to true to enable Midtrans
        
        if (selectedPaymentMethod === 'gateway' && midtransEnabled) {
            // Show Midtrans payment modal
            const midtransModal = new bootstrap.Modal(document.getElementById('midtransPaymentModal'));
            midtransModal.show();
            
            // Focus management for accessibility
            midtransModal._element.addEventListener('shown.bs.modal', function () {
                // Remove aria-hidden when modal is shown
                midtransModal._element.removeAttribute('aria-hidden');
            });
            
            // Add aria-hidden when modal is hidden
            midtransModal._element.addEventListener('hidden.bs.modal', function () {
                midtransModal._element.setAttribute('aria-hidden', 'true');
            });
            
            // Process Midtrans payment directly
            processPayment(cart, totalAmount);
        } else {
            // Process bank transfer payment (default)
            processPayment(cart, totalAmount);
        }
    }
    

    
    function getPaymentMethodName(method) {
        const methods = {
            'transfer': 'Transfer Bank Manual (Bebas Biaya Admin)',
            'ipaymu': 'Pembayaran Otomatis (iPaymu)',
            'gateway': 'Payment Gateway (Real-time)'
        };
        return methods[method] || method;
    }
    
    function processPayment(cart, totalAmount) {
        // Separate tabungan items from regular payment items
        const tabunganItems = cart.filter(item => item.type === 'tabungan');
        const regularItems = cart.filter(item => item.type !== 'tabungan');
        
        // Check if we have mixed items (tabungan + regular payments)
        if (tabunganItems.length > 0 && regularItems.length > 0) {
            showAlert('Tidak dapat memproses setoran tabungan bersamaan dengan pembayaran tagihan. Silakan pisahkan transaksi.', 'warning');
            return;
        }
        
        // Process tabungan items separately
        if (tabunganItems.length > 0) {
            processTabunganPayment(tabunganItems, totalAmount);
            return;
        }
        
        // Process regular payment items
        if (selectedPaymentMethod === 'transfer') {
            // Store cart data in session and redirect to bank transfer page
            const formData = new FormData();
            formData.append('cart_items', JSON.stringify(regularItems));
            formData.append('total_amount', totalAmount);
            formData.append('_token', '{{ csrf_token() }}');
            
            // Show loading message
            showAlert('Memproses transfer bank...', 'info');
            
            // Create AbortController for timeout
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout
            
            fetch('{{ route("student.bank-transfer.prepare") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                signal: controller.signal,
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                clearTimeout(timeoutId);
                if (data.success) {
                    // Clear cart from localStorage before redirecting
                    localStorage.removeItem('studentCart');
                    updateCartBadge();
                    // Redirect to bank transfer page
                    window.location.href = '{{ route("student.bank-transfer") }}';
                } else {
                    showAlert('Terjadi kesalahan: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                clearTimeout(timeoutId);
                console.error('Error:', error);
                if (error.name === 'AbortError') {
                    showAlert('Terjadi kesalahan: Timeout - Koneksi terlalu lama. Silakan coba lagi.', 'error');
                } else if (error.message.includes('Mixed Content') || error.message.includes('Failed to fetch')) {
                    showAlert('Terjadi kesalahan: Masalah koneksi ke server. Silakan refresh halaman dan coba lagi.', 'error');
                } else {
                    showAlert('Terjadi kesalahan saat memproses pembayaran: ' + error.message, 'error');
                }
            });
        } else if (selectedPaymentMethod === 'ipaymu') {
            // Payment Gateway (iPaymu) - proses real time
            showAlert('Memproses pembayaran via iPaymu...', 'info');

            const formData = new FormData();
            formData.append('cart_items', JSON.stringify(regularItems));
            formData.append('total_amount', totalAmount);
            formData.append('payment_method', 'ipaymu');
            formData.append('_token', '{{ csrf_token() }}');

            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 second timeout

            fetch('{{ route("student.cart.payment.ipaymu") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                signal: controller.signal,
                credentials: 'same-origin'
            })
            .then(response => {
                clearTimeout(timeoutId);
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('iPaymu response:', data);
                
                if (data.success && data.payment_url) {
                    // Clear cart from localStorage before redirecting
                    localStorage.removeItem('studentCart');
                    updateCartBadge();
                    
                    // Redirect to iPaymu payment page
                    window.location.href = data.payment_url;
                } else {
                    showAlert('Gagal membuat pembayaran: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                clearTimeout(timeoutId);
                console.error('Error:', error);
                if (error.name === 'AbortError') {
                    showAlert('Timeout - Koneksi terlalu lama. Silakan coba lagi.', 'error');
                } else {
                    showAlert('Terjadi kesalahan: ' + error.message, 'error');
                }
            });
        } else if (selectedPaymentMethod === 'gateway') {
            // Payment Gateway (Midtrans) - DISABLED
            showAlert('Metode pembayaran tidak tersedia', 'warning');

            const requestData = {
                cart_items: JSON.stringify(cart),
                total_amount: totalAmount,
                student_id: '{{ session("student_id") }}'
            };

            // Create AbortController for timeout
            const controller2 = new AbortController();
            const timeoutId2 = setTimeout(() => controller2.abort(), 10000); // 10 second timeout

            const paymentUrl = '{{ url("/api/midtrans/cart-payment-test") }}';
            console.log('Payment URL:', paymentUrl);
            
            fetch(paymentUrl, {
                method: 'POST',
                body: JSON.stringify(requestData),
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                signal: controller2.signal,
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                // Close Midtrans modal
                const midtransModal = bootstrap.Modal.getInstance(document.getElementById('midtransPaymentModal'));
                if (midtransModal) {
                    midtransModal.hide();
                }
                
                if (data.success && data.snap_token) {
                    // Clear cart from localStorage before opening Midtrans
                    localStorage.removeItem('studentCart');
                    updateCartBadge();
                    
                    // Open Midtrans Snap
                    snap.pay(data.snap_token, {
                        onSuccess: function(result) {
                            showAlert('Pembayaran berhasil!', 'success');
                            setTimeout(() => {
                                window.location.href = '{{ route("student.payment.history") }}';
                            }, 2000);
                        },
                        onPending: function(result) {
                            showAlert('Pembayaran pending. Silakan selesaikan pembayaran.', 'warning');
                            setTimeout(() => {
                                window.location.href = '{{ route("student.payment.history") }}';
                            }, 2000);
                        },
                        onError: function(result) {
                            showAlert('Pembayaran gagal. Silakan coba lagi.', 'error');
                        },
                        onClose: function() {
                            showAlert('Pembayaran dibatalkan.', 'warning');
                        }
                    });
                } else {
                    // Tampilkan pesan error dari backend
                    showAlert('Terjadi kesalahan: ' + (data.message || data.error || 'Gagal memproses pembayaran'), 'error');
                }
            })
            .catch(error => {
                clearTimeout(timeoutId2);
                console.error('Error:', error);
                
                // Close Midtrans modal
                const midtransModal = bootstrap.Modal.getInstance(document.getElementById('midtransPaymentModal'));
                if (midtransModal) {
                    midtransModal.hide();
                }
                
                let errorMessage = 'Terjadi kesalahan saat memproses pembayaran';
                
                if (error.name === 'AbortError') {
                    errorMessage = 'Timeout - Koneksi terlalu lama. Silakan coba lagi.';
                } else if (error.message.includes('Mixed Content')) {
                    errorMessage = 'Masalah keamanan koneksi. Silakan refresh halaman dan coba lagi.';
                } else if (error.message.includes('Failed to fetch')) {
                    errorMessage = 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.';
                } else if (error.message.includes('404')) {
                    errorMessage = 'Endpoint pembayaran tidak ditemukan. Silakan hubungi administrator.';
                } else if (error.message.includes('403')) {
                    errorMessage = 'Akses ditolak. Silakan login kembali.';
                } else if (error.message.includes('500')) {
                    errorMessage = 'Kesalahan server. Silakan coba lagi nanti.';
                } else if (error.message.includes('Network response was not ok')) {
                    errorMessage = 'Server tidak merespons dengan benar. Silakan coba lagi.';
                } else {
                    errorMessage += ': ' + error.message;
                }
                
                showAlert(errorMessage, 'error');
            });
        }
    }
    
    function showBankTransferModal(tabunganItem) {
        // Set modal content
        document.getElementById('instructionAmount').textContent = tabunganItem.amount;
        document.getElementById('summaryAmount').textContent = tabunganItem.amount;
        document.getElementById('summaryTotal').textContent = tabunganItem.amount;
        
        // Store tabungan item for later use
        window.currentTabunganItem = tabunganItem;
        
        // Reset form - hanya reset file upload dan notes
        document.getElementById('proofFile').value = '';
        document.getElementById('transferNotes').value = '';
        
        // Reset file upload preview
        document.getElementById('uploadContent').style.display = 'block';
        document.getElementById('filePreview').style.display = 'none';
        document.getElementById('uploadArea').classList.remove('border-success');
        
        // Update submit button state
        updateSubmitButton();
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('bankTransferModal'));
        modal.show();
    }
    
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            showAlert('Nomor rekening berhasil disalin!', 'success');
        }).catch(function(err) {
            console.error('Could not copy text: ', err);
            showAlert('Gagal menyalin nomor rekening', 'error');
        });
    }
    
    function updateSubmitButton() {
        const proofFile = document.getElementById('proofFile').files[0];
        const submitBtn = document.getElementById('submitTransferBtn');
        
        if (proofFile) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('btn-secondary');
            submitBtn.classList.add('btn-success');
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.remove('btn-success');
            submitBtn.classList.add('btn-secondary');
        }
    }
    
    function submitBankTransfer() {
        const tabunganItem = window.currentTabunganItem;
        if (!tabunganItem) {
            showAlert('Terjadi kesalahan: Data setoran tidak ditemukan', 'error');
            return;
        }
        
        // Get form data - hanya file dan notes yang diperlukan
        const proofFile = document.getElementById('proofFile').files[0];
        const transferNotes = document.getElementById('transferNotes').value.trim();
        
        // Validate form - hanya file yang wajib
        if (!proofFile) {
            showAlert('Mohon upload bukti transfer', 'warning');
            return;
        }
        
        // Validate file size (2MB)
        if (proofFile.size > 2 * 1024 * 1024) {
            showAlert('Ukuran file terlalu besar. Maksimal 2MB', 'warning');
            return;
        }
        
        showAlert('Memproses upload bukti transfer...', 'info');
        
        // Create FormData - hanya data yang diperlukan
        const formData = new FormData();
        formData.append('payment_type', 'manual');
        formData.append('payment_method', 'transfer');
        formData.append('amount', tabunganItem.setorAmount);
        formData.append('description', tabunganItem.keterangan || 'Setor Tabungan');
        formData.append('manual_proof_file', proofFile);
        formData.append('manual_notes', transferNotes);
        formData.append('_token', '{{ csrf_token() }}');
        
        // Create AbortController for timeout
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 second timeout for file upload
        
        fetch('{{ route("student.tabungan.process") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            signal: controller.signal,
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            clearTimeout(timeoutId);
            
            if (data.success) {
                // Clear cart from localStorage
                localStorage.removeItem('studentCart');
                updateCartBadge();
                
                console.log('Cart cleared for bank transfer payment'); // Debug log
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('bankTransferModal'));
                modal.hide();
                
                // Reset form - hanya field yang ada
                document.getElementById('proofFile').value = '';
                document.getElementById('transferNotes').value = '';
                
                showAlert('Setoran tabungan berhasil diajukan! Mohon menunggu persetujuan admin.', 'success');
                setTimeout(() => {
                    window.location.href = '{{ route("student.dashboard") }}';
                }, 2000);
            } else {
                showAlert('Terjadi kesalahan: ' + (data.message || 'Gagal memproses setoran tabungan'), 'error');
            }
        })
        .catch(error => {
            clearTimeout(timeoutId);
            console.error('Error:', error);
            
            if (error.name === 'AbortError') {
                showAlert('Terjadi kesalahan: Timeout - Koneksi terlalu lama. Silakan coba lagi.', 'error');
            } else if (error.message.includes('Mixed Content') || error.message.includes('Failed to fetch')) {
                showAlert('Terjadi kesalahan: Masalah koneksi ke server. Silakan refresh halaman dan coba lagi.', 'error');
            } else {
                showAlert('Terjadi kesalahan saat memproses setoran tabungan: ' + error.message, 'error');
            }
        });
    }
    
    function processTabunganPayment(tabunganItems, totalAmount) {
        if (selectedPaymentMethod === 'transfer') {
            // Show bank transfer modal for tabungan
            const tabunganItem = tabunganItems[0]; // Should only have one tabungan item
            showBankTransferModal(tabunganItem);
        } else if (selectedPaymentMethod === 'gateway') {
            // Process tabungan via payment gateway
            const tabunganItem = tabunganItems[0]; // Should only have one tabungan item
            
            showAlert('Memproses setoran tabungan...', 'info');
            
            const formData = new FormData();
            formData.append('payment_type', 'realtime');
            formData.append('payment_method', 'gateway');
            formData.append('amount', tabunganItem.setorAmount);
            formData.append('description', tabunganItem.keterangan || 'Setor Tabungan');
            formData.append('_token', '{{ csrf_token() }}');
            
            // Create AbortController for timeout
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout
            
            fetch('{{ route("student.tabungan.process") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                signal: controller.signal,
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                clearTimeout(timeoutId);
                
                if (data.success && data.snap_token) {
                    // Clear cart from localStorage before opening Midtrans
                    localStorage.removeItem('studentCart');
                    updateCartBadge();
                    
                    console.log('Cart cleared for Midtrans payment'); // Debug log
                    
                    // Open Midtrans Snap
                    snap.pay(data.snap_token, {
                        onSuccess: function(result) {
                            showAlert('Setoran tabungan berhasil!', 'success');
                            setTimeout(() => {
                                window.location.href = '{{ route("student.dashboard") }}';
                            }, 2000);
                        },
                        onPending: function(result) {
                            showAlert('Setoran tabungan pending. Silakan selesaikan pembayaran.', 'warning');
                            setTimeout(() => {
                                window.location.href = '{{ route("student.dashboard") }}';
                            }, 2000);
                        },
                        onError: function(result) {
                            showAlert('Setoran tabungan gagal. Silakan coba lagi.', 'error');
                        },
                        onClose: function() {
                            showAlert('Setoran tabungan dibatalkan.', 'warning');
                        }
                    });
                } else {
                    showAlert('Terjadi kesalahan: ' + (data.message || 'Gagal memproses setoran tabungan'), 'error');
                }
            })
            .catch(error => {
                clearTimeout(timeoutId);
                console.error('Error:', error);
                
                if (error.name === 'AbortError') {
                    showAlert('Terjadi kesalahan: Timeout - Koneksi terlalu lama. Silakan coba lagi.', 'error');
                } else if (error.message.includes('Mixed Content') || error.message.includes('Failed to fetch')) {
                    showAlert('Terjadi kesalahan: Masalah koneksi ke server. Silakan refresh halaman dan coba lagi.', 'error');
                } else {
                    showAlert('Terjadi kesalahan saat memproses setoran tabungan: ' + error.message, 'error');
                }
            });
        }
    }
    
    // Load cart when page loads
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM Content Loaded - Loading cart...'); // Debug log
        
        // Check if we should preserve cart (don't clear on cart page)
        if (shouldPreserveCart()) {
            console.log('Preserving cart on cart page'); // Debug log
        }
        
        // Load cart first
        loadCart();
        updateCartBadge();
        
        // Set default payment method to transfer
        selectedPaymentMethod = 'transfer';
        
        // Wait for modal to be shown before setting checked state
        const paymentMethodModal = document.getElementById('paymentMethodModal');
        if (paymentMethodModal) {
            paymentMethodModal.addEventListener('shown.bs.modal', function () {
                const transferRadio = document.getElementById('transfer');
                if (transferRadio) {
                    transferRadio.checked = true;
                }
            });
        }
        
        // Add event listeners for bank transfer modal form validation

        const proofFileInput = document.getElementById('proofFile');
        



        if (proofFileInput) {
            proofFileInput.addEventListener('change', updateSubmitButton);
        }
        
        // File upload handling for bank transfer modal
        const uploadArea = document.getElementById('uploadArea');
        
        if (proofFileInput) {
            proofFileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                const uploadContent = document.getElementById('uploadContent');
                const filePreview = document.getElementById('filePreview');
                const previewImage = document.getElementById('previewImage');
                const fileName = document.getElementById('fileName');
                
                if (file) {
                    // Validate file size (2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        showAlert('File terlalu besar. Maksimal 2MB.', 'error');
                        this.value = '';
                        return;
                    }
                    
                    // Validate file type
                    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
                    if (!allowedTypes.includes(file.type)) {
                        showAlert('Format file tidak didukung. Gunakan JPG, PNG, atau PDF.', 'error');
                        this.value = '';
                        return;
                    }
                    
                    fileName.textContent = file.name;
                    
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewImage.src = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    } else {
                        previewImage.src = '/assets/pdf-icon.png'; // You can add a PDF icon
                    }
                    
                    uploadContent.style.display = 'none';
                    filePreview.style.display = 'block';
                    uploadArea.classList.add('border-success');
                }
                
                updateSubmitButton();
            });
        }
        
        // Drag and drop functionality
        if (uploadArea) {
            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('dragover');
            });
            
            uploadArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
            });
            
            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    document.getElementById('proofFile').files = files;
                    document.getElementById('proofFile').dispatchEvent(new Event('change'));
                }
            });
        }
        
        // Debug: Check cart after loading
        setTimeout(() => {
            const cart = JSON.parse(localStorage.getItem('studentCart') || '[]');
            console.log('Cart after loading:', cart);
            console.log('Cart items count:', cart.length);
        }, 100);
    });
</script>
@endpush 