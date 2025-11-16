@extends('layouts.adminty')

@section('title', 'Transaksi Pembayaran Siswa')

@section('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/verification-modal.css') }}">
    <style>
        
        /* Styling untuk Select2 dropdown peserta didik dengan border default (putih/abu-abu) */
        /* Override SEMUA warna primary/hijau untuk student_search - gunakan selector yang sangat spesifik */
        body .select2-container--bootstrap-5 .select2-selection,
        body .select2-container--bootstrap-5.select2-container--focus .select2-selection,
        body .select2-container--bootstrap-5.select2-container--open .select2-selection {
            border: 1px solid #cccccc !important;
            border-color: #cccccc !important;
            border-radius: 2px !important;
            background-color: #fff !important;
        }
        
        body .select2-container--bootstrap-5.select2-container--focus .select2-selection {
            border-color: #cccccc !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.1) !important;
            outline: none !important;
        }
        
        body .select2-container--bootstrap-5.select2-container--open .select2-selection {
            border-color: #cccccc !important;
        }
        
        /* Override warna primary dari Bootstrap 5 theme */
        body .select2-container--bootstrap-5 .select2-selection[class*="border"],
        body .select2-container--bootstrap-5 .select2-selection {
            border-color: #cccccc !important;
        }
        
        /* Pastikan tidak ada warna hijau/teal/primary */
        body .select2-container--bootstrap-5 .select2-selection {
            background-image: none !important;
            background-color: #fff !important;
        }
        
        /* Override background pada rendered text */
        body .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered,
        body .select2-container--default .select2-selection--single .select2-selection__rendered {
            background-color: transparent !important;
            background: transparent !important;
            color: #333 !important;
        }
        
        /* Override warna dari Bootstrap 5 theme yang mungkin menggunakan primary */
        body .select2-container--bootstrap-5 .select2-selection--single {
            border-color: #cccccc !important;
            height: 38px !important;
            min-height: 38px !important;
            line-height: 38px;
            display: flex;
            align-items: center;
        }
        
        /* Styling untuk .select2-container .select2-selection--single */
        .select2-container .select2-selection--single {
            height: 40px;
            min-height: auto !important;
            padding: 1px 2px;
        }
        
        body .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            line-height: 38px !important;
            padding-left: 12px;
            padding-right: 30px;
            padding-top: 0;
            padding-bottom: 0;
            color: #333;
            background-color: transparent !important;
            background: transparent !important;
            display: flex;
            align-items: center;
            position: relative;
            top: 2px;
            transform: translateY(-1px);
        }
        
        body .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
            height: 36px;
            right: 8px;
        }
        
        body .select2-container--bootstrap-5 .select2-selection__arrow {
            height: 36px !important;
            right: 8px;
            top: 1px;
        }
        
        body .select2-container--bootstrap-5 .select2-selection__arrow b {
            border-color: #333 transparent transparent transparent;
            border-width: 5px 4px 0 4px;
            margin-top: -2px;
        }
        
        /* Pastikan tidak ada warna primary di semua state */
        body .select2-container--bootstrap-5.select2-container--below .select2-selection,
        body .select2-container--bootstrap-5.select2-container--above .select2-selection {
            border-color: #cccccc !important;
        }
        
        /* Override semua kemungkinan warna primary/teal/hijau dari theme */
        body .select2-container--bootstrap-5 .select2-selection[style*="#01a9ac"],
        body .select2-container--bootstrap-5 .select2-selection[style*="teal"],
        body .select2-container--bootstrap-5 .select2-selection[style*="primary"] {
            border-color: #cccccc !important;
        }
        
        /* Force override dengan inline style via JavaScript akan ditambahkan */
    </style>
    <script>
        // Force override warna Select2 setelah diinisialisasi
        // Function untuk force set border color
        window.forceSelect2DefaultColor = function() {
            $('.select2-container--bootstrap-5').each(function() {
                var $selection = $(this).find('.select2-selection');
                var $rendered = $(this).find('.select2-selection__rendered');
                
                if ($selection.length) {
                    $selection.css({
                        'border-color': '#cccccc !important',
                        'border': '1px solid #cccccc !important',
                        'background-color': '#fff !important'
                    });
                    // Force dengan attr juga
                    $selection.attr('style', function(i, style) {
                        return (style || '') + ' border-color: #cccccc !important; border: 1px solid #cccccc !important; background-color: #fff !important;';
                    });
                }
                
                // Hilangkan background pada rendered text
                if ($rendered.length) {
                    $rendered.css({
                        'background-color': 'transparent !important',
                        'background': 'transparent !important',
                        'color': '#333 !important'
                    });
                    $rendered.attr('style', function(i, style) {
                        return (style || '') + ' background-color: transparent !important; background: transparent !important; color: #333 !important;';
                    });
                }
            });
        };
        
        // Jalankan setelah DOM ready dan Select2 siap
        $(document).ready(function() {
            // Set setelah Select2 diinisialisasi (delay lebih lama)
            setTimeout(function() {
                window.forceSelect2DefaultColor();
            }, 500);
            
            // Set ulang saat Select2 dibuka/ditutup
            $(document).on('select2:open select2:close select2:select', function() {
                setTimeout(window.forceSelect2DefaultColor, 50);
            });
            
            // Set ulang saat focus
            $(document).on('focus', '#student_search', function() {
                setTimeout(window.forceSelect2DefaultColor, 50);
            });
            
            // Observer untuk perubahan DOM (jika Select2 ditambahkan secara dinamis)
            if (window.MutationObserver) {
                var observer = new MutationObserver(function(mutations) {
                    setTimeout(window.forceSelect2DefaultColor, 100);
                });
                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });
            }
        });
    </script>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-search"></i> Cari Transaksi Pembayaran</h4>
        </div>
        <div class="card-body">
                    <form id="searchForm">
                        <div class="row g-3 align-items-end">
                <div class="col-md-3">
                                <label for="student_status" class="form-label mb-2 fw-semibold">Status Siswa</label>
                                <select class="form-control select-primary" id="student_status" name="student_status" style="height: 38px; line-height: 38px;">
                                    <option value="1">Aktif</option>
                        <option value="0">Tidak Aktif</option>
                    </select>
                </div>
                <div class="col-md-6">
                                <label for="student_search" class="form-label mb-2 fw-semibold">Peserta Didik</label>
                                <select class="form-control select-primary" id="student_search" name="student_id" style="height: 38px;">
                                    <option value="">Pilih Peserta Didik</option>
                                </select>
                </div>
                <div class="col-md-3">
                                <label class="form-label mb-2 d-block">&nbsp;</label>
                                <button type="button" class="btn btn-primary w-100" id="btnCariData" style="height: 38px; line-height: 1.5;">
                                    <i class="fas fa-search me-2"></i>Cari Data
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Konten Pembayaran -->
    <div class="row mt-3" id="payment-content" style="display: none;">
            <!-- Informasi Siswa -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-user"></i> Informasi Siswa</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td style="width: 120px;"><strong>Tahun Ajaran</strong></td>
                                <td>:</td>
                                <td><span id="info_tahun_ajaran"></span></td>
                            </tr>
                            <tr>
                                <td style="width: 120px;"><strong>NIS</strong></td>
                                <td>:</td>
                                <td><span id="info_nis"></span></td>
                            </tr>
                            <tr>
                                <td style="width: 120px;"><strong>Nama Siswa</strong></td>
                                <td>:</td>
                                <td><span id="info_nama_siswa"></span></td>
                            </tr>
                            <tr>
                                <td style="width: 120px;"><strong>Kelas</strong></td>
                                <td>:</td>
                                <td><span id="info_kelas"></span></td>
                            </tr>
                            <tr>
                                <td style="width: 120px;"><strong>Status Siswa</strong></td>
                                <td>:</td>
                                <td><span id="info_status_siswa"></span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

            <!-- Transaksi Terakhir -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-history"></i> Transaksi Terakhir</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-bordered table-hover table-sm">
                            <thead class="table-dark">
                                <tr>
                                    <th>Pembayaran</th>
                                    <th>Tagihan</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="transaksi_terakhir_body">
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        <i class="fas fa-spinner fa-spin"></i> Memuat riwayat transaksi...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

            <!-- Cetak Bukti Pembayaran -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-print"></i> Cetak Bukti Pembayaran</h6>
                </div>
                    <div class="card-body">
                        <div class="mb-3">
                        <label for="print_date" class="form-label">Tanggal Transaksi</label>
                        <input type="date" class="form-control" id="print_date" value="{{ date('Y-m-d') }}">
                        <input type="hidden" id="student_id" value="">
                    </div>
                    <button type="button" class="btn btn-primary w-100 text-white" id="btnCetakKuitansi" onclick="printReceipt()" disabled>
                        <i class="fas fa-print"></i> Cetak
                    </button>
                </div>
            </div>
        </div>

        <!-- Data Tagihan -->
        <div class="col-12 mt-3">
        <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0"><i class="fas fa-credit-card"></i> Data Tagihan</h5>
                </div>
                <div class="card-body">
                    <!-- Toolbar Multi Pembayaran -->
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <button type="button" id="toggleMultiModeBtn" class="btn btn-light btn-sm">
                            <i class="fas fa-cart-plus me-1"></i> Mode Multi-Bayar: <span id="multiModeStatus">OFF</span>
                        </button>
                        <div class="bg-white text-dark px-3 py-1 rounded d-flex align-items-center gap-3" style="min-height: 32px;">
                            <span class="small text-nowrap">Dipilih: <span id="multiSelectedCount" class="fw-bold">0</span> item</span>
                            <span class="small text-nowrap">Total: <span id="multiSelectedTotal" class="fw-bold">Rp 0</span></span>
                        </div>
                        <button type="button" id="multiPayBtn" class="btn btn-success btn-sm text-white" disabled>
                            <i class="fas fa-money-bill-wave me-1"></i> Bayar Sekaligus
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Tab Navigation -->
                    <div class="tab-icon">
                    <ul class="nav nav-tabs" id="paymentTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                                <a class="nav-link active" id="bulanan-tab" data-toggle="tab" href="#bulanan" role="tab" aria-controls="bulanan" aria-selected="true">
                                <i class="fas fa-calendar-alt"></i> Bulanan
                                </a>
                    </li>
                    <li class="nav-item" role="presentation">
                                <a class="nav-link" id="bebas-tab" data-toggle="tab" href="#bebas" role="tab" aria-controls="bebas" aria-selected="false">
                                <i class="fas fa-money-bill-wave"></i> Bebas
                                </a>
                    </li>
                </ul>
                    </div>

                    <!-- Tab Content -->
                    <div class="tab-content" id="paymentTabsContent">
                        <!-- Tab Bulanan -->
                    <div class="tab-pane fade show active" id="bulanan" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mt-3 mb-2">
                                <h6 class="mb-0">Data Tagihan Bulanan</h6>
                                <div class="d-flex align-items-center gap-2">
                                    <small class="text-muted">Pilih siswa terlebih dahulu</small>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="refreshBulananData()" title="Refresh data tagihan bulanan">
                                        <i class="fas fa-sync-alt"></i> Refresh Data
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Pembayaran</th>
                                        <th>Juli</th>
                                        <th>Agustus</th>
                                        <th>September</th>
                                        <th>Oktober</th>
                                        <th>November</th>
                                        <th>Desember</th>
                                        <th>Januari</th>
                                        <th>Februari</th>
                                        <th>Maret</th>
                                        <th>April</th>
                                        <th>Mei</th>
                                        <th>Juni</th>
                                    </tr>
                                </thead>
                                <tbody id="bulanan_table_body">
                                        <tr>
                                            <td colspan="14" class="text-center text-muted">
                                                <i class="fas fa-spinner fa-spin"></i> Memuat data...
                                            </td>
                                        </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                        <!-- Tab Bebas -->
                    <div class="tab-pane fade" id="bebas" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mt-3 mb-2">
                                <h6 class="mb-0">Data Tagihan Bebas</h6>
                                <div class="d-flex align-items-center gap-2">
                                    <small class="text-muted">Pilih siswa terlebih dahulu</small>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="refreshBebasData()" title="Refresh data tagihan bebas">
                                        <i class="fas fa-sync-alt"></i> Refresh Data
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Pembayaran</th>
                                        <th>Total Tagihan</th>
                                        <th>Total Bayar</th>
                                        <th>Sisa</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="bebas_table_body">
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">
                                                <i class="fas fa-spinner fa-spin"></i> Memuat data...
                                            </td>
                                        </tr>
                                </tbody>
                            </table>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pembayaran Bulanan -->
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-credit-card"></i> Konfirmasi Pembayaran</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" onclick="closePaymentModal()" style="opacity: 1; font-size: 1.5rem; padding: 0.5rem;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Posis Pembayaran:</strong><br>
                        <span id="modal-pos-name"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Bulan:</strong><br>
                        <span id="modal-month-name"></span>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <strong>Nominal:</strong><br>
                        <span id="modal-bill-amount" class="h5 text-primary"></span>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-12">
                        <strong>Pilih Metode Pembayaran:</strong>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary text-white" onclick="closePaymentModal()">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-success text-white" onclick="confirmPayment('cash')">
                    <i class="fas fa-money-bill-wave"></i> Bayar Tunai
                </button>
                <button type="button" class="btn btn-info text-white" onclick="confirmPayment('savings')">
                    <i class="fas fa-piggy-bank"></i> Bayar dengan Tabungan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pembayaran Bebas -->
