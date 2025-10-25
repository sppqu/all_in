@extends('layouts.coreui')

@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Peserta Didik</title>

<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
    <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-check-circle me-2"></i>
                <span id="toastMessage"></span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    
    <div id="errorToast" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-exclamation-circle me-2"></i>
                <span id="errorToastMessage"></span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
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

.card-header h6 {
    margin-bottom: 0;
    color: #495057;
}

.alert-info {
    background-color: #d1ecf1;
    border-color: #bee5eb;
    color: #0c5460;
}

.gap-2 {
    gap: 0.5rem !important;
}

.form-check-input {
    cursor: pointer;
}

#bulkActions {
    transition: all 0.3s ease;
}

/* Toast Styling */
.toast-container {
    z-index: 1055;
}

.toast {
    min-width: 300px;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.toast.bg-success {
    background-color: #198754 !important;
}

.toast.bg-danger {
    background-color: #dc3545 !important;
}

.toast .toast-body {
    font-weight: 500;
    font-size: 0.9rem;
}

.toast .btn-close-white {
    filter: invert(1) grayscale(100%) brightness(200%);
}

/* Action Button Icon Colors */
.btn-info .fas.fa-eye {
    color: white !important;
}

.btn-danger .fas.fa-trash {
    color: white !important;
}

/* Ensure action buttons are clickable */
.action-btn {
    pointer-events: auto !important;
    cursor: pointer !important;
    position: relative !important;
    z-index: 1 !important;
}

.action-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Specific styling for detail button */
.btn-info.action-btn {
    text-decoration: none !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    pointer-events: auto !important;
    cursor: pointer !important;
    /* Ensure it behaves like a normal link */
    user-select: none !important;
    -webkit-user-select: none !important;
    -moz-user-select: none !important;
    -ms-user-select: none !important;
}

/* Prevent any interference with detail button */
.btn-info.action-btn:hover,
.btn-info.action-btn:focus,
.btn-info.action-btn:active {
    text-decoration: none !important;
    color: inherit !important;
}

/* Ensure no parent elements block clicks */
.action-buttons {
    pointer-events: auto !important;
    position: relative !important;
    z-index: 10 !important;
}

/* Fix any potential CSS conflicts */
.table td {
    pointer-events: auto !important;
}

.table td .action-buttons {
    pointer-events: auto !important;
}

/* Ensure body and html can scroll */
html, body {
    overflow: auto !important;
    position: static !important;
}

/* Prevent any modal interference */
body:not(.modal-open) {
    overflow: auto !important;
    position: static !important;
}

/* Ensure detail button is completely independent */
.btn-info.action-btn {
    position: relative !important;
    z-index: 100 !important;
    pointer-events: auto !important;
    cursor: pointer !important;
    text-decoration: none !important;
    /* Force link behavior */
    display: inline-block !important;
    text-decoration: none !important;
    color: inherit !important;
}

/* Prevent any interference with detail button */
.btn-info.action-btn:hover,
.btn-info.action-btn:focus,
.btn-info.action-btn:active,
.btn-info.action-btn:visited {
    text-decoration: none !important;
    color: inherit !important;
    outline: none !important;
}

/* Ensure detail button works as a normal link */
.btn-info.action-btn[href] {
    pointer-events: auto !important;
    cursor: pointer !important;
    user-select: none !important;
}

/* Simple detail link styling */
.detail-link {
    display: inline-block !important;
    padding: 0.375rem 0.75rem !important;
    background-color: #17a2b8 !important;
    color: white !important;
    text-decoration: none !important;
    border-radius: 0.25rem !important;
    margin-right: 5px !important;
    /* Ensure it's clickable */
    pointer-events: auto !important;
    cursor: pointer !important;
    /* No interference */
    position: relative !important;
    z-index: 1 !important;
}

.detail-link:hover {
    background-color: #138496 !important;
    color: white !important;
    text-decoration: none !important;
}

/* Ensure the page remains scrollable */
html, body {
    overflow: auto !important;
    position: static !important;
}

/* Ensure no modal interference */
.modal-open {
    overflow: auto !important;
}

/* Pagination Styling */
.pagination {
    margin-bottom: 0;
}

.pagination .page-link {
    color: #6c757d;
    border-color: #dee2e6;
    padding: 0.5rem 0.75rem;
    font-size: 0.9rem;
}

.pagination .page-link:hover {
    color: #495057;
    background-color: #e9ecef;
    border-color: #dee2e6;
}

.pagination .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
}

