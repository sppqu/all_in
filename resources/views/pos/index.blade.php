@extends('layouts.coreui')

@section('head')
<title>Nama Pos Pembayaran</title>
<style>
/* Action Button Icon Colors - Ensure white icons */
.btn-warning .fas,
.btn-warning .fa {
    color: white !important;
}

.btn-danger .fas,
.btn-danger .fa {
    color: white !important;
}
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Nama Pos Pembayaran</h4>
                    <a href="{{ route('pos.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Pos
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Nama Pos</th>
                                    <th>Deskripsi</th>
                                    <th width="120">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($posList as $i => $pos)
                                <tr>
                                    <td>{{ $i+1 }}</td>
                                    <td>{{ $pos->pos_name }}</td>
                                    <td>{{ $pos->pos_description }}</td>
                                    <td>
                                        <a href="{{ route('pos.edit', $pos->pos_id) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                        <form action="{{ route('pos.destroy', $pos->pos_id) }}" method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-danger btn-sm btn-delete" data-pos-name="{{ $pos->pos_name }}"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center">Belum ada data</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-danger text-white border-0">
        <h5 class="modal-title" id="deleteModalLabel">
          <i class="fa fa-trash me-2"></i>Konfirmasi Hapus POS
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center py-4">
        <div class="mb-3">
          <i class="fa fa-question-circle text-warning" style="font-size: 3rem;"></i>
        </div>
        <h5 class="mb-3">Yakin ingin menghapus POS <span id="modalPosName"></span>?</h5>
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
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {

        let formToDelete = null;
        document.querySelectorAll('.btn-delete').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const posName = this.getAttribute('data-pos-name');
                formToDelete = this.closest('form');
                document.getElementById('modalPosName').textContent = posName;
                const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
                modal.show();
            });
        });
        document.getElementById('confirmDeleteBtn').onclick = function() {
            if (formToDelete) formToDelete.submit();
        };
    });
</script>
@endsection 