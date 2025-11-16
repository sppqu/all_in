@extends('layouts.adminty')

@section('title', 'Metode Pembayaran')

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
                        <i class="fa fa-credit-card me-2"></i>Metode Pembayaran
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
                            <h6 class="text-muted mb-0">Total Metode: {{ $paymentMethods->count() }}</h6>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="openCreatePaymentMethodModal()">
                            <i class="fa fa-plus me-2"></i>Tambah Metode Pembayaran
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>No.</th>
                                    <th>Metode Pembayaran</th>
                                    <th>Kas</th>
                                    <th>Keterangan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paymentMethods as $index => $method)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $method->nama_metode }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $method->kas_nama }}</span>
                                        </td>
                                        <td>
                                            {{ $method->keterangan ?: '-' }}
                                        </td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input status-switch" 
                                                       type="checkbox" 
                                                       id="status_{{ $method->id }}" 
                                                       data-method-id="{{ $method->id }}"
                                                       {{ $method->status == 'ON' ? 'checked' : '' }}
                                                       onchange="togglePaymentMethodStatus({{ $method->id }}, this.checked)">
                                                <label class="form-check-label" for="status_{{ $method->id }}"></label>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-warning" 
                                                        onclick="editPaymentMethod({{ $method->id }}, '{{ addslashes($method->nama_metode) }}', {{ $method->kas_id }}, '{{ addslashes($method->keterangan ?: '') }}', '{{ $method->status }}')"
                                                        title="Edit">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        onclick="deletePaymentMethod({{ $method->id }}, '{{ addslashes($method->nama_metode) }}')"
                                                        title="Hapus">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fa fa-credit-card fa-3x mb-3"></i>
                                            <p>Belum ada metode pembayaran</p>
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

