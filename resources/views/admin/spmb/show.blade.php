@extends('layouts.adminty')

@section('title', 'Detail Pendaftaran SPMB')

@push('styles')
<style>
    .detail-card {
        background: #ffffff;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .document-item {
        background: rgba(0, 128, 96, 0.05);
        border: 2px solid rgba(0, 128, 96, 0.1);
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #008060 0%, #006d52 100%);
        border: none;
        color: white;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #006d52 0%, #004d3a 100%);
        color: white;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="font-size: 1.5rem;">
                <i class="fas fa-graduation-cap me-2"></i>Detail Pendaftaran #{{ $registration->id }}
            </h4>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">Informasi lengkap pendaftaran SPMB</p>
        </div>
        <div>
            <a href="{{ route('manage.spmb.registrations') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Kembali ke Daftar
            </a>
            <a href="{{ route('manage.spmb.edit', $registration->id) }}" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i>Edit Formulir
            </a>
        </div>
    </div>

    <!-- Registration Info -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="detail-card">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Informasi Pendaftar</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%"><strong>Nomor Pendaftaran:</strong></td>
                                <td>
                                    @if($registration->nomor_pendaftaran)
                                        <span class="badge bg-info">{{ $registration->nomor_pendaftaran }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Nama:</strong></td>
                                <td>{{ $registration->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>No. HP:</strong></td>
                                <td>{{ $registration->phone }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status Pendaftaran:</strong></td>
                                <td>{!! $registration->getStatusPendaftaranBadge() !!}</td>
                            </tr>
                            <tr>
                                <td><strong>Langkah:</strong></td>
                                <td>
                                    <span class="badge bg-primary">{{ $registration->step }}</span>
                                    <small class="text-muted ms-2">{{ $registration->getStepName() }}</small>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Daftar:</strong></td>
                                <td>{{ $registration->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Status Pembayaran</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%"><strong>Biaya Pendaftaran:</strong></td>
                                <td>
                                    @if($registration->registration_fee_paid)
                                        <span class="badge bg-success">Lunas</span>
                                    @else
                                        <span class="badge bg-danger">Belum</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Biaya SPMB:</strong></td>
                                <td>
                                    @if($registration->spmb_fee_paid)
                                        <span class="badge bg-success">Lunas</span>
                                    @else
                                        <span class="badge bg-danger">Belum</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($registration->form_data)
                <div class="mt-4">
                    <h6 class="text-muted mb-3">Data Formulir</h6>
                    <div class="row">
                        @php
                            $formData = $registration->form_data ?? [];
                        @endphp
                        @foreach($formData as $key => $value)
                        <div class="col-md-6 mb-2">
                            <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                            <span class="ms-2">
                                @if(is_array($value))
                                    {{ implode(', ', $value) }}
                                @else
                                    {{ $value }}
                                @endif
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Documents -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="detail-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Dokumen</h5>
                    <a href="{{ route('manage.spmb.edit-documents', $registration->id) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-upload me-1"></i>Edit Upload Dokumen
                    </a>
                </div>
                @if($registration->documents->count() > 0)
                    <div class="row">
                        @foreach($registration->documents as $document)
                        <div class="col-md-6 mb-3">
                            <div class="document-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $document->getDocumentTypeName() }}</h6>
                                        <small class="text-muted">{{ $document->file_name }}</small>
                                        <br>
                                        <small class="text-muted">{{ $document->getFileSizeHumanAttribute() }}</small>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('manage.spmb.view-document', $document->id) }}" 
                                           target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('manage.spmb.download-document', $document->id) }}" 
                                           class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Belum ada dokumen yang diupload</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Payments -->
    <div class="row">
        <div class="col-12">
            <div class="detail-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Riwayat Pembayaran</h5>
                    <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#paymentGuideModal">
                        <i class="fas fa-info-circle me-1"></i>Cara Cek Bukti
                    </button>
                </div>
                @if($registration->payments->count() > 0)
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
                                @foreach($registration->payments as $payment)
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
                                            @if($payment->payment_method === 'transfer_manual')
                                                <button type="button" class="btn btn-sm btn-outline-info" 
                                                        onclick="showPaymentProofModal({{ $payment->id }})" title="Lihat Bukti Transfer">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="editPayment({{ $payment->id }})" title="Edit Tarif">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="{{ route('manage.spmb.print-invoice', $payment->id) }}" 
                                               target="_blank" class="btn btn-sm btn-outline-info" title="Cetak Invoice">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="deletePayment({{ $payment->id }}, '{{ $payment->getTypeName() }}')" title="Hapus">
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
                        <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Belum ada riwayat pembayaran</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Payment Guide Modal -->
<div class="modal fade" id="paymentGuideModal" tabindex="-1" role="dialog" aria-labelledby="paymentGuideModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="paymentGuideModalLabel">
                    <i class="fas fa-info-circle me-2"></i>Panduan Verifikasi Bukti Pembayaran
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-eye me-2"></i>Cara Melihat Bukti Transfer
                        </h6>
                        <ol class="list-group list-group-numbered">
                            <li class="list-group-item border-0 px-0">
                                <strong>Klik tombol mata (👁️)</strong> pada kolom "Aksi" untuk melihat bukti transfer
                            </li>
                            <li class="list-group-item border-0 px-0">
                                <strong>Bukti akan terbuka</strong> di tab baru untuk memudahkan pemeriksaan
                            </li>
                            <li class="list-group-item border-0 px-0">
                                <strong>Periksa detail transfer:</strong>
                                <ul class="mt-2">
                                    <li>Nomor rekening tujuan</li>
                                    <li>Jumlah transfer</li>
                                    <li>Tanggal dan waktu transfer</li>
                                    <li>Nama pengirim</li>
                                </ul>
                            </li>
                        </ol>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-success mb-3">
                            <i class="fas fa-check-circle me-2"></i>Cara Verifikasi Pembayaran
                        </h6>
                        <ol class="list-group list-group-numbered">
                            <li class="list-group-item border-0 px-0">
                                <strong>Periksa bukti transfer</strong> dengan detail yang tercantum
                            </li>
                            <li class="list-group-item border-0 px-0">
                                <strong>Klik tombol centang (✓)</strong> untuk verifikasi jika bukti valid
                            </li>
                            <li class="list-group-item border-0 px-0">
                                <strong>Klik tombol X</strong> untuk menolak jika bukti tidak valid
                            </li>
                            <li class="list-group-item border-0 px-0">
                                <strong>Status pembayaran</strong> akan berubah otomatis setelah verifikasi
                            </li>
                        </ol>
                    </div>
                </div>
                
                <hr>
                
                <div class="alert alert-warning">
                    <h6 class="alert-heading">
                        <i class="fas fa-exclamation-triangle me-2"></i>Tips Verifikasi
                    </h6>
                    <ul class="mb-0">
                        <li>Pastikan jumlah transfer sesuai dengan yang tercantum</li>
                        <li>Periksa nomor rekening tujuan apakah sudah benar</li>
                        <li>Verifikasi tanggal transfer tidak lebih dari 7 hari</li>
                        <li>Jika ada keraguan, hubungi siswa untuk konfirmasi</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Proof Modal -->
<div class="modal fade" id="paymentProofModal" tabindex="-1" role="dialog" aria-labelledby="paymentProofModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="paymentProofModalLabel">
                    <i class="fas fa-receipt me-2"></i>Bukti Pembayaran
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="paymentProofContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2">Memuat bukti pembayaran...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Edit payment function
    function editPayment(paymentId) {
        const modalHtml = `
            <div class="modal fade" id="editPaymentModal" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">Edit Tarif Pembayaran</h5>
                            <button type="button" class="close text-white" data-dismiss="modal">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form id="editPaymentForm" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="amount">Jumlah (Rp)</label>
                                    <input type="number" class="form-control" id="amount" name="amount" required>
                                </div>
                                <div class="form-group">
                                    <label for="payment_method">Metode Pembayaran</label>
                                    <select class="form-control" id="payment_method" name="payment_method" required>
                                        <option value="cash">Tunai</option>
                                        <option value="transfer">Transfer Bank</option>
                                        <option value="online">Online Payment</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-control" id="status" name="status" required>
                                        <option value="pending">Pending</option>
                                        <option value="paid">Lunas</option>
                                        <option value="expired">Kadaluarsa</option>
                                        <option value="failed">Gagal</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;
        
        const existingModal = document.getElementById('editPaymentModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        document.getElementById('editPaymentForm').action = '{{ route("manage.spmb.update-payment", ":id") }}'.replace(':id', paymentId);
        
        $('#editPaymentModal').modal('show');
    }

    // Delete payment function
    function deletePayment(paymentId, paymentName) {
        if (confirm(`Apakah Anda yakin ingin menghapus pembayaran "${paymentName}"?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("manage.spmb.delete-payment", ":id") }}'.replace(':id', paymentId);
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            form.appendChild(methodField);
            
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Show payment proof modal function
    function showPaymentProofModal(paymentId) {
        $('#paymentProofModal').modal('show');
        loadPaymentProof(paymentId);
    }
    
    // Load payment proof content
    function loadPaymentProof(paymentId) {
        const contentDiv = document.getElementById('paymentProofContent');
        
        contentDiv.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-2">Memuat bukti pembayaran...</p>
            </div>
        `;
        
        const url = `{{ route('test-payment-proof-no-auth', ':id') }}`.replace(':id', paymentId);
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                contentDiv.innerHTML = `
                    ${data.payment.proof_of_payment && data.payment.proof_of_payment !== null && data.payment.proof_of_payment !== '' ? `
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-primary text-white text-center">
                                    <h6 class="mb-0">
                                        <i class="fas fa-file-image me-2"></i>Bukti Transfer
                                        <span class="badge bg-warning text-dark ms-2">Rp ${data.payment.amount.toLocaleString('id-ID')}</span>
                                    </h6>
                                </div>
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <img src="/storage/${data.payment.proof_of_payment}" 
                                             class="img-fluid rounded shadow" 
                                             style="max-height: 500px; max-width: 100%; border: 2px solid #dee2e6;" 
                                             alt="Bukti Transfer"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                        <div class="alert alert-info" style="display: none;">
                                            <i class="fas fa-info-circle me-2"></i>
                                            File tidak dapat ditampilkan sebagai gambar. 
                                            <a href="/storage/${data.payment.proof_of_payment}" target="_blank" class="btn btn-sm btn-primary ms-2">
                                                <i class="fas fa-external-link-alt me-1"></i>Buka File
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-center gap-3">
                                        <button type="button" class="btn btn-success btn-lg" onclick="verifyPayment(${data.payment.id})">
                                            <i class="fas fa-check me-2"></i>Terima
                                        </button>
                                        <button type="button" class="btn btn-danger btn-lg" onclick="rejectPayment(${data.payment.id})">
                                            <i class="fas fa-times me-2"></i>Tolak
                                        </button>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Referensi: ${data.payment.payment_reference} | 
                                            ${new Date(data.payment.created_at).toLocaleDateString('id-ID')}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    ` : `
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-warning text-dark text-center">
                                    <h6 class="mb-0">
                                        <i class="fas fa-exclamation-triangle me-2"></i>Belum Ada Bukti Transfer
                                    </h6>
                                </div>
                                <div class="card-body text-center">
                                    <div class="mb-4">
                                        <i class="fas fa-file-upload fa-3x text-muted"></i>
                                    </div>
                                    <p class="text-muted mb-3">
                                        Calon peserta didik belum mengupload bukti transfer.
                                    </p>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Silakan hubungi calon peserta didik untuk mengupload bukti transfer.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    `}
                `;
            } else {
                contentDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Gagal memuat bukti pembayaran: ${data.message || 'Terjadi kesalahan'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading payment proof:', error);
            contentDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Terjadi kesalahan saat memuat bukti pembayaran: ${error.message}
                    <br><small>Silakan coba lagi atau hubungi administrator.</small>
                </div>
            `;
        });
    }

    // Verify payment function
    function verifyPayment(paymentId) {
        if (confirm('Apakah Anda yakin ingin memverifikasi pembayaran ini?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("manage.spmb.verify-payment", ":id") }}'.replace(':id', paymentId);
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Reject payment function
    function rejectPayment(paymentId) {
        const reason = prompt('Masukkan alasan penolakan:');
        if (reason && reason.trim() !== '') {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("manage.spmb.reject-payment", ":id") }}'.replace(':id', paymentId);
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            const reasonField = document.createElement('input');
            reasonField.type = 'hidden';
            reasonField.name = 'rejection_reason';
            reasonField.value = reason.trim();
            form.appendChild(reasonField);
            
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
@endpush