.pagination .page-item.disabled .page-link {
    color: #6c757d;
    pointer-events: none;
    background-color: #fff;
    border-color: #dee2e6;
}

/* Modal Styling */
.modal-content {
    border-radius: 15px;
    overflow: hidden;
}

.modal-header {
    padding: 1.5rem;
}

.modal-body {
    padding: 2rem;
}

.modal-footer {
    padding: 1.5rem;
}

/* Animation for modal */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate__animated {
    animation-duration: 0.6s;
    animation-fill-mode: both;
}

.animate__fadeIn {
    animation-name: fadeInUp;
}

/* Icon animation */
.modal .fa-key {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
    }
}

/* Badge styling */
.badge.rounded-pill {
    font-size: 0.8rem;
    padding: 0.5rem 0.8rem;
}

/* Button hover effects */
.btn-warning:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(255, 193, 7, 0.3);
    transition: all 0.3s ease;
}

.btn-outline-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(108, 117, 125, 0.3);
    transition: all 0.3s ease;
}

/* Alert styling */
.alert {
    border-radius: 10px;
}

.alert-info {
    background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
    border-left: 4px solid #2196f3;
}

.alert-warning {
    background: linear-gradient(135deg, #fff3e0 0%, #fce4ec 100%);
    border-left: 4px solid #ff9800;
}

/* Code styling */
code {
    font-family: 'Courier New', monospace;
    font-weight: bold;
}

/* Ripple animation for modal */
@keyframes ripple {
    0% {
        transform: translate(-50%, -50%) scale(0.8);
        opacity: 1;
    }
    100% {
        transform: translate(-50%, -50%) scale(1.2);
        opacity: 0;
    }
}

/* Enhanced button hover effects */
.btn-warning:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(255, 193, 7, 0.4);
    transition: all 0.3s ease;
}

.btn-outline-secondary:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(108, 117, 125, 0.4);
    transition: all 0.3s ease;
}

/* Enhanced modal animations */
.modal.fade .modal-dialog {
    transition: transform 0.3s ease-out;
    transform: translate(0, -50px);
}

.modal.show .modal-dialog {
    transform: none;
}