<div class="modal fade" id="bebasPaymentModal" tabindex="-1" role="dialog" aria-labelledby="bebasPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-money-bill-wave"></i> Pembayaran Bebas</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" onclick="closeBebasPaymentModal()" style="opacity: 1; font-size: 1.5rem; padding: 0.5rem;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="bebasPaymentForm">
                    <input type="hidden" id="bebas_payment_student_id" name="student_id">
                    <input type="hidden" id="bebas_payment_payment_id" name="payment_id">
                    <input type="hidden" id="bebas_payment_date" name="payment_date" value="{{ date('Y-m-d') }}">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Posis Pembayaran:</strong><br>
                            <span id="bebas_payment_pos_name"></span>
                        </div>
                        <div class="col-md-6">
                            <strong>Total Tagihan:</strong><br>
                            <span id="bebas_payment_total_bill"></span>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <strong>Total Bayar:</strong><br>
                            <span id="bebas_payment_total_pay"></span>
                        </div>
                        <div class="col-md-6">
                            <strong>Sisa:</strong><br>
                            <span id="bebas_payment_sisa"></span>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="bebas_payment_amount" class="form-label">Nominal Pembayaran</label>
                            <input type="number" class="form-control" id="bebas_payment_amount" name="amount" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label for="bebas_payment_desc" class="form-label">Keterangan (Opsional)</label>
                            <input type="text" class="form-control" id="bebas_payment_desc" name="description" placeholder="Keterangan pembayaran">
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label class="form-label d-block mb-3">Metode Pembayaran</label>
                            <div class="d-flex gap-5">
                                <div class="form-check">
                                    <input class="form-control" type="radio" name="payment_method" id="bebas_cash" value="cash" checked>
                                    <label class="form-check-label" for="bebas_cash">
                                        Tunai
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-control" type="radio" name="payment_method" id="bebas_tabungan" value="tabungan">
                                    <label class="form-check-label" for="bebas_tabungan">
                                        Tabungan
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary text-white" onclick="closeBebasPaymentModal()">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-success text-white" onclick="processBebasPayment()">
                    <i class="fas fa-check"></i> Proses Pembayaran
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus Transaksi -->
<div class="modal fade" id="deleteTransactionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-trash"></i> Konfirmasi Hapus Transaksi</h5>
                <button type="button" class="btn-close btn-close-white" id="closeDeleteModal" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h6>Apakah Anda yakin ingin menghapus transaksi ini?</h6>
                    <div class="mt-3">
                        <strong>Detail Transaksi:</strong><br>
                        <span id="delete-transaction-detail"></span>
                    </div>
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-info-circle"></i>
                        <strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan dan akan mengembalikan status pembayaran menjadi belum lunas.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary text-white" id="cancelDeleteBtn" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-danger text-white" onclick="confirmDeleteTransaction()">
                    <i class="fas fa-trash"></i> Hapus Transaksi
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
// Fallback untuk SweetAlert2
if (typeof Swal === 'undefined') {
    window.Swal = {
        fire: function(options) {
            if (confirm(options.text || 'Konfirmasi?')) {
                return Promise.resolve({ isConfirmed: true });
            } else {
                return Promise.resolve({ isConfirmed: false });
            }
        }
    };
    console.log('SweetAlert2 loaded successfully');
}

$(document).ready(function() {
    console.log('Payment page loaded.');
    console.log('jQuery version:', $.fn.jquery);
    console.log('Student status element:', $('#student_status').length);
    console.log('Student search element:', $('#student_search').length);
    
    // Pastikan tombol cetak kuitansi dinonaktifkan saat halaman dimuat
    $('#btnCetakKuitansi').prop('disabled', true);
    
    // Add manual event handler for cancel button
    $('#cancelDeleteBtn').on('click', function() {
        console.log('Cancel button clicked');
        $('#deleteTransactionModal').modal('hide');
    });
    
    // Fallback with vanilla JavaScript
    document.getElementById('cancelDeleteBtn').addEventListener('click', function() {
        console.log('Cancel button clicked (vanilla JS)');
        const modal = document.getElementById('deleteTransactionModal');
        if (modal) {
            const bootstrapModal = bootstrap.Modal.getInstance(modal);
            if (bootstrapModal) {
                bootstrapModal.hide();
            } else {
                // Fallback if Bootstrap modal instance not found
                modal.style.display = 'none';
                modal.classList.remove('show');
                document.body.classList.remove('modal-open');
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) backdrop.remove();
            }
        }
    });
    
    // Add event handler for close button (X)
    document.getElementById('closeDeleteModal').addEventListener('click', function() {
        console.log('Close button clicked (vanilla JS)');
        const modal = document.getElementById('deleteTransactionModal');
        if (modal) {
            const bootstrapModal = bootstrap.Modal.getInstance(modal);
            if (bootstrapModal) {
                bootstrapModal.hide();
            } else {
                // Fallback if Bootstrap modal instance not found
                modal.style.display = 'none';
                modal.classList.remove('show');
                document.body.classList.remove('modal-open');
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) backdrop.remove();
            }
        }
    });
    
    // Add click outside modal handler
    document.getElementById('deleteTransactionModal').addEventListener('click', function(e) {
        if (e.target === this) {
            console.log('Clicked outside modal');
            const bootstrapModal = bootstrap.Modal.getInstance(this);
            if (bootstrapModal) {
                bootstrapModal.hide();
            } else {
                // Fallback if Bootstrap modal instance not found
                this.style.display = 'none';
                this.classList.remove('show');
                document.body.classList.remove('modal-open');
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) backdrop.remove();
            }
        }
    });
    
    // Add keyboard event handler (Escape key)
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('deleteTransactionModal');
            if (modal && modal.classList.contains('show')) {
                console.log('Escape key pressed');
                const bootstrapModal = bootstrap.Modal.getInstance(modal);
                if (bootstrapModal) {
                    bootstrapModal.hide();
                } else {
                    // Fallback if Bootstrap modal instance not found
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                    document.body.classList.remove('modal-open');
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) backdrop.remove();
                }
            }
        }
    });
    
    // Test modal functionality
    console.log('Testing modal elements...');
    console.log('Cancel button:', document.getElementById('cancelDeleteBtn'));
    console.log('Close button:', document.getElementById('closeDeleteModal'));
    console.log('Modal:', document.getElementById('deleteTransactionModal'));
    
    // Test if Bootstrap is available
    if (typeof bootstrap !== 'undefined') {
        console.log('Bootstrap is available');
        console.log('Bootstrap version:', bootstrap.VERSION);
    } else {
        console.warn('Bootstrap is not available');
    }
    
    // Load students on page load
    console.log('Loading students on page load...');
    loadStudents();
    
    // Add event listener for status change
    $('#student_status').on('change', function() {
        console.log('Status changed to:', $(this).val());
        loadStudents();
        // Nonaktifkan tombol cetak ketika status berubah
        $('#btnCetakKuitansi').prop('disabled', true);
        $('#payment-content').hide();
    });
    
    // Add event listener for student search change
    $('#student_search').on('change', function() {
        var selectedStudentId = $(this).val();
        if (!selectedStudentId) {
            // Jika tidak ada siswa yang dipilih, nonaktifkan tombol cetak
            $('#btnCetakKuitansi').prop('disabled', true);
            $('#payment-content').hide();
        }
    });
    
    // Test button click
    $('#btnCariData').on('click', function() {
        console.log('Search button clicked');
        var studentId = $('#student_search').val();
        console.log('Selected student ID:', studentId);
        
        if (!studentId) {
            alert('Pilih peserta didik terlebih dahulu!');
            return;
        }
        
        // Ambil detail siswa
        $.getJSON('/api/students/' + studentId + '/detail', function(res) {
            if (res.success) {
                console.log('Student detail loaded:', res);
                
                // Tampilkan informasi siswa
                $('#info_tahun_ajaran').text(res.student.tahun_ajaran);
                $('#info_nis').text(res.student.nis);
                $('#info_nama_siswa').text(res.student.nama);
                $('#info_kelas').text(res.student.kelas);
                $('#info_status_siswa').text(res.student.status);
                $('#student_id').val(res.student.student_id);
                
                console.log('Student selected - Student ID set to:', res.student.student_id);
                console.log('Hidden field value after setting:', $('#student_id').val());
                
                // Aktifkan tombol cetak kuitansi
                $('#btnCetakKuitansi').prop('disabled', false);
                
                // Tampilkan konten pembayaran (3 kolom)
                $('#payment-content').show();
                
                // Load transaction history
                loadTransactionHistory(studentId);
                
                // Ambil tagihan
                $.getJSON('/api/students/' + studentId + '/tagihan', function(tagihan) {
                    console.log('Tagihan data received:', tagihan);
                    
                    // Bulanan
                    var bulanList = ['Juli','Agustus','September','Oktober','November','Desember','Januari','Februari','Maret','April','Mei','Juni'];
                    var bulananHtml = '';
                    if (tagihan.tagihan && tagihan.tagihan.bulanan && tagihan.tagihan.bulanan.length > 0) {
                        // Grouping berdasarkan kombinasi payment_id dan pos_id
                        var grouped = {};
                        tagihan.tagihan.bulanan.forEach(function(item) {
                            // Buat key unik berdasarkan payment_id dan pos_id
                            var key = item.payment_id + '_' + (item.pos_pos_id || 1);
                            
                            if (!grouped[key]) {
                                grouped[key] = {
                                    nama: item.pos_name + ' - ' + (item.period_name || 'Tahun Ajaran'),
                                    period_name: item.period_name,
                                    pos_id: item.pos_pos_id || 1,
                                    bulan: {}
                                };
                            }
                            
                            grouped[key].bulan[item.month_month_id] = {
                                bill: item.bulan_bill,
                                status: item.bulan_status,
                                date_pay: item.bulan_date_pay
                            };
                        });
                        
                        var idx = 1;
                        for (var key in grouped) {
                            var paymentName = grouped[key].nama;
                            var posName = paymentName.split(' - ')[0];
                            var periodName = grouped[key].period_name || 'Tahun Ajaran';
                            
                            bulananHtml += '<tr><td>' + (idx++) + '</td><td>' + posName + ' - ' + periodName + '</td>';
                            for (var i = 1; i <= 12; i++) {
                                var b = grouped[key].bulan[i];
                                if (b) {
                                    if (b.status == 1 && b.date_pay) {
                                        // Format tanggal dengan moment.js
                                        var formattedDate = moment(b.date_pay).format('DD/MM/YY');
                                        bulananHtml += '<td class="sudah-bayar">' + formattedDate + '</td>';
                                    } else {
                                        let formattedBill = b.bill ? 'Rp ' + Number(b.bill).toLocaleString('id-ID') : '-';
                                        var escapedPosName = posName.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                                        bulananHtml += '<td class="belum-bayar clickable-payment" data-student-id="' + studentId + '" data-payment-id="' + key.split('_')[0] + '" data-pos-id="' + grouped[key].pos_id + '" data-month-id="' + i + '" data-bill="' + b.bill + '" data-pos-name="' + escapedPosName + '" data-month-name="' + bulanList[i-1] + '">' + formattedBill + '</td>';
                                    }
                                } else {
                                    bulananHtml += '<td>-</td>';
                                }
                            }
                            bulananHtml += '</tr>';
                        }
                    } else {
                        bulananHtml = '<tr><td colspan="14" class="text-center">Tidak ada data tagihan bulanan</td></tr>';
                    }
                    $('#bulanan_table_body').html(bulananHtml);
                    
                    // Event listener untuk klik pada nominal pembayaran
                    $('.clickable-payment').off('click').on('click', function(e) {
                        // Jika multi mode aktif atau klik berasal dari checkbox, jangan buka modal
                        if ((typeof multiMode !== 'undefined' && multiMode) || $(e.target).closest('.multi-check').length) {
                            return;
                        }
                        var studentId = $(this).data('student-id');
                        var paymentId = $(this).data('payment-id');
                        var monthId = $(this).data('month-id');
                        var bill = $(this).data('bill');
                        var posName = $(this).data('pos-name');
                        var monthName = $(this).data('month-name');
                        
                        // Tampilkan data di modal
                        $('#modal-pos-name').text(posName);
                        $('#modal-month-name').text(monthName);
                        $('#modal-bill-amount').text('Rp ' + Number(bill).toLocaleString('id-ID'));
                        
                        // Simpan data untuk proses pembayaran
                        $('#paymentModal').data('payment-data', {
                            studentId: studentId,
                            paymentId: paymentId,
                            monthId: monthId,
                            bill: bill,
                            posName: posName,
                            monthName: monthName
                        });
                        
                        // Tampilkan modal
                        $('#paymentModal').modal('show');
                    });
                    
                    // Bebas
                    var bebasHtml = '';
                    if (tagihan.tagihan && tagihan.tagihan.bebas && tagihan.tagihan.bebas.length > 0) {
                        tagihan.tagihan.bebas.forEach(function(item, idx) {
                            var sisa = (item.bebas_bill - item.bebas_total_pay);
                            let formattedBill = item.bebas_bill ? 'Rp ' + Number(item.bebas_bill).toLocaleString('id-ID') : '-';
                            let formattedTotalPay = item.bebas_total_pay ? 'Rp ' + Number(item.bebas_total_pay).toLocaleString('id-ID') : 'Rp 0';
                            let formattedSisa = sisa > 0 ? 'Rp ' + Number(sisa).toLocaleString('id-ID') : 'Rp 0';
                            let statusClass = sisa <= 0 ? 'lunas' : 'belum-lunas';
                            let statusText = sisa <= 0 ? 'Lunas' : 'Belum Lunas';

                            bebasHtml += '<tr>';
                            bebasHtml += '<td>' + (idx+1) + '</td>';
                            bebasHtml += '<td>' + item.pos_name + ' - ' + (item.period_name || 'Tahun Ajaran') + '</td>';
                            bebasHtml += '<td>' + formattedBill + '</td>';
                            bebasHtml += '<td>' + formattedTotalPay + '</td>';
                            bebasHtml += '<td>' + formattedSisa + '</td>';
                            bebasHtml += '<td><span class="badge bg-' + (sisa <= 0 ? 'success' : 'warning') + '">' + statusText + '</span></td>';
                            bebasHtml += '<td>';
            if (sisa > 0) {
                bebasHtml += '<div class="d-flex align-items-center gap-2">';
                bebasHtml += '<div class="form-check d-none multi-check"><input class="form-check-input multi-bebas" type="checkbox" data-sisa="' + sisa + '"></div>';
                bebasHtml += '<input type="number" min="1" max="' + sisa + '" class="form-control form-control-sm multi-bebas-amount d-none" placeholder="Nominal" style="width:120px">';
                bebasHtml += '<button class="btn btn-sm btn-primary text-white bebas-payment-btn" data-student-id="' + studentId + '" data-payment-id="' + item.payment_id + '" data-pos-name="' + item.pos_name + '" data-total-bill="' + item.bebas_bill + '" data-total-pay="' + item.bebas_total_pay + '" data-sisa="' + sisa + '">Bayar</button>';
                bebasHtml += '</div>';
            } else {
                                bebasHtml += '<span class="text-muted">Sudah Lunas</span>';
                            }
                            bebasHtml += '</td>';
                            bebasHtml += '</tr>';
                        });
                    } else {
                        bebasHtml = '<tr><td colspan="7" class="text-center">Tidak ada data tagihan bebas</td></tr>';
                    }
                    $('#bebas_table_body').html(bebasHtml);
                    
                        // Event listener untuk tombol bayar bebas
    $('.bebas-payment-btn').off('click').on('click', function() {
        var studentId = $(this).data('student-id');
        var paymentId = $(this).data('payment-id');
        var posName = $(this).data('pos-name');
        var totalBill = $(this).data('total-bill');
        var totalPay = $(this).data('total-pay');
        var sisa = $(this).data('sisa');
        
        // Reset form terlebih dahulu
        $('#bebasPaymentForm')[0].reset();
        $('#bebas_payment_amount').val('');
        $('#bebas_payment_desc').val('');
        
        // Populate modal
        $('#bebas_payment_student_id').val(studentId);
        $('#bebas_payment_payment_id').val(paymentId);
        $('#bebas_payment_pos_name').text(posName);
        $('#bebas_payment_total_bill').text('Rp ' + Number(totalBill).toLocaleString('id-ID'));
        $('#bebas_payment_total_pay').text('Rp ' + Number(totalPay).toLocaleString('id-ID'));
        $('#bebas_payment_sisa').text('Rp ' + Number(sisa).toLocaleString('id-ID'));
        $('#bebas_payment_amount').attr('max', sisa);
        
        // Show modal
        $('#bebasPaymentModal').modal('show');
    });
                }).fail(function(xhr, status, error) {
                    console.error('Error fetching tagihan:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);
                    $('#payment-content').html('<div class="alert alert-danger">Gagal mengambil data tagihan!</div>');
                });
            } else {
                console.log('Failed to load student detail');
                $('#payment-content').html('<div class="alert alert-danger">Gagal memuat data siswa!</div>');
            }
        });
    });
    
    function loadStudents() {
        var status = $('#student_status').val();
        console.log('Loading students with status:', status);
        
        // Reset Select2 dan tambahkan placeholder
        var select = $('#student_search');
        select.empty().append('<option value="">Pilih Peserta Didik</option>');
        
        // Set data untuk Select2 dengan AJAX
        select.select2({
            placeholder: 'Ketik NIS atau nama siswa untuk mencari...',
            allowClear: true,
            width: '100%',
            ajax: {
                url: '/api/students/search',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        term: params.term || '',
                        status: status
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.results || []
                    };
                },
                cache: true
            },
            language: {
                noResults: function() {
                    return "Tidak ada hasil ditemukan";
                },
                searching: function() {
                    return "Mencari...";
                },
                inputTooShort: function() {
                    return "Ketik minimal 1 karakter untuk mencari";
                }
            },
            minimumInputLength: 1
        }).on('select2:open select2:close', function() {
            // Force override warna setelah Select2 dibuka/ditutup
            setTimeout(function() {
                if (window.forceSelect2DefaultColor) {
                    window.forceSelect2DefaultColor();
                }
            }, 10);
        });
        
        // Force override warna setelah Select2 diinisialisasi
        setTimeout(function() {
            if (window.forceSelect2DefaultColor) {
                window.forceSelect2DefaultColor();
            }
        }, 100);
        
        console.log('Select2 initialized with AJAX search for status:', status);
    }
});

