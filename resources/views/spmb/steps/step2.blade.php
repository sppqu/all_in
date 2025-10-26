<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Langkah 2 - SPMB</title>
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
                    <div class="step-circle">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-label">Pendaftaran</div>
                </div>
                <div class="step-item active">
                    <div class="step-circle">2</div>
                    <div class="step-line"></div>
                    <div class="step-label">Pembayaran</div>
                </div>
                <div class="step-item">
                    <div class="step-circle">3</div>
                    <div class="step-line"></div>
                    <div class="step-label">Formulir</div>
                </div>
                <div class="step-item">
                    <div class="step-circle">4</div>
                    <div class="step-line"></div>
                    <div class="step-label">Dokumen</div>
                </div>
                <div class="step-item">
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
                            <i class="fas fa-qrcode"></i>
                        </div>
                        <h4>Pembayaran QRIS Step-2</h4>
                        <p class="mb-0">Pembayaran wajib untuk melanjutkan pendaftaran</p>
                    </div>
                    <div class="step-body">
                        {{-- Error Messages --}}
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <strong>Error!</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('warning'))
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                {{ session('warning') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="payment-info text-center">
                            <h6 class="mb-2" style="font-size: 0.95rem; font-weight: 600;">Biaya QRIS</h6>
                            <div class="amount-display">Rp {{ number_format(\App\Helpers\WaveHelper::getStep2QrisFee(), 0, ',', '.') }}</div>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle me-1"></i>
                                Default Rp 3.000
                            </small>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('spmb.dashboard') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Kembali ke Dashboard
                            </a>
                        @php
                            $existingPayment = $registration->payments()->where('type', 'registration_fee')->first();
                            $paymentStatus = $existingPayment ? $existingPayment->status : null;
                            $paymentMethod = $existingPayment ? $existingPayment->payment_method : null;
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
                        @elseif($paymentStatus === 'pending' && $paymentMethod === 'QRIS')
                            {{-- QRIS Pending - Show payment link --}}
                            <a href="{{ route('spmb.payment', $existingPayment->id) }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-qrcode me-2"></i>Lanjutkan Pembayaran
                            </a>
                        @elseif($paymentStatus === 'pending' && $paymentMethod === 'transfer_manual')
                            {{-- Transfer Manual Pending - Waiting verification --}}
                            <div class="alert alert-warning mb-3">
                                <i class="fas fa-clock me-2"></i>
                                <strong>Menunggu verifikasi admin</strong>
                                <p class="mb-2 mt-2">Bukti pembayaran sedang dalam proses verifikasi.</p>
                            </div>
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#paymentModal">
                                <i class="fas fa-eye me-1"></i>Lihat Status
                            </button>
                        @else
                            {{-- No payment or paid - Show payment button --}}
                            <form action="{{ route('spmb.step2.post') }}" method="POST" id="qrisPaymentForm">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-lg" id="btnPayQris">
                                    <i class="fas fa-qrcode me-2"></i>Bayar dengan QRIS Tripay
                                </button>
                            </form>
                        @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Loading state for payment button
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('qrisPaymentForm');
        const btn = document.getElementById('btnPayQris');
        
        console.log('QRIS Payment Form:', form);
        console.log('QRIS Payment Button:', btn);
        
        if (form) {
            form.addEventListener('submit', function(e) {
                console.log('Form submitted!');
                console.log('Form action:', form.action);
                
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses Pembayaran...';
                }
            });
        } else {
            console.error('Form qrisPaymentForm not found!');
        }
    });
    </script>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #008060, #006d52); color: white;">
                    <h5 class="modal-title" id="paymentModalLabel">
                        <i class="fas fa-credit-card me-2"></i>Pilih Metode Pembayaran
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
                                                <i class="fas fa-qrcode fa-3x text-success mb-3"></i>
                                                <h5 class="card-title">QRIS</h5>
                                                <p class="card-text text-muted">Pembayaran instan dengan QR Code</p>
                                                <small class="text-muted">E-wallet & Mobile Banking</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div> -->
                            
                            <!-- Manual Transfer -->
                            <div class="col-md-6 mb-3">
                                <div class="card payment-option h-100" style="border: 2px solid #e9ecef; transition: all 0.3s ease;">
                                    <div class="card-body text-center">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment_method" id="transfer_manual" value="transfer_manual" onchange="selectPaymentMethod('transfer_manual')">
                                            <label class="form-check-label w-100" for="transfer_manual" style="cursor: pointer;">
                                                <i class="fas fa-university fa-3x text-primary mb-3"></i>
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
                            <i class="fas fa-university me-2"></i>Informasi Transfer Manual
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
                                <p class="mb-0"><strong>Jumlah Transfer:</strong> Rp {{ number_format($settings->biaya_pendaftaran ?? 50000, 0, ',', '.') }}</p>
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
                                <p class="mb-0"><strong>Jumlah Transfer:</strong> Rp {{ number_format($settings->biaya_pendaftaran ?? 50000, 0, ',', '.') }}</p>
                            @endif
                        </div>

                        <form id="transferPaymentForm" method="POST" action="{{ route('spmb.step2.transfer') }}" enctype="multipart/form-data">
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
                            <i class="fas fa-qrcode me-2"></i>Pembayaran QRIS
                        </h6>
                        
                        <div class="text-center">
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Pembayaran QRIS akan diproses otomatis</strong>
                            </div>
                            
                            <form id="qrisPaymentForm" method="POST" action="{{ route('spmb.step2.post') }}">
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
    </script>
</body>
</html>
