<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Langkah 5 - SPMB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/spmb-steps.css') }}" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('spmb.dashboard') }}">
                <i class="fas fa-graduation-cap me-2"></i>SPMB
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-user me-1"></i>{{ session('spmb_name') }}
                </span>
                <form method="POST" action="{{ route('spmb.logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Progress Indicator -->
        <div class="step-progress">
            <div class="steps-indicator">
                <div class="step-item completed">
                    <div class="step-circle"><i class="fas fa-check"></i></div>
                    <div class="step-line"></div>
                    <div class="step-label">Pendaftaran</div>
                </div>
                <div class="step-item completed">
                    <div class="step-circle"><i class="fas fa-check"></i></div>
                    <div class="step-line"></div>
                    <div class="step-label">Pembayaran</div>
                </div>
                <div class="step-item completed">
                    <div class="step-circle"><i class="fas fa-check"></i></div>
                    <div class="step-line"></div>
                    <div class="step-label">Formulir</div>
                </div>
                <div class="step-item completed">
                    <div class="step-circle"><i class="fas fa-check"></i></div>
                    <div class="step-line"></div>
                    <div class="step-label">Dokumen</div>
                </div>
                <div class="step-item active">
                    <div class="step-circle">5</div>
                    <div class="step-line"></div>
                    <div class="step-label">Biaya SPMB</div>
                </div>
                <div class="step-item">
                    <div class="step-circle">6</div>
                    <div class="step-label">Selesai</div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="step-card">
                    <div class="step-header text-center">
                        <div class="step-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <h4>Pembayaran Biaya SPMB</h4>
                        <p class="mb-0">Lakukan pembayaran terakhir untuk menyelesaikan pendaftaran</p>
                    </div>
                    <div class="step-body">
                        <div class="payment-info text-center">
                            <h6 class="mb-2" style="font-size: 0.95rem; font-weight: 600;">Biaya SPMB</h5>
                            <div class="amount-display">Rp {{ number_format($settings->biaya_spmb ?? 200000, 0, ',', '.') }}</div>
                        
                        </div>

                        @if($additionalFees && $additionalFees->count() > 0)
                        <!-- Additional Fees Section -->
                        <div class="mt-4">
                            <h6 class="mb-3">
                                <i class="fas fa-plus-circle me-2"></i>Biaya Tambahan (Opsional)
                            </h6>
                            <div class="row">
                                @foreach($additionalFees as $fee)
                                    <div class="col-md-6 mb-3">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="card-title mb-0">{{ $fee->name }}</h6>
                                                    <span class="badge bg-{{ $fee->type === 'mandatory' ? 'danger' : ($fee->type === 'optional' ? 'info' : 'warning') }}">
                                                        {{ $fee->type === 'mandatory' ? 'Wajib' : ($fee->type === 'optional' ? 'Opsional' : 'Kondisional') }}
                                                    </span>
                                                </div>
                                                @if($fee->description)
                                                    <p class="card-text text-muted small mb-2">{{ $fee->description }}</p>
                                                @endif
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="h5 text-primary mb-0">Rp {{ number_format($fee->amount, 0, ',', '.') }}</span>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" 
                                                               id="additional_fee_{{ $fee->id }}" 
                                                               name="additional_fees[]" 
                                                               value="{{ $fee->id }}"
                                                               {{ $fee->type === 'mandatory' ? 'checked disabled' : '' }}
                                                               onchange="calculateAdditionalFees()">
                                                        <label class="form-check-label" for="additional_fee_{{ $fee->id }}">
                                                            {{ $fee->type === 'mandatory' ? 'Wajib' : 'Pilih' }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <!-- Total Calculation -->
                            <div class="card border-primary mt-3">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="mb-2">Rincian Biaya:</h6>
                                            <ul class="list-unstyled mb-0">
                                                <li class="d-flex justify-content-between">
                                                    <span>Biaya SPMB:</span>
                                                    <span>Rp {{ number_format($settings->biaya_spmb ?? 200000, 0, ',', '.') }}</span>
                                                </li>
                                                <li class="d-flex justify-content-between" id="additional-fees-list">
                                                    <span>Biaya Tambahan:</span>
                                                    <span id="additional-fees-total">Rp 0</span>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="mb-2">Total Pembayaran:</h6>
                                            <div class="h4 text-primary mb-0" id="total-payment">
                                                Rp {{ number_format($settings->biaya_spmb ?? 200000, 0, ',', '.') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="alert alert-success">
                            <h6 class="alert-heading">
                                <i class="fas fa-check-circle me-2"></i>Dokumen Berhasil Diupload!
                            </h6>
                            <p class="mb-0">
                                Semua dokumen telah berhasil diupload. Sekarang lakukan pembayaran biaya SPMB untuk menyelesaikan pendaftaran.
                            </p>
                        </div>
                        @php
                            $existingPayment = $registration->payments()->where('type', 'spmb_fee')->first();
                            $paymentStatus = $existingPayment ? $existingPayment->status : null;
                        @endphp
                        
                        @if($paymentStatus === 'failed')
                            <div class="alert alert-danger mb-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Bukti pembayaran ditolak!</strong>
                                <p class="mb-2 mt-2">Silakan upload ulang bukti pembayaran yang valid.</p>
                                @if($existingPayment && $existingPayment->notes)
                                    <p class="mb-2"><strong>Alasan penolakan:</strong> {{ $existingPayment->notes }}</p>
                                @endif
                            </div>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#paymentModal">
                                <i class="fas fa-upload me-1"></i>Upload Ulang Bukti
                            </button>
                        @elseif($paymentStatus === 'pending')
                            <div class="alert alert-warning mb-3">
                                <i class="fas fa-clock me-2"></i>
                                <strong>Menunggu verifikasi admin</strong>
                                <p class="mb-2 mt-2">Bukti pembayaran sedang dalam proses verifikasi.</p>
                            </div>
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#paymentModal">
                                <i class="fas fa-eye me-1"></i>Lihat Status
                            </button>
                        @else
                            <div class="d-flex justify-content-between action-buttons">
                                <a href="{{ route('spmb.dashboard') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>Kembali ke Dashboard
                                </a>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#paymentModal">
                                    <i class="fas fa-credit-card me-1"></i>Bayar Sekarang
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #008060, #006d52); color: white;">
                    <h5 class="modal-title" id="paymentModalLabel">
                        <i class="fas fa-credit-card me-2"></i>Pilih Metode Pembayaran SPMB
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                        <div class="row">
                            <!-- QRIS Payment -->
                            <!-- <div class="col-md-6 mb-3">
                                <div class="card payment-option h-100" style="border: 2px solid #e9ecef; transition: all 0.3s ease;">
                                    <div class="card-body text-center">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment_method" id="qris" value="qris" onchange="selectPaymentMethod('qris')">
                                            <label class="form-check-label w-100" for="qris" style="cursor: pointer;">
                                                <i class="fas fa-qrcode fa-2x text-success mb-3"></i>
                                                <h5 class="card-title">QRIS</h5>
                                                <p class="card-text text-muted">Pembayaran instan dengan QR Code</p>
                                                <small class="text-muted">E-wallet & Mobile Banking</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                             -->
                            <!-- Manual Transfer -->
                            <div class="col-md-6 mb-3">
                                <div class="card payment-option h-100" style="border: 2px solid #e9ecef; transition: all 0.3s ease;">
                                    <div class="card-body text-center">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment_method" id="transfer_manual" value="transfer_manual" onchange="selectPaymentMethod('transfer_manual')">
                                            <label class="form-check-label w-100" for="transfer_manual" style="cursor: pointer;">
                                                <i class="fas fa-university fa-2x text-primary mb-3"></i>
                                                <h5 class="card-title">Transfer Manual</h5>
                                                <p class="card-text text-muted">Transfer bank dengan upload bukti</p>
                                                <small class="text-muted">Manual Verification</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <!-- Transfer Manual Form -->
                    <div id="transferForm" style="display: none;">
                        <hr>
                        <h6 class="mb-3">
                            <i class="fas fa-university me-2"></i>Informasi Transfer Manual SPMB
                        </h6>
                        
                        <div class="alert alert-info">
                            <h6 class="mb-2">Informasi Rekening:</h6>
                            @if(isset($gatewayInfo))
                                <p class="mb-1"><strong>Bank:</strong> {{ $gatewayInfo->nama_bank ?? 'N/A' }}</p>
                                <div class="mb-1 account-number-wrapper">
                                    <strong>Nomor Rekening:</strong>
                                    <span class="bg-white px-2 py-1 rounded border account-number-display" id="accountNumber">{{ $gatewayInfo->norek_bank ?? 'N/A' }}</span>
                                    <button type="button" class="btn btn-outline-secondary btn-copy-account" onclick="copyToClipboard('accountNumber')" title="Copy nomor rekening">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                                <p class="mb-1"><strong>Atas Nama:</strong> {{ $gatewayInfo->nama_rekening ?? 'N/A' }}</p>
                                <p class="mb-0"><strong>Jumlah Transfer:</strong> <span id="transfer-amount-1">Rp {{ number_format($settings->biaya_spmb ?? 200000, 0, ',', '.') }}</span></p>
                            @else
                                <p class="mb-1"><strong>Bank:</strong> Bank BCA</p>
                                <div class="mb-1 account-number-wrapper">
                                    <strong>Nomor Rekening:</strong>
                                    <span class="bg-white px-2 py-1 rounded border account-number-display" id="accountNumber">1234567890</span>
                                    <button type="button" class="btn btn-outline-secondary btn-copy-account" onclick="copyToClipboard('accountNumber')" title="Copy nomor rekening">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                                <p class="mb-1"><strong>Atas Nama:</strong> SMK Teknologi Indonesia</p>
                                <p class="mb-0"><strong>Jumlah Transfer:</strong> <span id="transfer-amount-1">Rp {{ number_format($settings->biaya_spmb ?? 200000, 0, ',', '.') }}</span></p>
                            @endif
                        </div>

                        <form id="transferPaymentForm" method="POST" action="{{ route('spmb.step5.transfer') }}" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="proof_of_payment" class="form-label">
                                    <i class="fas fa-upload me-1"></i>Upload Bukti Transfer
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="file" class="form-control" id="proof_of_payment" name="proof_of_payment" 
                                       accept="image/*,.pdf" required>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Format yang diperbolehkan: JPG, PNG, PDF. Maksimal 5MB.
                                </div>
                                <div id="filePreview" class="mt-2" style="display: none;">
                                    <div class="alert alert-success d-flex align-items-center">
                                        <i class="fas fa-check-circle me-2"></i>
                                        <span id="fileName"></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="notes" class="form-label">Catatan (Opsional)</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3" 
                                          placeholder="Tambahkan catatan jika diperlukan"></textarea>
                            </div>
                        </form>
                    </div>

                    <!-- QRIS Form -->
                    <div id="qrisForm" style="display: none;">
                        <hr>
                        <h6 class="mb-3">
                            <i class="fas fa-qrcode me-2"></i>Pembayaran QRIS SPMB
                        </h6>
                        
                        <div class="text-center">
                            <div class="alert alert-info mb-3">
                                <h6 class="mb-2">Total Pembayaran</h6>
                                <div class="h4 text-primary mb-0" id="qris-total-amount">Rp {{ number_format($settings->biaya_spmb ?? 200000, 0, ',', '.') }}</div>
                            </div>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Pembayaran QRIS akan diproses otomatis</strong>
                            </div>
                            
                            <form id="qrisPaymentForm" method="POST" action="{{ route('spmb.step5.post') }}">
                                @csrf
                                <input type="hidden" name="payment_method" value="qris">
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" id="confirmPaymentBtn" class="btn btn-primary" style="display: none;">
                        <i class="fas fa-check me-1"></i>Konfirmasi Pembayaran
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedPaymentMethod = null;

        function selectPaymentMethod(method) {
            selectedPaymentMethod = method;
            
            // Reset all payment options
            document.querySelectorAll('.payment-option').forEach(option => {
                option.style.border = '2px solid #e9ecef';
                option.style.backgroundColor = 'white';
            });
            
            // Highlight selected option
            event.currentTarget.style.border = '2px solid #008060';
            event.currentTarget.style.backgroundColor = '#f8fff9';
            
            // Show/hide forms
            if (method === 'transfer_manual') {
                document.getElementById('transferForm').style.display = 'block';
                document.getElementById('qrisForm').style.display = 'none';
                document.getElementById('confirmPaymentBtn').style.display = 'inline-block';
            } else if (method === 'qris') {
                document.getElementById('transferForm').style.display = 'none';
                document.getElementById('qrisForm').style.display = 'block';
                document.getElementById('confirmPaymentBtn').style.display = 'inline-block';
            }
        }

        // Confirm payment
        document.getElementById('confirmPaymentBtn').addEventListener('click', function() {
            if (selectedPaymentMethod === 'transfer_manual') {
                document.getElementById('transferPaymentForm').submit();
            } else if (selectedPaymentMethod === 'qris') {
                document.getElementById('qrisPaymentForm').submit();
            }
        });

        // File upload feedback
        document.getElementById('proof_of_payment').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('filePreview');
            const fileName = document.getElementById('fileName');
            
            if (file) {
                // Validate file size (5MB max)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File terlalu besar. Maksimal 5MB.');
                    e.target.value = '';
                    preview.style.display = 'none';
                    return;
                }
                
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Format file tidak diperbolehkan. Hanya JPG, PNG, dan PDF.');
                    e.target.value = '';
                    preview.style.display = 'none';
                    return;
                }
                
                // Show preview
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                fileName.textContent = `File dipilih: ${file.name} (${fileSize} MB)`;
                preview.style.display = 'block';
                
                // Add visual feedback
                const input = e.target;
                input.style.borderColor = '#008060';
                input.style.backgroundColor = '#f8fff9';
            } else {
                preview.style.display = 'none';
            }
        });

        // Copy to clipboard function with fallback
        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            const text = element.textContent.trim();
            
            // Show loading state
            const button = event.target.closest('button');
            const originalIcon = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            button.disabled = true;
            
            // Try modern Clipboard API first
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(function() {
                    showCopySuccess(button, originalIcon);
                }).catch(function(err) {
                    console.error('Clipboard API failed: ', err);
                    fallbackCopyToClipboard(text, button, originalIcon);
                });
            } else {
                // Fallback for older browsers or non-secure contexts
                fallbackCopyToClipboard(text, button, originalIcon);
            }
        }
        
        // Fallback copy method
        function fallbackCopyToClipboard(text, button, originalIcon) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    showCopySuccess(button, originalIcon);
                } else {
                    showCopyError(button, originalIcon);
                }
            } catch (err) {
                console.error('Fallback copy failed: ', err);
                showCopyError(button, originalIcon);
            } finally {
                document.body.removeChild(textArea);
            }
        }
        
        // Show success feedback
        function showCopySuccess(button, originalIcon) {
            button.innerHTML = '<i class="fas fa-check text-success"></i>';
            button.classList.add('btn-success');
            button.classList.remove('btn-outline-secondary');
            button.disabled = false;
            
            // Reset button after 2 seconds
            setTimeout(function() {
                button.innerHTML = originalIcon;
                button.classList.remove('btn-success');
                button.classList.add('btn-outline-secondary');
            }, 2000);
        }
        
        // Show error feedback
        function showCopyError(button, originalIcon) {
            button.innerHTML = '<i class="fas fa-times text-danger"></i>';
            button.classList.add('btn-danger');
            button.classList.remove('btn-outline-secondary');
            button.disabled = false;
            
            // Reset button after 3 seconds
            setTimeout(function() {
                button.innerHTML = originalIcon;
                button.classList.remove('btn-danger');
                button.classList.add('btn-outline-secondary');
            }, 3000);
        }

        // Calculate additional fees total
        function calculateAdditionalFees() {
            console.log('Calculating additional fees...');
            const checkboxes = document.querySelectorAll('input[name="additional_fees[]"]:checked');
            let total = 0;
            
            console.log('Found checked checkboxes:', checkboxes.length);
            
            checkboxes.forEach(checkbox => {
                const feeCard = checkbox.closest('.card-body');
                const feeAmount = feeCard.querySelector('.h5.text-primary').textContent;
                const amount = parseInt(feeAmount.replace(/[^\d]/g, ''));
                console.log('Fee amount:', feeAmount, 'Parsed:', amount);
                total += amount;
            });
            
            console.log('Total additional fees:', total);
            
            // Update additional fees total (only show total, not details)
            const additionalFeesTotal = document.getElementById('additional-fees-total');
            if (additionalFeesTotal) {
                additionalFeesTotal.textContent = `Rp ${total.toLocaleString('id-ID')}`;
            }
            
            // Update additional fees list (keep it simple - just show total)
            const additionalFeesList = document.getElementById('additional-fees-list');
            if (additionalFeesList) {
                if (total > 0) {
                    additionalFeesList.innerHTML = '<span>Biaya Tambahan:</span><span>Rp ' + total.toLocaleString('id-ID') + '</span>';
                } else {
                    additionalFeesList.innerHTML = '<span>Biaya Tambahan:</span><span>Rp 0</span>';
                }
            }
            
            // Update total payment
            const spmbFee = {{ $settings->biaya_spmb ?? 200000 }};
            const grandTotal = spmbFee + total;
            console.log('SPMB Fee:', spmbFee, 'Grand Total:', grandTotal);
            
            const totalPayment = document.getElementById('total-payment');
            if (totalPayment) {
                totalPayment.textContent = `Rp ${grandTotal.toLocaleString('id-ID')}`;
            }
            
            // Update transfer amount in modal
            const transferAmountElements = document.querySelectorAll('#transfer-amount-1');
            transferAmountElements.forEach(element => {
                element.textContent = `Rp ${grandTotal.toLocaleString('id-ID')}`;
            });
            
            // Update QRIS total amount
            const qrisTotalElement = document.getElementById('qris-total-amount');
            if (qrisTotalElement) {
                qrisTotalElement.textContent = `Rp ${grandTotal.toLocaleString('id-ID')}`;
            }
        }

        // Add event listeners to checkboxes using event delegation
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, setting up event listeners...');
            
            // Use event delegation for better reliability
            document.addEventListener('change', function(e) {
                if (e.target && e.target.name === 'additional_fees[]') {
                    console.log('Checkbox changed:', e.target.id, 'Checked:', e.target.checked);
                    calculateAdditionalFees();
                }
            });
            
            // Initial calculation
            console.log('Running initial calculation...');
            calculateAdditionalFees();
        });
    </script>

    <!-- Mobile Bottom Navigation -->
    <div class="spmb-bottom-nav d-md-none" id="bottomNav">
        <div class="spmb-bottom-nav-content">
            <a href="{{ route('spmb.dashboard') }}" class="spmb-bottom-nav-btn spmb-bottom-nav-btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Dashboard</span>
            </a>
            @php
                $existingPayment = $registration->payments()->where('type', 'spmb_fee')->first();
                $paymentStatus = $existingPayment ? $existingPayment->status : null;
            @endphp
            
            @if($paymentStatus !== 'paid' && $paymentStatus !== 'pending')
                <button type="button" class="spmb-bottom-nav-btn spmb-bottom-nav-btn-primary" data-bs-toggle="modal" data-bs-target="#paymentModal">
                    <i class="fas fa-credit-card"></i>
                    <span>Bayar</span>
                </button>
            @endif
        </div>
    </div>

    <script>
        // Show bottom nav on mobile
        if (window.innerWidth <= 768) {
            document.getElementById('bottomNav')?.classList.add('active');
        }
        
        window.addEventListener('resize', function() {
            if (window.innerWidth <= 768) {
                document.getElementById('bottomNav')?.classList.add('active');
            } else {
                document.getElementById('bottomNav')?.classList.remove('active');
            }
        });
    </script>
</body>
</html>

