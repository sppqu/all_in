@extends('layouts.adminty')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Transfer SPMB ke Students</h4>
        <a href="{{ route('manage.spmb.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Kembali ke Dashboard
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($registrations->count() > 0)
        <form action="{{ route('manage.spmb.transfer-to-students.process') }}" method="POST" id="transferForm">
            @csrf
            <div class="row">
                <!-- Left Column - Registrations List -->
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-users me-2"></i>Pendaftar yang Diterima
                                <span class="badge bg-success ms-2">{{ $registrations->count() }} pendaftar</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th width="50">
                                                <input type="checkbox" id="selectAll" class="form-check-input">
                                            </th>
                                            <th>No. Pendaftaran</th>
                                            <th>Nama</th>
                                            <th>No. HP</th>
                                            <th>Kejuruan</th>
                                            <th>Tanggal Daftar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($registrations as $registration)
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="registration_ids[]" value="{{ $registration->id }}" 
                                                       class="form-check-input registration-checkbox">
                                            </td>
                                            <td>
                                                @if($registration->nomor_pendaftaran)
                                                    <span class="badge bg-info">{{ $registration->nomor_pendaftaran }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $registration->name }}</td>
                                            <td>{{ $registration->phone }}</td>
                                            <td>
                                                @if($registration->kejuruan)
                                                    <span class="badge bg-secondary">{{ $registration->kejuruan->nama_kejuruan }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $registration->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Transfer Settings -->
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-cog me-2"></i>Pengaturan Transfer
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="class_id" class="form-label">Kelas Tujuan <span class="text-danger">*</span></label>
                                <select class="form-select @error('class_id') is-invalid @enderror" id="class_id" name="class_id" required>
                                    <option value="">Pilih Kelas</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->class_id }}" {{ old('class_id') == $class->class_id ? 'selected' : '' }}>
                                            {{ $class->class_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('class_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="period_id" class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                                <select class="form-select @error('period_id') is-invalid @enderror" id="period_id" name="period_id" required>
                                    <option value="">Pilih Tahun Ajaran</option>
                                    @foreach($periods as $period)
                                        <option value="{{ $period->period_id }}" {{ old('period_id') == $period->period_id ? 'selected' : '' }}>
                                            {{ $period->period_name }} ({{ $period->status_text }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('period_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="create_spmb_bill" name="create_spmb_bill" value="1" checked>
                                    <label class="form-check-label" for="create_spmb_bill">
                                        <strong>Buat Tagihan Biaya SPMB</strong>
                                    </label>
                                </div>
                                <small class="form-text text-muted d-block mt-1">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Jika dicentang, tagihan biaya SPMB akan otomatis dibuat di sistem pembayaran (jenis BEBAS) untuk siswa yang belum membayar biaya SPMB.
                                </small>
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Informasi:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>NIS akan otomatis digenerate</li>
                                    <li>Data dari form SPMB akan dipindahkan</li>
                                    <li>Status siswa akan aktif</li>
                                    <li>Tagihan SPMB akan masuk ke jenis <strong>BEBAS</strong> (jika opsi diaktifkan)</li>
                                </ul>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary" id="transferBtn" disabled>
                                    <i class="fas fa-exchange-alt me-1"></i>Transfer ke Students
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Tidak Ada Pendaftar yang Dapat Ditransfer</h5>
                <p class="text-muted">Semua pendaftar yang diterima sudah ditransfer atau belum ada yang diterima.</p>
                <a href="{{ route('manage.spmb.index') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-1"></i>Kembali ke Dashboard
                </a>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const registrationCheckboxes = document.querySelectorAll('.registration-checkbox');
    const transferBtn = document.getElementById('transferBtn');
    const classSelect = document.getElementById('class_id');
    const periodSelect = document.getElementById('period_id');

    // Select all functionality
    selectAllCheckbox.addEventListener('change', function() {
        registrationCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateTransferButton();
    });

    // Individual checkbox change
    registrationCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectAllCheckbox();
            updateTransferButton();
        });
    });

    // Form validation
    [classSelect, periodSelect].forEach(select => {
        select.addEventListener('change', updateTransferButton);
    });

    function updateSelectAllCheckbox() {
        const checkedCount = document.querySelectorAll('.registration-checkbox:checked').length;
        const totalCount = registrationCheckboxes.length;
        
        selectAllCheckbox.checked = checkedCount === totalCount;
        selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < totalCount;
    }

    function updateTransferButton() {
        const selectedCount = document.querySelectorAll('.registration-checkbox:checked').length;
        const classSelected = classSelect.value !== '';
        const periodSelected = periodSelect.value !== '';
        
        if (selectedCount > 0 && classSelected && periodSelected) {
            transferBtn.disabled = false;
            transferBtn.innerHTML = `<i class="fas fa-exchange-alt me-1"></i>Transfer ${selectedCount} Siswa`;
        } else {
            transferBtn.disabled = true;
            transferBtn.innerHTML = '<i class="fas fa-exchange-alt me-1"></i>Transfer ke Students';
        }
    }

    // Form submission
    document.getElementById('transferForm').addEventListener('submit', function(e) {
        const selectedCount = document.querySelectorAll('.registration-checkbox:checked').length;
        
        if (selectedCount === 0) {
            alert('Pilih minimal satu pendaftar untuk ditransfer.');
            e.preventDefault();
            return false;
        }

        const confirmMessage = `Apakah Anda yakin ingin mentransfer ${selectedCount} pendaftar ke tabel students?`;
        if (!confirm(confirmMessage)) {
            e.preventDefault();
            return false;
        }
    });
});
</script>
@endpush