// ===== Multi Pembayaran (Bulanan + Bebas) =====
let multiMode = false;

function updateMultiToolbarSummary() {
    const count = $('.multi-check input[type="checkbox"]:checked').length;
    let total = 0;
    // Bulanan: gunakan data-bill dari sel
    $('.multi-bulanan:checked').each(function() {
        const cell = $(this).closest('td');
        const bill = parseFloat(cell.data('bill')) || 0;
        total += bill;
    });
    // Bebas: gunakan input nominal bila tersedia; fallback sisa
    $('.multi-bebas:checked').each(function() {
        const row = $(this).closest('tr');
        const payBtn = row.find('.bebas-payment-btn');
        const sisa = parseFloat(payBtn.data('sisa')) || 0;
        const input = row.find('.multi-bebas-amount');
        const val = parseFloat(input.val());
        total += (val && val > 0 && val <= sisa) ? val : sisa;
    });
    $('#multiSelectedCount').text(count);
    $('#multiSelectedTotal').text('Rp ' + total.toLocaleString('id-ID'));
    $('#multiPayBtn').prop('disabled', count === 0);
}

function enableMultiMode() {
    multiMode = true;
    $('#multiModeStatus').text('ON');
    // Tampilkan checkbox di sel bulanan yang belum dibayar
    $('#bulanan_table_body td.belum-bayar').each(function() {
        if ($(this).find('.multi-check').length === 0) {
            $(this).prepend('<div class="form-check form-check-inline me-1 multi-check"><input class="form-check-input multi-bulanan" type="checkbox"></div>');
        } else {
            $(this).find('.multi-check').removeClass('d-none');
        }
        $(this).addClass('multi-selectable');
    });
    // Tampilkan checkbox pada baris bebas yang masih ada sisa
    $('#bebas_table_body tr').each(function() {
        const btn = $(this).find('.bebas-payment-btn');
        const actionCell = $(this).find('td:last');
        if (btn.length && (parseFloat(btn.data('sisa')) || 0) > 0) {
            if (actionCell.find('.multi-check').length === 0) {
                actionCell.prepend('<div class="form-check d-inline-block me-2 multi-check"><input class="form-check-input multi-bebas" type="checkbox"></div>');
            } else {
                actionCell.find('.multi-check').removeClass('d-none');
            }
            // Tampilkan input nominal jika ada
            actionCell.find('.multi-bebas-amount').removeClass('d-none');
        }
    });
    // Pastikan listener aktif setelah re-render
    $(document).off('change.multi').on('change.multi', '.multi-check input[type="checkbox"]', function(){
        updateMultiToolbarSummary();
    });
    updateMultiToolbarSummary();
}

function disableMultiMode() {
    multiMode = false;
    $('#multiModeStatus').text('OFF');
    $('.multi-check').addClass('d-none');
    $('.multi-check input[type="checkbox"]').prop('checked', false);
    $('.multi-bebas-amount').addClass('d-none').val('');
    updateMultiToolbarSummary();
}

$(document).on('change', '.multi-check input[type="checkbox"]', function() {
    updateMultiToolbarSummary();
});

// Tombol toggle disembunyikan; tetap sediakan handler jika suatu saat ditampilkan kembali
$('#toggleMultiModeBtn').on('click', function() {
    if (multiMode) disableMultiMode(); else enableMultiMode();
});

$('#multiPayBtn').on('click', function() {
    const studentId = $('#student_id').val() || $('#student_search').val();
    if (!studentId) {
        alert('Pilih siswa terlebih dahulu');
        return;
    }
    const items = [];
    // Kumpulkan bulanan
    $('#bulanan_table_body td.belum-bayar .multi-bulanan:checked').each(function() {
        const cell = $(this).closest('td');
        const paymentId = parseInt(cell.data('payment-id'));
        const monthId = parseInt(cell.data('month-id'));
        const amount = parseFloat(cell.data('bill')) || 0;
        if (paymentId && monthId && amount > 0) {
            items.push({ type: 'bulanan', payment_id: paymentId, month_id: monthId, amount: amount });
        }
    });
    // Kumpulkan bebas (nominal input oleh kasir)
    $('#bebas_table_body .multi-bebas:checked').each(function() {
        const row = $(this).closest('tr');
        const btn = row.find('.bebas-payment-btn');
        const paymentId = parseInt(btn.data('payment-id'));
        const sisa = parseFloat(btn.data('sisa')) || 0;
        const input = row.find('.multi-bebas-amount');
        const val = parseFloat(input.val());
        if (!val || val <= 0 || val > sisa) {
            input.addClass('is-invalid');
            return; // skip jika tidak valid
        }
        input.removeClass('is-invalid');
        if (paymentId) {
            items.push({ type: 'bebas', payment_id: paymentId, amount: val });
        }
    });

    if (items.length === 0) {
        alert('Tidak ada item yang dipilih.');
        return;
    }

    const payload = {
        student_id: parseInt(studentId),
        payment_method: 'cash',
        payment_date: $('#print_date').val() || undefined,
        items: items
    };

    $.ajax({
        url: '/payment/multi-cash',
        method: 'POST',
        contentType: 'application/json',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: JSON.stringify(payload),
        success: function(response) {
            if (response.success) {
                if (window.showVerificationToast) {
                    window.showVerificationToast('success', 'Berhasil!', 'Multi pembayaran berhasil. Total: Rp ' + (response.total_amount||0).toLocaleString('id-ID'));
                }
                // Refresh tampilan data bulanan & bebas
                if (studentId) {
                    updateBulananDataOnly(studentId);
                    updateBebasDataOnly(studentId);
                    loadTransactionHistory(studentId);
                }
                // Tetap pertahankan multi mode ON
                if (typeof enableMultiMode === 'function') { setTimeout(enableMultiMode, 0); }
            } else {
                const msg = response.message || 'Gagal memproses multi pembayaran';
                if (window.showVerificationToast) { window.showVerificationToast('error', 'Gagal!', msg); } else { alert(msg); }
            }
        },
        error: function(xhr) {
            const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Server error saat memproses multi pembayaran';
            if (window.showVerificationToast) { window.showVerificationToast('error', 'Error', msg); } else { alert(msg); }
        }
    });
});

// Event listener untuk modal events
$(document).ready(function() {
    // Modal pembayaran events
    $('#paymentModal').on('hidden.bs.modal', function () {
        console.log('Modal pembayaran ditutup (event: hidden.bs.modal)');
        // Reset form dan data
        $(this).find('form')[0]?.reset();
        $(this).removeData('payment-data');
    });
    
    $('#paymentModal').on('hide.bs.modal', function () {
        console.log('Modal pembayaran akan ditutup (event: hide.bs.modal)');
    });
    
    // Modal pembayaran bebas events
    $('#bebasPaymentModal').on('hidden.bs.modal', function () {
        console.log('Modal pembayaran bebas ditutup (event: hidden.bs.modal)');
        // Reset form
        $(this).find('form')[0]?.reset();
    });
    
    $('#bebasPaymentModal').on('hide.bs.modal', function () {
        console.log('Modal pembayaran bebas akan ditutup (event: hide.bs.modal)');
    });
});

// Fungsi untuk menutup modal pembayaran
function closePaymentModal() {
    // Tutup modal menggunakan jQuery (Bootstrap 4)
        $('#paymentModal').modal('hide');
    
    // Reset form jika ada
    $('#paymentModal form')[0]?.reset();
    
    // Clear data yang tersimpan
    $('#paymentModal').removeData('payment-data');
    
    console.log('Modal pembayaran ditutup dan form direset');
}

// Fungsi untuk konfirmasi pembayaran
function confirmPayment(method) {
    var paymentData = $('#paymentModal').data('payment-data');
    if (!paymentData) {
        alert('Data pembayaran tidak ditemukan!');
        return;
    }

    var methodText = method === 'cash' ? 'Tunai' : 'Tabungan';
    var icon = method === 'cash' ? 'fas fa-money-bill-wave' : 'fas fa-piggy-bank';
    var buttonClass = method === 'cash' ? 'btn-success' : 'btn-info';

        // Tutup modal pembayaran terlebih dahulu
        $('#paymentModal').modal('hide');
        
        // Langsung proses pembayaran tanpa modal konfirmasi kedua
        processPayment(paymentData, method);
}

// Fungsi untuk memproses pembayaran
function processPayment(paymentData, method) {
    var requestData = {
        student_id: paymentData.studentId,
        payment_id: paymentData.paymentId,
        month_id: paymentData.monthId,
        amount: paymentData.bill,
        payment_method: method,
        // Tidak perlu mengirim payment_date, biarkan server yang menentukan
        _token: $('meta[name="csrf-token"]').attr('content')
    };

    console.log('Processing payment:', requestData);

    $.ajax({
        url: '/api/payment/process',
        method: 'POST',
        data: requestData,
        success: function(response) {
            console.log('Payment response received:', response);
            console.log('Payment date in response:', response.payment_date);
            console.log('Current date when response received:', new Date().toISOString().split('T')[0]);
            
            if (response.success) {
                // Update tampilan
                updatePaymentDisplay(paymentData);
                
                // Update riwayat transaksi
                if (response.latest_transactions) {
                    updateTransactionHistory(response.latest_transactions);
                }
                
                // Modal sudah ditutup sebelumnya, tidak perlu tutup lagi
                
                // Tampilkan notifikasi sukses dengan toast
                const successMessage = `Pembayaran Bulanan Berhasil!<br><strong>${paymentData.posName} - ${paymentData.monthName}</strong><br>Nominal: Rp ${Number(paymentData.bill).toLocaleString('id-ID')}<br>No. Pembayaran: ${response.payment_number}<br>Tanggal: ${response.payment_date}`;
                
                // Coba gunakan sistem toast yang ada
                if (window.showVerificationToast) {
                    window.showVerificationToast('success', 'Berhasil!', successMessage);
                } else if (window.fallbackToast) {
                    // Gunakan fallback toast system
                    window.fallbackToast('success', 'Berhasil!', successMessage);
                } else {
                    // Fallback: gunakan alert atau console log
                    console.log('Toast system not available, using fallback');
                    alert('Pembayaran Berhasil!\n' + successMessage.replace(/<br>/g, '\n').replace(/<[^>]*>/g, ''));
                }
                
                // Update hanya data bulanan tanpa reload semua
                updateBulananDataOnly(paymentData.studentId);
            } else {
                const errorMessage = response.message || 'Terjadi kesalahan saat memproses pembayaran';
                
                if (window.showVerificationToast) {
                    window.showVerificationToast('error', 'Gagal!', errorMessage);
                } else if (window.fallbackToast) {
                    // Gunakan fallback toast system
                    window.fallbackToast('error', 'Gagal!', errorMessage);
                } else {
                    // Fallback: gunakan alert atau console log
                    console.log('Toast system not available, using fallback');
                    alert('Pembayaran Gagal!\n' + errorMessage);
                }
            }
        },
        error: function(xhr, status, error) {
            console.error('Payment error:', error);
            
            const errorMessage = 'Terjadi kesalahan saat memproses pembayaran';
            
            if (window.showVerificationToast) {
                window.showVerificationToast('error', 'Gagal!', errorMessage);
            } else if (window.fallbackToast) {
                // Gunakan fallback toast system
                window.fallbackToast('error', 'Gagal!', errorMessage);
            } else {
                // Fallback: gunakan alert atau console log
                console.log('Toast system not available, using fallback');
                alert('Pembayaran Gagal!\n' + errorMessage);
            }
        }
    });
}

// Fungsi untuk update tampilan pembayaran
function updatePaymentDisplay(paymentData) {
    var targetCell = $('.clickable-payment[data-student-id="' + paymentData.studentId + '"][data-payment-id="' + paymentData.paymentId + '"][data-month-id="' + paymentData.monthId + '"]');
    
    if (targetCell.length > 0) {
        var today = new Date();
        var formattedDate = today.getDate().toString().padStart(2, '0') + '/' + 
                           (today.getMonth() + 1).toString().padStart(2, '0') + '/' + 
                           today.getFullYear().toString().slice(-2);
        
        targetCell.removeClass('belum-bayar clickable-payment')
                  .addClass('sudah-bayar')
                  .text(formattedDate)
                  .removeAttr('data-student-id data-payment-id data-month-id data-bill data-pos-name data-month-name')
                  .css('background-color', '#d4edda')
                  .css('color', '#155724');
        
        console.log('Payment display updated successfully');
    } else {
        console.log('Target cell not found for payment update');
        console.log('Payment data:', paymentData);
    }
}

