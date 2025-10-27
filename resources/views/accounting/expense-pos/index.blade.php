@extends('layouts.coreui')

@section('title', 'Transaksi Pengeluaran')
@section('content-header', 'Transaksi Pengeluaran')

@push('meta')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@push('head')
<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@push('scripts')
<script>
// Data untuk dropdown yang akan digunakan JavaScript
const receiptPosData = @json($receiptPos ?? []);
const expensePosData = @json($expensePos ?? []);
const paymentMethodsData = @json($paymentMethods ?? []);


</script>
@endpush

@section('content')


<div class="container-fluid px-4">
    <div class="row">
        <div class="col-12">
            <!-- Header dan Filter -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-gradient-primary text-white border-0">
                    <h4 class="mb-0">
                        <i class="fa fa-money-bill-wave me-2"></i>Transaksi Pengeluaran
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Filter Bar -->
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label for="start_date" class="form-label fw-bold text-primary">Filter Periode</label>
                            <input type="date" class="form-control border-primary" id="start_date" name="start_date" value="{{ $startDate ?? date('Y-m-d', strtotime('-3 months')) }}">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label fw-bold text-primary">&nbsp;</label>
                            <input type="date" class="form-control border-primary" id="end_date" name="end_date" value="{{ $endDate ?? date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-primary me-2 shadow-sm" onclick="filterPeriode()">
                                <i class="fa fa-filter me-1"></i>Filter Periode [F5]
                            </button>
                            
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content - Two Panels -->
            <div class="row">
                <!-- Left Panel - Transaction List -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-gradient-danger text-white border-0">
                            <h5 class="mb-0">
                                <i class="fa fa-list me-2"></i>Daftar Transaksi Pengeluaran
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light-danger">
                                        <tr>
                                            <th class="border-0 text-center" style="width: 8%;">NO.</th>
                                            <th class="border-0" style="width: 15%;">TANGGAL</th>
                                            <th class="border-0" style="width: 20%;">NO. TRANSAKSI</th>
                                            <th class="border-0" style="width: 35%;">KETERANGAN</th>
                                            <th class="border-0 text-end" style="width: 22%;">JUMLAH<br>PENGELUARAN</th>
                                        </tr>
                                    </thead>
                                    <tbody id="expenseTableBody">
                                        <!-- Data akan di-load melalui JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Panel - Transaction Details -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-gradient-success text-white border-0">
                            <h5 class="mb-0">
                                <i class="fa fa-info-circle me-2"></i>Detail Transaksi
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Transaction Details -->
                            <div class="mb-4">
                                <div class="row mb-2">
                                    <div class="col-4"><strong class="text-dark">Tgl/No Transaksi:</strong></div>
                                    <div class="col-8" id="detailTglNo">-</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong class="text-dark">Operator / Petugas:</strong></div>
                                    <div class="col-8" id="detailOperator">-</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong class="text-dark">Sumber Dana Milik Tahun:</strong></div>
                                    <div class="col-8" id="detailSumberDana">-</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong class="text-dark">Pengeluaran Untuk Tahun:</strong></div>
                                    <div class="col-8" id="detailTahunPengeluaran">-</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong class="text-dark">Cara Transaksi:</strong></div>
                                    <div class="col-8" id="detailCaraTransaksi">-</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong class="text-dark">Keterangan Transaksi:</strong></div>
                                    <div class="col-8" id="detailKeterangan">-</div>
                                </div>
                            </div>

                            <!-- Line Items Table -->
                            <div class="mb-3">
                                <h6 class="mb-2 text-primary fw-bold">
                                    <i class="fa fa-table me-1"></i>Detail Item Pengeluaran:
                                </h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="bg-gradient-warning text-dark">
                                            <tr>
                                                <th style="width: 10%;">NO</th>
                                                <th style="width: 25%;">POS PENERIMAAN</th>
                                                <th style="width: 25%;">POS PENGELUARAN</th>
                                                <th style="width: 25%;">KETERANGAN ITEM</th>
                                                <th style="width: 15%;">JUMLAH</th>
                                            </tr>
                                        </thead>
                                        <tbody id="detailItemTableBody">
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">Pilih transaksi untuk melihat detail</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-4">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-success me-2 shadow-sm" onclick="tambahTransaksi()">
                        <i class="fa fa-plus me-1"></i>Tambah [F2]
                    </button>
                    <button type="button" class="btn btn-primary me-2 shadow-sm" onclick="tambahCopy()">
                        <i class="fa fa-copy me-1"></i>Tambah + Copy [F8]
                    </button>
                    <button type="button" class="btn btn-warning me-2 shadow-sm" onclick="ubahTransaksi()">
                        <i class="fa fa-edit me-1"></i>Ubah [F3]
                    </button>
                    <button type="button" class="btn btn-danger me-2 shadow-sm" onclick="hapusTransaksi()">
                        <i class="fa fa-trash me-1"></i>Hapus [F4]
                    </button>
                    <button type="button" class="btn btn-info shadow-sm" onclick="cetakBukti()">
                        <i class="fa fa-print me-1"></i>Cetak Bukti [F6]
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <div id="liveToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center">
                <div class="toast-icon me-3">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="toast-message flex-grow-1">
                    <div class="toast-title fw-bold"></div>
                    <div class="toast-text"></div>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- Modal Tambah Transaksi Pengeluaran -->
