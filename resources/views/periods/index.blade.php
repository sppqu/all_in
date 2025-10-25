@extends('layouts.coreui')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Data Tahun Pelajaran</h4>
                    <a href="{{ route('periods.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Tahun Pelajaran
                    </a>
                </div>
                <div class="card-body">
                    

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="25%">Tahun Pelajaran</th>
                                    <th width="20%">Tahun Mulai</th>
                                    <th width="20%">Tahun Akhir</th>
                                    <th width="15%">Status</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($periods as $index => $period)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $period->period_name }}</strong>
                                    </td>
                                    <td>{{ $period->period_start }}</td>
                                    <td>{{ $period->period_end }}</td>
                                    <td>
                                        @if($period->period_status)
                                            <span class="badge bg-success">{{ $period->status_text }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $period->status_text }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            @if(!$period->period_status)
                                                <form action="{{ route('periods.set-active', $period) }}" method="POST" class="d-inline activate-form">
                                                    @csrf
                                                    <button type="button" class="btn btn-success btn-sm action-btn btn-activate" 
                                                            data-period-name="{{ $period->period_name }}"
                                                            title="Aktifkan">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <a href="{{ route('periods.edit', $period) }}" class="btn btn-warning btn-sm action-btn" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('periods.destroy', $period) }}" method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-danger btn-sm action-btn btn-delete" 
                                                        data-period-name="{{ $period->period_name }}"
                                                        title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">Tidak ada data tahun pelajaran</td>
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

<style>
.action-buttons {
    display: flex;
    gap: 4px;
    align-items: center;
}

.action-btn {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    font-size: 12px;
    transition: all 0.2s ease;
}

.action-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.75rem;
    padding: 0.35em 0.65em;
}

.btn-sm {
    font-size: 0.875rem;
    border-radius: 0.2rem;
}

/* Action Button Icon Colors - Ensure white icons */
.action-btn .fas,
.action-btn .fa {
    color: white !important;
}

.btn-success .fas,
.btn-success .fa {
    color: white !important;
}

.btn-warning .fas,
.btn-warning .fa {
    color: white !important;
}

.btn-danger .fas,
.btn-danger .fa {
    color: white !important;
}
</style>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-danger text-white border-0">
        <h5 class="modal-title" id="deleteModalLabel">
          <i class="fa fa-trash me-2"></i>Konfirmasi Hapus Tahun Pelajaran
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center py-4">
        <div class="mb-3">
          <i class="fa fa-question-circle text-warning" style="font-size: 3rem;"></i>
        </div>
        <h5 class="mb-3">Yakin ingin menghapus tahun pelajaran <span id="modalPeriodName"></span>?</h5>
        <p class="text-muted mb-0">Tindakan ini tidak dapat dibatalkan.</p>
      </div>
      <div class="modal-footer border-0 justify-content-center">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
          <i class="fa fa-times me-2"></i>Batal
        </button>
        <button type="button" class="btn btn-danger px-4" id="confirmDeleteBtn">
          <i class="fa fa-trash me-2"></i>Ya, Hapus
        </button>
      </div>
    </div>
  </div>
</div>
<!-- Modal Konfirmasi Aktifkan -->
<div class="modal fade" id="activateModal" tabindex="-1" aria-labelledby="activateModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-success text-white border-0">
        <h5 class="modal-title" id="activateModalLabel">
          <i class="fa fa-check me-2"></i>Konfirmasi Aktifkan Tahun Pelajaran
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center py-4">
        <div class="mb-3">
          <i class="fa fa-question-circle text-warning" style="font-size: 3rem;"></i>
        </div>
        <h5 class="mb-3">Aktifkan tahun pelajaran <span id="modalActivatePeriodName"></span>?</h5>
        <p class="text-muted mb-0">Tahun pelajaran lain akan dinonaktifkan.</p>
      </div>
      <div class="modal-footer border-0 justify-content-center">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
          <i class="fa fa-times me-2"></i>Batal
        </button>
        <button type="button" class="btn btn-success px-4" id="confirmActivateBtn">
          <i class="fa fa-check me-2"></i>Ya, Aktifkan
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {

        let formToDelete = null;
        document.querySelectorAll('.btn-delete').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const periodName = this.getAttribute('data-period-name');
                formToDelete = this.closest('form');
                document.getElementById('modalPeriodName').textContent = periodName;
                const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
                modal.show();
            });
        });
        document.getElementById('confirmDeleteBtn').onclick = function() {
            if (formToDelete) formToDelete.submit();
        };

        let formToActivate = null;
        document.querySelectorAll('.btn-activate').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const periodName = this.getAttribute('data-period-name');
                formToActivate = this.closest('form');
                document.getElementById('modalActivatePeriodName').textContent = periodName;
                const modal = new bootstrap.Modal(document.getElementById('activateModal'));
                modal.show();
            });
        });
        document.getElementById('confirmActivateBtn').onclick = function() {
            if (formToActivate) formToActivate.submit();
        };
    });
</script>
@endsection 