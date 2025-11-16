@extends('layouts.adminty')

@section('title', 'Kirim Tagihan Masal WhatsApp')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fab fa-whatsapp text-success me-2"></i>
                        Kirim Tagihan Masal WhatsApp
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Filter Section -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <label for="period_id" class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                            <select class="form-control select-primary" id="period_id" required>
                                <option value="all" selected>Semua Tahun Ajaran</option>
                                @foreach($periods as $period)
                                    <option value="{{ $period->period_id }}">
                                        {{ $period->period_start }}/{{ $period->period_end }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="pos_id" class="form-label">POS Pembayaran <span class="text-danger">*</span></label>
                            <select class="form-control select-primary" id="pos_id" required>
                                <option value="all" selected>Semua POS</option>
                                @foreach($posPembayaran as $pos)
                                    <option value="{{ $pos->pos_id }}">{{ $pos->pos_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="class_id" class="form-label">Kelas (Opsional)</label>
                            <select class="form-control select-primary" id="class_id">
                                <option value="" selected>Semua Kelas</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->class_id }}">{{ $class->class_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="month_id" class="form-label">Bulan (Opsional)</label>
                            <select class="form-control select-primary" id="month_id">
                                <option value="" selected>Semua Bulan</option>
                                <option value="1">Juli</option>
                                <option value="2">Agustus</option>
                                <option value="3">September</option>
                                <option value="4">Oktober</option>
                                <option value="5">November</option>
                                <option value="6">Desember</option>
                                <option value="7">Januari</option>
                                <option value="8">Februari</option>
                                <option value="9">Maret</option>
                                <option value="10">April</option>
                                <option value="11">Mei</option>
                                <option value="12">Juni</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="student_status" class="form-label">Status Siswa</label>
                            <select class="form-control select-primary" id="student_status">
                                <option value="" selected>Semua Status</option>
                                <option value="aktif">Aktif</option>
                                <option value="tidak aktif">Tidak Aktif</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="bill_type" class="form-label">Jenis Tagihan</label>
                            <select class="form-control select-primary" id="bill_type">
                                <option value="all" selected>Semua Tagihan</option>
                                <option value="bulanan">Tagihan Bulanan</option>
                                <option value="bebas">Tagihan Bebas</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <button type="button" class="btn btn-primary" id="btnSearchBills">
                                <i class="fa fa-search me-2"></i>Cari Tagihan
                            </button>

                            <button type="button" class="btn btn-success ms-2 text-white" id="btnSendConsolidated" style="display: none;" disabled>
                                <i class="fa fa-envelope me-2"></i>Kirim Pesan Semua
                            </button>
                        </div>
                    </div>

                    <!-- Filter Info -->
                    <div class="row mb-3" id="filterInfo" style="display: none;">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6 class="mb-2"><i class="fa fa-filter me-2"></i>Filter yang Dipilih:</h6>
                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>Tahun Ajaran:</strong><br>
                                        <span id="filterPeriod">-</span>
                                    </div>
                                    <div class="col-md-2">
                                        <strong>POS:</strong><br>
                                        <span id="filterPos">-</span>
                                    </div>
                                    <div class="col-md-2">
                                        <strong>Kelas:</strong><br>
                                        <span id="filterClass">-</span>
                                    </div>
                                    <div class="col-md-2">
                                        <strong>Bulan:</strong><br>
                                        <span id="filterMonth">-</span>
                                    </div>
                                    <div class="col-md-2">
                                        <strong>Status Siswa:</strong><br>
                                        <span id="filterStatus">-</span>
                                    </div>
                                    <div class="col-md-2">
                                        <strong>Jenis Tagihan:</strong><br>
                                        <span id="filterBillType">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4" id="statsSection" style="display: none;">
                        <div class="col-md-2">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title" id="totalStudents">0</h5>
                                    <p class="card-text small">Total Siswa</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title" id="totalBills">0</h5>
                                    <p class="card-text small">Total Tagihan</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title" id="unpaidBills">0</h5>
                                    <p class="card-text small">Belum Lunas</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title" id="withPhone">0</h5>
                                    <p class="card-text small">Ada No. HP</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title" id="selectedBills">0</h5>
                                    <p class="card-text small">Dipilih</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-secondary text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title" id="totalAmount">0</h5>
                                    <p class="card-text small">Total Nominal</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bills Table -->
                    <div class="table-responsive" id="billsTable" style="display: none;">
                        <table class="table table-striped table-hover" id="billsDataTable">
                            <thead class="table-dark">
                                <tr>
                                    <th width="50" style="text-align: center;">
                                        <div class="form-check d-flex justify-content-center">
                                            <input type="checkbox" id="selectAll" class="form-check-input" style="width: 18px; height: 18px; margin: 0; cursor: pointer;">
                                        </div>
                                    </th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>Status</th>
                                    <th>POS</th>
                                    <th>Total Tunggakan</th>
                                    <th>Jumlah Item</th>
                                    <th>No. HP</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="billsTableBody">
                                <!-- Data akan diisi via JavaScript -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Message Template -->
                    <div class="row mt-4" id="messageSection" style="display: none;">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fa fa-edit me-2"></i>Template Pesan (Opsional)
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <textarea class="form-control" id="messageTemplate" rows="4" 
                                        placeholder="Masukkan pesan tambahan yang akan ditampilkan di notifikasi WhatsApp..."></textarea>
                                    <small class="text-muted">
                                        Pesan ini akan ditambahkan ke template default tagihan SPP
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Progress -->
<div class="modal fade" id="progressModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-paper-plane text-success me-2"></i>
                    Mengirim Tagihan Masal
                </h5>
            </div>
            <div class="modal-body">
                <div class="progress mb-3">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" id="progressBar" style="width: 0%"></div>
                </div>
                <div class="text-center">
                    <span id="progressText">0 dari 0 tagihan terkirim</span>
                </div>
                
                <div class="mt-4">
                    <h6>Log Pengiriman:</h6>
                    <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                        <div id="progressLog">
                            <!-- Log akan diisi di sini -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btnCloseProgress" style="display: none;">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Checkbox Styling in Table */
.table-responsive .form-check {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 20px;
}

.table-responsive .form-check-input {
    width: 18px !important;
    height: 18px !important;
    margin: 0 !important;
    cursor: pointer;
    border: 2px solid #ced4da;
    border-radius: 0.25rem;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-color: #fff;
    position: relative;
    vertical-align: middle;
    flex-shrink: 0;
    transition: all 0.2s ease;
}

.table-responsive .form-check-input:checked {
    background-color: #01a9ac;
    border-color: #01a9ac;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='m6 10 3 3 6-6'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: center;
    background-size: 14px 14px;
}

.table-responsive .form-check-input:focus {
    border-color: #01a9ac;
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(1, 169, 172, 0.25);
}

.table-responsive .form-check-input:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background-color: #e9ecef;
}