<div class="modal fade" id="tambahTransaksiModal" tabindex="-1" aria-labelledby="tambahTransaksiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-gradient-dark text-white">
                <h5 class="modal-title" id="tambahTransaksiModalLabel">
                    <i class="fa fa-plus me-2"></i>Transaksi Pengeluaran
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTambahTransaksi">
                @csrf
                <div class="modal-body">
                    <!-- Transaction Details Section -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="tanggal_pengeluaran" class="form-label fw-bold text-dark">Tanggal Pengeluaran <span class="text-danger">*</span></label>
                                <input type="date" class="form-control border-secondary" id="tanggal_pengeluaran" name="tanggal_pengeluaran" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="no_transaksi" class="form-label fw-bold text-dark">No. Transaksi <span class="text-danger">*</span></label>
                                <input type="text" class="form-control border-secondary" id="no_transaksi" name="no_transaksi" placeholder="EXP20250823001" required readonly>
                                <small class="text-muted">Nomor transaksi akan di-generate otomatis</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="tahun_ajaran" class="form-label fw-bold text-dark">Tahun Ajaran <span class="text-danger">*</span></label>
                                <select class="form-select border-secondary" id="tahun_ajaran" name="tahun_ajaran" required>
                                    <option value="">Pilih Tahun</option>
                                    <option value="2024/2025">2024 / 2025</option>
                                    <option value="2025/2026">2025 / 2026</option>
                                    <option value="2026/2027">2026 / 2027</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dibayar_ke" class="form-label fw-bold text-dark">Dibayar Ke <span class="text-danger">*</span></label>
                                <input type="text" class="form-control border-secondary" id="dibayar_ke" name="dibayar_ke" placeholder="Nama penerima pembayaran" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="metode_pembayaran_id" class="form-label fw-bold text-dark">Metode Pembayaran <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select class="form-select border-secondary" id="metode_pembayaran_id" name="metode_pembayaran_id" required>
                                        <option value="">Pilih Metode</option>
                                        @foreach($paymentMethods ?? [] as $method)
                                            <option value="{{ $method->id }}">{{ $method->nama_metode }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-outline-success" onclick="refreshMetodePembayaran()">
                                        <i class="fa fa-refresh"></i>
                                    </button>
                                    <button type="button" class="btn btn-warning" onclick="setDefaultMetode()">
                                        <i class="fa fa-heart text-danger"></i> Set default
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="kas_id" class="form-label fw-bold text-dark">Kas <span class="text-danger">*</span></label>
                                <select class="form-select border-secondary" id="kas_id" name="kas_id" required>
                                    <option value="">Pilih Kas</option>
                                    @foreach($kasList ?? [] as $kas)
                                        <option value="{{ $kas->id }}">{{ $kas->nama_kas }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="keterangan_transaksi" class="form-label fw-bold text-dark">Keterangan Transaksi</label>
                                <textarea class="form-control border-secondary" id="keterangan_transaksi" name="keterangan_transaksi" rows="3" placeholder="Masukkan keterangan transaksi pengeluaran"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Expense Details Table Section -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-3">Rincian Pengeluaran :</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="rincianPengeluaranTable">
                                <thead class="bg-light-dark">
                                    <tr>
                                        <th style="width: 8%;" class="text-center">NO</th>
                                        <th style="width: 25%;">POS SUMBER DANA</th>
                                        <th style="width: 25%;">POS PENGELUARAN</th>
                                        <th style="width: 25%;">KETERANGAN ITEM</th>
                                        <th style="width: 17%;">JUMLAH</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-center">1</td>
                                        <td>
                                            <select class="form-select form-select-sm" name="pos_sumber_dana[]" required>
                                                <option value="">Pilih Pos</option>
                                                @foreach($receiptPos ?? [] as $pos)
                                                    @if(isset($pos->pos_id) && isset($pos->pos_name))
                                                        <option value="{{ $pos->pos_id }}">{{ $pos->pos_name }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-select form-select-sm" name="pos_pengeluaran[]" required>
                                                <option value="">Pilih Pos</option>
                                                @foreach($expensePos ?? [] as $pos)
                                                    @if(isset($pos->pos_id) && isset($pos->pos_name))
                                                        <option value="{{ $pos->pos_id }}">{{ $pos->pos_name }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm" name="keterangan_item[]" placeholder="Keterangan item" required>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm" name="jumlah_pengeluaran[]" placeholder="0" min="0" step="1" required onchange="hitungTotalPengeluaran()">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Table Action Buttons -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-secondary me-2" onclick="moveRowUp()">
                                    <i class="fa fa-arrow-up"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary me-2" onclick="moveRowDown()">
                                    <i class="fa fa-arrow-down"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger me-2" onclick="hapusBarisPengeluaran()">
                                    <i class="fa fa-trash me-1"></i>Hapus Baris
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="tambahBarisRincian()">
                                    <i class="fa fa-plus me-1"></i>Tambah Baris
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info me-2" onclick="buatPosPengeluaranBaru()">
                                    <i class="fa fa-plus me-1"></i>Buat Pos Pengeluaran Baru
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-warning" onclick="kelolaPosPengeluaran()">
                                    <i class="fa fa-cog me-1"></i>Kelola Pos Pengeluaran
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Section -->
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <small class="text-danger">* Wajib diisi</small>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="mb-2">
                                <strong class="text-primary">Total Rp <span id="totalPengeluaran">0</span></strong>
                            </div>
                            <div>
                                <small class="text-muted"><span id="itemCount">0</span> item</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                        <i class="fa fa-times me-2"></i>Batal [Esc]
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-check me-2"></i>Simpan [F5]
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Transaksi Pengeluaran -->
<div class="modal fade" id="editTransaksiModal" tabindex="-1" aria-labelledby="editTransaksiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-gradient-warning text-dark">
                <h5 class="modal-title" id="editTransaksiModalLabel">
                    <i class="fa fa-edit me-2"></i>Edit Transaksi Pengeluaran
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditTransaksi">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_transaksi_id" name="transaksi_id">
                <div class="modal-body">
                    <!-- Transaction Details Section -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="edit_tanggal_pengeluaran" class="form-label fw-bold text-warning">Tanggal Pengeluaran <span class="text-danger">*</span></label>
                                <input type="date" class="form-control border-warning" id="edit_tanggal_pengeluaran" name="tanggal_pengeluaran" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="edit_no_transaksi" class="form-label fw-bold text-warning">No. Transaksi <span class="text-danger">*</span></label>
                                <input type="text" class="form-control border-warning" id="edit_no_transaksi" name="no_transaksi" placeholder="EXP-001" required readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="edit_sumber_dana_tahun" class="form-label fw-bold text-warning">Sumber Dana Milik Tahun <span class="text-danger">*</span></label>
                                <select class="form-select border-warning" id="edit_sumber_dana_tahun" name="sumber_dana_tahun" required>
                                    <option value="">Pilih Tahun</option>
                                    <option value="2024/2025">2024 / 2025</option>
                                    <option value="2025/2026">2025 / 2026</option>
                                    <option value="2026/2027">2026 / 2027</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_pengeluaran_tahun" class="form-label fw-bold text-warning">Pengeluaran Untuk Tahun <span class="text-danger">*</span></label>
                                <select class="form-select border-warning" id="edit_pengeluaran_tahun" name="pengeluaran_tahun" required>
                                    <option value="">Pilih Tahun</option>
                                    <option value="2024/2025">2024 / 2025</option>
                                    <option value="2025/2026">2025 / 2026</option>
                                    <option value="2026/2027">2026 / 2027</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_metode_pembayaran" class="form-label fw-bold text-warning">Metode Pembayaran <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select class="form-select border-warning" id="edit_metode_pembayaran" name="metode_pembayaran" required>
                                        <option value="">Pilih Metode</option>
                                        @foreach($paymentMethods ?? [] as $method)
                                            <option value="{{ $method->id }}">{{ $method->nama_metode }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-outline-warning" onclick="refreshMetodePembayaran()">
                                        <i class="fa fa-refresh"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_dibayar_ke" class="form-label fw-bold text-warning">Dibayar Ke <span class="text-danger">*</span></label>
                                <input type="text" class="form-control border-warning" id="edit_dibayar_ke" name="dibayar_ke" placeholder="Nama penerima pembayaran" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_kas_id" class="form-label fw-bold text-warning">Kas <span class="text-danger">*</span></label>
                                <select class="form-select border-warning" id="edit_kas_id" name="kas_id" required>
                                    <option value="">Pilih Kas</option>
                                    @foreach($kasList ?? [] as $kas)
                                        <option value="{{ $kas->id }}">{{ $kas->nama_kas }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="edit_keterangan_transaksi" class="form-label fw-bold text-warning">Keterangan Transaksi</label>
                        <textarea class="form-control border-warning" id="edit_keterangan_transaksi" name="keterangan_transaksi" rows="3" placeholder="Masukkan keterangan transaksi pengeluaran"></textarea>
                    </div>

                    <!-- Expense Details Table Section -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-warning mb-3">Rincian Pengeluaran :</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="editRincianPengeluaranTable">
                                <thead class="bg-light-warning">
                                    <tr>
                                        <th style="width: 8%;" class="text-center">NO</th>
                                        <th style="width: 25%;">POS SUMBER DANA</th>
                                        <th style="width: 25%;">POS PENGELUARAN</th>
                                        <th style="width: 25%;">KETERANGAN ITEM</th>
                                        <th style="width: 17%;">JUMLAH</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data akan di-populate melalui JavaScript -->
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Table Action Buttons -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-secondary me-2" onclick="moveRowUpEdit()">
                                    <i class="fa fa-arrow-up"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary me-2" onclick="moveRowDownEdit()">
                                    <i class="fa fa-arrow-down"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger me-2" onclick="hapusBarisPengeluaranEdit()">
                                    <i class="fa fa-trash me-1"></i>Hapus Baris
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="tambahBarisRincianEdit()">
                                    <i class="fa fa-plus me-1"></i>Tambah Baris
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info me-2" onclick="buatPosPengeluaranBaru()">
                                    <i class="fa fa-plus me-1"></i>Buat Pos Pengeluaran Baru
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-warning" onclick="kelolaPosPengeluaran()">
                                    <i class="fa fa-cog me-1"></i>Kelola Pos Pengeluaran
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Section -->
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <small class="text-danger">* Wajib diisi</small>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="mb-2">
                                <strong class="text-primary">Total Rp <span id="editTotalPengeluaran">0</span></strong>
                            </div>
                            <div>
                                <small class="text-muted"><span id="editItemCount">0</span> item</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                        <i class="fa fa-times me-2"></i>Batal [Esc]
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fa fa-check me-2"></i>Update [F5]
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Buat Pos Pengeluaran Baru -->
<div class="modal fade" id="buatPosPengeluaranBaruModal" tabindex="-1" aria-labelledby="buatPosPengeluaranBaruModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title" id="buatPosPengeluaranBaruModalLabel">
                    <i class="fa fa-plus me-2"></i>Buat Pos Pengeluaran Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formBuatPosPengeluaranBaru">
                    <div class="mb-3">
                        <label class="form-label">Nama Pos <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_pos_pengeluaran_baru" name="nama_pos_pengeluaran_baru" placeholder="Nama pos pengeluaran" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" id="keterangan_pos_pengeluaran_baru" name="keterangan_pos_pengeluaran_baru" rows="3" placeholder="Keterangan pos pengeluaran"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status_pos_pengeluaran_baru" id="status_pengeluaran_on" value="1" checked>
                            <label class="form-check-label" for="status_pengeluaran_on">Aktif</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status_pos_pengeluaran_baru" id="status_pengeluaran_off" value="0">
                            <label class="form-check-label" for="status_pengeluaran_off">Non Aktif</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div class="text-start">
                    <small class="text-primary">* Wajib diisi</small>
                </div>
                <div>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                        <i class="fa fa-times me-2"></i>Tutup [Esc]
                    </button>
                    <button type="button" class="btn btn-success" onclick="simpanPosPengeluaranBaru()">
                        <i class="fa fa-check me-2"></i>Simpan [F5]
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Kelola Pos Pengeluaran -->
<div class="modal fade" id="kelolaPosPengeluaranModal" tabindex="-1" aria-labelledby="kelolaPosPengeluaranModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-info text-white">
                <h5 class="modal-title" id="kelolaPosPengeluaranModalLabel">
                    <i class="fa fa-cog me-2"></i>Kelola Pos Pengeluaran
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Daftar Pos Pengeluaran -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Daftar Pos Pengeluaran</h6>
                        <button type="button" class="btn btn-sm btn-primary" onclick="tambahPosPengeluaranDariKelola()">
                            <i class="fa fa-plus me-1"></i>Tambah Baru
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover" id="tabelPosPengeluaran">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Pos</th>
                                    <th>Keterangan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data akan di-load melalui JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fa fa-times me-2"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Pos Pengeluaran -->
<div class="modal fade" id="editPosPengeluaranModal" tabindex="-1" aria-labelledby="editPosPengeluaranModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-gradient-warning text-dark">
                <h5 class="modal-title" id="editPosPengeluaranModalLabel">
                    <i class="fa fa-edit me-2"></i>Edit Pos Pengeluaran
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditPosPengeluaran">
                    <input type="hidden" id="edit_pos_id" name="pos_id">
                    <div class="mb-3">
                        <label for="edit_nama_pos" class="form-label">Nama Pos <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_nama_pos" name="nama_pos" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_keterangan_pos" class="form-label">Keterangan</label>
                        <textarea class="form-control" id="edit_keterangan_pos" name="keterangan_pos" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="edit_status_pos" id="edit_status_aktif" value="1">
                            <label class="form-check-label" for="edit_status_aktif">Aktif</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="edit_status_pos" id="edit_status_nonaktif" value="0">
                            <label class="form-check-label" for="edit_status_nonaktif">Non Aktif</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fa fa-times me-2"></i>Batal
                </button>
                <button type="button" class="btn btn-warning" onclick="updatePosPengeluaran()">
                    <i class="fa fa-save me-2"></i>Update
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Data transaksi dari controller
const transactionsData = @json($transactions ?? []);

// Inisialisasi halaman
document.addEventListener('DOMContentLoaded', function() {
    // Set tanggal default dari controller
    document.getElementById('start_date').value = '{{ $startDate ?? date("Y-m-d", strtotime("-3 months")) }}';
    document.getElementById('end_date').value = '{{ $endDate ?? date("Y-m-d") }}';
    
    // Load data awal
    loadExpenseData();
    
    // Test authentication
    
});

// Filter periode
function filterPeriode() {
    loadExpenseData();
}

// Load data transaksi pengeluaran dari server
function loadExpenseData() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    // Show loading state
    const tbody = document.getElementById('expenseTableBody');
    tbody.innerHTML = `
        <tr>
            <td colspan="5" class="text-center py-4 text-muted">
                <i class="fa fa-spinner fa-spin fa-2x mb-2 text-primary"></i>
                <br>Memuat data...
            </td>
        </tr>
    `;
    
    // Fetch data dari server dengan filter periode
    fetch(`{{ route('manage.accounting.expense-transactions.index') }}?start_date=${startDate}&end_date=${endDate}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update global data
            window.transactionsData = data.transactions || [];
            // Render tabel dengan data baru
            renderExpenseTable(window.transactionsData);
        } else {
            console.error('Error loading data:', data.message);
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-4 text-danger">
                        <i class="fa fa-exclamation-triangle fa-2x mb-2"></i>
                        <br>Gagal memuat data: ${data.message}
                    </td>
                </tr>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-4 text-danger">
                <i class="fa fa-exclamation-triangle fa-2x mb-2"></i>
                <br>Gagal memuat data dari server
            </td>
        </tr>
        `;
    });
}

// Render tabel transaksi
function renderExpenseTable(data) {
    const tbody = document.getElementById('expenseTableBody');
    
    if (data.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-4 text-muted">
                    <i class="fa fa-inbox fa-2x mb-2 text-danger"></i>
                    <br>Tidak ada data transaksi pengeluaran
                </td>
            </tr>
        `;
        return;
    }
    
    let html = '';
    data.forEach((item, index) => {
        html += `
            <tr onclick="selectRow(${item.id})" style="cursor: pointer;">
                <td class="text-center text-primary">${index + 1}</td>
                <td class="text-dark">${formatDate(item.tanggal)}</td>
                <td class="text-primary">${item.no_transaksi}</td>
                <td class="text-dark">${item.keterangan || '-'}</td>
                <td class="text-end text-danger">Rp ${parseFloat(item.jumlah_pengeluaran).toLocaleString('id-ID')}</td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

// Format tanggal
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

// Select row
function selectRow(id) {
    // Remove previous selection
    document.querySelectorAll('#expenseTableBody tr').forEach(tr => {
        tr.classList.remove('table-active');
    });
    
    // Add selection to clicked row
    event.currentTarget.classList.add('table-active');
    
    // Store selected ID
    window.selectedExpenseId = id;
    
    // Load transaction details
    loadTransactionDetails(id);
}

// Load transaction details
function loadTransactionDetails(id) {
    // Fetch transaction details from server
    fetch(`{{ route('manage.accounting.expense-transactions.details', ':id') }}`.replace(':id', id), {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const transaction = data.transaction;
            const details = data.details;
            
            // Update detail fields
            document.getElementById('detailTglNo').textContent = `${formatDate(transaction.tanggal_pengeluaran)} / ${transaction.no_transaksi}`;
            document.getElementById('detailOperator').textContent = transaction.operator || 'Admin';
            document.getElementById('detailSumberDana').textContent = 'Kas Sekolah';
            document.getElementById('detailTahunPengeluaran').textContent = new Date(transaction.tanggal_pengeluaran).getFullYear();
            document.getElementById('detailCaraTransaksi').textContent = 'Tunai';
            document.getElementById('detailKeterangan').textContent = transaction.keterangan_transaksi || '-';
            
            // Update detail items table
            const tbody = document.getElementById('detailItemTableBody');
            if (details && details.length > 0) {
                let html = '';
                details.forEach((detail, index) => {
                    html += `
                        <tr class="table-warning">
                            <td class="text-center">${index + 1}</td>
                            <td class="text-primary">${detail.pos_sumber_dana_name || '-'}</td>
                            <td class="text-primary">${detail.pos_pengeluaran_name || '-'}</td>
                            <td class="text-dark">${detail.keterangan_item || '-'}</td>
                            <td class="text-end text-danger">Rp ${parseFloat(detail.jumlah).toLocaleString('id-ID')}</td>
                        </tr>
                    `;
                });
                tbody.innerHTML = html;
            } else {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center text-muted">Tidak ada detail items</td>
                    </tr>
                `;
            }
        } else {
            console.error('Error loading transaction details:', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Tambah transaksi baru
function tambahTransaksi() {
    
    
    // Reset form
    document.getElementById('formTambahTransaksi').reset();
    
    // Reset tabel rincian
    resetRincianTable();
    
    // Set tanggal default
    document.getElementById('tanggal_pengeluaran').value = new Date().toISOString().split('T')[0];
    
    // Show modal dulu
    try {
        const modalElement = document.getElementById('tambahTransaksiModal');
        
        if (!modalElement) {
            console.error('Modal element not found!');
            return;
        }
        
        if (typeof bootstrap === 'undefined') {
            console.error('Bootstrap is not loaded!');
            return;
        }
        
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
        
        // Generate nomor transaksi otomatis setelah modal terbuka
        setTimeout(() => {
            generateNomorTransaksi();
        }, 100);
        
    } catch (error) {
        console.error('Error showing modal:', error);
    }
}

// Generate nomor transaksi otomatis yang unik
function generateNomorTransaksi() {
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    
    // Format: EXPYYYYMMDDXXX (tanpa tanda -)
    const baseNo = `EXP${year}${month}${day}`;
    
    // Generate timestamp untuk memastikan unik
    const timestamp = Date.now();
    const uniqueId = timestamp.toString().slice(-3); // Ambil 3 digit terakhir dari timestamp
    
    // Format: EXPYYYYMMDDXXX
    const nextNumber = `${baseNo}${uniqueId}`;
    

    document.getElementById('no_transaksi').value = nextNumber;
    
    // Optional: Cek dari server untuk nomor yang lebih akurat
    // fetchNextNumberFromServer(baseNo);
}

// Function untuk fetch nomor dari server (opsional)
function fetchNextNumberFromServer(baseNo) {
    fetch('{{ route("manage.accounting.expense-transactions.get-next-number") }}', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('no_transaksi').value = data.next_number;
        }
    })
    .catch(error => {
        console.error('Error getting next number from server:', error);
    });
}

// Reset tabel rincian pengeluaran
function resetRincianTable() {
    
    const tbody = document.querySelector('#rincianPengeluaranTable tbody');
    tbody.innerHTML = `
        <tr>
            <td class="text-center">1</td>
            <td>
                <select class="form-select form-select-sm" name="pos_sumber_dana[]" required>
                    <option value="">Pilih Pos</option>
                    ${receiptPosData.map(pos => `<option value="${pos.pos_id}">${pos.pos_name}</option>`).join('')}
                </select>
            </td>
            <td>
                <select class="form-select form-select-sm" name="pos_pengeluaran[]" required>
                    <option value="">Pilih Pos</option>
                    ${expensePosData.map(pos => `<option value="${pos.pos_id}">${pos.pos_name}</option>`).join('')}
                </select>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="keterangan_item[]" placeholder="Keterangan item" required>
            </td>
            <td>
                <input type="number" class="form-control form-select-sm" name="jumlah_pengeluaran[]" placeholder="0" min="0" step="1" required onchange="hitungTotalPengeluaran()">
            </td>
        </tr>
    `;
    
    // Reset total dan item count
    document.getElementById('totalPengeluaran').textContent = '0';
    document.getElementById('itemCount').textContent = '0';
    
    // Jangan reset field no_transaksi karena sudah di-generate otomatis
    // document.getElementById('no_transaksi').value = '';
}

// Tambah baris baru ke tabel rincian
function tambahBarisRincian() {
    
    const tbody = document.querySelector('#rincianPengeluaranTable tbody');
    const currentRow = tbody.querySelectorAll('tr').length + 1;
    
    const row = document.createElement('tr');
    row.innerHTML = `
        <td class="text-center">${currentRow}</td>
        <td>
            <select class="form-select form-select-sm" name="pos_sumber_dana[]" required>
                <option value="">Pilih Pos</option>
                ${receiptPosData.map(pos => `<option value="${pos.pos_id}">${pos.pos_name}</option>`).join('')}
            </select>
        </td>
        <td>
            <select class="form-select form-select-sm" name="pos_pengeluaran[]" required>
                <option value="">Pilih Pos</option>
                ${expensePosData.map(pos => `<option value="${pos.pos_id}">${pos.pos_name}</option>`).join('')}
            </select>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm" name="keterangan_item[]" placeholder="Keterangan item" required>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm" name="jumlah_pengeluaran[]" placeholder="0" min="0" step="1" required onchange="hitungTotalPengeluaran()">
        </td>
    `;
    tbody.appendChild(row);
    
    updateNomorBaris();
    hitungTotalPengeluaran();
}

// Hapus baris dari tabel rincian
function hapusBarisPengeluaran() {
    const tbody = document.querySelector('#rincianPengeluaranTable tbody');
    const rows = tbody.querySelectorAll('tr');
    
    if (rows.length > 1) {
        rows[rows.length - 1].remove();
        updateNomorBaris();
        hitungTotalPengeluaran();
    } else {
        alert('Minimal harus ada 1 baris rincian');
    }
}

// Update nomor baris
function updateNomorBaris() {
    const rows = document.querySelectorAll('#rincianPengeluaranTable tbody tr');
    rows.forEach((row, index) => {
        row.cells[0].textContent = index + 1;
    });
}

// Hitung total pengeluaran
function hitungTotalPengeluaran() {
    const inputs = document.querySelectorAll('#rincianPengeluaranTable input[name="jumlah_pengeluaran[]"]');
    let total = 0;
    let validItems = 0;
    
    inputs.forEach(input => {
        const value = parseFloat(input.value) || 0;
        if (value > 0) {
            total += value;
            validItems++;
        }
    });
    
    document.getElementById('totalPengeluaran').textContent = total.toLocaleString('id-ID');
    document.getElementById('itemCount').textContent = validItems;
}

// Move row up
function moveRowUp() {
    const tbody = document.querySelector('#rincianPengeluaranTable tbody');
    const rows = tbody.querySelectorAll('tr');
    const selectedRow = tbody.querySelector('tr:focus-within');
    
    if (selectedRow && selectedRow.previousElementSibling) {
        tbody.insertBefore(selectedRow, selectedRow.previousElementSibling);
        updateNomorBaris();
    }
}

// Move row down
function moveRowDown() {
    const tbody = document.querySelector('#rincianPengeluaranTable tbody');
    const rows = tbody.querySelectorAll('tr');
    const selectedRow = tbody.querySelector('tr:focus-within');
    
    if (selectedRow && selectedRow.nextElementSibling) {
        tbody.insertBefore(selectedRow.nextElementSibling, selectedRow);
        updateNomorBaris();
    }
}

// Refresh metode pembayaran
function refreshMetodePembayaran() {
    // Reload metode pembayaran dari server
    location.reload();
}

// Set default metode pembayaran
function setDefaultMetode() {
    // Set default metode pembayaran (bisa disesuaikan)
    document.getElementById('metode_pembayaran').value = '1'; // ID untuk TUNAI
}

// Buat pos pengeluaran baru
function buatPosPengeluaranBaru() {
    // Reset form
    document.getElementById('formBuatPosPengeluaranBaru').reset();
    
    // Set default status ke aktif
    document.getElementById('status_pengeluaran_on').checked = true;
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('buatPosPengeluaranBaruModal'));
    modal.show();
}

// Kelola pos pengeluaran
function kelolaPosPengeluaran() {
    // Load data pos pengeluaran
    loadDataPosPengeluaran();
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('kelolaPosPengeluaranModal'));
    modal.show();
}

// Load data pos pengeluaran untuk tabel
function loadDataPosPengeluaran() {
    const tbody = document.querySelector('#tabelPosPengeluaran tbody');
    tbody.innerHTML = `
        <tr>
            <td colspan="5" class="text-center py-3 text-muted">
                <i class="fa fa-spinner fa-spin me-2"></i>Memuat data...
            </td>
        </tr>
    `;
    
    fetch('{{ route("manage.accounting.expense-transactions.get-expense-pos") }}', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
        .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            return response.text().then(text => {
                console.log('Error response:', text);
                throw new Error(`HTTP ${response.status}: ${text}`);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            renderTabelPosPengeluaran(data.pos_pengeluaran);
        } else {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-3 text-danger">
                        <i class="fa fa-exclamation-triangle me-2"></i>${data.message || 'Gagal memuat data'}
                    </td>
                </tr>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-3 text-danger">
                <i class="fa fa-exclamation-triangle me-2"></i>${error.message || 'Terjadi kesalahan'}
            </td>
        </tr>
    `;
    });
}

// Render tabel pos pengeluaran
function renderTabelPosPengeluaran(data) {
    const tbody = document.querySelector('#tabelPosPengeluaran tbody');
    
    if (data.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-3 text-muted">
                    <i class="fa fa-inbox me-2"></i>Tidak ada data pos pengeluaran
                </td>
            </tr>
        `;
        return;
    }
    
    let html = '';
    data.forEach((pos, index) => {
        const statusBadge = pos.is_active == 1 
            ? '<span class="badge bg-success">Aktif</span>'
            : '<span class="badge bg-danger">Non Aktif</span>';
        
        html += `
            <tr>
                <td>${index + 1}</td>
                <td>${pos.pos_name}</td>
                <td>${pos.pos_description || '-'}</td>
                <td>${statusBadge}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-warning me-1" onclick="editPosPengeluaran(${pos.pos_id})">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="hapusPosPengeluaran(${pos.pos_id})">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

// Edit pos pengeluaran
function editPosPengeluaran(posId) {
    // Load data pos untuk edit
    fetch(`{{ route('manage.accounting.expense-transactions.get-expense-pos-detail', ':id') }}`.replace(':id', posId), {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const pos = data.pos;
            
            console.log('Populating form with data:', pos);
            
            // Populate form
            document.getElementById('edit_pos_id').value = pos.pos_id;
            document.getElementById('edit_nama_pos').value = pos.pos_name;
            document.getElementById('edit_keterangan_pos').value = pos.pos_description || '';
            
            // Debug: Check if the field was actually set
            const namaPosField = document.getElementById('edit_nama_pos');
            console.log('nama_pos field element:', namaPosField);
            console.log('nama_pos field value after setting:', namaPosField.value);
            console.log('nama_pos field name attribute:', namaPosField.name);
            
            // Set status
            if (pos.is_active == 1) {
                document.getElementById('edit_status_aktif').checked = true;
            } else {
                document.getElementById('edit_status_nonaktif').checked = true;
            }
            
            console.log('Form populated. Values:');
            console.log('pos_id:', document.getElementById('edit_pos_id').value);
            console.log('nama_pos:', document.getElementById('edit_nama_pos').value);
            console.log('keterangan_pos:', document.getElementById('edit_keterangan_pos').value);
            console.log('status_aktif:', document.getElementById('edit_status_aktif').checked);
            console.log('status_nonaktif:', document.getElementById('edit_status_nonaktif').checked);
            
            // Close kelola modal and open edit modal
            bootstrap.Modal.getInstance(document.getElementById('kelolaPosPengeluaranModal')).hide();
            
            setTimeout(() => {
                const editModal = new bootstrap.Modal(document.getElementById('editPosPengeluaranModal'));
                editModal.show();
            }, 300);
            
        } else {
            showModernToast('error', 'Error!', data.message || 'Gagal memuat data pos');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showModernToast('error', 'Error!', 'Terjadi kesalahan saat memuat data pos');
    });
}

// Update pos pengeluaran
function updatePosPengeluaran() {
    const form = document.getElementById('formEditPosPengeluaran');
    
    // Validasi manual
    const namaPos = document.getElementById('edit_nama_pos').value.trim();
    const statusAktif = document.getElementById('edit_status_aktif');
    const statusNonaktif = document.getElementById('edit_status_nonaktif');
    
    // Validasi nama pos
    if (!namaPos) {
        showModernToast('error', 'Error!', 'Nama pos harus diisi');
        document.getElementById('edit_nama_pos').focus();
        return;
    }
    
    // Validasi status
    if (!statusAktif.checked && !statusNonaktif.checked) {
        showModernToast('error', 'Error!', 'Status harus dipilih');
        return;
    }
    
    // Debug: Log form data
    console.log('Form validation:', form.checkValidity());
    console.log('Form elements:', form.elements);
    
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    const posId = formData.get('pos_id');
    
    // Add _method for PUT request
    formData.append('_method', 'PUT');
    
    // Debug: Log all form data
    console.log('Form data being sent:');
    for (let [key, value] of formData.entries()) {
        console.log(key + ': ' + value);
    }
    
    // Debug: Check specific form elements
    console.log('Form elements check:');
    console.log('nama_pos element:', form.querySelector('[name="nama_pos"]'));
    console.log('nama_pos value:', form.querySelector('[name="nama_pos"]')?.value);
    console.log('edit_status_pos elements:', form.querySelectorAll('[name="edit_status_pos"]'));
    form.querySelectorAll('[name="edit_status_pos"]').forEach((el, index) => {
        console.log(`edit_status_pos[${index}]:`, el.checked, el.value);
    });
    
    showModernToast('info', 'Mengupdate...', 'Mengupdate pos pengeluaran');
    
    fetch(`{{ route('manage.accounting.expense-transactions.update-expense-pos', ':id') }}`.replace(':id', posId), {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Update response status:', response.status);
        if (!response.ok) {
            return response.text().then(text => {
                console.log('Update error response:', text);
                try {
                    const errorData = JSON.parse(text);
                    throw new Error(errorData.message || `HTTP ${response.status}: ${text}`);
                } catch (e) {
                    throw new Error(`HTTP ${response.status}: ${text}`);
                }
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Update response data:', data);
        if (data.success) {
            showModernToast('success', 'Berhasil!', 'Pos pengeluaran berhasil diupdate!');
            bootstrap.Modal.getInstance(document.getElementById('editPosPengeluaranModal')).hide();
            
            // Reload data
            setTimeout(() => {
                loadDataPosPengeluaran();
                // Reload dropdown pos pengeluaran di form utama
                loadExpenseData();
            }, 1000);
            
        } else {
            showModernToast('error', 'Error!', data.message || 'Gagal mengupdate pos');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showModernToast('error', 'Error!', error.message || 'Terjadi kesalahan saat mengupdate pos');
    });
}

// Hapus pos pengeluaran
function hapusPosPengeluaran(posId) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: 'Apakah Anda yakin ingin menghapus pos pengeluaran ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            showModernToast('info', 'Menghapus...', 'Menghapus pos pengeluaran');
            
            fetch(`{{ route('manage.accounting.expense-transactions.delete-expense-pos', ':id') }}`.replace(':id', posId), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showModernToast('success', 'Berhasil!', 'Pos pengeluaran berhasil dihapus!');
                    
                    // Reload data
                    setTimeout(() => {
                        loadDataPosPengeluaran();
                        // Reload dropdown pos pengeluaran di form utama
                        loadExpenseData();
                    }, 1000);
                    
                } else {
                    showModernToast('error', 'Error!', data.message || 'Gagal menghapus pos');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showModernToast('error', 'Error!', 'Terjadi kesalahan saat menghapus pos');
            });
        }
    });
}

// Tambah pos pengeluaran dari modal kelola
function tambahPosPengeluaranDariKelola() {
    // Close kelola modal
    bootstrap.Modal.getInstance(document.getElementById('kelolaPosPengeluaranModal')).hide();
    
    // Open buat pos modal
    setTimeout(() => {
        buatPosPengeluaranBaru();
    }, 300);
}

// Modern Toast Function
function showModernToast(type, title, message, duration = 5000) {
    const toastEl = document.getElementById('liveToast');
    const toastBody = toastEl.querySelector('.toast-body');
    const toastIcon = toastEl.querySelector('.toast-icon i');
    const toastTitle = toastEl.querySelector('.toast-title');
    const toastText = toastEl.querySelector('.toast-text');
    
    // Remove existing classes
    toastEl.className = 'toast align-items-center border-0';
    
    // Set content
    toastTitle.textContent = title;
    toastText.textContent = message;
    
    // Set type and icon
    switch(type) {
        case 'success':
            toastEl.classList.add('toast-success');
            toastIcon.className = 'fas fa-check-circle';
            break;
        case 'error':
            toastEl.classList.add('toast-error');
            toastIcon.className = 'fas fa-times-circle';
            break;
        case 'warning':
            toastEl.classList.add('toast-warning');
            toastIcon.className = 'fas fa-exclamation-triangle';
            break;
        case 'info':
            toastEl.classList.add('toast-info');
            toastIcon.className = 'fas fa-info-circle';
            break;
        default:
            toastEl.classList.add('toast-info');
            toastIcon.className = 'fas fa-info-circle';
    }
    
    // Show toast
    const toast = new bootstrap.Toast(toastEl, {
        autohide: true,
        delay: duration
    });
    
    toast.show();
}

// Simpan pos pengeluaran baru
function simpanPosPengeluaranBaru() {
    const form = document.getElementById('formBuatPosPengeluaranBaru');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    
    // Debug: Log form data
    console.log('Form data being sent:');
    for (let [key, value] of formData.entries()) {
        console.log(key + ': ' + value);
    }
    
    // Show loading toast
    showModernToast('info', 'Menyimpan...', 'Menyimpan pos pengeluaran baru');
    
    const url = '{{ route("manage.accounting.expense-transactions.store-expense-pos") }}';
    console.log('Sending request to:', url);
    
    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            showModernToast('success', 'Berhasil!', 'Pos pengeluaran baru berhasil dibuat!');
            bootstrap.Modal.getInstance(document.getElementById('buatPosPengeluaranBaruModal')).hide();
            
            // Reset form
            form.reset();
            
            // Reload page untuk menampilkan data terbaru
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showModernToast('error', 'Error!', data.message || 'Gagal menyimpan data');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showModernToast('error', 'Error!', 'Terjadi kesalahan saat menyimpan data: ' + error.message);
    });
}

// Edit functions untuk modal edit
function moveRowUpEdit() {
    const tbody = document.querySelector('#editRincianPengeluaranTable tbody');
    const rows = tbody.querySelectorAll('tr');
    const selectedRow = tbody.querySelector('tr:focus-within');
    
    if (selectedRow && selectedRow.previousElementSibling) {
        tbody.insertBefore(selectedRow, selectedRow.previousElementSibling);
        updateNomorBarisEdit();
    }
}

// Function untuk reset form edit
function resetEditForm() {
    try {
        // Reset form
        const form = document.getElementById('formEditTransaksi');
        if (form) form.reset();
        
        // Reset hidden field
        const editTransaksiId = document.getElementById('edit_transaksi_id');
        if (editTransaksiId) editTransaksiId.value = '';
        
        // Reset semua input fields secara manual
        const fields = [
            'edit_tanggal_pengeluaran',
            'edit_no_transaksi',
            'edit_sumber_dana_tahun',
            'edit_pengeluaran_tahun',
            'edit_metode_pembayaran',
            'edit_dibayar_ke',
            'edit_kas_id',
            'edit_keterangan_transaksi'
        ];
        
        fields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                if (field.type === 'select-one') {
                    field.selectedIndex = 0;
                } else {
                    field.value = '';
                }
            }
        });
        
        // Reset tabel rincian edit
        const tbody = document.querySelector('#editRincianPengeluaranTable tbody');
        if (tbody && receiptPosData && expensePosData) {
            tbody.innerHTML = `
                <tr>
                    <td class="text-center">1</td>
                    <td>
                        <select class="form-select form-select-sm" name="pos_sumber_dana[]" required>
                            <option value="">Pilih Pos</option>
                            ${receiptPosData.map(pos => `<option value="${pos.pos_id}">${pos.pos_name}</option>`).join('')}
                        </select>
                    </td>
                    <td>
                        <select class="form-select form-select-sm" name="pos_pengeluaran[]" required>
                            <option value="">Pilih Pos</option>
                            ${expensePosData.map(pos => `<option value="${pos.pos_id}">${pos.pos_name}</option>`).join('')}
                        </select>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" name="keterangan_item[]" placeholder="Keterangan item" required>
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm" name="jumlah_pengeluaran[]" placeholder="0" min="0" step="1" required onchange="hitungTotalPengeluaranEdit()">
                    </td>
                </tr>
            `;
        }
        
        // Reset total dan item count
        const totalElement = document.getElementById('editTotalPengeluaran');
        const itemCountElement = document.getElementById('editItemCount');
        
        if (totalElement) totalElement.textContent = '0';
        if (itemCountElement) itemCountElement.textContent = '0';
        
        // Reset selected expense ID
        window.selectedExpenseId = null;
        

        
    } catch (error) {
        console.error('Error resetting edit form:', error);
    }
}

function moveRowDownEdit() {
    const tbody = document.querySelector('#editRincianPengeluaranTable tbody');
    const rows = tbody.querySelectorAll('tr');
    const selectedRow = tbody.querySelector('tr:focus-within');
    
    if (selectedRow && selectedRow.nextElementSibling) {
        tbody.insertBefore(selectedRow.nextElementSibling, selectedRow);
        updateNomorBarisEdit();
    }
}

function hapusBarisPengeluaranEdit() {
    const tbody = document.querySelector('#editRincianPengeluaranTable tbody');
    const rows = tbody.querySelectorAll('tr');
    
    if (rows.length > 1) {
        rows[rows.length - 1].remove();
        updateNomorBarisEdit();
        hitungTotalPengeluaranEdit();
    } else {
        alert('Minimal harus ada 1 baris rincian');
    }
}

function tambahBarisRincianEdit() {
    const tbody = document.querySelector('#editRincianPengeluaranTable tbody');
    const currentRow = tbody.querySelectorAll('tr').length + 1;
    
    const row = document.createElement('tr');
    row.innerHTML = `
        <td class="text-center">${currentRow}</td>
        <td>
            <select class="form-select form-select-sm" name="pos_sumber_dana[]" required>
                <option value="">Pilih Pos</option>
                ${receiptPosData.map(pos => `<option value="${pos.pos_id}">${pos.pos_name}</option>`).join('')}
            </select>
        </td>
        <td>
            <select class="form-select form-select-sm" name="pos_pengeluaran[]" required>
                <option value="">Pilih Pos</option>
                ${expensePosData.map(pos => `<option value="${pos.pos_id}">${pos.pos_name}</option>`).join('')}
            </select>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm" name="keterangan_item[]" placeholder="Keterangan item" required>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm" name="jumlah_pengeluaran[]" placeholder="0" min="0" step="1" required onchange="hitungTotalPengeluaranEdit()">
        </td>
    `;
    tbody.appendChild(row);
    
    updateNomorBarisEdit();
    hitungTotalPengeluaranEdit();
}

// Function untuk handle error saat edit
function handleEditError(error, message = 'Terjadi kesalahan saat mengedit transaksi') {
    console.error('Edit Error:', error);
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: message
    });
}

function updateNomorBarisEdit() {
    const rows = document.querySelectorAll('#editRincianPengeluaranTable tbody tr');
    rows.forEach((row, index) => {
        row.cells[0].textContent = index + 1;
    });
}

function hitungTotalPengeluaranEdit() {
    const inputs = document.querySelectorAll('#editRincianPengeluaranTable input[name="jumlah_pengeluaran[]"]');
    let total = 0;
    let validItems = 0;
    
    inputs.forEach(input => {
        const value = parseFloat(input.value) || 0;
        if (value > 0) {
            total += value;
            validItems++;
        }
    });
    
    document.getElementById('editTotalPengeluaran').textContent = total.toLocaleString('id-ID');
    document.getElementById('editItemCount').textContent = validItems;
}

// Tambah + Copy
function tambahCopy() {
    if (!window.selectedExpenseId) {
        alert('Pilih transaksi yang akan di-copy terlebih dahulu');
        return;
    }
    // Implementasi copy
    alert('Fitur copy akan diimplementasikan');
}

// Ubah transaksi
function ubahTransaksi() {
    if (!window.selectedExpenseId) {
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan!',
            text: 'Pilih transaksi yang akan diubah terlebih dahulu'
        });
        return;
    }
    
    // Load data transaksi untuk edit
    loadTransactionForEdit(window.selectedExpenseId);
}