/* Card hover effects */
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Data Peserta Didik</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('students.export', request()->query()) }}" class="btn btn-info text-white">
                            <i class="fas fa-download"></i> Export Data
                        </a>
                        <a href="{{ url('/students-import') }}" class="btn btn-success text-white">
                            <i class="fas fa-upload"></i> Import Data
                        </a>
                        <a href="{{ route('students.move-class') }}" class="btn btn-warning text-white">
                            <i class="fas fa-exchange-alt"></i> Pindah Kelas
                        </a>
                        <a href="{{ route('students.graduate') }}" class="btn btn-success text-white">
                            <i class="fas fa-graduation-cap"></i> Kelulusan
                        </a>
                        <a href="{{ route('students.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Tambah Peserta Didik
                        </a>
                    </div>
                </div>
                <div class="card-body">

                    <!-- Filter Form -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-filter"></i> Filter Data</h6>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('students.index') }}" class="row g-3">
                                <div class="col-md-4">
                                    <label for="search" class="form-label">Cari NIS/Nama</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="search" 
                                           name="search" 
                                           value="{{ request('search') }}" 
                                           placeholder="Masukkan NIS, NISN, atau Nama...">
                                </div>
                                <div class="col-md-3">
                                    <label for="class_id" class="form-label">Kelas</label>
                                    <select class="form-select" id="class_id" name="class_id">
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
                                    <select class="form-select" id="status" name="status">
                                        <option value="">Semua Status</option>
                                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Aktif</option>
                                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Non-Aktif</option>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <div class="d-grid gap-2 w-100">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i> Filter
                                        </button>
                                        <a href="{{ route('students.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Reset
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Results Info -->
                    @if(request('search') || request('class_id') || request('status'))
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            Menampilkan {{ $students->count() }} dari {{ $students->total() }} data peserta didik
                            @if(request('search'))
                                dengan pencarian: <strong>"{{ request('search') }}"</strong>
                            @endif
                            @if(request('class_id'))
                                dari kelas: <strong>{{ $classes->where('class_id', request('class_id'))->first()->class_name ?? '' }}</strong>
                            @endif
                            @if(request('status'))
                                dengan status: <strong>{{ request('status') == '1' ? 'Aktif' : 'Non-Aktif' }}</strong>
                            @endif
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            Menampilkan {{ $students->count() }} dari {{ $students->total() }} data peserta didik ({{ $students->currentPage() }} dari {{ $students->lastPage() }} halaman)
                        </div>
                    @endif

                    <!-- Pagination Info -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="text-muted">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                Halaman {{ $students->currentPage() }} dari {{ $students->lastPage() }} 
                                ({{ $students->total() }} total data)
                            </small>
                        </div>
                        <div class="text-muted">
                            <small>
                                <i class="fas fa-list me-1"></i>
                                {{ $students->perPage() }} data per halaman
                            </small>
                        </div>
                    </div>

                    <!-- Bulk Actions -->
                    <div class="mb-3" id="bulkActions" style="display: none;">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="text-muted">Terpilih: <span id="selectedCount">0</span> item</span>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="selectAll()">
                                            <i class="fas fa-check-square"></i> Pilih Semua
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="deselectAll()">
                                            <i class="fas fa-square"></i> Batal Pilih
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteSelected()">
                                            <i class="fas fa-trash"></i> Hapus Terpilih
                                        </button>
                                        <button type="button" class="btn btn-success btn-sm" onclick="graduateSelected()">
                                            <i class="fas fa-graduation-cap"></i> Luluskan
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-sm" onclick="resetPasswordSelected()">
                                            <i class="fas fa-key"></i> Reset Password
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form id="bulkDeleteForm" action="/bulk-delete-students" method="POST">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="selected_ids" id="selectedIds">
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="3%">
                                            <input type="checkbox" id="selectAllCheckbox" class="form-check-input">
                                        </th>
                                        <th width="3%">No</th>
                                        <th width="12%">NIS</th>
                                        <th width="20%">Nama Lengkap</th>
                                        <th width="8%">JK</th>
                                        <th width="15%">Kelas</th>
                                        <th width="15%">No. Telp Org Tua</th>
                                        <th width="10%">Status</th>
                                        <th width="17%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($students as $index => $student)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="student_ids[]" value="{{ $student->student_id }}" class="form-check-input student-checkbox">
                                        </td>
                                        <td>{{ $students->firstItem() + $loop->index }}</td>
                                        <td>
                                            {{ $student->student_nis }}
                                            @if($student->student_nisn)
                                                <br><small class="text-muted">NISN: {{ $student->student_nisn }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $student->student_full_name }}
                                            @if($student->age)
                                                <br><small class="text-muted">{{ $student->age }} tahun</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $student->gender_text }}</span>
                                        </td>
                                        <td>
                                            {{ $student->class->class_name ?? '-' }}
                                        </td>
                                        <td>
                                            {{ $student->student_parent_phone ?? '-' }}
                                        </td>
                                        <td>
                                            @if($student->student_status)
                                                <span class="badge bg-success">{{ $student->status_text }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $student->status_text }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="{{ route('students.show', $student) }}" class="detail-link" title="Detail" style="display: inline-block; padding: 0.375rem 0.75rem; background-color: #17a2b8; color: white; text-decoration: none; border-radius: 0.25rem; margin-right: 5px;">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('students.edit', $student) }}" class="btn btn-warning btn-sm action-btn" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-secondary btn-sm action-btn btn-reset-password" 
                                                        data-student-id="{{ $student->student_id }}"
                                                        data-student-name="{{ $student->student_full_name }}"
                                                        title="Reset Password">
                                                    <i class="fas fa-key"></i>
                                                </button>
                                                <form action="{{ route('students.destroy', $student) }}" method="POST" class="d-inline delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-danger btn-sm action-btn btn-delete" 
                                                            data-student-name="{{ $student->student_full_name }}"
                                                            title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-center">
                                            @if(request('search') || request('class_id') || request('status'))
                                                Tidak ada data peserta didik yang sesuai dengan filter
                                            @else
                                                Tidak ada data peserta didik
                                            @endif
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        @if($students->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                <nav aria-label="Page navigation">
                                    <ul class="pagination">
                                        {{-- Previous Page Link --}}
                                        @if ($students->onFirstPage())
                                            <li class="page-item disabled">
                                                <span class="page-link">Sebelumnya</span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $students->previousPageUrl() }}">Sebelumnya</a>
                                            </li>
                                        @endif

                                        {{-- Pagination Elements --}}
                                        @php
                                            $currentPage = $students->currentPage();
                                            $lastPage = $students->lastPage();
                                            
                                            // Hitung range halaman yang akan ditampilkan (maksimal 10 nomor)
                                            $startPage = max(1, $currentPage - 4);
                                            $endPage = min($lastPage, $startPage + 9);
                                            
                                            // Jika endPage terlalu dekat dengan lastPage, sesuaikan startPage
                                            if ($endPage - $startPage < 9 && $startPage > 1) {
                                                $startPage = max(1, $endPage - 9);
                                            }
                                        @endphp

                                        {{-- Tampilkan halaman pertama jika tidak termasuk dalam range --}}
                                        @if($startPage > 1)
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $students->url(1) }}">1</a>
                                            </li>
                                            @if($startPage > 2)
                                                <li class="page-item disabled">
                                                    <span class="page-link">...</span>
                                                </li>
                                            @endif
                                        @endif

                                        {{-- Tampilkan range halaman utama --}}
                                        @for($page = $startPage; $page <= $endPage; $page++)
                                            @if ($page == $currentPage)
                                                <li class="page-item active">
                                                    <span class="page-link">{{ $page }}</span>
                                                </li>
                                            @else
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $students->url($page) }}">{{ $page }}</a>
                                                </li>
                                            @endif
                                        @endfor

                                        {{-- Tampilkan halaman terakhir jika tidak termasuk dalam range --}}
                                        @if($endPage < $lastPage)
                                            @if($endPage < $lastPage - 1)
                                                <li class="page-item disabled">
                                                    <span class="page-link">...</span>
                                                </li>
                                            @endif
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $students->url($lastPage) }}">{{ $lastPage }}</a>
                                            </li>
                                        @endif

                                        {{-- Next Page Link --}}
                                        @if ($students->hasMorePages())
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $students->nextPageUrl() }}">Selanjutnya</a>
                                            </li>
                                        @else
                                            <li class="page-item disabled">
                                                <span class="page-link">Selanjutnya</span>
                                            </li>
                                        @endif
                                    </ul>
                                </nav>
                            </div>
                        @endif
                    </form>
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
          <i class="fa fa-trash me-2"></i>Konfirmasi Hapus Peserta Didik
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center py-4">
        <div class="mb-3">
          <i class="fa fa-question-circle text-warning" style="font-size: 3rem;"></i>
        </div>
        <h5 class="mb-3">Yakin ingin menghapus peserta didik <span id="modalStudentName"></span>?</h5>
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

