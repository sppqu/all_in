@extends('layouts.adminty')

@section('title', 'Daftar Kas')

@push('styles')
<style>
    /* Switch styling - menggunakan styling dari template */
    .form-check.form-switch {
        padding-left: 0;
    }
    
    .form-check-input.status-switch {
        width: 3em;
        height: 1.5em;
        cursor: pointer;
        margin-left: 0;
    }
</style>
@endpush

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
                        <button type="button" class="btn btn-primary" onclick="openCreateKasModal()">
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
                                    <th>Saldo</th>
                                    <th>Status</th>
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
                                        <td class="text-end">
                                            <strong class="{{ $kas->saldo >= 0 ? 'text-success' : 'text-danger' }}">
                                                Rp {{ number_format($kas->saldo ?? 0, 0, ',', '.') }}
                                            </strong>
                                        </td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input status-switch" 
                                                       type="checkbox" 
                                                       id="status_{{ $kas->id }}" 
                                                       data-kas-id="{{ $kas->id }}"
                                                       {{ $kas->is_active ? 'checked' : '' }}
                                                       onchange="toggleKasStatus({{ $kas->id }}, this.checked)">
                                                <label class="form-check-label" for="status_{{ $kas->id }}"></label>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-warning" 
                                                        onclick="editKas({{ $kas->id }}, '{{ addslashes($kas->nama_kas) }}', '{{ $kas->jenis_kas }}', '{{ addslashes($kas->deskripsi ?: '') }}', {{ $kas->is_active ? 'true' : 'false' }})"
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
                                        <td colspan="6" class="text-center text-muted py-4">
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
                <button type="button" class="close text-white" onclick="closeCreateKasModal()" aria-label="Close" style="opacity: 1; font-size: 1.5rem; padding: 0; margin-left: 0.5rem; line-height: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
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
                        <select class="form-control select-primary" id="jenis" name="jenis" required>
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
                    <button type="button" class="btn btn-danger" onclick="closeCreateKasModal()">
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
                <button type="button" class="close text-white" onclick="closeEditKasModal()" aria-label="Close" style="opacity: 1; font-size: 1.5rem; padding: 0; margin-left: 0.5rem; line-height: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
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
                        <select class="form-control select-primary" id="edit_jenis" name="jenis" required>
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
                    <button type="button" class="btn btn-danger" onclick="closeEditKasModal()">
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
// showToast function is now global from adminty.blade.php layout
function openCreateKasModal() {
    $('#modalCreateKas').modal('show');
}

function closeCreateKasModal() {
    $('#modalCreateKas').modal('hide');
}

function editKas(id, namaKas, jenis, deskripsi, isActive) {
    // Set form action untuk update
    document.getElementById('formEditKas').action = '{{ route("manage.accounting.kas.index") }}/' + id;
    
    // Set form values
    document.getElementById('edit_nama_kas').value = namaKas;
    document.getElementById('edit_jenis').value = jenis;
    document.getElementById('edit_deskripsi').value = deskripsi;
    
    // Set is_active checkbox
    const isActiveCheckbox = document.getElementById('edit_is_active');
    if (isActiveCheckbox) {
        isActiveCheckbox.checked = isActive;
    }
    
    // Show modal using jQuery (Bootstrap 4 compatible)
    $('#modalEditKas').modal('show');
}

// Toggle kas status
function toggleKasStatus(kasId, isActive) {
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('is_active', isActive ? 1 : 0);
    
    // Disable switch while processing
    const switchElement = document.getElementById('status_' + kasId);
    switchElement.disabled = true;
    
    fetch('{{ url("manage/accounting/kas") }}/' + kasId + '/toggle-status', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                console.error('Non-JSON response:', text);
                throw new Error('Server returned non-JSON response');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Tidak perlu update badge karena sudah dihapus
            showToast('success', 'Berhasil!', data.message || 'Status kas berhasil diupdate');
        } else {
            // Revert switch
            switchElement.checked = !isActive;
            showToast('error', 'Gagal!', data.message || 'Gagal mengupdate status kas');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Revert switch
        switchElement.checked = !isActive;
        showToast('error', 'Error!', 'Terjadi kesalahan saat mengupdate status');
    })
    .finally(() => {
        // Re-enable switch
        switchElement.disabled = false;
    });
}

function closeEditKasModal() {
    $('#modalEditKas').modal('hide');
}

function deleteKas(id, namaKas) {
    // Create custom confirmation modal
    const modalHtml = `
        <div class="modal fade" id="deleteConfirmModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-danger text-white border-0">
                        <h5 class="modal-title" id="deleteConfirmModalLabel">
                            <i class="fa fa-exclamation-triangle me-2"></i>Konfirmasi Hapus
                        </h5>
                        <button type="button" class="close text-white" onclick="closeDeleteKasModal()" aria-label="Close" style="opacity: 1; font-size: 1.5rem; padding: 0; margin-left: 0.5rem; line-height: 1;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <div class="mb-3">
                            <i class="fa fa-trash-alt text-danger" style="font-size: 3rem;"></i>
                        </div>
                        <h6 class="mb-3">Apakah Anda yakin ingin menghapus kas ini?</h6>
                        <p class="text-muted mb-0">
                            <strong>"${namaKas.replace(/"/g, '&quot;').replace(/'/g, '&#39;')}"</strong><br>
                            <small>Data yang sudah dihapus tidak dapat dikembalikan!</small>
                        </p>
                    </div>
                    <div class="modal-footer border-0 justify-content-center">
                        <button type="button" class="btn btn-secondary px-4" onclick="closeDeleteKasModal()">
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
    
    // Show modal using jQuery (Bootstrap 4 compatible)
    $('#deleteConfirmModal').modal('show');
}

// Close modal function
function closeDeleteKasModal() {
    $('#deleteConfirmModal').modal('hide');
}

function confirmDelete(id) {
    // Hide modal first using jQuery (Bootstrap 4 compatible)
    $('#deleteConfirmModal').modal('hide');
    
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
            $('#modalCreateKas').modal('hide');
            
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
            $('#modalEditKas').modal('hide');
            
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