.table-responsive .form-check-input:hover:not(:disabled) {
    border-color: #01a9ac;
}

/* Table Header Checkbox */
.table-dark .form-check-input {
    border-color: rgba(255, 255, 255, 0.5);
}

.table-dark .form-check-input:checked {
    background-color: #01a9ac;
    border-color: #01a9ac;
}

.table-dark .form-check-input:focus {
    border-color: #01a9ac;
    box-shadow: 0 0 0 0.2rem rgba(1, 169, 172, 0.5);
}

/* Table Cell Alignment */
.table-responsive th:first-child,
.table-responsive td:first-child {
    text-align: center;
    vertical-align: middle;
}
</style>
@endpush

@section('scripts')
<script>
let billsData = [];
let selectedBills = [];

// Define showDetail function early (before DOM ready)
function showDetail(studentId) {
    // Find the data for this student
    const studentData = billsData.find(item => item.student_id == studentId);
    
    if (!studentData) {
        console.error('Student data not found for ID:', studentId);
        if (typeof showToast === 'function') {
            showToast('Data siswa tidak ditemukan', 'error');
        } else {
            alert('Data siswa tidak ditemukan');
        }
        return;
    }
    
    let detailHtml = `
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Nama Siswa:</strong> ${studentData.nama || '-'}<br>
                <strong>NIS:</strong> ${studentData.nis || '-'}<br>
                <strong>Kelas:</strong> ${studentData.kelas || '-'}
            </div>
            <div class="col-md-6">
                <strong>Total Tunggakan:</strong> <span class="text-danger fw-bold">Rp ${new Intl.NumberFormat('id-ID').format(studentData.unpaid_amount || 0)}</span><br>
                <strong>Jumlah Item:</strong> ${studentData.unpaid_bills || 0} item
            </div>
        </div>
        <hr>
        <h6>Detail Item Tunggakan:</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>POS</th>
                        <th>Jenis</th>
                        <th>Detail</th>
                        <th>Tagihan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    if (studentData.bill_details && studentData.bill_details.length > 0) {
        studentData.bill_details.forEach(item => {
            const statusBadge = item.is_paid ? 
                '<span class="badge bg-success">Lunas</span>' : 
                '<span class="badge bg-warning">Belum Lunas</span>';
            
            const jenisBadge = item.bill_type === 'bulanan' ? 
                '<span class="badge bg-primary">Bulanan</span>' : 
                '<span class="badge bg-info">Bebas</span>';
            
            detailHtml += `
                <tr>
                    <td>${item.pos_name || '-'}</td>
                    <td>${jenisBadge}</td>
                    <td>${item.detail || '-'}</td>
                    <td>Rp ${new Intl.NumberFormat('id-ID').format(item.amount || 0)}</td>
                    <td>${statusBadge}</td>
                </tr>
            `;
        });
    } else {
        detailHtml += '<tr><td colspan="5" class="text-center">Tidak ada data tagihan</td></tr>';
    }
    
    detailHtml += `
                </tbody>
            </table>
        </div>
    `;
    
    // Buat modal untuk menampilkan detail
    if ($('#detailModal').length === 0) {
        $('body').append(`
            <div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="detailModalLabel">Detail Tagihan Siswa</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body" id="detailModalBody">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        `);
    }
    
    $('#detailModalBody').html(detailHtml);
    $('#detailModal').modal('show');
}