<!-- Modal Create Payment Method -->
<div class="modal fade" id="modalCreatePaymentMethod" tabindex="-1" aria-labelledby="modalCreatePaymentMethodLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCreatePaymentMethodLabel">Membuat Metode Pembayaran Baru</h5>
                <button type="button" class="close text-white" onclick="closeCreatePaymentMethodModal()" aria-label="Close" style="opacity: 1; font-size: 1.5rem; padding: 0; margin-left: 0.5rem; line-height: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formCreatePaymentMethod" action="{{ route('manage.accounting.payment-methods.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nama_metode" class="form-label">
                            Nama Metode <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="nama_metode" name="nama_metode" required>
                    </div>
                    <div class="mb-3">
                        <label for="kas_id" class="form-label">
                            Kas <span class="text-danger">*</span>
                        </label>
                        <select class="form-control select-primary" id="kas_id" name="kas_id" required>
                            <option value="">Pilih Kas</option>
                            @foreach($kasList as $kas)
                                <option value="{{ $kas->id }}">{{ $kas->nama_kas }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">
                            <i class="fa fa-refresh me-1"></i>
                            <a href="#" onclick="refreshKasList()" class="text-decoration-none">Refresh Daftar Kas</a>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="status" name="status" value="ON" checked>
                            <label class="form-check-label" for="status">
                                <span class="status-text">ON</span> - Jika metode pembayaran ini tidak digunakan lagi bisa di OFF-kan
                            </label>
                        </div>
                    </div>
                    <div class="text-muted">
                        <small><span class="text-danger">*</span> Wajib diisi</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" onclick="closeCreatePaymentMethodModal()">
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

<!-- Modal Edit Payment Method -->
<div class="modal fade" id="modalEditPaymentMethod" tabindex="-1" aria-labelledby="modalEditPaymentMethodLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditPaymentMethodLabel">Edit Metode Pembayaran</h5>
                <button type="button" class="close text-white" onclick="closeEditPaymentMethodModal()" aria-label="Close" style="opacity: 1; font-size: 1.5rem; padding: 0; margin-left: 0.5rem; line-height: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditPaymentMethod" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_nama_metode" class="form-label">
                            Nama Metode <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="edit_nama_metode" name="nama_metode" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_kas_id" class="form-label">
                            Kas <span class="text-danger">*</span>
                        </label>
                        <select class="form-control select-primary" id="edit_kas_id" name="kas_id" required>
                            <option value="">Pilih Kas</option>
                            @foreach($kasList as $kas)
                                <option value="{{ $kas->id }}">{{ $kas->nama_kas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_keterangan" class="form-label">Keterangan</label>
                        <textarea class="form-control" id="edit_keterangan" name="keterangan" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="edit_status" name="status" value="ON">
                            <label class="form-check-label" for="edit_status">
                                <span class="edit-status-text">ON</span> - Jika metode pembayaran ini tidak digunakan lagi bisa di OFF-kan
                            </label>
                        </div>
                    </div>
                    <div class="text-muted">
                        <small><span class="text-danger">*</span> Wajib diisi</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" onclick="closeEditPaymentMethodModal()">
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
// Make functions global
window.editPaymentMethod = function(id, namaMetode, kasId, keterangan, status) {
    // Set form action untuk update
    document.getElementById('formEditPaymentMethod').action = '{{ route("manage.accounting.payment-methods.index") }}/' + id;
    
    // Set form values
    document.getElementById('edit_nama_metode').value = namaMetode || '';
    document.getElementById('edit_kas_id').value = kasId || '';
    document.getElementById('edit_keterangan').value = keterangan || '';
    document.getElementById('edit_status').checked = (status === 'ON');
    
    // Update status text
    const statusTextEl = document.querySelector('.edit-status-text');
    if (statusTextEl) {
        statusTextEl.textContent = status || 'OFF';
    }
    
    // Show modal using jQuery (Bootstrap 4 compatible)
    $('#modalEditPaymentMethod').modal('show');
};

// Store delete ID globally
window.deletePaymentMethodId = null;

window.deletePaymentMethod = function(id, namaMetode) {
    // Store ID globally
    window.deletePaymentMethodId = id;
    
    const modalHtml = `
        <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-danger text-white border-0">
                        <h5 class="modal-title" id="deleteConfirmModalLabel">
                            <i class="fa fa-exclamation-triangle me-2"></i>Konfirmasi Hapus
                        </h5>
                        <button type="button" class="close text-white" onclick="$('#deleteConfirmModal').modal('hide');" aria-label="Close" style="opacity: 1; font-size: 1.5rem; padding: 0; margin-left: 0.5rem; line-height: 1;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <div class="mb-3">
                            <i class="fa fa-trash-alt text-danger" style="font-size: 3rem;"></i>
                        </div>
                        <h6 class="mb-3">Apakah Anda yakin ingin menghapus metode pembayaran ini?</h6>
                        <p class="text-muted mb-0">
                            <strong>"${(namaMetode || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;')}"</strong><br>
                            <small>Data yang sudah dihapus tidak dapat dikembalikan!</small>
                        </p>
                    </div>
                    <div class="modal-footer border-0 justify-content-center">
                        <button type="button" class="btn btn-secondary px-4" onclick="$('#deleteConfirmModal').modal('hide');">
                            <i class="fa fa-times me-2"></i>Batal
                        </button>
                        <button type="button" class="btn btn-danger px-4" id="confirmDeleteBtn">
                            <i class="fa fa-trash me-2"></i>Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    const existingModal = document.getElementById('deleteConfirmModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Attach event listener after modal is added to DOM
    setTimeout(function() {
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', function() {
                window.confirmDeletePaymentMethod();
            });
        }
    }, 100);
    
    // Show modal using jQuery (Bootstrap 4 compatible)
    $('#deleteConfirmModal').modal('show');
};

window.confirmDeletePaymentMethod = function() {
    if (!window.deletePaymentMethodId) {
        console.error('Delete ID not found');
        showToast('error', 'Error!', 'ID metode pembayaran tidak ditemukan');
        return;
    }
    
    const id = window.deletePaymentMethodId;
    
    // Hide modal first using jQuery (Bootstrap 4 compatible)
    $('#deleteConfirmModal').modal('hide');
    
    // Show loading toast
    showToast('info', 'Memproses...', 'Sedang menghapus metode pembayaran...');
    
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('_method', 'DELETE');
    
    fetch('{{ route("manage.accounting.payment-methods.index") }}/' + id, {
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
            showToast('success', 'Berhasil!', data.message || 'Metode pembayaran berhasil dihapus!');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showToast('error', 'Gagal!', data.message || 'Gagal menghapus metode pembayaran');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'Error!', error.message || 'Terjadi kesalahan saat menghapus');
    })
    .finally(() => {
        // Reset delete ID
        window.deletePaymentMethodId = null;
    });
};

// Toggle payment method status
function togglePaymentMethodStatus(methodId, isActive) {
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('status', isActive ? 'ON' : 'OFF');
    
    // Disable switch while processing
    const switchElement = document.getElementById('status_' + methodId);
    switchElement.disabled = true;
    
    fetch('{{ url("manage/accounting/payment-methods") }}/' + methodId + '/toggle-status', {
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
            showToast('success', 'Berhasil!', data.message || 'Status metode pembayaran berhasil diupdate');
        } else {
            // Revert switch
            switchElement.checked = !isActive;
            showToast('error', 'Gagal!', data.message || 'Gagal mengupdate status metode pembayaran');
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

// showToast function is now global from adminty.blade.php layout

function openCreatePaymentMethodModal() {
    $('#modalCreatePaymentMethod').modal('show');
}

function closeCreatePaymentMethodModal() {
    $('#modalCreatePaymentMethod').modal('hide');
}

function closeEditPaymentMethodModal() {
    $('#modalEditPaymentMethod').modal('hide');
}

// Wait for DOM to be ready
$(document).ready(function() {
    // Handle status toggle untuk create
    const statusCheckbox = document.getElementById('status');
    if (statusCheckbox) {
        statusCheckbox.addEventListener('change', function() {
            const statusText = document.querySelector('.status-text');
            if (statusText) {
                statusText.textContent = this.checked ? 'ON' : 'OFF';
            }
        });
    }

    // Handle status toggle untuk edit
    const editStatusCheckbox = document.getElementById('edit_status');
    if (editStatusCheckbox) {
        editStatusCheckbox.addEventListener('change', function() {
            const statusText = document.querySelector('.edit-status-text');
            if (statusText) {
                statusText.textContent = this.checked ? 'ON' : 'OFF';
            }
        });
    }

    // Handle form submission untuk Create
    const createForm = document.getElementById('formCreatePaymentMethod');
    if (createForm) {
        createForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Show loading toast
            showToast('info', 'Memproses...', 'Sedang menyimpan metode pembayaran...');
            
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
                    showToast('success', 'Berhasil!', data.message);
                    this.reset();
                    $('#modalCreatePaymentMethod').modal('hide');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showToast('error', 'Gagal!', data.message || 'Gagal menyimpan metode pembayaran');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('error', 'Error!', error.message || 'Terjadi kesalahan saat menyimpan');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }

    // Handle form submission untuk Edit
    const editForm = document.getElementById('formEditPaymentMethod');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Show loading toast
            showToast('info', 'Memproses...', 'Sedang mengupdate metode pembayaran...');
            
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
                    showToast('success', 'Berhasil!', data.message);
                    $('#modalEditPaymentMethod').modal('hide');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showToast('error', 'Gagal!', data.message || 'Gagal mengupdate metode pembayaran');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('error', 'Error!', error.message || 'Terjadi kesalahan saat mengupdate');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }
});

function refreshKasList() {
    // Optional: reload kas list via AJAX
    location.reload();
}
</script>
@endpush