// Fungsi untuk update riwayat transaksi
function updateTransactionHistory(transactions) {
    console.log('Updating transaction history with:', transactions);
    console.log('Transactions type:', typeof transactions);
    console.log('Transactions length:', transactions.length);
    
    var tbody = $('#transaksi_terakhir_body');
    tbody.empty();
    
    if (transactions && transactions.length > 0) {
        transactions.forEach(function(transaction, index) {
            console.log('Processing transaction ' + index + ':', transaction);
            console.log('Transaction payment_date:', transaction.payment_date);
            console.log('Transaction log_trx_input_date:', transaction.log_trx_input_date);
            console.log('Transaction log_trx_id:', transaction.log_trx_id);
            console.log('Transaction log_trx_id type:', typeof transaction.log_trx_id);
            
            var formattedDate = moment(transaction.payment_date || transaction.log_trx_input_date).format('DD/MM/YY');
            var formattedAmount = 'Rp ' + Number(transaction.amount).toLocaleString('id-ID');
            
            // Format payment name dan number seperti di gambar
            var paymentDisplay = transaction.display_name || transaction.pos_name || 'Pembayaran';
            var paymentNumber = transaction.payment_number || '';
            
            // Ensure log_trx_id is valid
            var transactionId = transaction.log_trx_id;
            console.log('Transaction ID for button:', transactionId);
            
            var row = '<tr>';
            row += '<td><strong>' + paymentDisplay + '</strong><br><small class="text-muted">' + paymentNumber + '</small></td>';
            row += '<td>' + formattedAmount + '</td>';
            row += '<td>' + formattedDate + '</td>';
            row += '<td class="text-center">';
            row += '<button class="btn btn-sm btn-danger delete-transaction-btn" data-transaction-id="' + transactionId + '" data-payment-number="' + paymentNumber + '" data-amount="' + formattedAmount + '" data-date="' + formattedDate + '" data-payment-name="' + paymentDisplay + '" title="Hapus Transaksi">';
            row += '<i class="fas fa-trash"></i>';
            row += '</button>';
            row += '</td>';
            row += '</tr>';
            
            tbody.append(row);
        });
        
        // Attach event listeners for delete buttons
        $('.delete-transaction-btn').off('click').on('click', function() {
            var transactionId = $(this).data('transaction-id');
            var paymentNumber = $(this).data('payment-number');
            var amount = $(this).data('amount');
            var date = $(this).data('date');
            var paymentName = $(this).data('payment-name');
            
            console.log('Delete button clicked - Transaction ID:', transactionId);
            console.log('Delete button clicked - Transaction ID type:', typeof transactionId);
            console.log('Delete button clicked - Payment Number:', paymentNumber);
            console.log('Delete button clicked - Amount:', amount);
            console.log('Delete button clicked - Date:', date);
            console.log('Delete button clicked - Payment Name:', paymentName);
            
            showDeleteTransactionModal(transactionId, paymentNumber, amount, date, paymentName);
        });
    } else {
        tbody.html('<tr><td colspan="4" class="text-center text-muted">Belum ada transaksi</td></tr>');
    }
}

// Fungsi untuk load riwayat transaksi
function loadTransactionHistory(studentId) {
    console.log('Loading transaction history for student ID:', studentId);
    
    $.getJSON('/api/students/' + studentId + '/transactions', function(response) {
        console.log('Transaction history API response:', response);
        console.log('Response type:', typeof response);
        console.log('Response success:', response.success);
        console.log('Response transactions:', response.transactions);
        
        if (response.success && response.transactions) {
            console.log('Updating transaction history with', response.transactions.length, 'transactions');
            updateTransactionHistory(response.transactions);
        } else {
            console.log('No transactions found or empty response');
            $('#transaksi_terakhir_body').html('<tr><td colspan="4" class="text-center text-muted">Belum ada transaksi</td></tr>');
        }
    }).fail(function(xhr, status, error) {
        console.error('Error loading transaction history:', error);
        $('#transaksi_terakhir_body').html('<tr><td colspan="4" class="text-center text-danger">Gagal memuat riwayat transaksi</td></tr>');
    });
}

// Fungsi untuk memproses pembayaran bebas
function processBebasPayment() {
    var form = $('#bebasPaymentForm');
    var formData = new FormData(form[0]);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
    
    // Debug: Log form data
    console.log('Bebas payment form data:');
    for (var pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
    
    // Validasi client-side
    var amount = parseInt($('#bebas_payment_amount').val());
    var sisa = parseInt($('#bebas_payment_sisa').text().replace(/[^\d]/g, ''));
    var paymentMethod = $('input[name="payment_method"]:checked').val();
    
    console.log('Amount:', amount, 'Sisa:', sisa, 'Payment Method:', paymentMethod);
    
    if (amount < 1) {
        alert('Nominal pembayaran minimal Rp 1');
        return;
    }
    
    if (amount > sisa) {
        alert('Nominal pembayaran tidak boleh melebihi sisa tagihan');
        return;
    }

    // Jika pembayaran dengan tabungan, cek saldo tabungan
    if (paymentMethod === 'tabungan') {
        var studentId = $('#bebas_payment_student_id').val();
        
        // Cek saldo tabungan siswa
        $.getJSON('/api/students/' + studentId + '/tabungan', function(response) {
            if (response.success && response.tabungan) {
                var saldoTabungan = parseFloat(response.tabungan.saldo);
                
                if (saldoTabungan < amount) {
                    if (window.showVerificationToast) { window.showVerificationToast('error', '⚠️ Saldo Tidak Mencukupi', 'Saldo tabungan: Rp ' + saldoTabungan.toLocaleString('id-ID') + '\nNominal pembayaran: Rp ' + amount.toLocaleString('id-ID') + '\n\nSilakan top up tabungan terlebih dahulu atau pilih metode pembayaran lain.'); }
                    return;
                }
                
                // Jika saldo mencukupi, lanjutkan proses pembayaran
                processBebasPaymentAjax();
            } else {
                if (window.showVerificationToast) { window.showVerificationToast('error', 'Error', 'Siswa tidak memiliki rekening tabungan!'); }
                return;
            }
        });
    } else {
        // Jika pembayaran tunai, langsung proses
        processBebasPaymentAjax();
    }
    
}

// Fungsi untuk memproses pembayaran bebas via AJAX
function processBebasPaymentAjax() {
    var form = $('#bebasPaymentForm');
    var formData = new FormData(form[0]);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
    
    $.ajax({
        url: '/api/payment/bebas/process',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                // Update riwayat transaksi jika ada
                if (response.latest_transactions) {
                    updateTransactionHistory(response.latest_transactions);
                }
                
                if (window.showVerificationToast) { 
                    const successMessage = `Pembayaran Bebas Berhasil!<br><strong>${response.message}</strong>`;
                    window.showVerificationToast('success', 'Berhasil!', successMessage); 
                }
                
                $('#bebasPaymentModal').modal('hide');
                
                // Reset form
                $('#bebasPaymentForm')[0].reset();
                $('#bebas_payment_amount').val('');
                $('#bebas_payment_desc').val('');
                
                // Update hanya data bebas tanpa reload semua
                var studentId = $('#bebas_payment_student_id').val();
                if (studentId) {
                    updateBebasDataOnly(studentId);
                }
            } else {
                if (window.showVerificationToast) { window.showVerificationToast('error', 'Gagal!', response.message || 'Gagal memproses pembayaran bebas'); }
            }
        },
        error: function(xhr, status, error) {
            console.error('Bebas payment error:', error);
            console.error('Status:', status);
            console.error('Response:', xhr.responseText);
            console.error('Status code:', xhr.status);
            
            var errorMessage = 'Terjadi kesalahan saat memproses pembayaran bebas';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            if (window.showVerificationToast) { window.showVerificationToast('error', 'Error', errorMessage); }
        }
    });
}

// Event listener untuk tombol bayar bebas
$(document).on('click', '.bebas-payment-btn', function() {
    var studentId = $(this).data('student-id');
    var paymentId = $(this).data('payment-id');
    var posName = $(this).data('pos-name');
    var totalBill = $(this).data('total-bill');
    var totalPay = $(this).data('total-pay');
    var sisa = $(this).data('sisa');
    
    // Populate modal
    $('#bebas_payment_student_id').val(studentId);
    $('#bebas_payment_payment_id').val(paymentId);
    $('#bebas_payment_pos_name').text(posName);
    $('#bebas_payment_total_bill').text('Rp ' + Number(totalBill).toLocaleString('id-ID'));
    $('#bebas_payment_total_pay').text('Rp ' + Number(totalPay).toLocaleString('id-ID'));
    $('#bebas_payment_sisa').text('Rp ' + Number(sisa).toLocaleString('id-ID'));
    $('#bebas_payment_amount').attr('max', sisa);
    
    // Show modal
    $('#bebasPaymentModal').modal('show');
});

// Event listener untuk form pembayaran bebas
$('#bebasPaymentForm').on('submit', function(e) {
    e.preventDefault();
    processBebasPayment();
});

// Fungsi untuk memuat ulang data tagihan siswa
function loadStudentTagihan(studentId) {
    console.log('Reloading tagihan for student ID:', studentId);
    
    // Show loading state
    $('#payment-content').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><br>Memuat data tagihan...</div>');
    
    // Load student detail dan tagihan
    $.getJSON('/api/students/' + studentId + '/detail', function(studentResponse) {
        if (studentResponse.success) {
            console.log('Student detail loaded:', studentResponse.student);
            
            // Update informasi siswa
            $('#student_name').text(studentResponse.student.student_name);
            $('#student_nis').text(studentResponse.student.student_nis);
            $('#student_class').text(studentResponse.student.class_name);
            
            // Load tagihan
            $.getJSON('/api/students/' + studentId + '/tagihan', function(tagihanResponse) {
                if (tagihanResponse.success && tagihanResponse.tagihan) {
                    updateTagihanDisplay(tagihanResponse.tagihan, studentId);
                } else {
                    $('#payment-content').html('<div class="alert alert-danger">Gagal memuat data tagihan!</div>');
                }
            }).fail(function(xhr, status, error) {
                console.error('Error fetching tagihan:', error);
                $('#payment-content').html('<div class="alert alert-danger">Gagal mengambil data tagihan!</div>');
            });
        } else {
            console.log('Failed to load student detail');
            $('#payment-content').html('<div class="alert alert-danger">Gagal memuat data siswa!</div>');
        }
    });
}

// Fungsi untuk update hanya data bebas
function updateBebasDataOnly(studentId) {
    // Show loading state hanya untuk tabel bebas
    $('#bebas_table_body').html('<tr><td colspan="7" class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat data...</td></tr>');
    
    // Load hanya data tagihan bebas
    $.getJSON('/api/students/' + studentId + '/tagihan', function(tagihanResponse) {
        if (tagihanResponse.success && tagihanResponse.tagihan && tagihanResponse.tagihan.bebas) {
            updateBebasTableOnly(tagihanResponse.tagihan.bebas, studentId);
        } else {
            $('#bebas_table_body').html('<tr><td colspan="7" class="text-center text-danger">Gagal memuat data bebas</td></tr>');
        }
    }).fail(function(xhr, status, error) {
        console.error('Error fetching bebas data:', error);
        $('#bebas_table_body').html('<tr><td colspan="7" class="text-center text-danger">Gagal mengambil data bebas</td></tr>');
    });
}

// Fungsi untuk update hanya tabel bebas
function updateBebasTableOnly(bebasData, studentId) {
    
    var bebasHtml = '';
    if (bebasData && bebasData.length > 0) {
        bebasData.forEach(function(item, idx) {
            var sisa = (item.bebas_bill - item.bebas_total_pay);
            let formattedBill = item.bebas_bill ? 'Rp ' + Number(item.bebas_bill).toLocaleString('id-ID') : '-';
            let formattedTotalPay = item.bebas_total_pay ? 'Rp ' + Number(item.bebas_total_pay).toLocaleString('id-ID') : 'Rp 0';
            let formattedSisa = sisa > 0 ? 'Rp ' + Number(sisa).toLocaleString('id-ID') : 'Rp 0';
            let statusClass = sisa <= 0 ? 'lunas' : 'belum-lunas';
            let statusText = sisa <= 0 ? 'Lunas' : 'Belum Lunas';

            bebasHtml += '<tr>';
            bebasHtml += '<td>' + (idx+1) + '</td>';
            bebasHtml += '<td>' + item.pos_name + ' - ' + (item.period_name || 'Tahun Ajaran') + '</td>';
            bebasHtml += '<td>' + formattedBill + '</td>';
            bebasHtml += '<td>' + formattedTotalPay + '</td>';
            bebasHtml += '<td>' + formattedSisa + '</td>';
            bebasHtml += '<td><span class="badge bg-' + (sisa <= 0 ? 'success' : 'warning') + '">' + statusText + '</span></td>';
            bebasHtml += '<td>';
            if (sisa > 0) {
                bebasHtml += '<div class="d-flex align-items-center gap-2">';
                bebasHtml += '<div class="form-check d-none multi-check"><input class="form-check-input multi-bebas" type="checkbox" data-sisa="' + sisa + '"></div>';
                bebasHtml += '<input type="number" min="1" max="' + sisa + '" class="form-control form-control-sm multi-bebas-amount d-none" placeholder="Nominal" style="width:120px">';
                bebasHtml += '<button class="btn btn-sm btn-primary bebas-payment-btn" data-student-id="' + studentId + '" data-payment-id="' + item.payment_id + '" data-pos-name="' + item.pos_name + '" data-total-bill="' + item.bebas_bill + '" data-total-pay="' + item.bebas_total_pay + '" data-sisa="' + sisa + '">Bayar</button>';
                bebasHtml += '</div>';
            } else {
                bebasHtml += '<span class="text-muted">Sudah Lunas</span>';
            }
            bebasHtml += '</td>';
            bebasHtml += '</tr>';
        });
    } else {
        bebasHtml = '<tr><td colspan="7" class="text-center">Tidak ada data tagihan bebas</td></tr>';
    }
    $('#bebas_table_body').html(bebasHtml);
    
    // Re-attach event listener untuk tombol bayar bebas
    $('.bebas-payment-btn').off('click').on('click', function() {
        var studentId = $(this).data('student-id');
        var paymentId = $(this).data('payment-id');
        var posName = $(this).data('pos-name');
        var totalBill = $(this).data('total-bill');
        var totalPay = $(this).data('total-pay');
        var sisa = $(this).data('sisa');
        
        // Reset form terlebih dahulu
        $('#bebasPaymentForm')[0].reset();
        $('#bebas_payment_amount').val('');
        $('#bebas_payment_desc').val('');
        
        // Populate modal
        $('#bebas_payment_student_id').val(studentId);
        $('#bebas_payment_payment_id').val(paymentId);
        $('#bebas_payment_pos_name').text(posName);
        $('#bebas_payment_total_bill').text('Rp ' + Number(totalBill).toLocaleString('id-ID'));
        $('#bebas_payment_total_pay').text('Rp ' + Number(totalPay).toLocaleString('id-ID'));
        $('#bebas_payment_sisa').text('Rp ' + Number(sisa).toLocaleString('id-ID'));
        $('#bebas_payment_amount').attr('max', sisa);
        
        // Show modal
        $('#bebasPaymentModal').modal('show');
    });
}

