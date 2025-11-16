@extends('layouts.adminty')

@section('title', 'Form Pembayaran Online')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-credit-card me-2"></i>
                            Form Pembayaran Online
                        </h4>
                        <a href="{{ route('online-payment.student-bills', $student->student_id) }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Detail Tagihan -->
                            <div class="card border-primary mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Detail Tagihan</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td width="140"><strong>NIS:</strong></td>
                                                    <td>{{ $student->student_nis }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Nama Siswa:</strong></td>
                                                    <td>{{ $student->student_full_name }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Kelas:</strong></td>
                                                    <td>{{ $student->class->class_name ?? 'Kelas tidak ditemukan' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Jenis Tagihan:</strong></td>
                                                    <td>
                                                        <span class="badge bg-{{ $billType === 'bulanan' ? 'primary' : 'info' }}">
                                                            {{ $billType === 'bulanan' ? 'Bulanan' : 'Bebas' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td width="140"><strong>Pembayaran:</strong></td>
                                                    <td>{{ $bill->pos_name }}</td>
                                                </tr>
                                                @if($billType === 'bulanan')
                                                    @php
                                                        $monthNames = [
                                                            1 => 'Juli', 2 => 'Agustus', 3 => 'September', 4 => 'Oktober',
                                                            5 => 'November', 6 => 'Desember', 7 => 'Januari', 8 => 'Februari',
                                                            9 => 'Maret', 10 => 'April', 11 => 'Mei', 12 => 'Juni'
                                                        ];
                                                        $monthName = $monthNames[$bill->month_month_id] ?? 'Unknown';
                                                    @endphp
                                                    <tr>
                                                        <td><strong>Bulan:</strong></td>
                                                        <td>{{ $monthName }}</td>
                                                    </tr>
                                                @endif
                                                <tr>
                                                    <td><strong>Jumlah Tagihan:</strong></td>
                                                    <td>
                                                        <h5 class="text-primary mb-0">
                                                            Rp {{ number_format($bill->bulan_bill ?? $bill->bebas_bill, 0, ',', '.') }}
                                                        </h5>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Pembayaran -->
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-credit-card me-2"></i>Pilih Metode Pembayaran</h6>
                                </div>
                                <div class="card-body">
                                    <form id="paymentForm">
                                        <input type="hidden" name="student_id" value="{{ $student->student_id }}">
                                        <input type="hidden" name="bill_type" value="{{ $billType }}">
                                        <input type="hidden" name="bill_id" value="{{ $billType === 'bulanan' ? $bill->bulan_id : $bill->bebas_id }}">
                                        <input type="hidden" name="amount" value="{{ $bill->bulan_bill ?? $bill->bebas_bill }}">

                                        <!-- Tipe Pembayaran -->
                                        <div class="mb-4">
                                            <label class="form-label"><strong>Pilih Tipe Pembayaran:</strong></label>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="card payment-type-card" data-type="realtime">
                                                        <div class="card-body text-center">
                                                            <i class="fas fa-bolt fa-3x text-success mb-3"></i>
                                                            <h5>Pembayaran Real-time</h5>
                                                            <p class="text-muted mb-2">Pembayaran langsung melalui payment gateway</p>
                                                            <small class="text-success">
                                                                <i class="fas fa-check-circle me-1"></i>
                                                                Instan & Otomatis
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="card payment-type-card" data-type="manual">
                                                        <div class="card-body text-center">
                                                            <i class="fas fa-upload fa-3x text-primary mb-3"></i>
                                                            <h5>Pembayaran Manual</h5>
                                                            <p class="text-muted mb-2">Upload bukti transfer untuk verifikasi</p>
                                                            <small class="text-primary">
                                                                <i class="fas fa-clock me-1"></i>
                                                                Verifikasi Manual
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="hidden" name="payment_type" id="selectedType" required>
                                        </div>

                                        <!-- Metode Pembayaran (untuk Real-time) -->
                                        <div class="mb-4" id="realtimeMethods" style="display: none;">
                                            <label class="form-label"><strong>Pilih Metode Pembayaran Real-time:</strong></label>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="card payment-method-card" data-method="bank_transfer">
                                                        <div class="card-body text-center">
                                                            <i class="fas fa-university fa-2x text-primary mb-2"></i>
                                                            <h6>Transfer Bank</h6>
                                                            <small class="text-muted">BCA, BNI, Mandiri, BRI</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="card payment-method-card" data-method="credit_card">
                                                        <div class="card-body text-center">
                                                            <i class="fas fa-credit-card fa-2x text-success mb-2"></i>
                                                            <h6>Kartu Kredit</h6>
                                                            <small class="text-muted">Visa, Mastercard, JCB</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="card payment-method-card" data-method="e_wallet">
                                                        <div class="card-body text-center">
                                                            <i class="fas fa-mobile-alt fa-2x text-warning mb-2"></i>
                                                            <h6>E-Wallet</h6>
                                                            <small class="text-muted">OVO, DANA, GoPay, LinkAja</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="hidden" name="payment_method" id="selectedMethod" required>
                                        </div>

                                        <!-- Form Manual Payment -->
                                        <div class="mb-4" id="manualForm" style="display: none;">
                                            <label class="form-label"><strong>Informasi Transfer Manual:</strong></label>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="manualBankName" class="form-label">Bank Pengirim</label>
                                                        <select class="form-select" id="manualBankName" name="manual_bank_name">
                                                            <option value="">Pilih Bank</option>
                                                            <option value="BCA">BCA</option>
                                                            <option value="BNI">BNI</option>
                                                            <option value="Mandiri">Mandiri</option>
                                                            <option value="BRI">BRI</option>
                                                            <option value="CIMB Niaga">CIMB Niaga</option>
                                                            <option value="Bank Jateng">Bank Jateng</option>
                                                            <option value="Lainnya">Lainnya</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="manualAccountNumber" class="form-label">Nomor Rekening Pengirim</label>
                                                        <input type="text" class="form-control" id="manualAccountNumber" name="manual_account_number" placeholder="Masukkan nomor rekening">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="manualAccountName" class="form-label">Nama Pemilik Rekening</label>
                                                        <input type="text" class="form-control" id="manualAccountName" name="manual_account_name" placeholder="Nama sesuai buku tabungan">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="manualTransferAmount" class="form-label">Jumlah Transfer</label>
                                                        <input type="number" class="form-control" id="manualTransferAmount" name="manual_transfer_amount" value="{{ $bill->bulan_bill ?? $bill->bebas_bill }}" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="manualProofFile" class="form-label">Upload Bukti Transfer</label>
                                                <input type="file" class="form-control" id="manualProofFile" name="manual_proof_file" accept="image/*,.pdf" required>
                                                <div class="form-text">
                                                    Format: JPG, PNG, PDF. Maksimal 2MB
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="manualNotes" class="form-label">Catatan (Opsional)</label>
                                                <textarea class="form-control" id="manualNotes" name="manual_notes" rows="3" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                                            </div>
                                        </div>

                                        <!-- Konfirmasi Pembayaran -->
                                        <div class="alert alert-info">
                                            <h6><i class="fas fa-info-circle me-2"></i>Konfirmasi Pembayaran</h6>
                                            <p class="mb-2">Pastikan data pembayaran sudah benar sebelum melanjutkan:</p>
                                            <ul class="mb-0">
                                                <li>Nama siswa: <strong>{{ $student->student_full_name }}</strong></li>
                                                <li>Jenis pembayaran: <strong>{{ $bill->pos_name }}</strong></li>
                                                <li>Jumlah: <strong>Rp {{ number_format($bill->bulan_bill ?? $bill->bebas_bill, 0, ',', '.') }}</strong></li>
                                                <li>Tipe pembayaran: <strong id="selectedTypeText">Pilih tipe pembayaran</strong></li>
                                                <li id="methodInfo" style="display: none;">Metode pembayaran: <strong id="selectedMethodText">-</strong></li>
                                            </ul>
                                        </div>

                                        <!-- Tombol Submit -->
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                                                <i class="fas fa-credit-card me-2"></i>
                                                Lanjutkan ke Pembayaran
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <!-- Ringkasan Pembayaran -->
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-receipt me-2"></i>Ringkasan Pembayaran</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-6">Tagihan:</div>
                                        <div class="col-6 text-end">Rp {{ number_format($bill->bulan_bill ?? $bill->bebas_bill, 0, ',', '.') }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-6">Biaya Admin:</div>
                                        <div class="col-6 text-end">Rp 0</div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-6"><strong>Total:</strong></div>
                                        <div class="col-6 text-end"><strong>Rp {{ number_format($bill->bulan_bill ?? $bill->bebas_bill, 0, ',', '.') }}</strong></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Informasi Keamanan -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Keamanan</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            Data terenkripsi SSL
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            Transaksi aman
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            Bukti pembayaran otomatis
                                        </li>
                                        <li class="mb-0">
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            Support 24/7
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mb-0">Memproses pembayaran...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Handle payment type selection
    $('.payment-type-card').click(function() {
        $('.payment-type-card').removeClass('border-primary');
        $(this).addClass('border-primary');
        
        const type = $(this).data('type');
        $('#selectedType').val(type);
        
        // Update selected type text
        const typeTexts = {
            'realtime': 'Pembayaran Real-time',
            'manual': 'Pembayaran Manual'
        };
        $('#selectedTypeText').text(typeTexts[type]);
        
        // Show/hide appropriate sections
        if (type === 'realtime') {
            $('#realtimeMethods').show();
            $('#manualForm').hide();
            $('#methodInfo').show();
            // Reset manual form
            $('#manualForm input, #manualForm select, #manualForm textarea').val('');
        } else if (type === 'manual') {
            $('#realtimeMethods').hide();
            $('#manualForm').show();
            $('#methodInfo').hide();
            // Reset realtime method
            $('#selectedMethod').val('');
            $('.payment-method-card').removeClass('border-primary');
        }
        
        // Enable submit button
        $('#submitBtn').prop('disabled', false);
    });

    // Handle payment method selection (for realtime)
    $('.payment-method-card').click(function() {
        $('.payment-method-card').removeClass('border-primary');
        $(this).addClass('border-primary');
        
        const method = $(this).data('method');
        $('#selectedMethod').val(method);
        
        // Update selected method text
        const methodTexts = {
            'bank_transfer': 'Transfer Bank',
            'credit_card': 'Kartu Kredit',
            'e_wallet': 'E-Wallet'
        };
        $('#selectedMethodText').text(methodTexts[method]);
    });

    // Handle form submission
    $('#paymentForm').on('submit', function(e) {
        e.preventDefault();
        
        const selectedType = $('#selectedType').val();
        
        if (!selectedType) {
            Swal.fire({
                icon: 'warning',
                title: 'Pilih Tipe Pembayaran',
                text: 'Silakan pilih tipe pembayaran terlebih dahulu'
            });
            return;
        }

        if (selectedType === 'realtime') {
            if (!$('#selectedMethod').val()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih Metode Pembayaran',
                    text: 'Silakan pilih metode pembayaran real-time'
                });
                return;
            }
        } else if (selectedType === 'manual') {
            // Validate manual form
            if (!$('#manualBankName').val()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Bank Belum Dipilih',
                    text: 'Silakan pilih bank pengirim'
                });
                return;
            }
            
            if (!$('#manualAccountNumber').val()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Nomor Rekening Kosong',
                    text: 'Silakan masukkan nomor rekening pengirim'
                });
                return;
            }
            
            if (!$('#manualAccountName').val()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Nama Pemilik Rekening Kosong',
                    text: 'Silakan masukkan nama pemilik rekening'
                });
                return;
            }
            
            if (!$('#manualProofFile').val()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Bukti Transfer Belum Diupload',
                    text: 'Silakan upload bukti transfer'
                });
                return;
            }
        }

        // Show loading modal
        $('#loadingModal').modal('show');
        
        // Create FormData for file upload
        const formData = new FormData(this);
        
        // Submit form via AJAX
        $.ajax({
            url: '{{ route("online-payment.process") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    if (selectedType === 'realtime') {
                        // Process Midtrans payment
                        if (response.snap_token) {
                            // Open Midtrans Snap
                            snap.pay(response.snap_token, {
                                onSuccess: function(result) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Pembayaran Berhasil!',
                                        text: 'Pembayaran telah berhasil diproses',
                                        confirmButtonText: 'OK'
                                    }).then(() => {
                                        window.location.href = '{{ route("online-payment.history") }}';
                                    });
                                },
                                onPending: function(result) {
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Pembayaran Pending',
                                        text: 'Silakan selesaikan pembayaran',
                                        confirmButtonText: 'OK'
                                    }).then(() => {
                                        window.location.href = '{{ route("online-payment.history") }}';
                                    });
                                },
                                onError: function(result) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Pembayaran Gagal',
                                        text: 'Silakan coba lagi',
                                        confirmButtonText: 'OK'
                                    });
                                },
                                onClose: function() {
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Pembayaran Dibatalkan',
                                        text: 'Pembayaran telah dibatalkan',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'success',
                                title: 'Pembayaran Real-time Diproses!',
                                text: 'Nomor pembayaran: ' + response.order_id,
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.href = '{{ route("online-payment.history") }}';
                            });
                        }
                    } else {
                        // Manual payment success
                        Swal.fire({
                            icon: 'success',
                            title: 'Pembayaran Manual Berhasil Diajukan!',
                            text: 'Nomor pembayaran: ' + response.payment_number + '\nStatus: Menunggu verifikasi admin',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = '{{ route("online-payment.history") }}';
                        });
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan saat memproses pembayaran'
                });
            },
            complete: function() {
                $('#loadingModal').modal('hide');
            }
        });
    });

    // File size validation
    $('#manualProofFile').on('change', function() {
        const file = this.files[0];
        const maxSize = 2 * 1024 * 1024; // 2MB
        
        if (file && file.size > maxSize) {
            Swal.fire({
                icon: 'error',
                title: 'File Terlalu Besar',
                text: 'Ukuran file maksimal 2MB'
            });
            this.value = '';
        }
    });
});
</script>

<style>
.payment-method-card {
    cursor: pointer;
    transition: all 0.3s ease;
}

.payment-method-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.payment-method-card.border-primary,
.payment-type-card.border-primary {
    border-color: #007bff !important;
    background-color: #f8f9fa;
}

.payment-type-card {
    cursor: pointer;
    transition: all 0.3s ease;
}

.payment-type-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.payment-type-card.border-primary {
    border-color: #007bff !important;
    background-color: #f8f9fa;
}
</style>
@endpush 