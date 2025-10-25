@extends('layouts.coreui')

@section('title', 'Pencarian Siswa - Pembayaran Online')

<style>
/* Action Button Icon Colors - Ensure white icons */
.btn-outline-primary .fas,
.btn-outline-primary .fa {
    color: inherit !important;
}

.btn-outline-primary:hover .fas,
.btn-outline-primary:hover .fa {
    color: white !important;
}

.btn-outline-success .fas,
.btn-outline-success .fa {
    color: inherit !important;
}

.btn-outline-success:hover .fas,
.btn-outline-success:hover .fa {
    color: white !important;
}

.btn-outline-secondary .fas,
.btn-outline-secondary .fa {
    color: inherit !important;
}

.btn-outline-secondary:hover .fas,
.btn-outline-secondary:hover .fa {
    color: white !important;
}
</style>

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Pencarian Siswa</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <i class="fas fa-th me-1"></i>
                    <a href="{{ route('online-payment.index') }}">Home</a>
                </li>
                <li class="breadcrumb-item active">Pembayaran Online</li>
            </ol>
        </nav>
    </div>

    <!-- Green Line Separator -->
    <div class="border-bottom border-success mb-4" style="border-width: 2px !important;"></div>

    <!-- Filter and Search Section -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="d-flex align-items-center">
                <label class="me-3 fw-bold">Filter:</label>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-primary active" id="filterAll">
                        <i class="fas fa-list me-1"></i>Semua Transaksi
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="filterPending">
                        <i class="fas fa-clock me-1"></i>Menunggu
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="filterSuccess">
                        <i class="fas fa-check me-1"></i>Sukses
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="filterFailed">
                        <i class="fas fa-times me-1"></i>Gagal
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="input-group">
                <input type="text" class="form-control" id="searchInput" 
                       placeholder="No. Tagihan / NIS / Nama">
                <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th>Total Transaksi</th>
                            <th>Status Transaksi</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="searchResults">
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-search fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-0">Masukkan kata kunci pencarian untuk menemukan siswa</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted">
            Menampilkan <span id="showingStart">0</span> - <span id="showingEnd">0</span> dari <span id="totalResults">0</span> siswa
        </div>
        <nav aria-label="Pagination">
            <ul class="pagination pagination-sm mb-0" id="pagination">
                <!-- Pagination will be generated here -->
            </ul>
        </nav>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mb-0">Mencari siswa...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .btn-group .btn {
        border-radius: 0;
    }
    
    .btn-group .btn:first-child {
        border-top-left-radius: 0.375rem;
        border-bottom-left-radius: 0.375rem;
    }
    
    .btn-group .btn:last-child {
        border-top-right-radius: 0.375rem;
        border-bottom-right-radius: 0.375rem;
    }
    
    .table th {
        border-top: none;
        font-weight: 600;
        color: #495057;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .badge {
        font-size: 0.75rem;
    }
    
    .btn-group .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .pagination .page-link {
        color: #007bff;
        border-color: #dee2e6;
    }
    
    .pagination .page-item.active .page-link {
        background-color: #007bff;
        border-color: #007bff;
    }
    
    .breadcrumb-item + .breadcrumb-item::before {
        content: ">";
    }
    
    .form-check-input:checked {
        background-color: #007bff;
        border-color: #007bff;
    }
</style>
@endpush

@push('scripts')
<script>
// Global variables
let searchTimeout;
let currentFilter = 'all';
let currentPage = 1;
let studentsPerPage = 10;