// Fungsi untuk update hanya data bulanan
function updateBulananDataOnly(studentId) {
    // Show loading state hanya untuk tabel bulanan
    $('#bulanan_table_body').html('<tr><td colspan="14" class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat data...</td></tr>');
    
    // Load hanya data tagihan bulanan
    $.getJSON('/api/students/' + studentId + '/tagihan', function(tagihanResponse) {
        if (tagihanResponse.success && tagihanResponse.tagihan && tagihanResponse.tagihan.bulanan) {
            updateBulananTableOnly(tagihanResponse.tagihan.bulanan, studentId);
        } else {
            $('#bulanan_table_body').html('<tr><td colspan="14" class="text-center text-danger">Gagal memuat data bulanan</td></tr>');
        }
    }).fail(function(xhr, status, error) {
        console.error('Error fetching bulanan data:', error);
        $('#bulanan_table_body').html('<tr><td colspan="14" class="text-center text-danger">Gagal mengambil data bulanan</td></tr>');
    });
}

// Fungsi untuk update hanya tabel bulanan
function updateBulananTableOnly(bulananData, studentId) {
    console.log('=== UPDATE BULANAN TABLE ONLY ===');
    console.log('Input bulananData:', bulananData);
    console.log('Student ID:', studentId);
    
    var bulanList = ['Juli','Agustus','September','Oktober','November','Desember','Januari','Februari','Maret','April','Mei','Juni'];
    var bulananHtml = '';
    
    if (bulananData && bulananData.length > 0) {
        var grouped = {};
        bulananData.forEach(function(item, index) {
            console.log('Processing item ' + index + ':', item);
            
            if (!grouped[item.payment_id]) {
                grouped[item.payment_id] = {
                    nama: item.pos_name + ' - ' + (item.period_name || 'Tahun Ajaran'),
                    period_name: item.period_name,
                    bulan: {}
                };
            }
            grouped[item.payment_id].nama = item.pos_name + ' - ' + (item.period_name || 'Tahun Ajaran');
            grouped[item.payment_id].period_name = item.period_name;
            grouped[item.payment_id].bulan[item.month_month_id] = {
                bill: item.bulan_bill,
                status: item.bulan_status,
                date_pay: item.bulan_date_pay
            };
            
            console.log('Added to grouped[' + item.payment_id + '].bulan[' + item.month_month_id + ']:', {
                bill: item.bulan_bill,
                status: item.bulan_status,
                date_pay: item.bulan_date_pay
            });
        });
        
        console.log('Final grouped data:', grouped);
        
        var idx = 1;
        for (var key in grouped) {
            var paymentName = grouped[key].nama;
            var posName = paymentName.split(' - ')[0];
            var periodName = grouped[key].period_name || 'Tahun Ajaran';
            
            bulananHtml += '<tr><td>' + (idx++) + '</td><td>' + posName + ' - ' + periodName + '</td>';
            for (var i = 1; i <= 12; i++) {
                var b = grouped[key].bulan[i];
                if (b) {
                    // Debug logging untuk bulanan
                    console.log('Month ' + i + ' data:', b);
                    console.log('Status:', b.status, 'Date pay:', b.date_pay);
                    console.log('Status type:', typeof b.status, 'Status value:', b.status);
                    console.log('Date pay type:', typeof b.date_pay, 'Date pay value:', b.date_pay);
                    
                    // Debug: tampilkan detail lengkap untuk troubleshooting
                    console.log('=== DETAIL DEBUG MONTH ' + i + ' ===');
                    console.log('Raw data:', b);
                    console.log('Status:', b.status, 'Type:', typeof b.status);
                    console.log('Date pay:', b.date_pay, 'Type:', typeof b.date_pay);
                    console.log('Bill:', b.bill, 'Type:', typeof b.bill);
                    
                    // Logika status yang lebih robust: cek status = 1 ATAU ada date_pay
                    var isPaid = false;
                    var paidReason = '';
                    
                    if (b.status == 1) {
                        isPaid = true;
                        paidReason = 'status == 1';
                    } else if (b.date_pay && b.date_pay !== null && b.date_pay !== '') {
                        isPaid = true;
                        paidReason = 'ada date_pay: ' + b.date_pay;
                    }
                    
                    console.log('Is paid:', isPaid, 'Reason:', paidReason);
                    
                    if (isPaid) {
                        // Format tanggal dengan moment.js
                        var formattedDate = '';
                        if (b.date_pay && b.date_pay !== null && b.date_pay !== '') {
                            formattedDate = moment(b.date_pay).format('DD/MM/YY');
                        } else {
                            // Jika status = 1 tapi tidak ada date_pay, gunakan tanggal hari ini
                            formattedDate = moment().format('DD/MM/YY');
                        }
                        bulananHtml += '<td class="sudah-bayar">' + formattedDate + '</td>';
                        console.log('Month ' + i + ' marked as PAID (hijau) - ' + paidReason);
                    } else {
                        let formattedBill = b.bill ? 'Rp ' + Number(b.bill).toLocaleString('id-ID') : '-';
                        var escapedPosName = posName.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                        bulananHtml += '<td class="belum-bayar clickable-payment" data-student-id="' + studentId + '" data-payment-id="' + key.split('_')[0] + '" data-pos-id="' + grouped[key].pos_id + '" data-month-id="' + i + '" data-bill="' + b.bill + '" data-pos-name="' + escapedPosName + '" data-month-name="' + bulanList[i-1] + '">' + formattedBill + '</td>';
                        console.log('Month ' + i + ' marked as UNPAID (merah) - status=' + b.status + ', date_pay=' + b.date_pay);
                    }
                } else {
                    bulananHtml += '<td>-</td>';
                }
            }
            bulananHtml += '</tr>';
        }
    } else {
        bulananHtml = '<tr><td colspan="14" class="text-center">Tidak ada data tagihan bulanan</td></tr>';
    }
    
    console.log('Final HTML:', bulananHtml);
    $('#bulanan_table_body').html(bulananHtml);
    console.log('=== END UPDATE BULANAN TABLE ONLY ===');
    
    // Re-attach event listener untuk klik pada nominal pembayaran
    $('.clickable-payment').off('click').on('click', function(e) {
        if ((typeof multiMode !== 'undefined' && multiMode) || $(e.target).closest('.multi-check').length) {
            return;
        }
        var studentId = $(this).data('student-id');
        var paymentId = $(this).data('payment-id');
        var monthId = $(this).data('month-id');
        var bill = $(this).data('bill');
        var posName = $(this).data('pos-name');
        var monthName = $(this).data('month-name');
        
        // Tampilkan data di modal
        $('#modal-pos-name').text(posName);
        $('#modal-month-name').text(monthName);
        $('#modal-bill-amount').text('Rp ' + Number(bill).toLocaleString('id-ID'));
        
        // Simpan data untuk proses pembayaran
        $('#paymentModal').data('payment-data', {
            studentId: studentId,
            paymentId: paymentId,
            monthId: monthId,
            bill: bill,
            posName: posName,
            monthName: monthName
        });
        
        // Tampilkan modal
        $('#paymentModal').modal('show');
    });

    // Jika multi mode aktif, terapkan checkbox pada elemen yang baru dirender
    if (typeof multiMode !== 'undefined' && multiMode) {
        enableMultiMode();
    }
}

// Fungsi untuk update tampilan tagihan
function updateTagihanDisplay(tagihan, studentId) {
    // Bulanan
    var bulanList = ['Juli','Agustus','September','Oktober','November','Desember','Januari','Februari','Maret','April','Mei','Juni'];
    var bulananHtml = '';
    if (tagihan.bulanan && tagihan.bulanan.length > 0) {
        // Grouping berdasarkan kombinasi payment_id dan pos_id
        var grouped = {};
        tagihan.bulanan.forEach(function(item) {
            // Buat key unik berdasarkan payment_id dan pos_id
            var key = item.payment_id + '_' + (item.pos_pos_id || 1);
            
            if (!grouped[key]) {
                grouped[key] = {
                    nama: item.pos_name + ' - ' + (item.period_name || 'Tahun Ajaran'),
                    period_name: item.period_name,
                    pos_id: item.pos_pos_id || 1,
                    bulan: {}
                };
            }
            
            grouped[key].bulan[item.month_month_id] = {
                bill: item.bulan_bill,
                status: item.bulan_status,
                date_pay: item.bulan_date_pay
            };
        });
        
        var idx = 1;
        for (var key in grouped) {
            var paymentName = grouped[key].nama;
            var posName = paymentName.split(' - ')[0];
            var periodName = grouped[key].period_name || 'Tahun Ajaran';
            
            bulananHtml += '<tr><td>' + (idx++) + '</td><td>' + posName + ' - ' + periodName + '</td>';
            for (var i = 1; i <= 12; i++) {
                var b = grouped[key].bulan[i];
                if (b) {
                    // Debug logging untuk bulanan
                    console.log('Month ' + i + ' data:', b);
                    console.log('Status:', b.status, 'Date pay:', b.date_pay);
                    
                    // Perbaiki logika status: jika ada date_pay, berarti sudah bayar
                    if (b.date_pay) {
                        // Format tanggal dengan moment.js
                        var formattedDate = moment(b.date_pay).format('DD/MM/YY');
                        bulananHtml += '<td class="sudah-bayar">' + formattedDate + '</td>';
                        console.log('Month ' + i + ' marked as PAID (hijau) - ada date_pay');
                    } else {
                        let formattedBill = b.bill ? 'Rp ' + Number(b.bill).toLocaleString('id-ID') : '-';
                        var escapedPosName = posName.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                        bulananHtml += '<td class="belum-bayar clickable-payment" data-student-id="' + studentId + '" data-payment-id="' + key.split('_')[0] + '" data-pos-id="' + grouped[key].pos_id + '" data-month-id="' + i + '" data-bill="' + b.bill + '" data-pos-name="' + escapedPosName + '" data-month-name="' + bulanList[i-1] + '">' + formattedBill + '</td>';
                        console.log('Month ' + i + ' marked as UNPAID (merah) - tidak ada date_pay');
                    }
                } else {
                    bulananHtml += '<td>-</td>';
                }
            }
            bulananHtml += '</tr>';
        }
    } else {
        bulananHtml = '<tr><td colspan="14" class="text-center">Tidak ada data tagihan bulanan</td></tr>';
    }
    $('#bulanan_table_body').html(bulananHtml);
    
    // Event listener untuk klik pada nominal pembayaran
    $('.clickable-payment').off('click').on('click', function(e) {
        if ((typeof multiMode !== 'undefined' && multiMode) || $(e.target).closest('.multi-check').length) {
            return;
        }
        var studentId = $(this).data('student-id');
        var paymentId = $(this).data('payment-id');
        var monthId = $(this).data('month-id');
        var bill = $(this).data('bill');
        var posName = $(this).data('pos-name');
        var monthName = $(this).data('month-name');
        
        // Tampilkan data di modal
        $('#modal-pos-name').text(posName);
        $('#modal-month-name').text(monthName);
        $('#modal-bill-amount').text('Rp ' + Number(bill).toLocaleString('id-ID'));
        
        // Simpan data untuk proses pembayaran
        $('#paymentModal').data('payment-data', {
            studentId: studentId,
            paymentId: paymentId,
            monthId: monthId,
            bill: bill,
            posName: posName,
            monthName: monthName
        });
        
        // Tampilkan modal
        $('#paymentModal').modal('show');
    });
    
    // Bebas
    var bebasHtml = '';
    if (tagihan.bebas && tagihan.bebas.length > 0) {
        tagihan.bebas.forEach(function(item, idx) {
            var sisa = (item.bebas_bill - item.bebas_total_pay);
            let formattedBill = item.bebas_bill ? 'Rp ' + Number(item.bebas_bill).toLocaleString('id-ID') : '-';
            let formattedTotalPay = item.bebas_total_pay ? 'Rp ' + Number(item.bebas_total_pay).toLocaleString('id-ID') : 'Rp 0';
            let formattedSisa = sisa > 0 ? 'Rp ' + Number(sisa).toLocaleString('id-ID') : 'Rp 0';
            let statusClass = sisa <= 0 ? 'lunas' : 'belum-lunas';
            let statusText = sisa <= 0 ? 'Lunas' : 'Belum Lunas';

            bebasHtml += '<tr>';
            bebasHtml += '<td>' + (idx+1) + '</td>';
            bebasHtml += '<td>' + item.pos_name + ' - ' + (item.period_name || 'Tahun Ajaran') + '</td>';
            bebasHtml += '<td>' + formattedBill + '</td>';
            bebasHtml += '<td>' + formattedTotalPay + '</td>';
            bebasHtml += '<td>' + formattedSisa + '</td>';
            bebasHtml += '<td><span class="badge bg-' + (sisa <= 0 ? 'success' : 'warning') + '">' + statusText + '</span></td>';
            bebasHtml += '<td>';
            if (sisa > 0) {
                bebasHtml += '<div class="d-flex align-items-center gap-2">';
                bebasHtml += '<div class="form-check d-none multi-check"><input class="form-check-input multi-bebas" type="checkbox" data-sisa="' + sisa + '"></div>';
                bebasHtml += '<input type="number" min="1" max="' + sisa + '" class="form-control form-control-sm multi-bebas-amount d-none" placeholder="Nominal" style="width:120px">';
                bebasHtml += '<button class="btn btn-sm btn-primary bebas-payment-btn" data-student-id="' + studentId + '" data-payment-id="' + item.payment_id + '" data-pos-name="' + item.pos_name + '" data-total-bill="' + item.bebas_bill + '" data-total-pay="' + item.bebas_total_pay + '" data-sisa="' + sisa + '">Bayar</button>';
                bebasHtml += '</div>';
            } else {
                bebasHtml += '<span class="text-muted">Sudah Lunas</span>';
            }
            bebasHtml += '</td>';
            bebasHtml += '</tr>';
        });
    } else {
        bebasHtml = '<tr><td colspan="7" class="text-center">Tidak ada data tagihan bebas</td></tr>';
    }
    $('#bebas_table_body').html(bebasHtml);
    
    // Event listener untuk tombol bayar bebas
    $('.bebas-payment-btn').off('click').on('click', function() {
        var studentId = $(this).data('student-id');
        var paymentId = $(this).data('payment-id');
        var posName = $(this).data('pos-name');
        var totalBill = $(this).data('total-bill');
        var totalPay = $(this).data('total-pay');
        var sisa = $(this).data('sisa');
        
        // Populate modal
        $('#bebas_payment_student_id').val(studentId);
        $('#bebas_payment_payment_id').val(paymentId);
        $('#bebas_payment_pos_name').text(posName);
        $('#bebas_payment_total_bill').text('Rp ' + Number(totalBill).toLocaleString('id-ID'));
        $('#bebas_payment_total_pay').text('Rp ' + Number(totalPay).toLocaleString('id-ID'));
        $('#bebas_payment_sisa').text('Rp ' + Number(sisa).toLocaleString('id-ID'));
        $('#bebas_payment_amount').attr('max', sisa);
        
        // Show modal
        $('#bebasPaymentModal').modal('show');
    });
}

