@extends('layouts.adminty')

@section('title', 'Laporan Tunggakan Siswa')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fa fa-exclamation-triangle me-2"></i>
                        Laporan Tunggakan Siswa
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Filter Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fa fa-filter me-2"></i>
                                        Filter Laporan
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form method="GET" action="{{ route('manage.laporan.tunggakan-siswa') }}" id="filterForm">
                                        <div class="row">
                                            <!-- Tahun Pelajaran -->
                                            <div class="col-md-3 mb-3">
                                                <label for="period_id" class="form-label">Tahun Pelajaran</label>
                                                <select class="form-control select-primary" id="period_id" name="period_id">
                                                    <option value="">Semua Tahun Pelajaran</option>
                                                    @foreach($periods as $period)
                                                        <option value="{{ $period->period_id }}" {{ $periodId == $period->period_id ? 'selected' : '' }}>
                                                            {{ $period->period_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <!-- Bulan -->
                                            <div class="col-md-3 mb-3">
                                                <label for="month_id" class="form-label">Sampai Bulan</label>
                                                <select class="form-control select-primary" id="month_id" name="month_id">
                                                    <option value="">Semua Bulan</option>
                                                    @foreach($months as $month)
                                                        <option value="{{ $month['id'] }}" {{ $monthId == $month['id'] ? 'selected' : '' }}>
                                                            {{ $month['name'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            
                                            <!-- Siswa -->
                                            <div class="col-md-3 mb-3">
                                                <label for="student_id" class="form-label">Siswa</label>
                                                <select class="form-control select-primary" id="student_id" name="student_id">
                                                    <option value="">Semua Siswa</option>
                                                    @foreach($students as $student)
                                                        <option value="{{ $student->student_id }}" {{ $studentId == $student->student_id ? 'selected' : '' }}>
                                                            {{ $student->student_nis }} - {{ $student->student_full_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            
                                            <!-- POS -->
                                            <div class="col-md-3 mb-3">
                                                <label for="pos_id" class="form-label">POS</label>
                                                <select class="form-control select-primary" id="pos_id" name="pos_id">
                                                    <option value="">Semua POS</option>
                                                    @foreach($posList as $pos)
                                                        <option value="{{ $pos->pos_id }}" {{ $posId == $pos->pos_id ? 'selected' : '' }}>
                                                            {{ $pos->pos_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            
                                            <!-- Kelas -->
                                            <div class="col-md-3 mb-3">
                                                <label for="class_id" class="form-label">Kelas</label>
                                                <select class="form-control select-primary" id="class_id" name="class_id">
                                                    <option value="">Semua Kelas</option>
                                                    @foreach($classes as $class)
                                                        <option value="{{ $class->class_id }}" {{ $classId == $class->class_id ? 'selected' : '' }}>
                                                            {{ $class->class_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            
                                            <!-- Status Siswa -->
                                            <div class="col-md-3 mb-3">
                                                <label for="student_status" class="form-label">Status Siswa</label>
                                                <select class="form-control select-primary" id="student_status" name="student_status">
                                                    <option value="">Semua Status</option>
                                                    <option value="1" {{ $studentStatus === '1' ? 'selected' : '' }}>Aktif</option>
                                                    <option value="0" {{ $studentStatus === '0' ? 'selected' : '' }}>Tidak Aktif</option>
                                                </select>
                                            </div>
                                            
                                            <!-- Action Buttons -->
                                            <div class="col-md-6 mb-3 d-flex align-items-end">
                                                <input type="hidden" name="filter" value="1">
                                                <button type="submit" class="btn btn-primary me-2">
                                                    <i class="fa fa-search me-1"></i>
                                                    Filter
                                                </button>
                                                <button type="button" class="btn btn-secondary me-2" onclick="resetFilter()">
                                                    <i class="fa fa-refresh me-1"></i>
                                                    Reset
                                                </button>
                                                <button type="button" class="btn btn-danger me-2" onclick="exportPdf()" style="color: white;">
                                                    <i class="fa fa-file-pdf me-1" style="color: white;"></i>
                                                    Export PDF
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alert jika belum ada filter -->
                    @if(!request()->has('filter') && !request()->filled('period_id') && !request()->filled('student_id') && !request()->filled('pos_id') && !request()->filled('class_id') && !request()->filled('student_status'))
                        <div class="alert alert-info mb-4" role="alert">
                            <i class="fa fa-info-circle me-2"></i>
                            <strong>Silakan pilih filter terlebih dahulu</strong> untuk menampilkan data tunggakan siswa. Klik tombol <strong>"Filter"</strong> setelah memilih filter yang diinginkan.
                        </div>
                    @endif

                    <!-- Summary Cards - hanya tampilkan jika ada filter -->
                    @if(request()->has('filter') || request()->filled('period_id') || request()->filled('student_id') || request()->filled('pos_id') || request()->filled('class_id') || request()->filled('student_status'))
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Total Tunggakan</h6>
                                            <h3 class="mb-0">Rp {{ number_format($totalTunggakan, 0, ',', '.') }}</h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fa fa-money-bill-wave fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Jumlah Siswa</h6>
                                            <h3 class="mb-0">{{ number_format($totalSiswa, 0, ',', '.') }}</h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fa fa-users fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Rata-rata Tunggakan</h6>
                                            <h3 class="mb-0">Rp {{ number_format($rataRataTunggakan, 0, ',', '.') }}</h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fa fa-calculator fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Data Table - hanya tampilkan jika ada filter -->
                    @if(request()->has('filter') || request()->filled('period_id') || request()->filled('student_id') || request()->filled('pos_id') || request()->filled('class_id') || request()->filled('student_status'))
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tunggakanTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>Status</th>
                                    <th>POS</th>
                                    <th>Total Tunggakan</th>
                                    <th>Jumlah Item</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tunggakanData as $index => $data)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $data['student_nis'] }}</td>
                                    <td>{{ $data['student_name'] }}</td>
                                    <td>{{ $data['class_name'] }}</td>
                                    <td>
                                        @if($data['student_status'] == 1)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-danger">Tidak Aktif</span>
                                        @endif
                                    </td>
                                    <td>{{ $data['pos_list'] }}</td>
                                    <td class="text-danger fw-bold">Rp {{ number_format($data['total_tunggakan'], 0, ',', '.') }}</td>
                                    <td>
                                        <span class="badge bg-warning">{{ $data['jumlah_item'] }} item</span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" onclick="showDetail({{ $data['student_id'] }})" style="color: white;">
                                            <i class="fa fa-eye me-1" style="color: white;"></i>
                                            Detail
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">Tidak ada data tunggakan untuk periode yang dipilih.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">Detail Tunggakan Siswa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detailModalBody">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger mr-2" onclick="downloadTunggakanPdf()" style="color: white;">
                    <i class="fas fa-file-pdf mr-1" style="color: white;"></i>Unduh PDF
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Global variable to store current student data
    let currentStudentData = null;
    
    // Define functions first
    function resetFilter() {
        // Reset form ke URL tanpa parameter
        window.location.href = '{{ route("manage.laporan.tunggakan-siswa") }}';
    }

    function exportPdf() {
        const form = document.getElementById('filterForm');
        const formData = new FormData(form);
        
        // Create a new form for POST request
        const exportForm = document.createElement('form');
        exportForm.method = 'POST';
        exportForm.action = '{{ route("manage.laporan.tunggakan-siswa.export-pdf") }}';
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        exportForm.appendChild(csrfToken);
        
        // Add form data
        for (let [key, value] of formData.entries()) {
            if (value) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                exportForm.appendChild(input);
            }
        }
        
        document.body.appendChild(exportForm);
        exportForm.submit();
        document.body.removeChild(exportForm);
    }

    function showDetail(studentId) {
        // Find the data for this student
        const data = @json($tunggakanData ?? []);
        const studentData = data.find(item => item.student_id == studentId);
        
        // Store current student data globally
        currentStudentData = studentData;
        
        if (studentData) {
            let detailHtml = `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Nama Siswa:</strong> ${studentData.student_name || '-'}<br>
                        <strong>NIS:</strong> ${studentData.student_nis || '-'}<br>
                        <strong>Kelas:</strong> ${studentData.class_name || '-'}
                    </div>
                    <div class="col-md-6">
                        <strong>Total Tunggakan:</strong> <span class="text-danger font-weight-bold">Rp ${new Intl.NumberFormat('id-ID').format(studentData.total_tunggakan || 0)}</span><br>
                        <strong>Jumlah Item:</strong> ${studentData.jumlah_item || 0} item
                    </div>
                </div>
                <hr>
                <h6>Detail Item Tunggakan:</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 5%; text-align: center;">No.</th>
                                <th style="width: 40%;">POS</th>
                                <th style="width: 20%;">Tagihan</th>
                                <th style="width: 20%;">Terbayar</th>
                                <th style="width: 15%;">Tunggakan</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
             
            if (studentData.detail_tunggakan && studentData.detail_tunggakan.length > 0) {
                studentData.detail_tunggakan.forEach((item, index) => {
                    const bill = parseFloat(item.bill || 0);
                    const pay = parseFloat(item.pay || 0);
                    const tunggakan = parseFloat(item.tunggakan || 0);
                    
                    detailHtml += `
                        <tr>
                            <td style="text-align: center; color: #000;">${index + 1}</td>
                            <td>${item.pos_name || '-'}</td>
                            <td>Rp ${new Intl.NumberFormat('id-ID').format(bill)}</td>
                            <td>Rp ${new Intl.NumberFormat('id-ID').format(pay)}</td>
                            <td class="text-danger font-weight-bold">Rp ${new Intl.NumberFormat('id-ID').format(tunggakan)}</td>
                        </tr>
                    `;
                });
            } else {
                detailHtml += `
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data tunggakan</td>
                    </tr>
                `;
            }
            
            detailHtml += `
                        </tbody>
                    </table>
                </div>
            `;
            
            document.getElementById('detailModalBody').innerHTML = detailHtml;
            // Use jQuery for Bootstrap 4 modal
            $('#detailModal').modal('show');
        } else {
            alert('Data siswa tidak ditemukan');
        }
    }

    function printTunggakan() {
        if (!currentStudentData) {
            alert('Tidak ada data siswa yang dipilih');
            return;
        }
        
        // Create print window content
        let printContent = `
            <!DOCTYPE html>
            <html lang="id">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Cetak Tunggakan - ${currentStudentData.student_name}</title>
                <style>
                    * {
                        margin: 0;
                        padding: 0;
                        box-sizing: border-box;
                    }
                    
                    body {
                        font-family: 'Times New Roman', serif;
                        font-size: 12px;
                        line-height: 1.4;
                        color: #000;
                        background: white;
                        padding: 20px;
                    }
                    
                                         .header {
                         text-align: left;
                         margin-bottom: 20px;
                         position: relative;
                     }
                     
                     .school-info {
                         font-size: 11px;
                         line-height: 1.3;
                     }
                     
                     .school-name {
                         font-weight: bold;
                         font-size: 12px;
                     }
                     
                                          .receipt-title {
                         position: absolute;
                         top: 0;
                         right: 0;
                         border: 2px solid #000;
                         padding: 8px 15px;
                         text-align: center;
                         font-weight: bold;
                         font-size: 12px;
                     }
                     
                     .divider {
                         border-top: 1px solid #000;
                         margin: 15px 0;
                     }
                     
                     .student-info {
                        margin-bottom: 20px;
                    }
                    
                    .info-row {
                        display: flex;
                        margin-bottom: 5px;
                    }
                    
                    .info-label {
                        width: 120px;
                        font-weight: bold;
                    }
                    
                    .info-value {
                        flex: 1;
                    }
                    
                    .table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 20px;
                    }
                    
                    .table th,
                    .table td {
                        border: 1px solid #000;
                        padding: 8px;
                        text-align: left;
                        font-size: 11px;
                    }
                    
                    .table th {
                        background-color: #f5f5f5;
                        font-weight: bold;
                        text-align: center;
                    }
                    
                    .text-center {
                        text-align: center;
                    }
                    
                    .text-right {
                        text-align: right;
                    }
                    
                    .text-danger {
                        color: #dc3545;
                    }
                    
                    .fw-bold {
                        font-weight: bold;
                    }
                    
                    .total-row {
                        background-color: #f8f9fa;
                        font-weight: bold;
                    }
                    
                    .footer {
                        margin-top: 30px;
                        text-align: center;
                    }
                    
                    .signature-section {
                        display: flex;
                        justify-content: space-between;
                        margin-top: 50px;
                    }
                    
                    .signature-box {
                        text-align: center;
                        flex: 1;
                        margin: 0 20px;
                    }
                    
                    .signature-line {
                        border-top: 1px solid #000;
                        width: 150px;
                        margin: 30px auto 10px auto;
                    }
                    
                    @media print {
                        body {
                            padding: 0;
                        }
                    }
                </style>
            </head>
            <body>
                                 <div class="header">
                    <div class="school-info">
                        <div class="school-name">{{ ($school_profile->nama_sekolah ?? 'NAMA LEMBAGA ANDA') }}</div>
                        <div>{{ ($school_profile->alamat ?? 'Alamat Lembaga Anda') }}</div>
                        <div>Telp: {{ ($school_profile->no_telp ?? 'No. Telepon') }}</div>
                    </div>
                    
                                         <div class="receipt-title">
                        LAPORAN TUNGGAKAN SISWA
                    </div>
                </div>
                
                <div class="divider"></div>
                
                <div class="student-info">
                   <div class="info-row">
                       <div class="info-label">Nama Siswa</div>
                       <div class="info-value">: ${currentStudentData.student_name}</div>
                   </div>
                   <div class="info-row">
                        <div class="info-label">NIS</div>
                        <div class="info-value">: ${currentStudentData.student_nis}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Kelas</div>
                        <div class="info-value">: ${currentStudentData.class_name}</div>
                    </div>
               </div>
               
                                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 5%">No.</th>
                            <th style="width: 40%">POS Pembayaran</th>
                            <th style="width: 20%">Tagihan</th>
                            <th style="width: 20%">Terbayar</th>
                            <th style="width: 15%">Tunggakan</th>
                        </tr>
                    </thead>
                    <tbody>
         `;
         
         // Add table rows
         currentStudentData.detail_tunggakan.forEach((item, index) => {
             printContent += `
                         <tr>
                             <td class="text-center" style="color: #000;">${index + 1}</td>
                             <td>${item.pos_name}</td>
                             <td class="text-right">Rp ${new Intl.NumberFormat('id-ID').format(item.bill)}</td>
                             <td class="text-right">Rp ${new Intl.NumberFormat('id-ID').format(item.pay)}</td>
                             <td class="text-right text-danger fw-bold">Rp ${new Intl.NumberFormat('id-ID').format(item.tunggakan)}</td>
                         </tr>
             `;
         });
         
         // Add total row
         printContent += `
                         <tr class="total-row">
                             <td colspan="4" class="text-center fw-bold">TOTAL TUNGGAKAN</td>
                             <td class="text-right fw-bold text-danger">Rp ${new Intl.NumberFormat('id-ID').format(currentStudentData.total_tunggakan)}</td>
                         </tr>
                    </tbody>
                </table>
                
                <div class="footer">
                    <p>Dicetak pada: ${new Date().toLocaleDateString('id-ID')} ${new Date().toLocaleTimeString('id-ID')}</p>
                </div>
                
                <div class="signature-section">
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <div>Petugas</div>
                    </div>
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <div>Orang Tua/Wali</div>
                    </div>
                </div>
            </body>
            </html>
        `;
        
        // Open print window
        const printWindow = window.open('', '_blank');
        printWindow.document.write(printContent);
        printWindow.document.close();
        
        // Wait for content to load then print
        printWindow.onload = function() {
            printWindow.print();
        };
    }

    function downloadTunggakanPdf() {
        if (!currentStudentData) {
            alert('Tidak ada data siswa yang dipilih');
            return;
        }
        const form = document.getElementById('filterForm');
        const formData = new FormData(form);
        const exportForm = document.createElement('form');
        exportForm.method = 'POST';
        exportForm.action = '{{ route("manage.laporan.tunggakan-siswa.export-pdf-student") }}';
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        exportForm.appendChild(csrfToken);
        // include current filters
        for (let [key, value] of formData.entries()) {
            if (value) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                exportForm.appendChild(input);
            }
        }
        // force current student id
        const sid = document.createElement('input');
        sid.type = 'hidden';
        sid.name = 'student_id';
        sid.value = currentStudentData.student_id;
        exportForm.appendChild(sid);
        document.body.appendChild(exportForm);
        exportForm.submit();
        document.body.removeChild(exportForm);
    }
    
    // Make functions global immediately (not waiting for DOM ready)
    window.showDetail = showDetail;
    window.downloadTunggakanPdf = downloadTunggakanPdf;
    window.resetFilter = resetFilter;
    window.exportPdf = exportPdf;
    
    // Initialize DataTable after DOM is ready
    $(document).ready(function() {
        // Initialize DataTable if available
        if (typeof $.fn.DataTable !== 'undefined' && $('#tunggakanTable').length) {
            $('#tunggakanTable').DataTable({
                pageLength: 25,
                order: [[6, 'desc']], // Sort by total tunggakan descending
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                }
            });
        }
    });

 </script>
 @endsection 