// Hapus transaksi
function hapusTransaksi() {
    if (!window.selectedExpenseId) {
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan!',
            text: 'Pilih transaksi yang akan dihapus terlebih dahulu'
        });
        return;
    }
    
    // Konfirmasi hapus dengan SweetAlert2
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: 'Apakah Anda yakin ingin menghapus transaksi ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Proses hapus transaksi
            deleteTransaction(window.selectedExpenseId);
        }
    });
}

// Function untuk menghapus transaksi dari server
function deleteTransaction(transactionId) {
    // Show loading state
    Swal.fire({
        title: 'Menghapus...',
        text: 'Sedang menghapus transaksi',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Kirim request delete ke server
    fetch(`{{ route('manage.accounting.expense-transactions.destroy', ':id') }}`.replace(':id', transactionId), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Success message
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            });
            
            // Reset selected ID
            window.selectedExpenseId = null;
            
            // Remove selection from table
            document.querySelectorAll('#expenseTableBody tr').forEach(tr => {
                tr.classList.remove('table-active');
            });
            
            // Clear detail panel
            clearDetailPanel();
            
            // Reload data dari server
            setTimeout(() => {
                loadExpenseData();
            }, 1000);
            
        } else {
            // Error message
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Error: ' + data.message
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: 'Terjadi kesalahan saat menghapus transaksi'
        });
    });
}

