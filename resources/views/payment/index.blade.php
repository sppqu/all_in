@extends('layouts.adminty')

@section('head')
<title>Jenis Pembayaran</title>
<style>
/* Action Button Styling */
.action-buttons {
    display: flex;
    gap: 4px;
    align-items: center;
    justify-content: center;
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

/* Action Button Icon Colors - Ensure white icons */
.btn-warning .fas,
.btn-warning .fa,
.btn-warning.action-btn .fas,
.btn-warning.action-btn .fa {
    color: white !important;
}

.btn-danger .fas,
.btn-danger .fa,
.btn-danger.action-btn .fas,
.btn-danger.action-btn .fa {
    color: white !important;
}

.btn-primary .fas,
.btn-primary .fa,
.btn-primary.action-btn .fas,
.btn-primary.action-btn .fa {
    color: white !important;
}

.table td {
    vertical-align: middle;
}
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Jenis Pembayaran</h4>
                    <a href="{{ route('payment.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Jenis
                    </a>
                </div>
                <div class="card-body">
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-success align-middle text-center">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Pembayaran</th>
                                    <th>Jenis Pembayaran</th>
                                    <th>Tarif Pembayaran</th>
                                    <th>Tipe</th>
                                    <th>Tahun Pelajaran</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="align-middle text-center">
                                @forelse($payments as $i => $payment)
                                <tr>
                                    <td>{{ $i+1 }}</td>
                                    <td>{{ $payment->pos->pos_name ?? '-' }}</td>
                                    <td>{{ $payment->pos->pos_name ?? '-' }} - T.P {{ $payment->period->period_name ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('payment.setting', $payment->payment_id) }}" class="btn btn-sm {{ $payment->payment_type == 'BULAN' ? 'btn-danger text-white' : 'btn-primary' }} d-inline-flex align-items-center">
                                            <i class="fa fa-shopping-cart me-1"></i> Setting Pembayaran
                                        </a>
                                    </td>
                                    <td>
                                        {{ $payment->payment_type == 'BULAN' ? 'Bulanan' : 'Bebas' }}
                                        @if($payment->is_for_spmb)
                                            <span class="badge bg-success ms-1" title="Aktif untuk SPMB">
                                                <i class="fas fa-check-circle me-1"></i>SPMB
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $payment->period->period_name ?? '-' }}</td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('payment.edit', $payment->payment_id) }}" class="btn btn-warning btn-sm action-btn" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('payment.destroy', $payment->payment_id) }}" method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-danger btn-sm action-btn btn-delete" 
                                                        data-payment-name="{{ $payment->pos->pos_name ?? '-' }} - {{ $payment->period->period_name ?? '-' }}"
                                                        title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center">Belum ada data</td></tr>
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
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-danger text-white border-0">
        <h5 class="modal-title" id="deleteModalLabel">
          <i class="fa fa-trash me-2"></i>Konfirmasi Hapus Jenis Pembayaran
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" onclick="closeDeleteModal()" style="opacity: 1; font-size: 1.5rem; padding: 0.5rem;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center py-4">
        <div class="mb-3">
          <i class="fa fa-question-circle text-warning" style="font-size: 3rem;"></i>
        </div>
        <h5 class="mb-3">Yakin ingin menghapus <strong id="modalPaymentName"></strong>?</h5>
        <p class="text-muted mb-0">Tindakan ini tidak dapat dibatalkan.</p>
      </div>
      <div class="modal-footer border-0 justify-content-center">
        <button type="button" class="btn btn-secondary px-4" data-dismiss="modal" onclick="closeDeleteModal()">
          <i class="fa fa-times me-2"></i>Batal
        </button>
        <button type="button" class="btn btn-danger px-4" id="confirmDeleteBtn" onclick="confirmDelete()">
          <i class="fa fa-trash me-2"></i>Ya, Hapus
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
    let formToDelete = null;
    
    // Fungsi untuk membuka modal delete
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.btn-delete').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const paymentName = this.getAttribute('data-payment-name');
                formToDelete = this.closest('form');
                document.getElementById('modalPaymentName').textContent = paymentName;
                
                // Tampilkan modal - gunakan jQuery untuk Bootstrap 4
                if (typeof $ !== 'undefined' && $.fn.modal) {
                    $('#deleteModal').modal('show');
                } else {
                    console.error('jQuery modal not available');
                }
            });
        });
    });
    
    // Fungsi untuk menutup modal delete - Global
    window.closeDeleteModal = function() {
        if (typeof $ !== 'undefined' && $.fn.modal) {
            $('#deleteModal').modal('hide');
        } else {
            const modal = document.getElementById('deleteModal');
            if (modal) {
                modal.style.display = 'none';
                modal.classList.remove('show');
                document.body.classList.remove('modal-open');
            }
        }
    };
    
    // Fungsi untuk konfirmasi delete - Global
    window.confirmDelete = function() {
        if (formToDelete) {
            formToDelete.submit();
        }
    };
</script>
@endsection 