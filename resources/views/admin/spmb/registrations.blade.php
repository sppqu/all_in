@extends('layouts.coreui')

@push('styles')
<style>
    .table-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 25px;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
    }

    .btn-primary {
        background: linear-gradient(135deg, #008060 0%, #006d52 100%);
        border: none;
        border-radius: 15px;
        padding: 12px 24px;
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
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0, 128, 96, 0.4);
    }

    .btn-outline-primary {
        border: 2px solid #008060;
        color: #008060;
        background: transparent;
        border-radius: 15px;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-outline-primary:hover {
        background: #008060;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 128, 96, 0.3);
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }

    .action-btn {
        width: 35px;
        height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.3s ease;
        border: 1px solid;
    }

    .action-btn.btn-outline-primary {
        color: #008060;
        border-color: #008060;
    }

    .action-btn.btn-outline-primary:hover {
        background: #008060;
        color: white;
    }

    .action-btn.btn-outline-info {
        color: #17a2b8;
        border-color: #17a2b8;
    }

    .action-btn.btn-outline-info:hover {
        background: #17a2b8;
        color: white;
    }

    .action-btn.btn-outline-danger {
        color: #dc3545;
        border-color: #dc3545;
    }

    .action-btn.btn-outline-danger:hover {
        background: #dc3545;
        color: white;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #008060;
        box-shadow: 0 0 0 0.2rem rgba(0, 128, 96, 0.25);
    }

    .table th {
        background: #f8f9fa;
        font-weight: 600;
        color: #333;
        border-bottom: 2px solid #008060;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 128, 96, 0.05);
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="font-size: 1.5rem;">
                <i class="fas fa-list me-2"></i>Data Pendaftaran SPMB
            </h4>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">Kelola data pendaftaran SPMB</p>
        </div>
        <a href="{{ route('manage.spmb.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Kembali ke Dashboard
        </a>
    </div>

    <!-- Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-end align-items-center mb-3 desktop-menu">
                <div class="d-flex gap-2 align-items-center">
                    <!-- Bulk Actions -->
                    <div class="bulk-actions d-none">
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-danger dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-trash me-1"></i>Bulk Actions
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item text-danger" href="#" onclick="bulkDelete()">
                                    <i class="fas fa-trash me-2"></i>Hapus Terpilih
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-success" href="#" onclick="bulkUpdateStatus('diterima')">
                                    <i class="fas fa-check me-2"></i>Setuju Terpilih
                                </a></li>
                                <li><a class="dropdown-item text-warning" href="#" onclick="bulkUpdateStatus('ditolak')">
                                    <i class="fas fa-times me-2"></i>Tolak Terpilih
                                </a></li>
                            </ul>
                        </div>
                        <span class="ms-2 text-muted selected-count">0 dipilih</span>
                    </div>
                    <!-- Action Buttons -->
                    <a href="{{ route('manage.spmb.create') }}" class="btn btn-success d-none">
                        <i class="fas fa-user-plus me-1"></i>Tambah Pendaftar
                    </a>
                    <a href="{{ route('manage.spmb.export-excel', request()->query()) }}" class="btn btn-primary">
                        <i class="fas fa-file-excel me-1"></i>Export Excel
                    </a>
                    <a href="{{ route('manage.spmb.export-pdf', request()->query()) }}" class="btn btn-danger">
                        <i class="fas fa-file-pdf me-1"></i>Export PDF
                    </a>
                </div>
            </div>
            
            <!-- Filter Form -->
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-3">
                    <select name="status_pendaftaran" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status_pendaftaran') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="diterima" {{ request('status_pendaftaran') == 'diterima' ? 'selected' : '' }}>Diterima</option>
                        <option value="ditolak" {{ request('status_pendaftaran') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="kejuruan_id" class="form-select">
                        <option value="">Semua Kejuruan</option>
                        @foreach($kejuruan as $k)
                            <option value="{{ $k->id }}" {{ request('kejuruan_id') == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kejuruan }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama, phone, atau nomor pendaftaran..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Registrations Table -->
    <div class="table-card">
        <div class="p-4">
            <div class="table-responsive">
                <form id="bulkForm" method="POST" action="{{ route('manage.spmb.bulk-action') }}">
                    @csrf
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="50">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>No.</th>
                                <th>No. Pendaftaran</th>
                                <th>Nama</th>
                                <th>No. HP</th>
                                <th>Kejuruan</th>
                                <th>Status Pendaftaran</th>
                                <th>Langkah</th>
                                <th>Biaya Pendaftaran</th>
                                <th>Biaya SPMB</th>
                                <th>Tanggal Daftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registrations as $registration)
                            <tr>
                                <td>
                                    <input type="checkbox" name="selected_ids[]" value="{{ $registration->id }}" class="form-check-input row-checkbox">
                                </td>
                                <td>{{ $registration->id }}</td>
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
                                <td>
                                    {!! $registration->getStatusPendaftaranBadge() !!}
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $registration->step }}</span>
                                </td>
                                <td>
                                    @if($registration->registration_fee_paid)
                                        <span class="text-success">
                                            <i class="fas fa-check-circle"></i> Lunas
                                        </span>
                                    @else
                                        <span class="text-danger">
                                            <i class="fas fa-times-circle"></i> Belum
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($registration->spmb_fee_paid)
                                        <span class="text-success">
                                            <i class="fas fa-check-circle"></i> Lunas
                                        </span>
                                    @else
                                        <span class="text-danger">
                                            <i class="fas fa-times-circle"></i> Belum
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $registration->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('manage.spmb.show', $registration->id) }}" 
                                           class="action-btn btn-outline-primary" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('manage.spmb.print-form', $registration->id) }}" 
                                           class="action-btn btn-outline-info" title="Cetak Formulir" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        <button type="button" class="action-btn btn-outline-danger" 
                                                onclick="deleteRegistration({{ $registration->id }})" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="12" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada data pendaftaran</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </form>
            </div>

            <!-- Pagination -->
            @if($registrations->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $registrations->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus data pendaftaran ini?</p>
                <p class="text-danger"><strong>Data yang dihapus tidak dapat dikembalikan!</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function deleteRegistration(id) {
        document.getElementById('deleteForm').action = '{{ route("manage.spmb.destroy", ":id") }}'.replace(':id', id);
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    function updateStatus(id, status) {
        if (confirm(`Apakah Anda yakin ingin mengubah status menjadi ${status}?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("manage.spmb.update-registration-status", ":id") }}'.replace(':id', id);
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'status_pendaftaran';
            statusInput.value = status;
            
            form.appendChild(csrfToken);
            form.appendChild(statusInput);
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Bulk Actions
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        const bulkActions = document.querySelector('.bulk-actions');
        const selectedCount = document.querySelector('.selected-count');

        // Select All functionality
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                rowCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateBulkActions();
            });
        }

        // Individual checkbox functionality
        rowCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateBulkActions();
                
                // Update select all checkbox
                if (selectAllCheckbox) {
                    const totalCheckboxes = rowCheckboxes.length;
                    const checkedCheckboxes = document.querySelectorAll('.row-checkbox:checked').length;
                    
                    selectAllCheckbox.checked = checkedCheckboxes === totalCheckboxes;
                    selectAllCheckbox.indeterminate = checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes;
                }
            });
        });

        function updateBulkActions() {
            const checkedCheckboxes = document.querySelectorAll('.row-checkbox:checked');
            const count = checkedCheckboxes.length;
            
            if (count > 0 && bulkActions && selectedCount) {
                bulkActions.classList.remove('d-none');
                selectedCount.textContent = `${count} dipilih`;
            } else if (bulkActions) {
                bulkActions.classList.add('d-none');
            }
        }
    });

    function bulkDelete() {
        const checkedCheckboxes = document.querySelectorAll('.row-checkbox:checked');
        if (checkedCheckboxes.length === 0) {
            alert('Pilih minimal satu data untuk dihapus!');
            return;
        }

        if (confirm(`Apakah Anda yakin ingin menghapus ${checkedCheckboxes.length} data yang dipilih?\n\nData yang dihapus tidak dapat dikembalikan!`)) {
            const form = document.getElementById('bulkForm');
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete';
            form.appendChild(actionInput);
            form.submit();
        }
    }

    function bulkUpdateStatus(status) {
        const checkedCheckboxes = document.querySelectorAll('.row-checkbox:checked');
        if (checkedCheckboxes.length === 0) {
            alert('Pilih minimal satu data untuk diubah statusnya!');
            return;
        }

        const statusText = status === 'diterima' ? 'diterima' : 'ditolak';
        if (confirm(`Apakah Anda yakin ingin mengubah status ${checkedCheckboxes.length} data menjadi ${statusText}?`)) {
            const form = document.getElementById('bulkForm');
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'update_status';
            form.appendChild(actionInput);
            
            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'status_pendaftaran';
            statusInput.value = status;
            form.appendChild(statusInput);
            
            form.submit();
        }
    }
</script>
@endpush