// Function untuk clear detail panel
function clearDetailPanel() {
    document.getElementById('detailTglNo').textContent = '-';
    document.getElementById('detailOperator').textContent = '-';
    document.getElementById('detailSumberDana').textContent = '-';
    document.getElementById('detailTahunPengeluaran').textContent = '-';
    document.getElementById('detailCaraTransaksi').textContent = '-';
    document.getElementById('detailKeterangan').textContent = '-';
    
    const tbody = document.getElementById('detailItemTableBody');
    tbody.innerHTML = `
        <tr>
            <td colspan="5" class="text-center text-muted">Pilih transaksi untuk melihat detail</td>
        </tr>
    `;
}

// Function untuk load data transaksi untuk edit
function loadTransactionForEdit(transactionId) {
    // Show loading state
    Swal.fire({
        title: 'Memuat Data...',
        text: 'Sedang memuat data transaksi untuk edit',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Fetch transaction details untuk edit
    fetch(`{{ route('manage.accounting.expense-transactions.edit', ':id') }}`.replace(':id', transactionId), {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const transaction = data.transaction;
            const details = data.details;
            
            // Populate form fields dengan fallback values
            const editTransaksiId = document.getElementById('edit_transaksi_id');
            const editTanggalPengeluaran = document.getElementById('edit_tanggal_pengeluaran');
            const editNoTransaksi = document.getElementById('edit_no_transaksi');
            const editSumberDanaTahun = document.getElementById('edit_sumber_dana_tahun');
            const editPengeluaranTahun = document.getElementById('edit_pengeluaran_tahun');
            const editMetodePembayaran = document.getElementById('edit_metode_pembayaran');
            const editDibayarKe = document.getElementById('edit_dibayar_ke');
            const editKasId = document.getElementById('edit_kas_id');
            const editKeteranganTransaksi = document.getElementById('edit_keterangan_transaksi');
            
            if (editTransaksiId) editTransaksiId.value = transaction.id || '';
            if (editTanggalPengeluaran) editTanggalPengeluaran.value = transaction.tanggal_pengeluaran || transaction.tanggal || '';
            if (editNoTransaksi) editNoTransaksi.value = transaction.no_transaksi || '';
            
            // Handle tahun ajaran dengan berbagai kemungkinan nama field
            const tahunAjaran = transaction.sumber_dana_tahun || transaction.pengeluaran_tahun || transaction.tahun_ajaran || '2025/2026';
            if (editSumberDanaTahun) editSumberDanaTahun.value = tahunAjaran;
            if (editPengeluaranTahun) editPengeluaranTahun.value = tahunAjaran;
            
            // Handle metode pembayaran dengan berbagai kemungkinan nama field
            const metodePembayaran = transaction.metode_pembayaran_id || transaction.metode_pembayaran || transaction.payment_method_id || '';
            if (editMetodePembayaran) editMetodePembayaran.value = metodePembayaran;
            
            // Handle field lainnya
            if (editDibayarKe) editDibayarKe.value = transaction.dibayar_ke || transaction.paid_to || '';
            if (editKasId) editKasId.value = transaction.kas_id || transaction.cash_id || '';
            if (editKeteranganTransaksi) editKeteranganTransaksi.value = transaction.keterangan_transaksi || transaction.keterangan || '';
            
            // Populate expense details table
            populateEditExpenseDetails(details);
            
            // Close loading
            Swal.close();
            
            // Show edit modal
            const modal = new bootstrap.Modal(document.getElementById('editTransaksiModal'));
            modal.show();
            
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Error: ' + data.message
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.close();
        
        if (error.name === 'TypeError' && error.message.includes('fetch')) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Terjadi kesalahan saat memuat data untuk edit: ' + error.message
            });
        }
    });
}