// Fungsi untuk mencetak kuitansi
function printReceipt() {
    var studentId = $('#student_id').val();
    
    // Fallback: try to get student_id from the selected student dropdown
    if (!studentId) {
        studentId = $('#student_search').val();
    }
    
    // Ambil tanggal yang dipilih
    var selectedDate = $('#print_date').val();
    
    // Tanggal sudah dalam format Y-m-d, tidak perlu convert
    var formattedDate = selectedDate;
    
    // Generate payment number dengan timestamp
    var paymentNumber = 'PAY-' + new Date().getTime();
    
    // Validasi student_id
    if (!studentId) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'ID Siswa tidak ditemukan. Silakan pilih siswa terlebih dahulu.'
        });
        return;
    }
    
    // Debug logging
    console.log('=== PRINT RECEIPT DEBUG ===');
    console.log('Student ID:', studentId);
    console.log('Selected Date:', formattedDate);
    console.log('Hidden field value:', $('#student_id').val());
    console.log('Dropdown value:', $('#student_search').val());
    console.log('==========================');
    
    // Ambil data transaksi dari riwayat transaksi yang sesuai dengan tanggal yang dipilih
    var transactionData = [];
    var selectedDate = $('#print_date').val();
    
    $('#transaksi_terakhir_body tr').each(function() {
        var paymentText = $(this).find('td:first').text().trim();
        var amountText = $(this).find('td:nth-child(2)').text().trim();
        var dateText = $(this).find('td:nth-child(3)').text().trim(); // Kolom tanggal
        
        // Konversi format tanggal dari DD/MM/YY ke YYYY-MM-DD untuk perbandingan
        var transactionDate = '';
        if (dateText && dateText !== '') {
            var dateParts = dateText.split('/');
            if (dateParts.length === 3) {
                var day = dateParts[0];
                var month = dateParts[1];
                var year = '20' + dateParts[2]; // Asumsi tahun 20xx
                transactionDate = year + '-' + month + '-' + day;
            }
        }
        
        // Hanya ambil transaksi yang sesuai dengan tanggal yang dipilih
        if (paymentText && paymentText !== 'Tidak ada transaksi' && paymentText !== 'Memuat riwayat transaksi...' && transactionDate === selectedDate) {
            var amount = parseInt(amountText.replace(/[^\d]/g, '')) || 20000;
            
            // Hilangkan nomor referensi pembayaran (PAY-XXXXX)
            var cleanDescription = paymentText.replace(/\s*PAY-[A-Z0-9-]+\s*/g, '').trim();
            
            transactionData.push({
                description: cleanDescription,
                amount: amount
            });
        }
    });
    
    console.log('Selected date:', selectedDate);
    console.log('Filtered transaction data:', transactionData);
    
    console.log('Transaction data from history:', transactionData);
    
    // Jika tidak ada transaksi pada tanggal yang dipilih, kirim data kosong (tanpa default)
    var baseParams = {
        payment_number: paymentNumber,
        student_id: studentId,
        payment_type: 'bulanan',
        payment_date: formattedDate,
        payment_method: 'Tunai',
        _t: new Date().getTime()
    };

    if (transactionData.length > 0) {
        baseParams.amount = transactionData.reduce((sum, t) => sum + (t.amount || 0), 0);
        baseParams.description = 'Rincian terlampir';
    } else {
        baseParams.amount = 0;
        baseParams.description = '';
    }

    baseParams.transaction_data = JSON.stringify(transactionData);

    // Buka kuitansi di tab baru dengan data siswa dan tanggal yang dipilih
    var receiptUrl = '/generate-receipt?' + $.param(baseParams);
    
    console.log('Receipt URL:', receiptUrl);
    console.log('=== END DEBUG ===');
    window.open(receiptUrl, '_blank');
}

// Fungsi untuk menampilkan modal konfirmasi hapus transaksi
function showDeleteTransactionModal(transactionId, paymentNumber, amount, date, paymentName) {
    console.log('Showing delete transaction modal');
    console.log('Transaction ID:', transactionId);
    console.log('Payment Number:', paymentNumber);
    console.log('Amount:', amount);
    console.log('Date:', date);
    console.log('Payment Name:', paymentName);
    
    var detailText = '<strong>' + paymentName + '</strong><br>';
    detailText += 'No. Pembayaran: ' + paymentNumber + '<br>';
    detailText += 'Nominal: ' + amount + '<br>';
    detailText += 'Tanggal: ' + date;
    
    $('#delete-transaction-detail').html(detailText);
    $('#deleteTransactionModal').data('transaction-id', transactionId);
    
    // Show modal with debugging
    try {
        const modal = document.getElementById('deleteTransactionModal');
        if (modal) {
            console.log('Modal element found, showing...');
            const bootstrapModal = new bootstrap.Modal(modal);
            bootstrapModal.show();
            console.log('Modal shown successfully');
        } else {
            console.error('Modal element not found');
        }
    } catch (error) {
        console.error('Error showing modal:', error);
        // Fallback to jQuery
        $('#deleteTransactionModal').modal('show');
    }
}

// Fungsi untuk konfirmasi hapus transaksi
function confirmDeleteTransaction() {
    var transactionId = $('#deleteTransactionModal').data('transaction-id');
    var studentId = $('#student_id').val();
    
    // Fallback: try to get student_id from the selected student dropdown
    if (!studentId) {
        studentId = $('#student_search').val();
    }
    
    // Ensure transaction_id is an integer
    transactionId = parseInt(transactionId);
    
    console.log('Delete transaction - Transaction ID:', transactionId);
    console.log('Delete transaction - Student ID from hidden field:', $('#student_id').val());
    console.log('Delete transaction - Student ID from dropdown:', $('#student_search').val());
    console.log('Delete transaction - Final Student ID:', studentId);
    
    if (!transactionId || isNaN(transactionId)) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'ID Transaksi tidak valid'
        });
        return;
    }
    
    if (!studentId) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'ID Siswa tidak ditemukan. Silakan pilih siswa terlebih dahulu.'
        });
        return;
    }
    
    // Show loading state
    $('#deleteTransactionModal .btn-danger').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menghapus...');
    
    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    console.log('CSRF Token:', csrfToken);
    
    var requestData = {
        transaction_id: transactionId,
        student_id: studentId,
        _token: csrfToken
    };
    
    console.log('Sending delete request with data:', requestData);
    
    $.ajax({
        url: '/api/payment/delete-transaction',
        method: 'POST',
        data: requestData,
        success: function(response) {
            // Reset button state first
            $('#deleteTransactionModal .btn-danger').prop('disabled', false).html('<i class="fas fa-trash"></i> Hapus Transaksi');
            
            if (response.success) {
                // Show success toast dengan multiple fallback
                if (window.showVerificationToast) {
                    window.showVerificationToast('success', 'Berhasil!', 'Transaksi berhasil dihapus');
                } else if (window.fallbackToast) {
                    window.fallbackToast('success', 'Berhasil!', 'Transaksi berhasil dihapus');
                } else {
                    console.log('Toast system not available, using fallback');
                    alert('Transaksi berhasil dihapus!');
                }
                
                // Reload transaction history
                loadTransactionHistory(studentId);
                
                // Reload payment data to update status
                if (response.payment_type === 'bulanan') {
                    updateBulananDataOnly(studentId);
                } else if (response.payment_type === 'bebas') {
                    updateBebasDataOnly(studentId);
                }
                
                // Close modal after successful deletion - pastikan modal benar-benar tertutup
                try {
                    const modal = document.getElementById('deleteTransactionModal');
                    if (modal) {
                        const bootstrapModal = bootstrap.Modal.getInstance(modal);
                        if (bootstrapModal) {
                            bootstrapModal.hide();
                        } else {
                            $('#deleteTransactionModal').modal('hide');
                        }
                    }
                } catch (error) {
                    console.error('Error closing modal:', error);
                    $('#deleteTransactionModal').modal('hide');
                }
                
                // Force hide modal jika masih terlihat
                setTimeout(() => {
                    $('#deleteTransactionModal').modal('hide');
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                }, 100);
                
            } else {
                // Show error toast dengan multiple fallback
                if (window.showVerificationToast) {
                    window.showVerificationToast('error', 'Gagal!', response.message || 'Gagal menghapus transaksi');
                } else if (window.fallbackToast) {
                    window.fallbackToast('error', 'Gagal!', response.message || 'Gagal menghapus transaksi');
                } else {
                    console.log('Toast system not available, using fallback');
                    alert('Gagal menghapus transaksi: ' + (response.message || 'Terjadi kesalahan'));
                }
            }
        },
        error: function(xhr, status, error) {
            $('#deleteTransactionModal').modal('hide');
            $('#deleteTransactionModal .btn-danger').prop('disabled', false).html('<i class="fas fa-trash"></i> Hapus Transaksi');
            
            console.error('Error deleting transaction:', error);
            console.error('Status:', status);
            console.error('Response Text:', xhr.responseText);
            console.error('Response JSON:', xhr.responseJSON);
            console.error('Status Code:', xhr.status);
            
            var errorMessage = 'Terjadi kesalahan saat menghapus transaksi';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.responseText) {
                errorMessage = 'Server error: ' + xhr.responseText;
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMessage
            });
        }
    });
}

// Fungsi untuk menutup modal pembayaran bebas
function closeBebasPaymentModal() {
    // Tutup modal
    $('#bebasPaymentModal').modal('hide');
    
    // Reset form
    $('#bebasPaymentForm')[0].reset();
    $('#bebas_payment_amount').val('');
    $('#bebas_payment_desc').val('');
    
    // Reset radio button ke default (tunai)
    $('#bebas_cash').prop('checked', true);
    
    // Clear error states jika ada
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
    
    console.log('Modal pembayaran bebas ditutup dan form direset');
}

// Fungsi untuk memproses pembayaran bebas
function processBebasPayment() {
    var form = $('#bebasPaymentForm');
    var formData = new FormData(form[0]);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
    
    // Debug: Log form data
    console.log('Bebas payment form data:');
    for (var pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
    
    // Validasi client-side
    var amount = parseInt($('#bebas_payment_amount').val());
    var sisa = parseInt($('#bebas_payment_sisa').text().replace(/[^\d]/g, ''));
    var paymentMethod = $('input[name="payment_method"]:checked').val();
    
    console.log('Amount:', amount, 'Sisa:', sisa, 'Payment Method:', paymentMethod);
    
    if (amount < 1) {
        alert('Nominal pembayaran minimal Rp 1');
        return;
    }
    
    if (amount > sisa) {
        alert('Nominal pembayaran tidak boleh melebihi sisa tagihan');
        return;
    }

    // Jika pembayaran dengan tabungan, cek saldo tabungan
    if (paymentMethod === 'tabungan') {
        var studentId = $('#bebas_payment_student_id').val();
        
        // Cek saldo tabungan siswa
        $.getJSON('/api/students/' + studentId + '/tabungan', function(response) {
            if (response.success && response.tabungan) {
                var saldoTabungan = parseFloat(response.tabungan.saldo);
                
                if (saldoTabungan < amount) {
                    if (window.showVerificationToast) { window.showVerificationToast('error', '⚠️ Saldo Tidak Mencukupi', 'Saldo tabungan: Rp ' + saldoTabungan.toLocaleString('id-ID') + '\nNominal pembayaran: Rp ' + amount.toLocaleString('id-ID') + '\n\nSilakan top up tabungan terlebih dahulu atau pilih metode pembayaran lain.'); }
                    return;
                }
                
                // Jika saldo mencukupi, lanjutkan proses pembayaran
                processBebasPaymentAjax();
            } else {
                if (window.showVerificationToast) { window.showVerificationToast('error', 'Error', 'Siswa tidak memiliki rekening tabungan!'); }
                return;
            }
        });
    } else {
        // Jika pembayaran tunai, langsung proses
        processBebasPaymentAjax();
    }
    
}

// Fungsi untuk memproses pembayaran bebas via AJAX
function processBebasPaymentAjax() {
    var form = $('#bebasPaymentForm');
    var formData = new FormData(form[0]);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
    
    $.ajax({
        url: '/api/payment/bebas/process',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                // Update riwayat transaksi jika ada
                if (response.latest_transactions) {
                    updateTransactionHistory(response.latest_transactions);
                }
                
                if (window.showVerificationToast) { 
                    const successMessage = `Pembayaran Bebas Berhasil!<br><strong>${response.message}</strong>`;
                    window.showVerificationToast('success', 'Berhasil!', successMessage); 
                }
                
                $('#bebasPaymentModal').modal('hide');
                
                // Reset form
                $('#bebasPaymentForm')[0].reset();
                $('#bebas_payment_amount').val('');
                $('#bebas_payment_desc').val('');
                
                // Update hanya data bebas tanpa reload semua
                var studentId = $('#bebas_payment_student_id').val();
                if (studentId) {
                    updateBebasDataOnly(studentId);
                }
            } else {
                if (window.showVerificationToast) { window.showVerificationToast('error', 'Gagal!', response.message || 'Gagal memproses pembayaran bebas'); }
            }
        },
        error: function(xhr, status, error) {
            console.error('Bebas payment error:', error);
            console.error('Status:', status);
            console.error('Response:', xhr.responseText);
            console.error('Status code:', xhr.status);
            
            var errorMessage = 'Terjadi kesalahan saat memproses pembayaran bebas';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            if (window.showVerificationToast) { window.showVerificationToast('error', 'Error', errorMessage); }
        }
    });
}

// Event listener untuk tombol bayar bebas
$(document).on('click', '.bebas-payment-btn', function() {
    var studentId = $(this).data('student-id');
    var paymentId = $(this).data('payment-id');
    var posName = $(this).data('pos-name');
    var totalBill = $(this).data('total-bill');
    var totalPay = $(this).data('total-pay');
    var sisa = $(this).data('sisa');
    
    // Populate modal
    $('#bebas_payment_student_id').val(studentId);
    $('#bebas_payment_payment_id').val(paymentId);
    $('#bebas_payment_pos_name').text(posName);
    $('#bebas_payment_total_bill').text('Rp ' + Number(totalBill).toLocaleString('id-ID'));
    $('#bebas_payment_total_pay').text('Rp ' + Number(totalPay).toLocaleString('id-ID'));
    $('#bebas_payment_sisa').text('Rp ' + Number(sisa).toLocaleString('id-ID'));
    $('#bebas_payment_amount').attr('max', sisa);
    
    // Show modal
    $('#bebasPaymentModal').modal('show');
});

// Event listener untuk form pembayaran bebas
$('#bebasPaymentForm').on('submit', function(e) {
    e.preventDefault();
    processBebasPayment();
});

