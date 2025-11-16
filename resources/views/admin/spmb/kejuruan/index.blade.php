@extends('layouts.adminty')

@push('styles')
<style>
    .stats-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    .stats-card:hover {
        transform: translateY(-2px);
    }
    .table-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 10px;
    }
    .status-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    .bulk-actions {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stats-card text-center p-4">
                <div class="text-primary mb-2">
                    <i class="fas fa-graduation-cap fa-2x"></i>
                </div>
                <h3 class="mb-1">{{ $stats['total'] }}</h3>
                <p class="text-muted mb-0">Total Kejuruan</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stats-card text-center p-4">
                <div class="text-success mb-2">
                    <i class="fas fa-check-circle fa-2x"></i>
                </div>
                <h3 class="mb-1">{{ $stats['aktif'] }}</h3>
                <p class="text-muted mb-0">Aktif</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stats-card text-center p-4">
                <div class="text-warning mb-2">
                    <i class="fas fa-pause-circle fa-2x"></i>
                </div>
                <h3 class="mb-1">{{ $stats['tidak_aktif'] }}</h3>
                <p class="text-muted mb-0">Tidak Aktif</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stats-card text-center p-4">
                <div class="text-info mb-2">
                    <i class="fas fa-users fa-2x"></i>
                </div>
                <h3 class="mb-1">{{ $stats['total_pendaftar'] }}</h3>
                <p class="text-muted mb-0">Total Pendaftar</p>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-1" style="font-size: 1.5rem;">
                        <i class="fas fa-graduation-cap me-2"></i>Data Kejuruan
                    </h4>
                    <p class="text-muted mb-0" style="font-size: 0.9rem;">Kelola data kejuruan SPMB</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('manage.spmb.settings') }}" class="btn btn-outline-info">
                        <i class="fas fa-cogs me-1"></i>Pengaturan
                    </a>
                    <a href="{{ route('manage.spmb.kejuruan.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Tambah Kejuruan
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions -->
    <div class="bulk-actions" id="bulkActions" style="display: none;">
        <form method="POST" action="{{ route('manage.spmb.kejuruan.bulk-action') }}" id="bulkForm">
            @csrf
            <div class="row align-items-center">
                <div class="col-md-6">
                    <strong>Bulk Actions:</strong>
                    <select name="action" class="form-select d-inline-block w-auto ms-2" required>
                        <option value="">Pilih Aksi</option>
                        <option value="activate">Aktifkan</option>
                        <option value="deactivate">Nonaktifkan</option>
                        <option value="delete">Hapus</option>
                    </select>
                </div>
                <div class="col-md-6 text-end">
                    <button type="submit" class="btn btn-warning me-2" onclick="return confirm('Yakin melakukan aksi ini?')">
                        <i class="fas fa-check me-1"></i>Terapkan
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="clearSelection()">
                        <i class="fas fa-times me-1"></i>Batal
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Kejuruan Table -->
    <div class="table-card">
        <div class="p-4">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                            </th>
                            <th>Kode</th>
                            <th>Nama Kejuruan</th>
                            <th>Deskripsi</th>
                            <th>Kuota</th>
                            <th>Pendaftar</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kejuruan as $k)
                        <tr>
                            <td>
                                <input type="checkbox" class="kejuruan-checkbox" value="{{ $k->id }}" onchange="updateBulkActions()">
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $k->kode_kejuruan }}</span>
                            </td>
                            <td>
                                <strong>{{ $k->nama_kejuruan }}</strong>
                            </td>
                            <td>
                                @if($k->deskripsi)
                                    <small class="text-muted">{{ Str::limit($k->deskripsi, 50) }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($k->kuota)
                                    <span class="badge bg-info">{{ $k->kuota }}</span>
                                @else
                                    <span class="text-muted">Tidak Terbatas</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $k->registrations_count }}</span>
                                @if($k->kuota && $k->registrations_count >= $k->kuota)
                                    <br><small class="text-danger">Kuota Penuh</small>
                                @endif
                            </td>
                            <td>
                                @if($k->aktif)
                                    <span class="status-badge bg-success text-white">Aktif</span>
                                @else
                                    <span class="status-badge bg-secondary text-white">Tidak Aktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('manage.spmb.kejuruan.show', $k->id) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('manage.spmb.kejuruan.edit', $k->id) }}" 
                                       class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('manage.spmb.kejuruan.toggle-status', $k->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-{{ $k->aktif ? 'warning' : 'success' }}">
                                            <i class="fas fa-{{ $k->aktif ? 'pause' : 'play' }}"></i>
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="deleteKejuruan({{ $k->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-graduation-cap fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Belum ada data kejuruan</p>
                                <a href="{{ route('manage.spmb.kejuruan.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i>Tambah Kejuruan
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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
                <p>Apakah Anda yakin ingin menghapus kejuruan ini?</p>
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
    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.kejuruan-checkbox');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
        
        updateBulkActions();
    }

    function updateBulkActions() {
        const checkboxes = document.querySelectorAll('.kejuruan-checkbox:checked');
        const bulkActions = document.getElementById('bulkActions');
        const bulkForm = document.getElementById('bulkForm');
        
        if (checkboxes.length > 0) {
            bulkActions.style.display = 'block';
            
            // Update hidden inputs
            bulkForm.innerHTML = '@csrf';
            checkboxes.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'kejuruan_ids[]';
                input.value = checkbox.value;
                bulkForm.appendChild(input);
            });
        } else {
            bulkActions.style.display = 'none';
        }
    }

    function clearSelection() {
        const checkboxes = document.querySelectorAll('.kejuruan-checkbox');
        const selectAll = document.getElementById('selectAll');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        selectAll.checked = false;
        
        updateBulkActions();
    }

    function deleteKejuruan(id) {
        document.getElementById('deleteForm').action = '{{ route("manage.spmb.kejuruan.destroy", ":id") }}'.replace(':id', id);
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>
@endpush
