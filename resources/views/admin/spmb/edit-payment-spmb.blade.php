<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pembayaran SPMB - SPMB Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #ffffff;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: #ffffff !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid #e9ecef;
            z-index: 1030;
            position: relative;
        }

        .navbar-brand {
            color: #008060 !important;
            font-weight: 700;
            font-size: 1.5rem;
        }

        .navbar-text {
            color: #008060 !important;
        }

        .detail-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideInUp 0.6s ease-out;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .btn-primary {
            background: linear-gradient(135deg, #008060, #00a86b);
            border: none;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #006b4f, #008060);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 128, 96, 0.3);
        }

        .payment-item {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border: 2px solid #008060;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .payment-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 128, 96, 0.2);
        }

        .alert {
            border-radius: 15px;
            border: none;
            padding: 15px 20px;
        }

        .transfer-info {
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .payment-method {
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #008060;
            box-shadow: 0 0 0 0.2rem rgba(0, 128, 96, 0.25);
        }

        .btn-outline-primary:hover {
            background-color: #008060;
            border-color: #008060;
            color: white;
        }

        .btn-outline-success:hover {
            background-color: #28a745;
            border-color: #28a745;
            color: white;
        }

        .btn-outline-info:hover {
            background-color: #0dcaf0;
            border-color: #0dcaf0;
            color: white;
        }

        .btn-outline-danger:hover {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
        }

        /* Copy Account Button Styling */
        .btn-copy-account {
            padding: 0.15rem 0.35rem !important;
            font-size: 0.7rem;
            min-width: unset;
            width: 24px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .account-number-wrapper {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .account-number-wrapper strong {
            flex-shrink: 0;
        }

        .account-number-wrapper .account-number-display {
            flex-shrink: 0;
        }

        .account-number-wrapper .btn-copy-account {
            flex-shrink: 0;
        }

        @media (max-width: 768px) {
            .btn-copy-account {
                padding: 0.1rem 0.3rem !important;
                font-size: 0.65rem;
                width: 22px;
                height: 22px;
            }
            
            .account-number-wrapper {
                gap: 0.3rem;
            }
            
            .account-number-wrapper strong {
                font-size: 0.9rem;
            }
            
            .account-number-wrapper .account-number-display {
                font-size: 0.85rem;
                padding: 0.25rem 0.5rem !important;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('manage.spmb.index') }}">
                <i class="fas fa-money-bill-wave me-2"></i>Edit Pembayaran SPMB
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="{{ route('manage.spmb.show', $registration->id) }}">
                    <i class="fas fa-arrow-left me-1"></i>Kembali ke Detail
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Registration Info -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="detail-card">
                    <div class="p-4">
                        <h4 class="mb-4">Edit Pembayaran SPMB - Pendaftaran #{{ $registration->id }}</h4>
                        <p class="text-muted">Nama: <strong>{{ $registration->name }}</strong> | No. HP: <strong>{{ $registration->phone }}</strong></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Status -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="detail-card">
                    <div class="p-4">
                        <h5 class="mb-4">Status Pembayaran SPMB</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="payment-item">
                                    <h6 class="mb-2">Biaya Pendaftaran</h6>
                                    <p class="mb-2">
                                        @if($registration->registration_fee_paid)
                                            <span class="badge bg-success fs-6">
                                                <i class="fas fa-check-circle me-1"></i>Lunas
                                            </span>
                                        @else
                                            <span class="badge bg-warning fs-6">
                                                <i class="fas fa-clock me-1"></i>Belum Lunas
                                            </span>
                                        @endif
                                    </p>
                                    <small class="text-muted">Status pembayaran biaya pendaftaran</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="payment-item">
                                    <h6 class="mb-2">Biaya SPMB</h6>
                                    <p class="mb-2">
                                        @if($registration->spmb_fee_paid)
                                            <span class="badge bg-success fs-6">
                                                <i class="fas fa-check-circle me-1"></i>Lunas
                                            </span>
                                        @else
                                            <span class="badge bg-warning fs-6">
                                                <i class="fas fa-clock me-1"></i>Belum Lunas
                                            </span>
                                        @endif
                                    </p>
                                    <small class="text-muted">Status pembayaran biaya SPMB</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="detail-card">
                    <div class="p-4">
                        <h5 class="mb-4">Metode Pembayaran SPMB</h5>
                        
                        <!-- Transfer Manual -->
                        <div class="payment-method mb-4">
                            <div class="payment-item">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">
                                        <i class="fas fa-university me-2 text-primary"></i>Transfer Manual
                                    </h6>
                                    <button class="btn btn-outline-primary btn-sm" onclick="toggleTransferInfo()">
                                        <i class="fas fa-info-circle me-1"></i>Info Rekening
                                    </button>
                                </div>
                                
                                <div id="transferInfo" class="transfer-info" style="display: none;">
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
                                            <p class="mb-0"><strong>Jumlah Transfer:</strong> Rp {{ number_format($settings->biaya_spmb ?? 200000, 0, ',', '.') }}</p>
                                        @else
                                            <p class="mb-0">Informasi rekening belum dikonfigurasi.</p>
                                        @endif
                                    </div>
                                </div>
                                
                                <form action="{{ route('manage.spmb.create-payment-spmb', $registration->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="payment_method" value="transfer_manual">
                                    <input type="hidden" name="type" value="spmb_fee">
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="amount" class="form-label">Jumlah Pembayaran (Rp)</label>
                                            <input type="number" class="form-control" id="amount" name="amount" 
                                                   value="{{ $settings->biaya_spmb ?? 200000 }}" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="payment_reference" class="form-label">Nomor Referensi Transfer</label>
                                            <input type="text" class="form-control" id="payment_reference" name="payment_reference" 
                                                   placeholder="Masukkan nomor referensi transfer" required>
                                        </div>
                                    </div>
                                    
                        <!-- Note: File upload removed as spmb_payments table doesn't have proof_of_payment field -->
                                    
                                    <div class="mb-3">
                                        <label for="notes" class="form-label">Catatan (Opsional)</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="3" 
                                                  placeholder="Tambahkan catatan jika diperlukan"></textarea>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-1"></i>Kirim Bukti Transfer
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Payment Gateway Tripay -->
                        <div class="payment-method">
                            <div class="payment-item">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">
                                        <i class="fas fa-credit-card me-2 text-success"></i>Payment Gateway Tripay
                                    </h6>
                                    <span class="badge bg-warning">Coming Soon</span>
                                </div>
                                
                                <div class="alert alert-warning">
                                    <i class="fas fa-clock me-2"></i>
                                    <strong>Coming Soon:</strong> Fitur payment gateway Tripay sedang dalam pengembangan. 
                                    Silakan gunakan metode transfer manual untuk sementara waktu.
                                </div>
                                
                                <button class="btn btn-outline-success" disabled>
                                    <i class="fas fa-credit-card me-1"></i>Bayar dengan Tripay
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment History -->
        <div class="row">
            <div class="col-12">
                <div class="detail-card">
                    <div class="p-4">
                        <h5 class="mb-4">Riwayat Pembayaran SPMB</h5>
                        @if($registration->payments->where('type', 'spmb_fee')->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Jenis</th>
                                            <th>Jumlah</th>
                                            <th>Metode</th>
                                            <th>Status</th>
                                            <th>Tanggal</th>
                                            <th>Referensi</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($registration->payments->where('type', 'spmb_fee') as $payment)
                                        <tr>
                                            <td>{{ $payment->getTypeName() }}</td>
                                            <td>{{ $payment->getAmountFormattedAttribute() }}</td>
                                            <td>{{ $payment->getPaymentMethodName() }}</td>
                                            <td>
                                                @if($payment->status == 'paid')
                                                    <span class="badge bg-success">Lunas</span>
                                                @elseif($payment->status == 'skipped')
                                                    <span class="badge bg-info">Di-skip</span>
                                                @elseif($payment->status == 'pending')
                                                    <span class="badge bg-warning">Pending</span>
                                                @elseif($payment->status == 'expired')
                                                    <span class="badge bg-danger">Kadaluarsa</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $payment->getStatusName() }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <small class="text-muted">{{ $payment->payment_reference }}</small>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    @if($payment->proof_of_payment)
                                                        <a href="{{ route('manage.spmb.view-payment-proof', $payment->id) }}" 
                                                           target="_blank" class="btn btn-sm btn-outline-info" title="Lihat Bukti Transfer">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    @endif
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="editPayment({{ $payment->id }})" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="deletePayment({{ $payment->id }})" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Belum ada riwayat pembayaran SPMB</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle transfer info
        function toggleTransferInfo() {
            const transferInfo = document.getElementById('transferInfo');
            if (transferInfo.style.display === 'none') {
                transferInfo.style.display = 'block';
            } else {
                transferInfo.style.display = 'none';
            }
        }

        // Edit payment function
        function editPayment(paymentId) {
            // Create modal for editing payment
            const modalHtml = `
                <div class="modal fade" id="editPaymentModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Pembayaran</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form id="editPaymentForm" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="amount" class="form-label">Jumlah (Rp)</label>
                                        <input type="number" class="form-control" id="amount" name="amount" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="payment_method" class="form-label">Metode Pembayaran</label>
                                        <select class="form-select" id="payment_method" name="payment_method" required>
                                            <option value="transfer_manual">Transfer Manual</option>
                                            <option value="tripay">Tripay Gateway</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="pending">Pending</option>
                                            <option value="paid">Lunas</option>
                                            <option value="expired">Kadaluarsa</option>
                                            <option value="failed">Gagal</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `;
            
            // Remove existing modal if any
            const existingModal = document.getElementById('editPaymentModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Add modal to body
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Set form action
            document.getElementById('editPaymentForm').action = '{{ route("manage.spmb.update-payment", ":id") }}'.replace(':id', paymentId);
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('editPaymentModal'));
            modal.show();
        }

        // Delete payment function
        function deletePayment(paymentId) {
            if (confirm('Apakah Anda yakin ingin menghapus pembayaran ini?')) {
                // Create a form to submit delete request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("manage.spmb.delete-payment", ":id") }}'.replace(':id', paymentId);
                
                // Add CSRF token
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);
                
                // Add method override for DELETE
                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';
                form.appendChild(methodField);
                
                document.body.appendChild(form);
                form.submit();
            }
        }

        // File upload preview
        document.getElementById('proof_of_payment').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                console.log('File selected:', file.name, 'Size:', fileSize + ' MB');
                
                // Add visual feedback
                const input = e.target;
                input.style.borderColor = '#008060';
                input.style.backgroundColor = '#f8fff9';
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
