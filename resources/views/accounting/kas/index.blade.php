@extends('layouts.coreui')

@section('title', 'Daftar Kas')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fa fa-wallet me-2"></i>Daftar Kas
                    </h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fa fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="text-muted mb-0">Total Kas: {{ $kasList->count() }}</h6>
                        </div>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreateKas">
                            <i class="fa fa-plus me-2"></i>Tambah Kas
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>No.</th>
                                    <th>Nama Kas</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kasList as $index => $kas)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            {{ $kas->nama_kas }}
                                        </td>
                                        <td>
                                            {{ $kas->deskripsi ?: '-' }}
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-warning" 
                                                        onclick="editKas({{ $kas->id }}, '{{ addslashes($kas->nama_kas) }}', '{{ $kas->jenis_kas }}', '{{ addslashes($kas->deskripsi ?: '') }}')"
                                                        title="Edit">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        onclick="deleteKas({{ $kas->id }}, '{{ addslashes($kas->nama_kas) }}')"
                                                        title="Hapus">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <i class="fa fa-inbox fa-3x mb-3"></i>
                                            <p>Belum ada data kas</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Create Kas -->
<div class="modal fade" id="modalCreateKas" tabindex="-1" aria-labelledby="modalCreateKasLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCreateKasLabel">Membuat Kas Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formCreateKas" action="{{ route('manage.accounting.kas.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nama_kas" class="form-label">
                            Nama Kas <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="nama_kas" name="nama_kas" required>
                    </div>
                    <div class="mb-3">
                        <label for="jenis" class="form-label">
                            Jenis Kas <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="jenis" name="jenis" required>
                            <option value="">Pilih Jenis Kas</option>
                            <option value="cash">Tunai</option>
                            <option value="bank">Bank</option>
                            <option value="e_wallet">E-Wallet</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Keterangan</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
                    </div>
                    <div class="text-muted">
                        <small><span class="text-danger">*</span> Wajib diisi</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                        <i class="fa fa-times me-2"></i>Tutup [Esc]
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-check me-2"></i>Simpan [F5]
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Kas -->
<div class="modal fade" id="modalEditKas" tabindex="-1" aria-labelledby="modalEditKasLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditKasLabel">Edit Kas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditKas" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_nama_kas" class="form-label">
                            Nama Kas <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="edit_nama_kas" name="nama_kas" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_jenis" class="form-label">
                            Jenis Kas <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="edit_jenis" name="jenis" required>
                            <option value="">Pilih Jenis Kas</option>
                            <option value="cash">Tunai</option>
                            <option value="bank">Bank</option>
                            <option value="e_wallet">E-Wallet</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_deskripsi" class="form-label">Keterangan</label>
                        <textarea class="form-control" id="edit_deskripsi" name="deskripsi" rows="3"></textarea>
                    </div>
                    <div class="text-muted">
                        <small><span class="text-danger">*</span> Wajib diisi</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                        <i class="fa fa-times me-2"></i>Tutup [Esc]
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-check me-2"></i>Update [F5]
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Toast notification function
function showToast(type, title, message) {
    console.log('showToast called with:', { type, title, message }); // Debug
    
    const toastId = 'toast-' + Date.now();
    const iconClass = type === 'success' ? 'fa-check-circle text-success' : 
                     type === 'info' ? 'fa-info-circle text-info' : 
                     'fa-exclamation-circle text-danger';
    
    // Force specific colors untuk memastikan tidak ada override
    const headerStyle = type === 'success' 
        ? 'background-color: #198754 !important; color: white !important;' 
        : type === 'info'
        ? 'background-color: #0dcaf0 !important; color: white !important;'
        : 'background-color: #dc3545 !important; color: white !important;';
    
    const toastHtml = `
        <div class="toast" id="${toastId}" role="alert" aria-live="assertive" aria-atomic="true" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <div class="toast-header" style="${headerStyle} border: none;">
                <i class="fa ${iconClass} me-2"></i>
                <strong class="me-auto">${title}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" style="background-color: white !important; color: black !important; font-weight: 500; border: 1px solid #dee2e6; padding: 12px 16px;">
                ${message || 'Pesan tidak tersedia'}
            </div>
        </div>
    `;
    
    // Add toast to body
    document.body.insertAdjacentHTML('beforeend', toastHtml);
    
    // Show toast
    const toast = new bootstrap.Toast(document.getElementById(toastId), {
        delay: 4000
    });
    toast.show();
    
    // Remove toast element after hiding
    document.getElementById(toastId).addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}
function editKas(id, namaKas, jenis, deskripsi) {
    // Set form action untuk update
    document.getElementById('formEditKas').action = '{{ route("manage.accounting.kas.index") }}/' + id;
    
    // Set form values
    document.getElementById('edit_nama_kas').value = namaKas;
    document.getElementById('edit_jenis').value = jenis;
    document.getElementById('edit_deskripsi').value = deskripsi;
    
    // Show modal
    new bootstrap.Modal(document.getElementById('modalEditKas')).show();
}

function deleteKas(id, namaKas) {
    // Create custom confirmation modal
    const modalHtml = `
        <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-danger text-white border-0">
                        <h5 class="modal-title" id="deleteConfirmModalLabel">
                            <i class="fa fa-exclamation-triangle me-2"></i>Konfirmasi Hapus
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <div class="mb-3">
                            <i class="fa fa-trash-alt text-danger" style="font-size: 3rem;"></i>
                        </div>
                        <h6 class="mb-3">Apakah Anda yakin ingin menghapus kas ini?</h6>
                        <p class="text-muted mb-0">
                            <strong>"${namaKas}"</strong><br>
                            <small>Data yang sudah dihapus tidak dapat dikembalikan!</small>
                        </p>
                    </div>
                    <div class="modal-footer border-0 justify-content-center">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                            <i class="fa fa-times me-2"></i>Batal
                        </button>
                        <button type="button" class="btn btn-danger px-4" onclick="confirmDelete(${id})">
                            <i class="fa fa-trash me-2"></i>Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('deleteConfirmModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
}

function confirmDelete(id) {
    // Hide modal first
    bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal')).hide();
    
    // Show loading toast
    showToast('info', 'Memproses...', 'Sedang menghapus data kas...');
    
    // Create form data untuk AJAX
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('_method', 'DELETE');
    
    // Send AJAX request
    fetch('{{ route("manage.accounting.kas.index") }}/' + id, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Show success toast
            showToast('success', 'Berhasil!', data.message || 'Kas berhasil dihapus!');
            
            // Refresh page after a short delay
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showToast('error', 'Gagal!', data.message || 'Gagal menghapus kas');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'Error!', 'Terjadi kesalahan saat menghapus');
    });
}

// Handle form submission untuk Create
document.getElementById('formCreateKas').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Disable button dan show loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Menyimpan...';
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            // Try to get JSON error response
            return response.text().then(text => {
                console.log('Error response text:', text);
                try {
                    const errorData = JSON.parse(text);
                    throw new Error(errorData.message || `Server error: ${response.status}`);
                } catch (e) {
                    // If not JSON, throw generic error
                    throw new Error(`Server error: ${response.status} - ${text.substring(0, 100)}`);
                }
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Show success toast
            showToast('success', 'Berhasil!', data.message);
            
            // Reset form dan close modal
            this.reset();
            bootstrap.Modal.getInstance(document.getElementById('modalCreateKas')).hide();
            
            // Refresh page after a short delay
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showToast('error', 'Gagal!', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'Error!', error.message || 'Terjadi kesalahan saat menyimpan');
    })
    .finally(() => {
        // Re-enable button
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// Handle form submission untuk Edit
document.getElementById('formEditKas').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Disable button dan show loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Mengupdate...';
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            // Try to get JSON error response
            return response.text().then(text => {
                console.log('Error response text:', text);
                try {
                    const errorData = JSON.parse(text);
                    throw new Error(errorData.message || `Server error: ${response.status}`);
                } catch (e) {
                    // If not JSON, throw generic error
                    throw new Error(`Server error: ${response.status} - ${text.substring(0, 100)}`);
                }
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Show success toast
            showToast('success', 'Berhasil!', data.message);
            
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('modalEditKas')).hide();
            
            // Refresh page after a short delay
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showToast('error', 'Gagal!', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'Error!', error.message || 'Terjadi kesalahan saat mengupdate');
    })
    .finally(() => {
        // Re-enable button
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});
</script>
@endpush