<!-- Modal Konfirmasi Reset Password -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-secondary text-white border-0">
        <h5 class="modal-title" id="resetPasswordModalLabel">
          <i class="fa fa-key me-2"></i>Konfirmasi Reset Password
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center py-4">
        <div class="mb-3">
          <i class="fa fa-key text-warning" style="font-size: 3rem;"></i>
        </div>
        <h5 class="mb-3">Reset password untuk peserta didik <span id="modalResetStudentName"></span>?</h5>
        <p class="text-muted mb-0">Password akan diubah menjadi: <strong>password123</strong></p>
      </div>
      <div class="modal-footer border-0 justify-content-center">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
          <i class="fa fa-times me-2"></i>Batal
        </button>
        <button type="button" class="btn btn-secondary px-4" id="confirmResetPasswordBtn">
          <i class="fa fa-key me-2"></i>Ya, Reset Password
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Konfirmasi Reset Password Massal -->
<div class="modal fade" id="resetPasswordMassalModal" tabindex="-1" aria-labelledby="resetPasswordMassalModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg">
      <!-- Header dengan gradient yang lebih menarik -->
      <div class="modal-header border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);">
        <div class="d-flex align-items-center">
          <div class="bg-white bg-opacity-25 rounded-circle p-3 me-3 shadow-sm">
            <i class="fa fa-key text-white" style="font-size: 1.8rem;"></i>
          </div>
          <div>
            <h5 class="modal-title text-white mb-0" id="resetPasswordMassalModalLabel">
              <strong>🔐 Reset Password Massal</strong>
            </h5>
            <small class="text-white-75">Konfirmasi aksi reset password untuk multiple peserta didik</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <!-- Body dengan animasi dan informasi detail yang lebih menarik -->
      <div class="modal-body p-5">
        <div class="text-center mb-4">
          <!-- Icon animasi dengan efek yang lebih menarik -->
          <div class="position-relative mb-4">
            <div class="bg-gradient-warning rounded-circle d-inline-flex align-items-center justify-content-center shadow-lg" style="width: 100px; height: 100px; background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);">
              <i class="fa fa-key text-white" style="font-size: 3rem; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));"></i>
            </div>
            <div class="position-absolute top-0 start-100 translate-middle">
              <span class="badge bg-danger rounded-pill shadow-sm" id="modalResetMassalCount" style="font-size: 1rem; padding: 0.6rem 1rem;"></span>
            </div>
            <!-- Efek ripple -->
            <div class="position-absolute top-50 start-50 translate-middle" style="width: 120px; height: 120px; border: 3px solid rgba(255, 193, 7, 0.3); border-radius: 50%; animation: ripple 2s infinite;"></div>
          </div>
          
          <!-- Pesan utama dengan styling yang lebih menarik -->
          <div class="mb-4">
            <h3 class="text-dark mb-2 fw-bold">
              Reset Password untuk <span class="text-primary" id="modalResetMassalCountText" style="font-size: 2.5rem; text-shadow: 0 2px 4px rgba(0,0,0,0.1);"></span> Peserta Didik?
            </h3>
            <p class="text-muted fs-5">Tindakan ini akan mengubah password semua peserta didik yang dipilih</p>
          </div>
          
          <!-- Informasi detail dengan card yang lebih menarik -->
          <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);">
            <div class="card-body p-4">
              <div class="d-flex align-items-start">
                <div class="bg-info bg-opacity-10 rounded-circle p-2 me-3 mt-1">
                  <i class="fa fa-info-circle text-info" style="font-size: 1.2rem;"></i>
                </div>
                <div class="text-start flex-grow-1">
                  <h6 class="text-info fw-bold mb-3">📋 Informasi Reset Password:</h6>
                  <ul class="mb-0 ps-3" style="list-style: none;">
                    <li class="mb-2">
                      <i class="fa fa-check-circle text-success me-2"></i>
                      Semua password akan diubah menjadi: 
                      <code class="bg-warning bg-opacity-25 px-3 py-2 rounded fw-bold fs-6">password123</code>
                    </li>
                    <li class="mb-2">
                      <i class="fa fa-sign-in-alt text-primary me-2"></i>
                      Peserta didik harus login ulang dengan password baru
                    </li>
                    <li class="mb-2">
                      <i class="fa fa-exclamation-triangle text-warning me-2"></i>
                      Tindakan ini tidak dapat dibatalkan
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Warning box dengan styling yang lebih menarik -->
          <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #fff3e0 0%, #fce4ec 100%); border-left: 4px solid #ff9800;">
            <div class="card-body p-4">
              <div class="d-flex align-items-center">
                <div class="bg-warning bg-opacity-20 rounded-circle p-2 me-3">
                  <i class="fa fa-exclamation-triangle text-warning" style="font-size: 1.3rem;"></i>
                </div>
                <div>
                  <h6 class="text-warning fw-bold mb-1">⚠️ Peringatan Penting:</h6>
                  <p class="text-dark mb-0">Pastikan semua peserta didik yang dipilih benar sebelum melanjutkan. Tindakan ini akan mempengaruhi akses login mereka.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Footer dengan tombol yang lebih menarik -->
      <div class="modal-footer border-0 bg-light justify-content-center gap-4 p-4">
        <button type="button" class="btn btn-outline-secondary px-5 py-3 fw-bold" data-bs-dismiss="modal" style="border-radius: 25px; min-width: 140px;">
          <i class="fa fa-times me-2"></i>
          Batal
        </button>
        <button type="button" class="btn btn-warning px-5 py-3 fw-bold shadow-lg" id="confirmResetPasswordMassalBtn" style="border-radius: 25px; min-width: 180px; background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); border: none;">
          <i class="fa fa-key me-2"></i>
          Ya, Reset Password
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
    let studentToResetPassword = null;
    
    // Debug: Log when page loads
    console.log('Students index page loaded');
    console.log('Action buttons found:', document.querySelectorAll('.action-btn').length);
    console.log('Detail links found:', document.querySelectorAll('.detail-link').length);
    
    // Event delegation untuk tombol delete dan reset password
    document.addEventListener('click', function(e) {
        // Tombol delete
        if (e.target.closest('.btn-delete')) {
            e.preventDefault();
            const btn = e.target.closest('.btn-delete');
            const studentName = btn.getAttribute('data-student-name');
            formToDelete = btn.closest('form');
            document.getElementById('modalStudentName').textContent = studentName;
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }
        
        // Tombol reset password
        if (e.target.closest('.btn-reset-password')) {
            e.preventDefault();
            const btn = e.target.closest('.btn-reset-password');
            const studentId = btn.getAttribute('data-student-id');
            const studentName = btn.getAttribute('data-student-name');
            studentToResetPassword = studentId;
            document.getElementById('modalResetStudentName').textContent = studentName;
            const modal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
            modal.show();
        }
    });
    
    // Konfirmasi delete
    document.getElementById('confirmDeleteBtn').onclick = function() {
        if (formToDelete) formToDelete.submit();
    };
    
    // Konfirmasi reset password
    document.getElementById('confirmResetPasswordBtn').onclick = function() {
        if (studentToResetPassword) {
            resetPassword(studentToResetPassword);
        }
    };
    
    // Konfirmasi reset password massal
    document.getElementById('confirmResetPasswordMassalBtn').onclick = function() {
        resetPasswordMassal();
    };

    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const studentCheckboxes = document.querySelectorAll('.student-checkbox');
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.getElementById('selectedCount');
    const selectedIds = document.getElementById('selectedIds');

    // Update selected count and show/hide bulk actions
    function updateBulkActions() {
        const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
        const count = checkedBoxes.length;
        
        selectedCount.textContent = count;
        
        if (count > 0) {
            bulkActions.style.display = 'block';
        } else {
            bulkActions.style.display = 'none';
        }

        // Update hidden input with selected IDs
        const ids = Array.from(checkedBoxes).map(cb => cb.value);
        selectedIds.value = JSON.stringify(ids);
    }

    // Select all checkbox functionality
    selectAllCheckbox.addEventListener('change', function() {
        studentCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActions();
    });

    // Individual checkbox functionality
    studentCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBulkActions();
            
            // Update select all checkbox
            const allChecked = document.querySelectorAll('.student-checkbox:checked').length;
            const totalCheckboxes = studentCheckboxes.length;
            
            if (allChecked === 0) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = false;
            } else if (allChecked === totalCheckboxes) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = true;
            } else {
                selectAllCheckbox.indeterminate = true;
            }
        });
    });

    // Select all function
    window.selectAll = function() {
        studentCheckboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
        selectAllCheckbox.checked = true;
        selectAllCheckbox.indeterminate = false;
        updateBulkActions();
    };

    // Deselect all function
    window.deselectAll = function() {
        studentCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
        updateBulkActions();
    };

    // Delete selected function
    window.deleteSelected = function() {
        const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
        const count = checkedBoxes.length;
        
        if (count === 0) {
            showToast('Pilih data yang akan dihapus terlebih dahulu!', 'error');
            return;
        }

        const confirmMessage = `Yakin ingin menghapus ${count} data peserta didik yang dipilih?`;
        if (confirm(confirmMessage)) {
            const form = document.getElementById('bulkDeleteForm');
            
            // Debug: Log form details
            console.log('Form action:', form.action);
            console.log('Form method:', form.method);
            console.log('Selected IDs:', document.getElementById('selectedIds').value);
            
            // Ensure method is POST
            form.method = 'POST';
            
            // Submit form
            form.submit();
        }
    };

    // Graduate selected function
    window.graduateSelected = function() {
        const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
        const count = checkedBoxes.length;
        
        if (count === 0) {
            showToast('Pilih data yang akan diluluskan terlebih dahulu!', 'error');
            return;
        }

        const confirmMessage = `Yakin ingin meluluskan ${count} data peserta didik yang dipilih?`;
        if (confirm(confirmMessage)) {
            const form = document.getElementById('bulkDeleteForm'); // Reusing the form for simplicity, but ideally, this would be a separate form
            
            // Debug: Log form details
            console.log('Form action:', form.action);
            console.log('Form method:', form.method);
            console.log('Selected IDs:', document.getElementById('selectedIds').value);
            
            // Ensure method is POST
            form.method = 'POST';
            form.action = '/bulk-graduate-students'; // Assuming a new route for graduation
            
            // Submit form
            form.submit();
        }
    };

    // Reset password selected function
    window.resetPasswordSelected = function() {
        const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
        const count = checkedBoxes.length;
        
        if (count === 0) {
            showToast('Pilih data yang akan direset password terlebih dahulu!', 'error');
            return;
        }

        // Update modal dengan jumlah yang dipilih
        document.getElementById('modalResetMassalCount').textContent = count;
        document.getElementById('modalResetMassalCountText').textContent = count;
        
        // Tampilkan modal konfirmasi
        const modal = new bootstrap.Modal(document.getElementById('resetPasswordMassalModal'));
        modal.show();
        
        // Tambahkan animasi pada modal
        setTimeout(() => {
            const modalElement = document.getElementById('resetPasswordMassalModal');
            modalElement.classList.add('animate__animated', 'animate__fadeIn');
        }, 100);
    };

    // Fungsi untuk menampilkan toast
    function showToast(message, type = 'success') {
        if (type === 'success') {
            document.getElementById('toastMessage').textContent = message;
            const toast = new bootstrap.Toast(document.getElementById('successToast'));
            toast.show();
        } else {
            document.getElementById('errorToastMessage').textContent = message;
            const toast = new bootstrap.Toast(document.getElementById('errorToast'));
            toast.show();
        }
    }

    // Reset password function
    window.resetPassword = function(studentId) {
        fetch(`/students/${studentId}/reset-password`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Tutup modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('resetPasswordModal'));
                modal.hide();
                
                // Tampilkan toast sukses
                showToast('Password berhasil direset menjadi: password123', 'success');
                
                // Reset variabel
                studentToResetPassword = null;
            } else {
                showToast('Gagal reset password: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Terjadi kesalahan saat reset password', 'error');
        });
    };

    // Reset password massal function
    window.resetPasswordMassal = function() {
        const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
        const count = checkedBoxes.length;
        
        if (count === 0) {
            showToast('Pilih data yang akan direset password terlebih dahulu!', 'error');
            return;
        }

        // Update modal dengan jumlah yang dipilih
        document.getElementById('modalResetMassalCount').textContent = count;
        document.getElementById('modalResetMassalCountText').textContent = count;
        
        // Tampilkan modal konfirmasi
        const modal = new bootstrap.Modal(document.getElementById('resetPasswordMassalModal'));
        modal.show();
        
        // Tambahkan animasi pada modal
        setTimeout(() => {
            const modalElement = document.getElementById('resetPasswordMassalModal');
            modalElement.classList.add('animate__animated', 'animate__fadeIn');
        }, 100);
    };

    // Fungsi untuk eksekusi reset password massal
    function executeResetPasswordMassal() {
        const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
        const studentIds = Array.from(checkedBoxes).map(cb => cb.value);
        
        fetch('/students/reset-password-massal', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ student_ids: studentIds }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Tutup modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('resetPasswordMassalModal'));
                modal.hide();
                
                // Tampilkan toast sukses
                showToast(`Password berhasil direset untuk ${data.count} peserta didik menjadi: password123`, 'success');
                
                // Uncheck semua checkbox
                studentCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
                updateBulkActions();
            } else {
                showToast('Gagal reset password massal: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Terjadi kesalahan saat reset password massal', 'error');
        });
    }

    // Update event listener untuk reset password massal
    document.getElementById('confirmResetPasswordMassalBtn').onclick = function() {
        executeResetPasswordMassal();
    };
});
</script>
@endsection 