// Fungsi untuk memuat ulang data tagihan siswa
function loadStudentTagihan(studentId) {
    console.log('Reloading tagihan for student ID:', studentId);
    
    // Show loading state
    $('#payment-content').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><br>Memuat data tagihan...</div>');
    
    // Load student detail dan tagihan
    $.getJSON('/api/students/' + studentId + '/detail', function(studentResponse) {
        if (studentResponse.success) {
            console.log('Student detail loaded:', studentResponse.student);
            
            // Update informasi siswa
            $('#student_name').text(studentResponse.student.student_name);
            $('#student_nis').text(studentResponse.student.student_nis);
            $('#student_class').text(studentResponse.student.class_name);
            
            // Load tagihan
            $.getJSON('/api/students/' + studentId + '/tagihan', function(tagihanResponse) {
                if (tagihanResponse.success && tagihanResponse.tagihan) {
                    updateTagihanDisplay(tagihanResponse.tagihan, studentId);
                } else {
                    $('#payment-content').html('<div class="alert alert-danger">Gagal memuat data tagihan!</div>');
                }
            }).fail(function(xhr, status, error) {
                console.error('Error fetching tagihan:', error);
                $('#payment-content').html('<div class="alert alert-danger">Gagal mengambil data tagihan!</div>');
            });
        } else {
            console.log('Failed to load student detail');
            $('#payment-content').html('<div class="alert alert-danger">Gagal memuat data siswa!</div>');
        }
    });
}

// Fungsi untuk update hanya data bebas
function updateBebasDataOnly(studentId) {
    // Show loading state hanya untuk tabel bebas
    $('#bebas_table_body').html('<tr><td colspan="7" class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat data...</td></tr>');
    
    // Load hanya data tagihan bebas
    $.getJSON('/api/students/' + studentId + '/tagihan', function(tagihanResponse) {
        if (tagihanResponse.success && tagihanResponse.tagihan && tagihanResponse.tagihan.bebas) {
            updateBebasTableOnly(tagihanResponse.tagihan.bebas, studentId);
        } else {
            $('#bebas_table_body').html('<tr><td colspan="7" class="text-center text-danger">Gagal memuat data bebas</td></tr>');
        }
    }).fail(function(xhr, status, error) {
        console.error('Error fetching bebas data:', error);
        $('#bebas_table_body').html('<tr><td colspan="7" class="text-center text-danger">Gagal mengambil data bebas</td></tr>');
    });
}

// Fungsi untuk update hanya tabel bebas
function updateBebasTableOnly(bebasData, studentId) {
    
    var bebasHtml = '';
    if (bebasData && bebasData.length > 0) {
        bebasData.forEach(function(item, idx) {
            var sisa = (item.bebas_bill - item.bebas_total_pay);
            let formattedBill = item.bebas_bill ? 'Rp ' + Number(item.bebas_bill).toLocaleString('id-ID') : '-';
            let formattedTotalPay = item.bebas_total_pay ? 'Rp ' + Number(item.bebas_total_pay).toLocaleString('id-ID') : 'Rp 0';
            let formattedSisa = sisa > 0 ? 'Rp ' + Number(sisa).toLocaleString('id-ID') : 'Rp 0';
            let statusClass = sisa <= 0 ? 'lunas' : 'belum-lunas';
            let statusText = sisa <= 0 ? 'Lunas' : 'Belum Lunas';

            bebasHtml += '<tr>';
            bebasHtml += '<td>' + (idx+1) + '</td>';
            bebasHtml += '<td>' + item.pos_name + ' - ' + (item.period_name || 'Tahun Ajaran') + '</td>';
            bebasHtml += '<td>' + formattedBill + '</td>';
            bebasHtml += '<td>' + formattedTotalPay + '</td>';
            bebasHtml += '<td>' + formattedSisa + '</td>';
            bebasHtml += '<td><span class="badge bg-' + (sisa <= 0 ? 'success' : 'warning') + '">' + statusText + '</span></td>';
            bebasHtml += '<td>';
            if (sisa > 0) {
                bebasHtml += '<button class="btn btn-sm btn-primary bebas-payment-btn" data-student-id="' + studentId + '" data-payment-id="' + item.payment_id + '" data-pos-name="' + item.pos_name + '" data-total-bill="' + item.bebas_bill + '" data-total-pay="' + item.bebas_total_pay + '" data-sisa="' + sisa + '">Bayar</button>';
            } else {
                bebasHtml += '<span class="text-muted">Sudah Lunas</span>';
            }
            bebasHtml += '</td>';
            bebasHtml += '</tr>';
        });
    } else {
        bebasHtml = '<tr><td colspan="7" class="text-center">Tidak ada data tagihan bebas</td></tr>';
    }
    $('#bebas_table_body').html(bebasHtml);
    
    // Re-attach event listener untuk tombol bayar bebas
    $('.bebas-payment-btn').off('click').on('click', function() {
        var studentId = $(this).data('student-id');
        var paymentId = $(this).data('payment-id');
        var posName = $(this).data('pos-name');
        var totalBill = $(this).data('total-bill');
        var totalPay = $(this).data('total-pay');
        var sisa = $(this).data('sisa');
        
        // Reset form terlebih dahulu
        $('#bebasPaymentForm')[0].reset();
        $('#bebas_payment_amount').val('');
        $('#bebas_payment_desc').val('');
        
        // Populate modal
        $('#bebas_payment_student_id').val(studentId);
        $('#bebas_payment_payment_id').val(paymentId);
        $('#bebas_payment_pos_name').text(posName);
        $('#bebas_payment_total_bill').text('Rp ' + Number(totalBill).toLocaleString('id-ID'));
        $('#bebas_payment_total_pay').text('Rp ' + Number(totalPay).toLocaleString('id-ID'));
        $('#bebas_payment_sisa').text('Rp ' + Number(sisa).toLocaleString('id-ID'));
        $('#bebas_payment_amount').attr('max', sisa);
        
        // Show modal
        $('#bebasPaymentModal').modal('show');
    });

    // Jika multi mode aktif, terapkan checkbox pada elemen yang baru dirender
    if (typeof multiMode !== 'undefined' && multiMode) {
        enableMultiMode();
    }
}

// Fungsi untuk update hanya data bulanan
function updateBulananDataOnly(studentId) {
    // Show loading state hanya untuk tabel bulanan
    $('#bulanan_table_body').html('<tr><td colspan="14" class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat data...</td></tr>');
    
    // Load hanya data tagihan bulanan
    $.getJSON('/api/students/' + studentId + '/tagihan', function(tagihanResponse) {
        if (tagihanResponse.success && tagihanResponse.tagihan && tagihanResponse.tagihan.bulanan) {
            updateBulananTableOnly(tagihanResponse.tagihan.bulanan, studentId);
        } else {
            $('#bulanan_table_body').html('<tr><td colspan="14" class="text-center text-danger">Gagal memuat data bulanan</td></tr>');
        }
    }).fail(function(xhr, status, error) {
        console.error('Error fetching bulanan data:', error);
        $('#bulanan_table_body').html('<tr><td colspan="14" class="text-center text-danger">Gagal mengambil data bulanan</td></tr>');
    });
}

// Fungsi untuk update hanya tabel bulanan
function updateBulananTableOnly(bulananData, studentId) {
    var bulanList = ['Juli','Agustus','September','Oktober','November','Desember','Januari','Februari','Maret','April','Mei','Juni'];
    var bulananHtml = '';
    
    if (bulananData && bulananData.length > 0) {
        var grouped = {};
        bulananData.forEach(function(item, index) {
            
            if (!grouped[item.payment_id]) {
                grouped[item.payment_id] = {
                    nama: item.pos_name + ' - ' + (item.period_name || 'Tahun Ajaran'),
                    period_name: item.period_name,
                    bulan: {}
                };
            }
            grouped[item.payment_id].nama = item.pos_name + ' - ' + (item.period_name || 'Tahun Ajaran');
            grouped[item.payment_id].period_name = item.period_name;
            grouped[item.payment_id].bulan[item.month_month_id] = {
                bill: item.bulan_bill,
                status: item.bulan_status,
                date_pay: item.bulan_date_pay
            };
        });
        
        var idx = 1;
        for (var key in grouped) {
            var paymentName = grouped[key].nama;
            var posName = paymentName.split(' - ')[0];
            var periodName = grouped[key].period_name || 'Tahun Ajaran';
            
            bulananHtml += '<tr><td>' + (idx++) + '</td><td>' + posName + ' - ' + periodName + '</td>';
            for (var i = 1; i <= 12; i++) {
                var b = grouped[key].bulan[i];
                if (b) {
                    if (b.status == 1 && b.date_pay) {
                        // Format tanggal dengan moment.js
                        var formattedDate = moment(b.date_pay).format('DD/MM/YY');
                        bulananHtml += '<td class="sudah-bayar">' + formattedDate + '</td>';
                    } else {
                        let formattedBill = b.bill ? 'Rp ' + Number(b.bill).toLocaleString('id-ID') : '-';
                        var escapedPosName = posName.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                        bulananHtml += '<td class="belum-bayar clickable-payment" data-student-id="' + studentId + '" data-payment-id="' + key.split('_')[0] + '" data-pos-id="' + grouped[key].pos_id + '" data-month-id="' + i + '" data-bill="' + b.bill + '" data-pos-name="' + escapedPosName + '" data-month-name="' + bulanList[i-1] + '">' + formattedBill + '</td>';
                    }
                } else {
                    bulananHtml += '<td>-</td>';
                }
            }
            bulananHtml += '</tr>';
        }
    } else {
        bulananHtml = '<tr><td colspan="14" class="text-center">Tidak ada data tagihan bulanan</td></tr>';
    }
    
    $('#bulanan_table_body').html(bulananHtml);
    
    // Re-attach event listener untuk klik pada nominal pembayaran
    $('.clickable-payment').off('click').on('click', function() {
        var studentId = $(this).data('student-id');
        var paymentId = $(this).data('payment-id');
        var monthId = $(this).data('month-id');
        var bill = $(this).data('bill');
        var posName = $(this).data('pos-name');
        var monthName = $(this).data('month-name');
        
        // Tampilkan data di modal
        $('#modal-pos-name').text(posName);
        $('#modal-month-name').text(monthName);
        $('#modal-bill-amount').text('Rp ' + Number(bill).toLocaleString('id-ID'));
        
        // Simpan data untuk proses pembayaran
        $('#paymentModal').data('payment-data', {
            studentId: studentId,
            paymentId: paymentId,
            monthId: monthId,
            bill: bill,
            posName: posName,
            monthName: monthName
        });
        
        // Tampilkan modal
        $('#paymentModal').modal('show');
    });
}

// Fungsi untuk update tampilan tagihan
function updateTagihanDisplay(tagihan, studentId) {
    // Bulanan
    var bulanList = ['Juli','Agustus','September','Oktober','November','Desember','Januari','Februari','Maret','April','Mei','Juni'];
    var bulananHtml = '';
    if (tagihan.bulanan && tagihan.bulanan.length > 0) {
        // Grouping berdasarkan kombinasi payment_id dan pos_id
        var grouped = {};
        tagihan.bulanan.forEach(function(item) {
            // Buat key unik berdasarkan payment_id dan pos_id
            var key = item.payment_id + '_' + (item.pos_pos_id || 1);
            
            if (!grouped[key]) {
                grouped[key] = {
                    nama: item.pos_name + ' - ' + (item.period_name || 'Tahun Ajaran'),
                    period_name: item.period_name,
                    pos_id: item.pos_pos_id || 1,
                    bulan: {}
                };
            }
            
            grouped[key].bulan[item.month_month_id] = {
                bill: item.bulan_bill,
                status: item.bulan_status,
                date_pay: item.bulan_date_pay
            };
        });
        
        var idx = 1;
        for (var key in grouped) {
            var paymentName = grouped[key].nama;
            var posName = paymentName.split(' - ')[0];
            var periodName = grouped[key].period_name || 'Tahun Ajaran';
            
            bulananHtml += '<tr><td>' + (idx++) + '</td><td>' + posName + ' - ' + periodName + '</td>';
            for (var i = 1; i <= 12; i++) {
                var b = grouped[key].bulan[i];
                if (b) {
                    if (b.status == 1 && b.date_pay) {
                        // Format tanggal dengan moment.js
                        var formattedDate = moment(b.date_pay).format('DD/MM/YY');
                        bulananHtml += '<td class="sudah-bayar">' + formattedDate + '</td>';
                    } else {
                        let formattedBill = b.bill ? 'Rp ' + Number(b.bill).toLocaleString('id-ID') : '-';
                        var escapedPosName = posName.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                        bulananHtml += '<td class="belum-bayar clickable-payment" data-student-id="' + studentId + '" data-payment-id="' + key.split('_')[0] + '" data-pos-id="' + grouped[key].pos_id + '" data-month-id="' + i + '" data-bill="' + b.bill + '" data-pos-name="' + escapedPosName + '" data-month-name="' + bulanList[i-1] + '">' + formattedBill + '</td>';
                    }
                } else {
                    bulananHtml += '<td>-</td>';
                }
            }
            bulananHtml += '</tr>';
        }
    } else {
        bulananHtml = '<tr><td colspan="14" class="text-center">Tidak ada data tagihan bulanan</td></tr>';
    }
    $('#bulanan_table_body').html(bulananHtml);
    
    // Event listener untuk klik pada nominal pembayaran
    $('.clickable-payment').off('click').on('click', function() {
        var studentId = $(this).data('student-id');
        var paymentId = $(this).data('payment-id');
        var monthId = $(this).data('month-id');
        var bill = $(this).data('bill');
        var posName = $(this).data('pos-name');
        var monthName = $(this).data('month-name');
        
        // Tampilkan data di modal
        $('#modal-pos-name').text(posName);
        $('#modal-month-name').text(monthName);
        $('#modal-bill-amount').text('Rp ' + Number(bill).toLocaleString('id-ID'));
        
        // Simpan data untuk proses pembayaran
        $('#paymentModal').data('payment-data', {
            studentId: studentId,
            paymentId: paymentId,
            monthId: monthId,
            bill: bill,
            posName: posName,
            monthName: monthName
        });
        
        // Tampilkan modal
        $('#paymentModal').modal('show');
    });
    
    // Bebas
    var bebasHtml = '';
    if (tagihan.bebas && tagihan.bebas.length > 0) {
        tagihan.bebas.forEach(function(item, idx) {
            var sisa = (item.bebas_bill - item.bebas_total_pay);
            let formattedBill = item.bebas_bill ? 'Rp ' + Number(item.bebas_bill).toLocaleString('id-ID') : '-';
            let formattedTotalPay = item.bebas_total_pay ? 'Rp ' + Number(item.bebas_total_pay).toLocaleString('id-ID') : 'Rp 0';
            let formattedSisa = sisa > 0 ? 'Rp ' + Number(sisa).toLocaleString('id-ID') : 'Rp 0';
            let statusClass = sisa <= 0 ? 'lunas' : 'belum-lunas';
            let statusText = sisa <= 0 ? 'Lunas' : 'Belum Lunas';

            bebasHtml += '<tr>';
            bebasHtml += '<td>' + (idx+1) + '</td>';
            bebasHtml += '<td>' + item.pos_name + ' - ' + (item.period_name || 'Tahun Ajaran') + '</td>';
            bebasHtml += '<td>' + formattedBill + '</td>';
            bebasHtml += '<td>' + formattedTotalPay + '</td>';
            bebasHtml += '<td>' + formattedSisa + '</td>';
            bebasHtml += '<td><span class="badge bg-' + (sisa <= 0 ? 'success' : 'warning') + '">' + statusText + '</span></td>';
            bebasHtml += '<td>';
            if (sisa > 0) {
                bebasHtml += '<button class="btn btn-sm btn-primary bebas-payment-btn" data-student-id="' + studentId + '" data-payment-id="' + item.payment_id + '" data-pos-name="' + item.pos_name + '" data-total-bill="' + item.bebas_bill + '" data-total-pay="' + item.bebas_total_pay + '" data-sisa="' + sisa + '">Bayar</button>';
            } else {
                bebasHtml += '<span class="text-muted">Sudah Lunas</span>';
            }
            bebasHtml += '</td>';
            bebasHtml += '</tr>';
        });
    } else {
        bebasHtml = '<tr><td colspan="7" class="text-center">Tidak ada data tagihan bebas</td></tr>';
    }
    $('#bebas_table_body').html(bebasHtml);
    
    // Event listener untuk tombol bayar bebas
    $('.bebas-payment-btn').off('click').on('click', function() {
        var studentId = $(this).data('student-id');
        var paymentId = $(this).data('payment-id');
        var posName = $(this).data('pos-name');
        var totalBill = $(this).data('total-bill');
        var totalPay = $(this).data('total-pay');
        var sisa = $(this).data('sisa');
        
        // Populate modal
        $('#bebas_payment_student_id').val(studentId);
        $('#bebas_payment_payment_id').val(paymentId);
        $('#bebas_payment_pos_name').text(posName);
        $('#bebas_payment_total_bill').text('Rp ' + Number(totalBill).toLocaleString('id-ID'));
        $('#bebas_payment_total_pay').text('Rp ' + Number(totalPay).toLocaleString('id-ID'));
        $('#bebas_payment_sisa').text('Rp ' + Number(sisa).toLocaleString('id-ID'));
        $('#bebas_payment_amount').attr('max', sisa);
        
        // Show modal
        $('#bebasPaymentModal').modal('show');
    });

    // Jika multi mode aktif, terapkan checkbox pada elemen yang baru dirender
    if (typeof multiMode !== 'undefined' && multiMode) {
        enableMultiMode();
    }
}