// Make showDetail function global
window.showDetail = showDetail;

$(document).ready(function() {
    // Search bills
    $('#btnSearchBills').on('click', function() {
        searchBills();
    });

    // Default values are now set in HTML with 'selected' attribute

    // Select all students
    $('#selectAll').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.student-checkbox').prop('checked', isChecked);
        updateSelectedBills();
    });

    // Update selected bills when checkbox changes
    $(document).on('change', '.student-checkbox', function() {
        updateSelectedBills();
    });

    // Handle detail button click using event delegation
    $(document).on('click', '.btn-detail-bill', function() {
        const studentId = $(this).data('student-id');
        if (studentId) {
            window.showDetail(studentId);
        }
    });

    // Send consolidated bills
    $('#btnSendConsolidated').on('click', function() {
        if (selectedBills.length === 0) {
            showToast('Pilih tagihan yang akan dikirim terlebih dahulu!', 'warning');
            return;
        }
        
        const messageTemplate = $('#messageTemplate').val().trim();
        sendConsolidatedBills(messageTemplate);
    });

    
});

function searchBills() {
    const periodId = $('#period_id').val();
    const posId = $('#pos_id').val();
    const classId = $('#class_id').val();
    const monthId = $('#month_id').val();
    const studentStatus = $('#student_status').val();
    const billType = $('#bill_type').val();

    console.log('Search parameters:', { periodId, posId, classId, monthId, studentStatus, billType });

    if (!periodId || !posId) {
        showToast('Pilih Tahun Ajaran dan POS Pembayaran terlebih dahulu!', 'warning');
        return;
    }

    $('#btnSearchBills').prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-2"></i>Mencari...');

    const requestData = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        period_id: periodId,
        pos_id: posId,
        class_id: classId,
        month_id: monthId,
        student_status: studentStatus,
        bill_type: billType
    };

    console.log('Request data:', requestData);
    console.log('CSRF token:', $('meta[name="csrf-token"]').attr('content'));

    $.ajax({
        url: '/manage/bulk-whatsapp/bills',
        method: 'POST',
        data: requestData,
        success: function(response) {
            console.log('Success response:', response);
            if (response.success) {
                billsData = response.bills;
                
                // Debug info untuk kasus 'all'
                if (billType === 'all') {
                    const bulananCount = response.bills.filter(b => b.bill_type === 'bulanan').length;
                    const bebasCount = response.bills.filter(b => b.bill_type === 'bebas').length;
                    console.log('Bill type breakdown:', {
                        total: response.bills.length,
                        bulanan: bulananCount,
                        bebas: bebasCount
                    });
                }
                
                displayBills(response.bills);
                displayStats(response.stats);
                displayFilterInfo();
                                 $('#billsTable, #statsSection, #messageSection, #filterInfo').show();

                $('#btnSendConsolidated').show();
            } else {
                showToast(response.message || 'Gagal mengambil data tagihan', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error response:', { xhr, status, error });
            console.error('Response text:', xhr.responseText);
            showToast('Terjadi kesalahan saat mengambil data tagihan', 'error');
        },
        complete: function() {
            $('#btnSearchBills').prop('disabled', false).html('<i class="fa fa-search me-2"></i>Cari Tagihan');
        }
    });
}

function displayBills(bills) {
    console.log('Displaying bills:', bills);
    const tbody = $('#billsTableBody');
    tbody.empty();

    if (!bills || bills.length === 0) {
        console.log('No bills to display');
        tbody.append('<tr><td colspan="10" class="text-center">Tidak ada data tagihan</td></tr>');
        return;
    }

    bills.forEach(function(student, index) {
        console.log('Processing student:', student);
        const row = `
            <tr>
                <td style="text-align: center;">
                    <div class="form-check d-flex justify-content-center">
                        <input type="checkbox" class="form-check-input student-checkbox" 
                               value="${student.student_id}" data-index="${index}"
                               style="width: 18px; height: 18px; margin: 0; cursor: pointer;"
                               ${!student.has_phone ? 'disabled' : ''}>
                    </div>
                </td>
                <td>${student.nis || '-'}</td>
                <td>${student.nama || '-'}</td>
                <td>${student.kelas || '-'}</td>
                <td>
                    <span class="badge bg-success">Aktif</span>
                </td>
                <td>${student.pos_list || '-'}</td>
                <td class="text-danger fw-bold">Rp ${Number(student.unpaid_amount || 0).toLocaleString('id-ID')}</td>
                <td>
                    <span class="badge bg-warning">${student.unpaid_bills} item</span>
                </td>
                <td>
                    ${student.has_phone ? 
                        '<span class="badge bg-info">Ada</span>' : 
                        '<span class="badge bg-danger">Tidak Ada</span>'
                    }
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-info btn-detail-bill" data-student-id="${student.student_id}" style="cursor: pointer;">
                        <i class="fa fa-eye"></i> Detail
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });

    updateSelectedBills();
}

function displayStats(stats) {
    console.log('Displaying stats:', stats);
    $('#totalStudents').text(stats.total_students || 0);
    $('#totalBills').text(stats.total_bills || 0);
    $('#unpaidBills').text(stats.unpaid_bills || 0);
    $('#withPhone').text(stats.with_phone || 0);
    $('#totalAmount').text('Rp ' + Number(stats.total_amount || 0).toLocaleString('id-ID'));
}

function displayFilterInfo() {
    const periodId = $('#period_id').val();
    const posId = $('#pos_id').val();
    const classId = $('#class_id').val();
    const monthId = $('#month_id').val();
    const studentStatus = $('#student_status').val();
    const billType = $('#bill_type').val();
    
    // Tahun Ajaran
    if (periodId === 'all') {
        $('#filterPeriod').text('Semua Tahun Ajaran');
    } else if (periodId) {
        const periodText = $('#period_id option:selected').text();
        $('#filterPeriod').text(periodText);
    } else {
        $('#filterPeriod').text('-');
    }
    
    // POS
    if (posId === 'all') {
        $('#filterPos').text('Semua POS');
    } else if (posId) {
        const posText = $('#pos_id option:selected').text();
        $('#filterPos').text(posText);
    } else {
        $('#filterPos').text('-');
    }
    
    // Kelas
    if (classId) {
        const classText = $('#class_id option:selected').text();
        $('#filterClass').text(classText);
    } else {
        $('#filterClass').text('Semua Kelas');
    }
    
    // Bulan
    if (monthId) {
        const monthText = $('#month_id option:selected').text();
        $('#filterMonth').text(monthText);
    } else {
        $('#filterMonth').text('Semua Bulan');
    }
    
    // Status Siswa
    if (studentStatus) {
        $('#filterStatus').text(studentStatus === 'aktif' ? 'Aktif' : 'Tidak Aktif');
    } else {
        $('#filterStatus').text('Semua Status');
    }
    
    // Jenis Tagihan
    if (billType) {
        if (billType === 'bulanan') {
            $('#filterBillType').text('Tagihan Bulanan');
        } else if (billType === 'bebas') {
            $('#filterBillType').text('Tagihan Bebas');
        } else if (billType === 'all') {
            $('#filterBillType').text('Semua Tagihan');
        }
    } else {
        $('#filterBillType').text('Semua Tagihan');
    }
}

function updateSelectedBills() {
    selectedBills = [];
    $('.student-checkbox:checked').each(function() {
        const studentId = $(this).val();
        const index = $(this).data('index');
        const student = billsData[index];
        
        // Tambahkan semua bill details dari student yang dipilih
        if (student.bill_details) {
            student.bill_details.forEach(function(bill) {
                if (!bill.is_paid) { // Hanya tagihan yang belum lunas
                    selectedBills.push({
                        student_id: studentId,
                        bill_id: bill.bill_id,
                        bill_type: bill.bill_type
                    });
                }
            });
        }
    });

    $('#selectedBills').text(selectedBills.length);
    $('#selectedCount').text(selectedBills.length);
    
    if (selectedBills.length > 0) {
        $('#btnSendConsolidated').prop('disabled', false);
    } else {
        $('#btnSendConsolidated').prop('disabled', true);
    }
}




function sendSingleBill(bill, messageTemplate, current, total) {
    console.log('sendSingleBill called with:', { bill, messageTemplate, current, total });
    
    return new Promise(function(resolve) {
        console.log('Making AJAX request to /manage/bulk-whatsapp/send with data:', {
            bills: [bill],
            message_template: messageTemplate
        });
        
        $.ajax({
            url: '/manage/bulk-whatsapp/send',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                bills: [bill],
                message_template: messageTemplate
            },
            timeout: 30000, // 30 second timeout
            success: function(response) {
                console.log('Bulk bill send response:', response);
                
                if (response.success && response.results.length > 0) {
                    const result = response.results[0];
                    const studentName = billsData.find(b => b.bill_id == bill.bill_id)?.nama || 'Unknown';
                    console.log('Found student name:', studentName, 'for bill_id:', bill.bill_id);
                    
                    const resultData = {
                        success: result.status === 'success',
                        student_name: studentName,
                        message: result.message
                    };
                    console.log('Resolving with:', resultData);
                    resolve(resultData);
                } else {
                    console.log('Invalid response structure:', response);
                    resolve({
                        success: false,
                        student_name: 'Unknown',
                        message: 'Response tidak valid'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Bulk bill sending error:', { xhr, status, error });
                
                let errorMessage = 'Gagal mengirim tagihan';
                if (status === 'timeout') {
                    errorMessage = 'Request timeout (30 detik)';
                } else if (xhr.status === 0) {
                    errorMessage = 'Tidak dapat terhubung ke server';
                } else if (xhr.status === 500) {
                    errorMessage = 'Error server internal';
                } else if (xhr.status === 404) {
                    errorMessage = 'Endpoint tidak ditemukan';
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        errorMessage = response.message || errorMessage;
                    } catch (e) {
                        errorMessage = `Error: ${xhr.status} ${xhr.statusText}`;
                    }
                }
                
                resolve({
                    success: false,
                    student_name: 'Unknown',
                    message: errorMessage
                });
            }
        });
    });
}

function addProgressLog(message, type) {
    const logDiv = $('<div>').addClass(`text-${type} mb-1`).text(message);
    $('#progressLog').append(logDiv);
    $('#progressLog').scrollTop($('#progressLog')[0].scrollHeight);
}

// Close progress modal
$('#btnCloseProgress').on('click', function() {
    $('#progressModal').modal('hide');
});

function sendConsolidatedBills(messageTemplate) {
    if (selectedBills.length === 0) {
        showToast('Pilih tagihan yang akan dikirim terlebih dahulu!', 'warning');
        return;
    }

    console.log('Starting consolidated bills sending:', {
        totalBills: selectedBills.length,
        messageTemplate: messageTemplate,
        selectedBills: selectedBills
    });
    
    // Disable buttons during sending
    $('#btnSendConsolidated').prop('disabled', true);
    
    // Show progress modal
    $('#progressModal').modal('show');
    $('#progressBar').css('width', '0%');
    $('#progressText').text(`0 dari ${selectedBills.length} tagihan terkirim`);
    $('#progressLog').empty();
    $('#btnCloseProgress').hide();

    // Send consolidated bills
    $.ajax({
        url: '/manage/bulk-whatsapp/send-consolidated',
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            bills: selectedBills,
            message_template: messageTemplate
        },
        timeout: 60000, // 60 second timeout for consolidated
        success: function(response) {
            console.log('Consolidated bills response:', response);
            
            if (response.success) {
                let successCount = 0;
                let failedCount = 0;
                
                response.results.forEach(function(result) {
                    if (result.status === 'success') {
                        successCount++;
                        addProgressLog(`✅ ${result.student_id}: Berhasil dikirim (${result.total_bills} tagihan, Total: Rp ${result.total_amount})`, 'success');
                    } else {
                        failedCount++;
                        addProgressLog(`❌ ${result.student_id}: ${result.message}`, 'danger');
                    }
                });
                
                // Update progress
                $('#progressBar').css('width', '100%');
                $('#progressText').text(`Selesai! Berhasil: ${successCount}, Gagal: ${failedCount}`);
                $('#btnCloseProgress').show();
                
                showToast(`Pengiriman konsolidasi selesai! Berhasil: ${successCount}, Gagal: ${failedCount}`, 'success');
            } else {
                addProgressLog(`❌ Error: ${response.message}`, 'danger');
                $('#btnCloseProgress').show();
                showToast(response.message || 'Gagal mengirim pesan konsolidasi', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('Consolidated bills error:', { xhr, status, error });
            
            let errorMessage = 'Terjadi kesalahan saat mengirim pesan konsolidasi';
            if (status === 'timeout') {
                errorMessage = 'Timeout: Request terlalu lama';
            } else if (xhr.status === 0) {
                errorMessage = 'Error: Tidak bisa terhubung ke server';
            } else if (xhr.status === 500) {
                errorMessage = 'Error: Server internal error';
            } else if (xhr.status === 404) {
                errorMessage = 'Error: Endpoint tidak ditemukan';
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = 'Error: ' + xhr.responseJSON.message;
            } else if (xhr.responseText) {
                errorMessage = 'Error: ' + xhr.responseText;
            }
            
            addProgressLog(`❌ ${errorMessage}`, 'danger');
            $('#btnCloseProgress').show();
            showToast(errorMessage, 'error');
        },
        complete: function() {
            // Re-enable buttons
            $('#btnSendConsolidated').prop('disabled', false);
        }
    });
}




</script>
@endsection