$(document).ready(function() {

    // Initialize
    loadStudents();

    // Filter button handlers
    $('.btn-group .btn').on('click', function() {
        $('.btn-group .btn').removeClass('btn-primary active').addClass('btn-outline-secondary');
        $(this).removeClass('btn-outline-secondary').addClass('btn-primary active');
        
        currentFilter = $(this).attr('id').replace('filter', '').toLowerCase();
        currentPage = 1;
        loadStudents();
    });

    // Search functionality
    $('#searchBtn').on('click', function() {
        currentPage = 1;
        loadStudents();
    });

    $('#searchInput').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            currentPage = 1;
            loadStudents();
        }
    });

    // Real-time search with debounce
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            currentPage = 1;
            loadStudents();
        }, 500);
    });

    // Select all checkbox
    $('#selectAll').on('change', function() {
        $('.student-checkbox').prop('checked', $(this).is(':checked'));
    });

    function loadStudents() {
        const searchQuery = $('#searchInput').val().trim();
        
        // Show loading
        $('#searchResults').html(`
            <tr>
                <td colspan="8" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 mb-0">Memuat data siswa...</p>
                </td>
            </tr>
        `);

        $.ajax({
            url: '{{ route("online-payment.find-student") }}',
            method: 'POST',
            data: {
                search: searchQuery,
                filter: currentFilter,
                page: currentPage,
                per_page: studentsPerPage,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    displayResults(response.students, response.total, response.current_page);
                } else {
                    showError('Terjadi kesalahan saat memuat data siswa');
                }
            },
            error: function(xhr) {
                showError('Terjadi kesalahan saat memuat data siswa');
            }
        });
    }

    function displayResults(students, total, currentPage) {
        const resultsContainer = $('#searchResults');
        
        if (students.length === 0) {
            resultsContainer.html(`
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="fas fa-user-slash fa-3x text-muted mb-3 d-block"></i>
                        <p class="text-muted mb-0">Tidak ada siswa yang ditemukan</p>
                        <small class="text-muted">Coba dengan kata kunci atau filter yang berbeda</small>
                    </td>
                </tr>
            `);
            updatePagination(0, 0, 0);
            return;
        }

        let html = '';
        
        students.forEach(function(student, index) {
            const className = student.class ? student.class.class_name : 'Kelas tidak ditemukan';
            const statusClass = student.student_status ? 'success' : 'danger';
            const statusText = student.student_status ? 'Aktif' : 'Tidak Aktif';
            
            // Get transaction statistics from transfers
            const transfers = student.transfers || [];
            const totalTransactions = transfers.length;
            const pendingTransactions = transfers.filter(p => p.status === 0).length;
            const successTransactions = transfers.filter(p => p.status === 1).length;
            const failedTransactions = transfers.filter(p => p.status === 2).length;
            
            html += `
                <tr>
                    <td>
                        <input type="checkbox" class="form-check-input student-checkbox" value="${student.student_id}">
                    </td>
                    <td>${student.student_nis}</td>
                    <td>${student.student_full_name}</td>
                    <td>${className}</td>
                    <td>
                        <span class="badge bg-${statusClass}">${statusText}</span>
                    </td>
                    <td>${totalTransactions} transaksi</td>
                    <td>
                        <span class="badge bg-warning me-1">${pendingTransactions} pending</span>
                        <span class="badge bg-success me-1">${successTransactions} sukses</span>
                        <span class="badge bg-danger">${failedTransactions} gagal</span>
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                    onclick="viewStudentBills(${student.student_id})" 
                                    title="Lihat Tagihan">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success" 
                                    onclick="viewStudentBills(${student.student_id})" 
                                    title="Bayar Online">
                                <i class="fas fa-credit-card"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        resultsContainer.html(html);
        
        // Update pagination info
        const start = ((currentPage - 1) * studentsPerPage) + 1;
        const end = Math.min(start + studentsPerPage - 1, total);
        updatePagination(start, end, total);
        
        // Generate pagination
        generatePagination(currentPage, Math.ceil(total / studentsPerPage));
    }

    function updatePagination(start, end, total) {
        $('#showingStart').text(start);
        $('#showingEnd').text(end);
        $('#totalResults').text(total);
    }

    function generatePagination(currentPage, totalPages) {
        const pagination = $('#pagination');
        let html = '';
        
        if (totalPages <= 1) {
            pagination.html('');
            return;
        }

        // Previous button
        html += `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="changePage(${currentPage - 1})">
                    Previous
                </a>
            </li>
        `;

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                html += `
                    <li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
                    </li>
                `;
            } else if (i === currentPage - 3 || i === currentPage + 3) {
                html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }

        // Next button
        html += `
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="changePage(${currentPage + 1})">
                    Next
                </a>
            </li>
        `;

        pagination.html(html);
    }

    function showError(message) {
        $('#searchResults').html(`
            <tr>
                <td colspan="8" class="text-center py-4">
                    <div class="alert alert-danger mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ${message}
                    </div>
                </td>
            </tr>
        `);
    }
});

function changePage(page) {
    if (page < 1) return;
    currentPage = page;
    loadStudents();
}

function viewStudentBills(studentId) {
    window.location.href = `{{ url('online-payment/student') }}/${studentId}/bills`;
}
</script>
@endpush 