// Fungsi untuk mencetak kuitansi
function printReceipt() {
    var studentId = $('#student_id').val();
    
    // Fallback: try to get student_id from the selected student dropdown
    if (!studentId) {
        studentId = $('#student_search').val();
    }
    
    // Ambil tanggal yang dipilih
    var selectedDate = $('#print_date').val();
    
    // Tanggal sudah dalam format Y-m-d, tidak perlu convert
    var formattedDate = selectedDate;
    
    // Generate payment number dengan timestamp
    var paymentNumber = 'PAY-' + new Date().getTime();
    
    // Validasi student_id
    if (!studentId) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'ID Siswa tidak ditemukan. Silakan pilih siswa terlebih dahulu.'
        });
        return;
    }
    
    // Debug logging
    console.log('=== PRINT RECEIPT DEBUG ===');
    console.log('Student ID:', studentId);
    console.log('Selected Date:', formattedDate);
    console.log('Hidden field value:', $('#student_id').val());
    console.log('Dropdown value:', $('#student_search').val());
    console.log('==========================');
    
    // Ambil data transaksi dari riwayat transaksi yang sesuai dengan tanggal yang dipilih
    var transactionData = [];
    var selectedDate = $('#print_date').val();
    
    $('#transaksi_terakhir_body tr').each(function() {
        var paymentText = $(this).find('td:first').text().trim();
        var amountText = $(this).find('td:nth-child(2)').text().trim();
        var dateText = $(this).find('td:nth-child(3)').text().trim(); // Kolom tanggal
        
        // Konversi format tanggal dari DD/MM/YY ke YYYY-MM-DD untuk perbandingan
        var transactionDate = '';
        if (dateText && dateText !== '') {
            var dateParts = dateText.split('/');
            if (dateParts.length === 3) {
                var day = dateParts[0];
                var month = dateParts[1];
                var year = '20' + dateParts[2]; // Asumsi tahun 20xx
                transactionDate = year + '-' + month + '-' + day;
            }
        }
        
        // Hanya ambil transaksi yang sesuai dengan tanggal yang dipilih
        if (paymentText && paymentText !== 'Tidak ada transaksi' && paymentText !== 'Memuat riwayat transaksi...' && transactionDate === selectedDate) {
            var amount = parseInt(amountText.replace(/[^\d]/g, '')) || 20000;
            
            // Hilangkan nomor referensi pembayaran (PAY-XXXXX)
            var cleanDescription = paymentText.replace(/\s*PAY-[A-Z0-9-]+\s*/g, '').trim();
            
            transactionData.push({
                description: cleanDescription,
                amount: amount
            });
        }
    });
    
    console.log('Selected date:', selectedDate);
    console.log('Filtered transaction data:', transactionData);
    
    console.log('Transaction data from history:', transactionData);
    
    // Buka kuitansi di tab baru dengan data siswa dan tanggal yang dipilih
    var receiptUrl = '/generate-receipt?' + $.param({
        payment_number: paymentNumber,
        student_id: studentId,
        payment_type: 'bulanan',
        amount: 20000,
        description: 'SPP - T.A 2025/2026',
        payment_date: formattedDate,
        payment_method: 'Tunai',
        transaction_data: JSON.stringify(transactionData),
        _t: new Date().getTime() // Force refresh cache
    });
    
    console.log('Receipt URL:', receiptUrl);
    console.log('=== END DEBUG ===');
    window.open(receiptUrl, '_blank');
}

// Fungsi untuk menampilkan modal konfirmasi hapus transaksi
function showDeleteTransactionModal(transactionId, paymentNumber, amount, date, paymentName) {
    console.log('Showing delete transaction modal');
    console.log('Transaction ID:', transactionId);
    console.log('Payment Number:', paymentNumber);
    console.log('Amount:', amount);
    console.log('Date:', date);
    console.log('Payment Name:', paymentName);
    
    var detailText = '<strong>' + paymentName + '</strong><br>';
    detailText += 'No. Pembayaran: ' + paymentNumber + '<br>';
    detailText += 'Nominal: ' + amount + '<br>';
    detailText += 'Tanggal: ' + date;
    
    $('#delete-transaction-detail').html(detailText);
    $('#deleteTransactionModal').data('transaction-id', transactionId);
    
    // Show modal with debugging
    try {
        const modal = document.getElementById('deleteTransactionModal');
        if (modal) {
            console.log('Modal element found, showing...');
            const bootstrapModal = new bootstrap.Modal(modal);
            bootstrapModal.show();
            console.log('Modal shown successfully');
        } else {
            console.error('Modal element not found');
        }
    } catch (error) {
        console.error('Error showing modal:', error);
        // Fallback to jQuery
        $('#deleteTransactionModal').modal('show');
    }
}

// Fungsi ini sudah didefinisikan di atas
</script>

<style>
.clickable-payment {
    cursor: pointer;
    transition: background-color 0.3s;
}

.clickable-payment:hover {
    background-color: #e3f2fd !important;
}

.sudah-bayar {
    background-color: #d4edda !important;
    color: #155724 !important;
    font-weight: normal;
}

.belum-bayar {
    background-color: #f8d7da !important;
    color: #721c24 !important;
}

.table-success {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.lunas {
    background-color: #d4edda !important;
    color: #155724 !important;
}

.belum-lunas {
    background-color: #fff3cd !important;
    color: #856404 !important;
}

.delete-transaction-btn {
    transition: all 0.3s ease;
    border-radius: 4px;
    padding: 4px 8px;
}

.delete-transaction-btn i {
    color: white !important;
}

.delete-transaction-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
}

.delete-transaction-btn:active {
    transform: scale(0.95);
}
</style>

<!-- Simple Toast System -->
<script>
// showToast function is now global from adminty.blade.php layout
// Global toast functions
window.showVerificationToast = window.showToast;
window.fallbackToast = window.showToast;

// Auto-refresh data setelah pembayaran online berhasil
function setupAutoRefresh() {
    // Refresh data setiap 30 detik untuk memastikan sinkronisasi
    // Dinonaktifkan sesuai permintaan user
    
    // Refresh data saat tab menjadi visible
    // Dinonaktifkan
    
    // Refresh data saat window focus
    // Dinonaktifkan
}

// Nonaktifkan auto-refresh: biarkan user refresh manual bila perlu
// Tetap jaga event change student untuk load data
$(document).ready(function() {
    // Default OFF; user bisa menekan tombol untuk ON
    
    $('#student_search').on('change', function() {
        var studentId = $(this).val();
        if (studentId) {
            updateBulananDataOnly(studentId);
            updateBebasDataOnly(studentId);
            loadTransactionHistory(studentId);
        }
    });
});

// Fungsi untuk manual refresh data bulanan
function refreshBulananData() {
    var currentStudentId = $('#student_search').val();
    console.log('Refresh bulanan clicked - Student ID:', currentStudentId);
    console.log('Student search element:', $('#student_search'));
    console.log('Student search value:', $('#student_search').val());
    
    // Coba cari student ID dari berbagai sumber
    if (!currentStudentId) {
        // Coba dari global variable jika ada
        if (typeof window.currentStudentId !== 'undefined') {
            currentStudentId = window.currentStudentId;
            console.log('Using global currentStudentId:', currentStudentId);
        }
        
        // Coba dari info siswa yang sudah ditampilkan
        var infoNis = $('#info_nis').text();
        if (infoNis && infoNis.trim() !== '') {
            console.log('Found NIS from info:', infoNis);
            // Gunakan NIS sebagai fallback
            currentStudentId = infoNis;
        }
    }
    
    if (currentStudentId && currentStudentId !== '') {
        console.log('Manual refresh data bulanan for student:', currentStudentId);
        
        // Debug: tampilkan data yang di-fetch
        $.getJSON('/api/students/' + currentStudentId + '/tagihan', function(tagihanResponse) {
            console.log('=== API RESPONSE DEBUG ===');
            console.log('Raw API response:', tagihanResponse);
            console.log('Response type:', typeof tagihanResponse);
            console.log('Response keys:', Object.keys(tagihanResponse));
            
            if (tagihanResponse.success && tagihanResponse.tagihan && tagihanResponse.tagihan.bulanan) {
                console.log('Bulanan data from API:', tagihanResponse.tagihan.bulanan);
                console.log('Bulanan data type:', typeof tagihanResponse.tagihan.bulanan);
                console.log('Bulanan data length:', tagihanResponse.tagihan.bulanan.length);
                
                // Log setiap item bulanan untuk debugging
                tagihanResponse.tagihan.bulanan.forEach(function(item, index) {
                    console.log('Bulanan item ' + index + ':', {
                        payment_id: item.payment_id,
                        month_id: item.month_month_id,
                        status: item.bulan_status,
                        date_pay: item.bulan_date_pay,
                        bill: item.bulan_bill,
                        pos_name: item.pos_name,
                        period_name: item.period_name
                    });
                    
                    // Debug field types
                    console.log('Item ' + index + ' field types:', {
                        'bulan_status type': typeof item.bulan_status,
                        'bulan_status value': item.bulan_status,
                        'bulan_date_pay type': typeof item.bulan_date_pay,
                        'bulan_date_pay value': item.bulan_date_pay
                    });
                });
                
                console.log('=== CALLING UPDATE BULANAN TABLE ONLY ===');
                updateBulananTableOnly(tagihanResponse.tagihan.bulanan, currentStudentId);
            } else {
                console.log('Failed to load bulanan data:', tagihanResponse);
                console.log('Success:', tagihanResponse.success);
                console.log('Tagihan exists:', !!tagihanResponse.tagihan);
                console.log('Bulanan exists:', !!(tagihanResponse.tagihan && tagihanResponse.tagihan.bulanan));
                $('#bulanan_table_body').html('<tr><td colspan="14" class="text-center text-danger">Gagal memuat data bulanan</td></tr>');
            }
        }).fail(function(xhr, status, error) {
            console.error('Error fetching bulanan data:', error);
            console.error('XHR status:', xhr.status);
            console.error('XHR response:', xhr.responseText);
            $('#bulanan_table_body').html('<tr><td colspan="14" class="text-center text-danger">Gagal mengambil data bulanan</td></tr>');
        });
        
        // Tampilkan toast notifikasi
        showToast('success', 'Refresh Data', 'Data tagihan bulanan berhasil diperbarui!');
        console.log('Bulanan data refreshed successfully');
    } else {
        showToast('warning', 'Peringatan', 'Pilih siswa terlebih dahulu!');
        console.log('No student selected for refresh bulanan');
        
        // Tampilkan debugging info
        console.log('Available student info:');
        console.log('- student_search value:', $('#student_search').val());
        console.log('- info_nis text:', $('#info_nis').text());
        console.log('- global currentStudentId:', window.currentStudentId);
    }
}

// Fungsi untuk manual refresh data bebas
function refreshBebasData() {
    var currentStudentId = $('#student_search').val();
    console.log('Refresh bebas clicked - Student ID:', currentStudentId);
    
    // Coba cari student ID dari berbagai sumber
    if (!currentStudentId) {
        // Coba dari global variable jika ada
        if (typeof window.currentStudentId !== 'undefined') {
            currentStudentId = window.currentStudentId;
            console.log('Using global currentStudentId:', currentStudentId);
        }
        
        // Coba dari info siswa yang sudah ditampilkan
        var infoNis = $('#info_nis').text();
        if (infoNis && infoNis.trim() !== '') {
            console.log('Found NIS from info:', infoNis);
            // Gunakan NIS sebagai fallback
            currentStudentId = infoNis;
        }
    }
    
    if (currentStudentId && currentStudentId !== '') {
        console.log('Manual refresh data bebas for student:', currentStudentId);
        // Refresh data bebas
        $.getJSON('/api/students/' + currentStudentId + '/tagihan', function(tagihanResponse) {
            if (tagihanResponse.success && tagihanResponse.tagihan && tagihanResponse.tagihan.bebas) {
                updateBebasTableOnly(tagihanResponse.tagihan.bebas, currentStudentId);
                showToast('success', 'Refresh Data', 'Data tagihan bebas berhasil diperbarui!');
                console.log('Bebas data refreshed successfully');
            } else {
                showToast('error', 'Error', 'Gagal memuat data tagihan bebas!');
                console.log('Failed to load bebas data:', tagihanResponse);
            }
        }).fail(function(xhr, status, error) {
            console.error('Error fetching bebas data:', error);
            showToast('error', 'Error', 'Gagal mengambil data tagihan bebas!');
        });
    } else {
        showToast('warning', 'Peringatan', 'Pilih siswa terlebih dahulu!');
        console.log('No student selected for refresh bebas');
        
        // Tampilkan debugging info
        console.log('Available student info:');
        console.log('- student_search value:', $('#student_search').val());
        console.log('- info_nis text:', $('#info_nis').text());
        console.log('- global currentStudentId:', window.currentStudentId);
    }
}


</script>
@endsection 