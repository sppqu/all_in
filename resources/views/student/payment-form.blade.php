@extends('layouts.student')

@section('title', 'Form Pembayaran')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Bill Information -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-file-invoice me-2"></i>Informasi Tagihan
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Jenis Tagihan:</strong></td>
                            <td>
                                <span class="badge bg-{{ $billType === 'bulanan' ? 'primary' : 'info' }}">
                                    {{ ucfirst($billType) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Nama Tagihan:</strong></td>
                            <td>{{ $bill->pos_name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Jumlah:</strong></td>
                            <td>
                                <strong class="text-primary">
                                    Rp {{ number_format($billType === 'bulanan' ? $bill->bulan_bill : $bill->bebas_bill, 0, ',', '.') }}
                                </strong>
                            </td>
                        </tr>
                        @if($bill->period_start && $bill->period_end)
                        <tr>
                            <td><strong>Periode:</strong></td>
                            <td>
                                {{ \Carbon\Carbon::parse($bill->period_start)->format('M Y') }} - 
                                {{ \Carbon\Carbon::parse($bill->period_end)->format('M Y') }}
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Student Information -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user-graduate me-2"></i>Informasi Siswa
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>NIS:</strong></td>
                            <td>{{ $student->student_nis }}</td>
                        </tr>
                        <tr>
                            <td><strong>Nama:</strong></td>
                            <td>{{ $student->student_full_name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Kelas:</strong></td>
                            <td>{{ $student->class ? $student->class->class_name : '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Payment Form -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>Form Pembayaran
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('student.payment.process') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="student_id" value="{{ $student->student_id }}">
                        <input type="hidden" name="bill_type" value="{{ $billType }}">
                        <input type="hidden" name="bill_id" value="{{ $billType === 'bulanan' ? $bill->bulan_id : $bill->bebas_id }}">
                        <input type="hidden" name="amount" value="{{ $billType === 'bulanan' ? $bill->bulan_bill : $bill->bebas_bill }}">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_type" class="form-label">
                                        <i class="fas fa-money-bill me-2"></i>Tipe Pembayaran
                                    </label>
                                    <select class="form-select @error('payment_type') is-invalid @enderror" 
                                            id="payment_type" name="payment_type" required>
                                        <option value="">Pilih tipe pembayaran</option>
                                        <option value="realtime">Pembayaran Real-time (Tripay)</option>
                                        <option value="manual">Transfer Manual</option>
                                    </select>
                                    @error('payment_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method Selection (for realtime) -->
                        <div id="payment_method_section" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="payment_method" class="form-label">
                                            <i class="fas fa-credit-card me-2"></i>Metode Pembayaran
                                        </label>
                                        <select class="form-select @error('payment_method') is-invalid @enderror" 
                                                id="payment_method" name="payment_method">
                                            <option value="">Pilih metode pembayaran</option>
                                            <option value="BRIVA">BRI Virtual Account</option>
                                            <option value="MANDIRI">Mandiri Virtual Account</option>
                                            <option value="BNI">BNI Virtual Account</option>
                                            <option value="BCA">BCA Virtual Account</option>
                                            <option value="OVO">OVO</option>
                                            <option value="DANA">DANA</option>
                                            <option value="SHOPEEPAY">ShopeePay</option>
                                            <option value="GOPAY">GoPay</option>
                                            <option value="LINKAJA">LinkAja</option>
                                            <option value="QRIS">QRIS</option>
                                        </select>
                                        @error('payment_method')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_description" class="form-label">
                                        <i class="fas fa-comment me-2"></i>Keterangan (Opsional)
                                    </label>
                                    <textarea class="form-control @error('payment_description') is-invalid @enderror" 
                                              id="payment_description" name="description" rows="3"
                                              placeholder="Tambahkan keterangan pembayaran..."></textarea>
                                    @error('payment_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Manual Payment Section -->
                        <div id="manual_payment_section" style="display: none;">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle me-2"></i>Instruksi Transfer Manual</h6>
                                <p class="mb-2">Silakan transfer ke rekening berikut:</p>
                                <ul class="mb-2">
                                    <li><strong>Bank:</strong> {{ $schoolBank->nama_bank ?? 'Belum diatur' }}</li>
                                    <li><strong>No. Rekening:</strong> {{ $schoolBank->norek_bank ?? 'Belum diatur' }}</li>
                                    <li><strong>Atas Nama:</strong> {{ $schoolBank->nama_rekening ?? 'Belum diatur' }}</li>
                                    <li><strong>Jumlah:</strong> Rp {{ number_format($billType === 'bulanan' ? $bill->bulan_bill : $bill->bebas_bill, 0, ',', '.') }}</li>
                                </ul>
                                <p class="mb-0">Setelah transfer, upload bukti pembayaran di bawah ini.</p>
                            </div>

                            <div class="mb-3">
                                <label for="manual_proof_file" class="form-label">
                                    <i class="fas fa-upload me-2"></i>Upload Bukti Pembayaran
                                </label>
                                <input type="file" class="form-control @error('manual_proof_file') is-invalid @enderror" 
                                       id="manual_proof_file" name="manual_proof_file" 
                                       accept="image/*,.pdf">
                                <div class="form-text">Format: JPG, JPEG, PNG, PDF (Maks. 2MB)</div>
                                @error('manual_proof_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>



                            <div class="mb-3">
                                <label for="manual_notes" class="form-label">Catatan Tambahan</label>
                                <textarea class="form-control" id="manual_notes" name="manual_notes" rows="2" placeholder="Catatan tambahan (opsional)"></textarea>
                            </div>
                        </div>

                        <!-- Realtime Payment Section -->
                        <div id="realtime_payment_section" style="display: none;">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle me-2"></i>Pembayaran Real-time via Tripay</h6>
                                <p class="mb-2">Anda akan diarahkan ke halaman pembayaran Tripay untuk menyelesaikan transaksi.</p>
                                <ul class="mb-0">
                                    <li>Pembayaran aman dan terpercaya</li>
                                    <li>Mendukung berbagai metode pembayaran</li>
                                    <li>Konfirmasi otomatis setelah pembayaran</li>
                                </ul>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('student.bills') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Kembali
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-paper-plane me-2"></i>Proses Pembayaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentType = document.getElementById('payment_type');
    const paymentMethodSection = document.getElementById('payment_method_section');
    const manualSection = document.getElementById('manual_payment_section');
    const realtimeSection = document.getElementById('realtime_payment_section');
    const submitBtn = document.getElementById('submitBtn');

    paymentType.addEventListener('change', function() {
        // Hide all sections first
        paymentMethodSection.style.display = 'none';
        manualSection.style.display = 'none';
        realtimeSection.style.display = 'none';

        if (this.value === 'realtime') {
            paymentMethodSection.style.display = 'block';
            realtimeSection.style.display = 'block';
        } else if (this.value === 'manual') {
            manualSection.style.display = 'block';
        }
    });

    // Handle form submission for realtime payment
    document.querySelector('form').addEventListener('submit', function(e) {
        const paymentType = document.getElementById('payment_type').value;
        
        if (paymentType === 'realtime') {
            e.preventDefault();
            
            const paymentMethod = document.getElementById('payment_method').value;
            if (!paymentMethod) {
                alert('Silakan pilih metode pembayaran');
                return;
            }

            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';

            // Submit form via AJAX
            const formData = new FormData(this);
            
            // Log form data for debugging
            console.log('Form data being sent:');
            for (let [key, value] of formData.entries()) {
                console.log(key + ': ' + value);
            }
            
            fetch('{{ route("student.payment.process") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                console.log('Raw response:', response);
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                return response.json();
            })
            .then(data => {
                console.log('Payment response:', data);
                console.log('Success:', data.success);
                console.log('Redirect URL:', data.redirect_url);
                console.log('Message:', data.message);
                
                if (data.success) {
                    // Redirect ke Tripay checkout jika ada redirect_url
                    if (data.redirect_url) {
                        window.location.href = data.redirect_url;
                    } else {
                        alert('Pembayaran gagal diproses. Silakan coba lagi atau hubungi admin.');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Proses Pembayaran';
                    }
                } else {
                    console.error('Payment error:', data.message);
                    alert('Error: ' + data.message);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Proses Pembayaran';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memproses pembayaran');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Proses Pembayaran';
            });
        }
    });
});
</script>
@endpush 