// Function untuk populate tabel rincian pengeluaran di modal edit
function populateEditExpenseDetails(details) {
    const tbody = document.querySelector('#editRincianPengeluaranTable tbody');
    
    if (!tbody) {
        console.error('Edit table body not found');
        return;
    }
    
    if (details && details.length > 0) {
        let html = '';
        details.forEach((detail, index) => {
            // Handle berbagai kemungkinan nama field dari server
            const posSumberDanaId = detail.pos_sumber_dana_id || detail.pos_sumber_dana || detail.receipt_pos_id || detail.receipt_pos || '';
            const posPengeluaranId = detail.pos_pengeluaran_id || detail.pos_pengeluaran || detail.expense_pos_id || detail.expense_pos || '';
            const keteranganItem = detail.keterangan_item || detail.keterangan || detail.description || '';
            const jumlah = detail.jumlah || detail.jumlah_pengeluaran || detail.amount || detail.nominal || 0;
            
            // Sanitize data untuk mencegah XSS
            const safeKeteranganItem = keteranganItem.replace(/[<>]/g, '');
            const safeJumlah = parseFloat(jumlah) || 0;
            
            html += `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td>
                        <select class="form-select form-select-sm" name="pos_sumber_dana[]" required>
                            <option value="">Pilih Pos</option>
                            ${receiptPosData.map(pos => `<option value="${pos.pos_id}" ${posSumberDanaId == pos.pos_id ? 'selected' : ''}>${pos.pos_name}</option>`).join('')}
                        </select>
                    </td>
                    <td>
                        <select class="form-select form-select-sm" name="pos_pengeluaran[]" required>
                            <option value="">Pilih Pos</option>
                            ${expensePosData.map(pos => `<option value="${pos.pos_id}" ${posPengeluaranId == pos.pos_id ? 'selected' : ''}>${pos.pos_name}</option>`).join('')}
                        </select>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" name="keterangan_item[]" value="${safeKeteranganItem}" placeholder="Keterangan item" required>
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm" name="jumlah_pengeluaran[]" value="${safeJumlah}" placeholder="0" min="0" step="1" required onchange="hitungTotalPengeluaranEdit()">
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
        
        // Update total dan item count
        setTimeout(() => {
            hitungTotalPengeluaranEdit();
        }, 100);
        
    } else {
        // Jika tidak ada detail, buat satu baris kosong
        if (receiptPosData && expensePosData) {
            tbody.innerHTML = `
                <tr>
                    <td class="text-center">1</td>
                    <td>
                        <select class="form-select form-select-sm" name="pos_sumber_dana[]" required>
                            <option value="">Pilih Pos</option>
                            ${receiptPosData.map(pos => `<option value="${pos.pos_id}">${pos.pos_name}</option>`).join('')}
                        </select>
                    </td>
                    <td>
                        <select class="form-select form-select-sm" name="pos_pengeluaran[]" required>
                            <option value="">Pilih Pos</option>
                            ${expensePosData.map(pos => `<option value="${pos.pos_id}">${pos.pos_name}</option>`).join('')}
                        </select>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" name="keterangan_item[]" placeholder="Keterangan item" required>
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm" name="jumlah_pengeluaran[]" placeholder="0" min="0" step="1" required onchange="hitungTotalPengeluaranEdit()">
                    </td>
                </tr>
            `;
        }
        
        // Reset total dan item count
        const totalElement = document.getElementById('editTotalPengeluaran');
        const itemCountElement = document.getElementById('editItemCount');
        
        if (totalElement) totalElement.textContent = '0';
        if (itemCountElement) itemCountElement.textContent = '0';
    }
}

// Cetak bukti
function cetakBukti() {
    if (!window.selectedExpenseId) {
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan!',
            text: 'Pilih transaksi yang akan dicetak terlebih dahulu'
        });
        return;
    }
    
    // Show loading state
    Swal.fire({
        title: 'Memuat...',
        text: 'Sedang memuat bukti pengeluaran',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Open print page in new window
    const printUrl = `{{ route('manage.accounting.expense-transactions.print', ':id') }}`.replace(':id', window.selectedExpenseId);
    
    try {
        // Close loading
        Swal.close();
        
        // Open print window
        const printWindow = window.open(printUrl, '_blank', 'width=800,height=900,scrollbars=yes,resizable=yes');
        
        if (!printWindow) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Popup diblokir. Silakan aktifkan popup untuk mencetak bukti.'
            });
            return;
        }
        
        // Focus to print window
        printWindow.focus();
        
        // Auto print when loaded (optional)
        printWindow.onload = function() {
            // Uncomment line below for auto print
            // printWindow.print();
        };
        
    } catch (error) {
        console.error('Error opening print window:', error);
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: 'Terjadi kesalahan saat membuka halaman cetak'
        });
    }
}

// Handle form submit tambah dengan proteksi duplikasi
let isSubmitting = false; // Flag untuk mencegah multiple submission

document.getElementById('formTambahTransaksi').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Cek apakah sedang dalam proses submit
    if (isSubmitting) {

        return;
    }
    
    // Set flag submit
    isSubmitting = true;
    
    const formData = new FormData(this);
    
    // Validasi form
    if (!formData.get('no_transaksi')) {
        alert('No. Transaksi harus diisi!');
        isSubmitting = false;
        return;
    }
    
    if (!formData.get('tanggal_pengeluaran')) {
        alert('Tanggal Pengeluaran harus diisi!');
        isSubmitting = false;
        return;
    }
    
    if (!formData.get('tahun_ajaran')) {
        alert('Tahun Ajaran harus dipilih!');
        isSubmitting = false;
        return;
    }
    
    if (!formData.get('dibayar_ke')) {
        alert('Dibayar Ke harus diisi!');
        isSubmitting = false;
        return;
    }
    
    if (!formData.get('metode_pembayaran_id')) {
        alert('Metode Pembayaran harus dipilih!');
        isSubmitting = false;
        return;
    }
    
    if (!formData.get('kas_id')) {
        alert('Kas harus dipilih!');
        isSubmitting = false;
        return;
    }
    
    // Ambil data dari form
    const posSumberDana = formData.getAll('pos_sumber_dana[]');
    const posPengeluaran = formData.getAll('pos_pengeluaran[]');
    const keteranganItem = formData.getAll('keterangan_item[]');
    const jumlahPengeluaran = formData.getAll('jumlah_pengeluaran[]');
    
    // Validasi minimal 1 item
    if (posSumberDana.length === 0 || !posSumberDana[0]) {
        alert('Pilih Pos Sumber Dana terlebih dahulu!');
        isSubmitting = false;
        return;
    }
    
    if (posPengeluaran.length === 0 || !posPengeluaran[0]) {
        alert('Pilih Pos Pengeluaran terlebih dahulu!');
        isSubmitting = false;
        return;
    }
    
    // Validasi pos harus berupa ID yang valid
    if (!posSumberDana[0] || posSumberDana[0] === '' || isNaN(parseInt(posSumberDana[0]))) {
        alert('Pos Sumber Dana harus dipilih dengan benar!');
        isSubmitting = false;
        return;
    }
    
    if (!posPengeluaran[0] || posPengeluaran[0] === '' || isNaN(parseInt(posPengeluaran[0]))) {
        alert('Pos Pengeluaran harus dipilih dengan benar!');
        isSubmitting = false;
        return;
    }
    
    // Validasi jumlah pengeluaran
    if (!jumlahPengeluaran[0] || parseFloat(jumlahPengeluaran[0]) <= 0) {
        alert('Jumlah Pengeluaran harus diisi dan lebih dari 0!');
        isSubmitting = false;
        return;
    }
    
    // Validasi keterangan item
    if (!keteranganItem[0] || keteranganItem[0].trim() === '') {
        alert('Keterangan Item harus diisi!');
        isSubmitting = false;
        return;
    }
    
    // Log data yang akan dikirim
    const requestData = {
        tanggal_pengeluaran: formData.get('tanggal_pengeluaran'),
        no_transaksi: formData.get('no_transaksi'),
        tahun_ajaran: formData.get('tahun_ajaran'),
        dibayar_ke: formData.get('dibayar_ke'),
        metode_pembayaran_id: formData.get('metode_pembayaran_id'),
        kas_id: formData.get('kas_id'),
        keterangan_transaksi: formData.get('keterangan_transaksi'),
        pos_sumber_dana: posSumberDana,
        pos_pengeluaran: posPengeluaran,
        keterangan_item: keteranganItem,
        jumlah_pengeluaran: jumlahPengeluaran
    };
    
    
    
    // Disable submit button untuk mencegah multiple click
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Menyimpan...';
    
    // Kirim data ke server
    fetch('{{ route("manage.accounting.expense-transactions.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => {

        if (!response.ok) {
            throw response;
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            });
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('tambahTransaksiModal'));
            modal.hide();
            
            // Reset form
            document.getElementById('formTambahTransaksi').reset();
            resetRincianTable();
            
            // Reload data dari server dengan delay yang lebih lama
            setTimeout(() => {
                loadExpenseData();
            }, 1000);
            
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Error: ' + data.message
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (error.status === 422) {
            // Validation error
            error.json().then(data => {
                console.error('Server response:', data);
                if (data.errors) {
                    let errorMessage = 'Error validasi:\n';
                    Object.keys(data.errors).forEach(key => {
                        errorMessage += `- ${key}: ${data.errors[key].join(', ')}\n`;
                    });
                    alert(errorMessage);
                } else {
                    alert('Error: ' + (data.message || 'Terjadi kesalahan saat menyimpan data'));
                }
            }).catch(() => {
                alert('Terjadi kesalahan saat menyimpan data');
            });
        } else if (error.status === 401) {
            alert('Anda tidak memiliki akses. Silakan login ulang.');
        } else if (error.status === 500) {
            alert('Terjadi kesalahan server. Silakan coba lagi.');
        } else {
            alert('Terjadi kesalahan saat menyimpan data');
        }
    })
    .finally(() => {
        // Reset flag dan button
        isSubmitting = false;
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// Handle form submit edit
document.getElementById('formEditTransaksi').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validasi form
    const formData = new FormData(this);
    
    if (!formData.get('tanggal_pengeluaran')) {
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan!',
            text: 'Tanggal Pengeluaran harus diisi!'
        });
        return;
    }
    
    if (!formData.get('sumber_dana_tahun')) {
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan!',
            text: 'Sumber Dana Milik Tahun harus dipilih!'
        });
        return;
    }
    
    if (!formData.get('pengeluaran_tahun')) {
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan!',
            text: 'Pengeluaran Untuk Tahun harus dipilih!'
        });
        return;
    }
    
    if (!formData.get('metode_pembayaran')) {
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan!',
            text: 'Metode Pembayaran harus dipilih!'
        });
        return;
    }
    
    if (!formData.get('dibayar_ke')) {
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan!',
            text: 'Dibayar Ke harus diisi!'
        });
        return;
    }
    
    if (!formData.get('kas_id')) {
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan!',
            text: 'Kas harus dipilih!'
        });
        return;
    }
    
    // Ambil data dari form
    const posSumberDana = formData.getAll('pos_sumber_dana[]');
    const posPengeluaran = formData.getAll('pos_pengeluaran[]');
    const keteranganItem = formData.getAll('keterangan_item[]');
    const jumlahPengeluaran = formData.getAll('jumlah_pengeluaran[]');
    
    // Validasi minimal 1 item
    if (posSumberDana.length === 0 || !posSumberDana[0]) {
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan!',
            text: 'Pilih Pos Sumber Dana terlebih dahulu!'
        });
        return;
    }
    
    if (posPengeluaran.length === 0 || !posPengeluaran[0]) {
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan!',
            text: 'Pilih Pos Pengeluaran terlebih dahulu!'
        });
        return;
    }
    
    if (!jumlahPengeluaran[0] || parseFloat(jumlahPengeluaran[0]) <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan!',
            text: 'Jumlah Pengeluaran harus diisi dan lebih dari 0!'
        });
        return;
    }
    
    if (!keteranganItem[0] || keteranganItem[0].trim() === '') {
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan!',
            text: 'Keterangan Item harus diisi!'
        });
        return;
    }
    
    // Prepare data untuk update
    const updateData = {
        tanggal_pengeluaran: formData.get('tanggal_pengeluaran'),
        sumber_dana_tahun: formData.get('sumber_dana_tahun'),
        pengeluaran_tahun: formData.get('pengeluaran_tahun'),
        metode_pembayaran: formData.get('metode_pembayaran'),
        dibayar_ke: formData.get('dibayar_ke'),
        kas_id: formData.get('kas_id'),
        keterangan_transaksi: formData.get('keterangan_transaksi'),
        pos_sumber_dana: posSumberDana,
        pos_pengeluaran: posPengeluaran,
        keterangan_item: keteranganItem,
        jumlah_pengeluaran: jumlahPengeluaran
    };
    
    // Show loading state
    Swal.fire({
        title: 'Mengupdate...',
        text: 'Sedang mengupdate transaksi',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Kirim request update ke server
    const transactionId = formData.get('transaksi_id');
    fetch(`{{ route('manage.accounting.expense-transactions.update', ':id') }}`.replace(':id', transactionId), {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(updateData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Success message
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            });
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('editTransaksiModal'));
    modal.hide();
            
            // Reset selected ID
            window.selectedExpenseId = null;
            
            // Remove selection from table
            document.querySelectorAll('#expenseTableBody tr').forEach(tr => {
                tr.classList.remove('table-active');
            });
            
            // Clear detail panel
            clearDetailPanel();
            
            // Reload data dari server
            setTimeout(() => {
                loadExpenseData();
            }, 1000);
            
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Error: ' + data.message
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.close();
        
        if (error.name === 'TypeError' && error.message.includes('fetch')) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.'
            });
        } else if (error.status === 422) {
            // Validation error
            Swal.fire({
                icon: 'warning',
                title: 'Validasi Gagal!',
                text: 'Mohon periksa kembali data yang diinput'
            });
        } else if (error.status === 401) {
            Swal.fire({
                icon: 'error',
                title: 'Akses Ditolak!',
                text: 'Anda tidak memiliki akses. Silakan login ulang.'
            });
        } else if (error.status === 500) {
            Swal.fire({
                icon: 'error',
                title: 'Error Server!',
                text: 'Terjadi kesalahan server. Silakan coba lagi.'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Terjadi kesalahan saat mengupdate transaksi: ' + error.message
            });
        }
    });
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.key === 'F2') {
        e.preventDefault();
        tambahTransaksi();
    } else if (e.key === 'F3') {
        e.preventDefault();
        ubahTransaksi();
    } else if (e.key === 'F4') {
        e.preventDefault();
        hapusTransaksi();
    } else if (e.key === 'F5') {
        e.preventDefault();
        filterPeriode();
    } else if (e.key === 'F6') {
        e.preventDefault();
        cetakBukti();
    } else if (e.key === 'F8') {
        e.preventDefault();
        tambahCopy();
    }
});

// Event listener untuk modal edit
document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editTransaksiModal');
    if (editModal) {
        editModal.addEventListener('hidden.bs.modal', function() {
            // Reset form edit saat modal ditutup
            if (typeof resetEditForm === 'function') {
                resetEditForm();
            }
        });
        
        // Tambahkan event listener untuk tombol close
        const closeButtons = editModal.querySelectorAll('[data-bs-dismiss="modal"]');
        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                if (typeof resetEditForm === 'function') {
                    resetEditForm();
                }
            });
        });
    }
    
    // Tambahkan event listener untuk tombol batal
    const cancelButton = document.querySelector('#formEditTransaksi button[type="button"]');
    if (cancelButton) {
        cancelButton.addEventListener('click', function() {
            if (typeof resetEditForm === 'function') {
                resetEditForm();
            }
        });
    }
});
</script>
@endpush

<style>
/* Gradient Backgrounds */
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-gradient-danger {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

.bg-light-danger {
    background-color: #ffe6e6 !important;
}

/* Table Styling */
.table-hover tbody tr:hover {
    background-color: rgba(255, 107, 107, 0.1) !important;
    transform: scale(1.01);
    transition: all 0.2s ease;
}

.table-active {
    background-color: rgba(255, 107, 107, 0.2) !important;
    border-left: 4px solid #dc3545;
}

/* Button Styling */
.btn-group .btn {
    border-radius: 8px;
    margin-right: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    color: white !important;
}

.btn-group .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    color: white !important;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

/* Tombol Warning dengan font putih */
.btn-warning {
    color: white !important;
}

.btn-warning:hover {
    color: white !important;
}

/* Semua tombol action dengan font putih */
.btn-success, .btn-primary, .btn-warning, .btn-danger, .btn-info {
    color: white !important;
}

.btn-success:hover, .btn-primary:hover, .btn-warning:hover, .btn-danger:hover, .btn-info:hover {
    color: white !important;
}

/* Modal styling */
.modal-xl {
    max-width: 1200px;
}

.modal-header {
    border-bottom: none;
    padding: 1.5rem;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    border-top: none;
    padding: 1.5rem;
}

/* Table styling untuk rincian pengeluaran */
#rincianPengeluaranTable,
#editRincianPengeluaranTable {
    border: 1px solid #dee2e6;
}

#rincianPengeluaranTable th,
#editRincianPengeluaranTable th {
    background-color: #f8f9fa;
    border-color: #dee2e6;
    font-weight: 600;
    font-size: 0.875rem;
}

#rincianPengeluaranTable td,
#editRincianPengeluaranTable td {
    border-color: #dee2e6;
    vertical-align: middle;
}

/* Form control styling */
.form-control,
.form-select {
    border-color: #6c757d !important;
}

.form-control:focus,
.form-select:focus {
    border-color: #6c757d;
    box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.25);
}

/* Button group styling */
.btn-group .btn {
    margin-right: 0.5rem;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

/* Light background colors */
.bg-light-success {
    background-color: #d1edff !important;
}

.bg-light-warning {
    background-color: #fff3cd !important;
}

.bg-light-dark {
    background-color: #f8f9fa !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .modal-xl {
        max-width: 95%;
        margin: 1rem;
    }
    
    .modal-body {
        padding: 1rem;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
}

/* Animation for table rows */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

#expenseTableBody tr {
    animation: fadeIn 0.3s ease-in-out;
}

/* Custom scrollbar */
.table-responsive::-webkit-scrollbar {
    width: 8px;
}

.table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb {
    background: #dc3545;
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #c82333;
}

/* Custom Toast Styles */
.toast {
    min-width: 350px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.12);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    overflow: hidden;
}

.toast-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
}

.toast-error {
    background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
    color: white;
}

.toast-info {
    background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
    color: white;
}

.toast-warning {
    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
    color: #212529;
}

.toast-icon {
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
}

.toast-title {
    font-size: 1rem;
    margin-bottom: 2px;
}

.toast-text {
    font-size: 0.875rem;
    opacity: 0.9;
}

.toast-body {
    padding: 16px;
}

/* Animation */
.toast.showing {
    animation: slideInRight 0.3s ease-out;
}

.toast.hide {
    animation: slideOutRight 0.3s ease-in;
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOutRight {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}
</style>

<script>
// Auto-reload data saat halaman pertama kali dibuka
let isInitialized = false; // Flag untuk mencegah multiple initialization

document.addEventListener('DOMContentLoaded', function() {
    if (isInitialized) {

        return;
    }
    

    isInitialized = true;
    
    // Load data pertama kali
    loadExpenseData();
    
    // Set interval untuk auto-reload setiap 30 detik (opsional)
    // setInterval(loadExpenseData, 30000);
});

// Event listener untuk filter periode dengan debounce
let filterTimeout;
document.getElementById('start_date').addEventListener('change', function() {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(() => {
        loadExpenseData();
    }, 300);
});

document.getElementById('end_date').addEventListener('change', function() {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(() => {
        loadExpenseData();
    }, 300);
});

// Keyboard shortcuts untuk modal pos pengeluaran baru
document.addEventListener('keydown', function(e) {
    // F5 untuk simpan pos pengeluaran baru
    if (e.key === 'F5' && document.getElementById('buatPosPengeluaranBaruModal').classList.contains('show')) {
        e.preventDefault();
        simpanPosPengeluaranBaru();
    }
    
    // Esc untuk tutup modal
    if (e.key === 'Escape' && document.getElementById('buatPosPengeluaranBaruModal').classList.contains('show')) {
        e.preventDefault();
        bootstrap.Modal.getInstance(document.getElementById('buatPosPengeluaranBaruModal')).hide();
    }
});
</script>
