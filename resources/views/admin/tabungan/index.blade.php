@extends('layouts.adminty')

@section('title', 'Kelola Tabungan')

@section('head')
<style>
    /* Fix untuk widget card icon tidak tertutup */
    .widget-card-1 {
        overflow: visible !important;
        margin-top: 20px;
    }
    
    .widget-card-1 .card-block {
        padding: 1.25rem;
        padding-top: 15px;
        padding-right: 80px;
        position: relative;
        text-align: left !important;
    }
    
    .widget-card-1 .card1-icon {
        position: absolute;
        top: -15px;
        right: 20px;
        left: auto;
        width: 60px;
        height: 60px;
        font-size: 35px;
        border-radius: 8px;
        display: flex;
        color: #fff;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease-in-out;
        z-index: 10;
    }
    
    .widget-card-1:hover .card1-icon {
        top: -25px;
    }
    
    .widget-card-1 .card-block h6,
    .widget-card-1 .card-block h4,
    .widget-card-1 .card-block span,
    .widget-card-1 .card-block small,
    .widget-card-1 .card-block div {
        text-align: left !important;
    }
    
    .widget-card-1 .card-block > h6:first-child {
        margin-right: 70px;
        padding-right: 0;
    }
    
    /* Action Buttons Styling */
    .action-buttons {
        flex-wrap: nowrap;
    }
    
    .action-btn {
        transition: all 0.3s ease;
        border: none !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    
    .action-btn.btn-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
    }
    
    .action-btn.btn-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
    }
    
    .action-btn.btn-info {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
    }
    
    .action-btn.btn-outline-primary {
        border: 1px solid #01a9ac !important;
        color: #01a9ac !important;
        background: white !important;
    }
    
    .action-btn.btn-outline-primary:hover {
        background: #01a9ac !important;
        color: white !important;
    }
    
    .action-btn.btn-outline-danger {
        border: 1px solid #ef4444 !important;
        color: #ef4444 !important;
        background: white !important;
    }
    
    .action-btn.btn-outline-danger:hover {
        background: #ef4444 !important;
        color: white !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Kelola Tabungan</h4>
        <a href="{{ route('manage.tabungan.create') }}" class="btn btn-primary text-white">
            <i class="fas fa-bank me-2 text-white"></i>Tambah Tabungan
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card widget-card-1" style="position: relative;">
                <div class="card-block">
                    <div class="card1-icon bg-c-blue" style="background: linear-gradient(135deg, #01a9ac 0%, #0ac282 100%);">
                        <i class="feather icon-users"></i>
                    </div>
                    <h6 class="mb-0" style="color: #919aa3; font-size: 0.85rem;">Total Siswa dengan Tabungan</h6>
                    <h4 class="mt-2 mb-0" style="color: #01a9ac; font-weight: 600;">{{ $totalStudents }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card widget-card-1" style="position: relative;">
                <div class="card-block">
                    <div class="card1-icon bg-c-green" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i class="feather icon-briefcase"></i>
                    </div>
                    <h6 class="mb-0" style="color: #919aa3; font-size: 0.85rem;">Total Saldo Tabungan</h6>
                    <h4 class="mt-2 mb-0" style="color: #10b981; font-weight: 600;">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card widget-card-1" style="position: relative;">
                <div class="card-block">
                    <div class="card1-icon bg-c-blue" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                        <i class="feather icon-bar-chart-2"></i>
                    </div>
                    <h6 class="mb-0" style="color: #919aa3; font-size: 0.85rem;">Rata-rata Saldo per Siswa</h6>
                    <h4 class="mt-2 mb-0" style="color: #3b82f6; font-weight: 600;">Rp {{ number_format($averageSaldo, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
    </div>

         <!-- Filter Section -->
     <div class="card border-0 shadow-sm mb-4">
         <div class="card-header bg-transparent border-0">
             <h6 class="mb-0 fw-bold">Filter Pencarian</h6>
         </div>
         <div class="card-body">
             <form method="GET" action="{{ route('manage.tabungan.index') }}" class="row g-3">
                 <div class="col-md-4">
                     <label for="search" class="form-label">NIS / Nama Siswa</label>
                     <input type="text" class="form-control" id="search" name="search" 
                            value="{{ request('search') }}" placeholder="Cari berdasarkan NIS atau nama...">
                 </div>
                 <div class="col-md-3">
                     <label for="class_id" class="form-label">Kelas</label>
                     <select class="form-control select-primary" id="class_id" name="class_id">
                         <option value="">Semua Kelas</option>
                         @foreach($classes as $class)
                             <option value="{{ $class->class_id }}" {{ request('class_id') == $class->class_id ? 'selected' : '' }}>
                                 {{ $class->class_name }}
                             </option>
                         @endforeach
                     </select>
                 </div>
                 <div class="col-md-3">
                     <label for="status" class="form-label">Status</label>
                     <select class="form-control select-primary" id="status" name="status">
                         <option value="">Semua Status</option>
                         <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                         <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                     </select>
                 </div>
                 <div class="col-md-2 d-flex align-items-end">
                     <div class="d-grid gap-2 w-100">
                         <button type="submit" class="btn btn-primary text-white">
                             <i class="fas fa-search me-1 text-white"></i>Cari
                         </button>
                     </div>
                 </div>
             </form>
             @if(request('search') || request('class_id') || request('status'))
                 <div class="mt-3">
                     <a href="{{ route('manage.tabungan.index') }}" class="btn btn-outline-secondary btn-sm">
                         <i class="fas fa-times me-1"></i>Hapus Filter
                     </a>
                 </div>
             @endif
         </div>
     </div>
 
     <!-- Tabungan Table -->
     <div class="card border-0 shadow-sm">
         <div class="card-header bg-transparent border-0">
             <h6 class="mb-0 fw-bold">Daftar Tabungan Siswa</h6>
         </div>
         <div class="card-body">
            @if($tabungan->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                                                 <th>No</th>
                                 <th>NIS</th>
                                 <th>Nama Siswa</th>
                                 <th>Kelas</th>
                                 <th>Saldo</th>
                                 <th>Tanggal Input</th>
                                 <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tabungan as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->student_nis }}</td>
                                    <td>{{ $item->student_full_name }}</td>
                                    <td>{{ $item->class_name }}</td>
                                    <td>
                                        <span class="fw-bold text-success">
                                            Rp {{ number_format($item->saldo, 0, ',', '.') }}
                                        </span>
                                    </td>
                                                                         <td>{{ \Carbon\Carbon::parse($item->tabungan_input_date)->format('d/m/Y H:i') }}</td>
                                     <td>
                                        <div class="action-buttons d-flex align-items-center gap-1">
                                            <a href="{{ route('manage.tabungan.setoran', $item->tabungan_id) }}" 
                                               class="action-btn btn btn-sm btn-success text-white" 
                                               title="Setoran"
                                               style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; padding: 0; border-radius: 6px;">
                                                <i class="fas fa-arrow-up"></i>
                                            </a>
                                            <a href="{{ route('manage.tabungan.penarikan', $item->tabungan_id) }}" 
                                               class="action-btn btn btn-sm btn-warning text-white" 
                                               title="Penarikan"
                                               style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; padding: 0; border-radius: 6px;">
                                                <i class="fas fa-arrow-down"></i>
                                            </a>
                                            <a href="{{ route('manage.tabungan.riwayat', $item->tabungan_id) }}" 
                                               class="action-btn btn btn-sm btn-info text-white" 
                                               title="Riwayat"
                                               style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; padding: 0; border-radius: 6px;">
                                                <i class="fas fa-list-alt"></i>
                                            </a>
                                            <a href="{{ route('manage.tabungan.edit', $item->tabungan_id) }}" 
                                               class="action-btn btn btn-sm btn-outline-primary" 
                                               title="Edit"
                                               style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; padding: 0; border-radius: 6px;">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>
                                            <form action="{{ route('manage.tabungan.destroy', $item->tabungan_id) }}" 
                                                  method="POST" 
                                                  class="d-inline delete-form"
                                                  style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" 
                                                        class="action-btn btn btn-sm btn-outline-danger btn-delete-tabungan" 
                                                        data-tabungan-id="{{ $item->tabungan_id }}"
                                                        data-student-name="{{ addslashes($item->student_full_name) }}"
                                                        title="Hapus"
                                                        style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; padding: 0; border-radius: 6px;">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                                 </div>
 
                 <!-- Pagination -->
                 <div class="d-flex justify-content-center mt-4">
                     {{ $tabungan->links('pagination::simple-bootstrap-4') }}
                 </div>
 
             @else
                <div class="text-center py-5">
                    <i class="fas fa-piggy-bank fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">Belum ada data tabungan</h6>
                    <p class="text-muted mb-0">Klik tombol "Tambah Tabungan" untuk menambahkan data</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus Tabungan -->
<div class="modal fade" id="deleteTabunganModal" tabindex="-1" role="dialog" aria-labelledby="deleteTabunganModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-danger text-white border-0">
        <h5 class="modal-title" id="deleteTabunganModalLabel">
          <i class="fa fa-trash me-2"></i>Konfirmasi Hapus Tabungan
        </h5>
        <button type="button" class="close text-white" onclick="closeDeleteTabunganModal()" aria-label="Close" style="opacity: 1; font-size: 1.5rem; padding: 0; margin-left: 0.5rem; line-height: 1;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center py-4">
        <div class="mb-3">
          <i class="fa fa-question-circle text-warning" style="font-size: 3rem;"></i>
        </div>
        <h5 class="mb-3">Yakin ingin menghapus tabungan <span id="modalTabunganStudentName"></span>?</h5>
        <p class="text-muted mb-0">Tindakan ini tidak dapat dibatalkan.</p>
      </div>
      <div class="modal-footer border-0 justify-content-center">
        <button type="button" class="btn btn-secondary px-4" onclick="closeDeleteTabunganModal()">
          <i class="fa fa-times me-2"></i>Batal
        </button>
        <button type="button" class="btn btn-danger px-4" id="confirmDeleteTabunganBtn">
          <i class="fa fa-trash me-2"></i>Ya, Hapus
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        let formToDelete = null;

        // Delete button handler
        $('.btn-delete-tabungan').on('click', function(e) {
            e.preventDefault();
            const studentName = $(this).data('student-name');
            formToDelete = $(this).closest('form');
            $('#modalTabunganStudentName').text(studentName);
            $('#deleteTabunganModal').modal('show');
        });

        // Confirm delete
        $('#confirmDeleteTabunganBtn').on('click', function() {
            if (formToDelete) {
                formToDelete.submit();
            }
        });
    });

    // Close modal function
    function closeDeleteTabunganModal() {
        $('#deleteTabunganModal').modal('hide');
    }
</script>
@endsection 