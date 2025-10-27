@extends('layouts.coreui')

@section('title', 'Pos Penerimaan')

@push('head')
<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

<style>
/* Mengubah warna font tombol menjadi putih */
.btn {
    color: white !important;
    font-weight: 500;
}

.btn:hover {
    color: white !important;
}

.btn:focus {
    color: white !important;
}

.btn:active {
    color: white !important;
}

/* Khusus untuk tombol outline, tetap gunakan warna asli */
.btn-outline-primary,
.btn-outline-secondary,
.btn-outline-success,
.btn-outline-danger,
.btn-outline-warning,
.btn-outline-info {
    color: inherit !important;
}

.btn-outline-primary:hover,
.btn-outline-secondary:hover,
.btn-outline-success:hover,
.btn-outline-danger:hover,
.btn-outline-warning:hover,
.btn-outline-info:hover {
    color: white !important;
}

/* Table Styling - Efek hover dan selection */
.table-row-hover {
    cursor: pointer;
    transition: all 0.2s ease;
}

.table-row-hover:hover {
    background-color: rgba(0, 123, 255, 0.1) !important;
    transform: scale(1.01);
}

.table-active {
    background-color: rgba(0, 123, 255, 0.2) !important;
    border-left: 4px solid #007bff;
}

/* Animation for table rows */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

#transaksiTable tr {
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
    background: #007bff;
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #0056b3;
}
</style>

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Panel Kiri: Daftar Transaksi -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>Transaksi Penerimaan Lain
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Filter Section -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Filter Periode</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="tanggal_awal" value="{{ date('Y-m-d', strtotime('-3 months')) }}">
                                <span class="input-group-text">s/d</span>
                                <input type="date" class="form-control" id="tanggal_akhir" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button type="button" class="btn btn-primary me-2" onclick="filterPeriode()">
                                <i class="fas fa-filter me-1"></i>Filter Periode [F5]
                            </button>
                            <!-- <a href="{{ route('manage.accounting.pos-view') }}" class="btn btn-info" title="Lihat Pos Penerimaan">
                                <i class="fas fa-eye me-1"></i>Lihat Pos
                            </a> -->
                        </div>
                    </div>

                    <!-- Tabel Transaksi -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>NO.</th>
                                    <th>TANGGAL</th>
                                    <th>NO. TRANSAKSI</th>
                                    <th>KAS</th>
                                    <th>KETERANGAN</th>
                                    <th>JUMLAH PENERIMAAN</th>
                                </tr>
                            </thead>
                            <tbody id="transaksiTable">
                                @foreach($transaksiList as $transaksi)
                                <tr class="cursor-pointer table-row-hover" onclick="selectTransaksi({{ $transaksi->id }})" data-transaksi-id="{{ $transaksi->id }}">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ \Carbon\Carbon::parse($transaksi->tanggal_penerimaan)->format('d-m-Y') }}</td>
                                    <td>{{ $transaksi->no_transaksi }}</td>
                                    <td>{{ $transaksi->kas_name ?? '-' }}</td>
                                    <td>{{ $transaksi->keterangan_transaksi ?: '-' }}</td>
                                    <td class="text-end">Rp {{ number_format($transaksi->total_penerimaan, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>



                    <!-- Summary Panel Kiri -->
                    <!-- <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-1">Total Penerimaan</h6>
                                    <h4 class="text-success mb-0" id="totalPenerimaan">Rp {{ number_format($transaksiList->sum('total_penerimaan'), 0, ',', '.') }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-1">Jumlah Transaksi</h6>
                                    <h4 class="text-info mb-0" id="jumlahTransaksi">{{ $transaksiList->count() }} transaksi</h4>
                                </div>
                            </div>
                        </div>
                    </div> -->
                </div>
            </div>
        </div>

        <!-- Panel Kanan: Detail Transaksi -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Detail Transaksi
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Informasi Transaksi -->
                    <div id="transaksiInfo" class="mb-4">
                        <div class="row mb-2">
                            <div class="col-4"><strong class="text-dark">Tgl/No Transaksi:</strong></div>
                            <div class="col-8" id="infoTglNo">-</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><strong class="text-dark">Operator / Petugas:</strong></div>
                            <div class="col-8" id="infoOperator">-</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><strong class="text-dark">Terima Dari:</strong></div>
                            <div class="col-8" id="infoTerimaDari">-</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><strong class="text-dark">Untuk Tahun Pelajaran:</strong></div>
                            <div class="col-8" id="infoTahunAjaran">-</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><strong class="text-dark">Cara Transaksi:</strong></div>
                            <div class="col-8" id="infoCaraTransaksi">-</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><strong class="text-dark">Kas:</strong></div>
                            <div class="col-8" id="infoKas">-</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><strong class="text-dark">Keterangan Transaksi:</strong></div>
                            <div class="col-8" id="infoKeterangan">-</div>
                        </div>
                    </div>

                    <!-- Tabel Item Detail -->
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-primary">
                                                                    <tr>
                                        <th>NO</th>
                                        <th>POS PENERIMAAN</th>
                                        <th>KETERANGAN ITEM</th>
                                        <th>JUMLAH</th>
                                    </tr>
                            </thead>
                            <tbody id="itemDetailTable">
                                <!-- Data akan diisi via JavaScript -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Summary Panel Kanan -->
                    <!-- <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-1">Total Item</h6>
                                    <h4 class="text-warning mb-0" id="totalItemDetail">Rp 0</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-1">Jumlah Item</h6>
                                    <h4 class="text-info mb-0" id="jumlahItem">0 item</h4>
                                </div>
                            </div>
                        </div>
                    </div> -->
                </div>
            </div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center">
                    <!-- Tombol Aksi -->
                    <div class="d-flex justify-content-start mb-3">
                        <button type="button" class="btn btn-success me-2" onclick="tambahTransaksi()">
                            <i class="fa fa-plus me-1"></i>Tambah [F2]
                        </button>
                        <button type="button" class="btn btn-primary me-2" onclick="tambahTransaksiCopy()">
                            <i class="fa fa-copy me-1"></i>Tambah + Copy [F8]
                        </button>
                        <button type="button" class="btn btn-warning me-2" onclick="ubahTransaksi()">
                            <i class="fa fa-edit me-1"></i>Ubah [F3]
                        </button>
                        <button type="button" class="btn btn-danger me-2" onclick="hapusTransaksi()">
                            <i class="fa fa-trash me-1"></i>Hapus [F4]
                        </button>
                        <button type="button" class="btn btn-info me-2" onclick="cetakBukti()">
                            <i class="fa fa-print me-1"></i>Cetak Bukti [F6]
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Transaksi -->
<div class="modal fade" id="tambahTransaksiModal" tabindex="-1" aria-labelledby="tambahTransaksiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="tambahTransaksiModalLabel">
                    <i class="fas fa-plus me-2"></i>Tambah Transaksi Penerimaan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formTambahTransaksi">
                    <!-- Detail Transaksi -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Penerimaan <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_penerimaan" name="tanggal_penerimaan" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                            <select class="form-select" id="tahun_ajaran" name="tahun_ajaran" required>
                                <option value="">Pilih Tahun Ajaran</option>
                                <option value="2024/2025">2024/2025</option>
                                <option value="2025/2026" selected>2025/2026</option>
                                <option value="2026/2027">2026/2027</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Diterima Dari <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="diterima_dari" name="diterima_dari" placeholder="Nama pemberi" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                            <select class="form-select" id="metode_pembayaran" name="metode_pembayaran" required>
                                <option value="">Pilih Metode</option>
                                @foreach($paymentMethods as $method)
                                    <option value="{{ $method->id }}">{{ $method->nama_metode }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Kas <span class="text-danger">*</span></label>
                            <select class="form-select" id="kas_id" name="kas_id" required>
                                <option value="">Pilih Kas</option>
                                @foreach($kasList as $kas)
                                    <option value="{{ $kas->id }}">{{ $kas->nama_kas }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Keterangan Transaksi</label>
                            <textarea class="form-control" id="keterangan_transaksi" name="keterangan_transaksi" rows="2" placeholder="Keterangan transaksi"></textarea>
                        </div>
                    </div>

                    <!-- Rincian Item -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Rincian Penerimaan</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="rincianTable">
                                    <thead class="table-secondary">
                                        <tr>
                                            <th width="5%">No.</th>
                                            <th width="25%">Pos Penerimaan</th>
                                            <th width="40%">Keterangan Item</th>
                                            <th width="20%">Jumlah</th>
                                            <th width="10%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Baris akan ditambahkan via JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="tambahBaris()">
                                    <i class="fas fa-plus me-1"></i>Tambah Baris
                                </button>
                                <div class="text-end">
                                    <strong>Total: <span id="totalPenerimaanModal">Rp 0</span></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" onclick="simpanTransaksi()">
                    <i class="fas fa-save me-2"></i>Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Transaksi -->
<div class="modal fade" id="editTransaksiModal" tabindex="-1" aria-labelledby="editTransaksiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="editTransaksiModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Transaksi Penerimaan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditTransaksi">
                    <input type="hidden" id="editTransaksiId" name="transaksi_id">
                    
                    <!-- Detail Transaksi -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Penerimaan <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="editTanggalPenerimaan" name="tanggal_penerimaan" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editTahunAjaran" name="tahun_ajaran" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Diterima Dari <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editDiterimaDari" name="diterima_dari" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                            <select class="form-select" id="editMetodePembayaran" name="metode_pembayaran" required>
                                <option value="">Pilih Metode</option>
                                @foreach($paymentMethods as $method)
                                    <option value="{{ $method->id }}">{{ $method->nama_metode }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Kas <span class="text-danger">*</span></label>
                            <select class="form-select" id="editKasId" name="kas_id" required>
                                <option value="">Pilih Kas</option>
                                @foreach($kasList as $kas)
                                    <option value="{{ $kas->id }}">{{ $kas->nama_kas }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Keterangan Transaksi</label>
                            <textarea class="form-control" id="editKeteranganTransaksi" name="keterangan_transaksi" rows="2"></textarea>
                        </div>
                    </div>
                    
                    <!-- Rincian Transaksi -->
                    <div class="mb-3">
                        <label class="form-label">Rincian Transaksi <span class="text-danger">*</span></label>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="editRincianTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 8%;">NO</th>
                                        <th style="width: 30%;">POS PENERIMAAN</th>
                                        <th style="width: 40%;">KETERANGAN ITEM</th>
                                        <th style="width: 22%;">JUMLAH PENERIMAAN</th>
                                        <th style="width: 10%;">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data akan diisi via JavaScript -->
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-sm btn-success" onclick="tambahBarisEdit()">
                            <i class="fas fa-plus me-1"></i>Tambah Baris
                        </button>
                    </div>
                    
                    <!-- Total -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Total Penerimaan</label>
                            <input type="text" class="form-control" id="editTotalPenerimaan" readonly>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning" onclick="simpanEditTransaksi()">
                    <i class="fas fa-save me-1"></i>Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Pos Penerimaan -->
<div class="modal fade" id="editPosModal" tabindex="-1" aria-labelledby="editPosModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="editPosModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Pos Penerimaan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditPos">
                    <input type="hidden" id="edit_pos_id" name="pos_id">
                    <div class="mb-3">
                        <label class="form-label">Nama Pos <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_pos_name" name="pos_name" placeholder="Nama pos penerimaan" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" id="edit_pos_description" name="pos_description" rows="3" placeholder="Keterangan pos"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning" onclick="updatePos()">
                    <i class="fas fa-save me-2"></i>Update
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Buat Pos Penerimaan Baru -->
<div class="modal fade" id="buatPosBaruModal" tabindex="-1" aria-labelledby="buatPosBaruModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="buatPosBaruModalLabel">
                    <i class="fas fa-plus me-2"></i>Buat Pos Penerimaan Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formBuatPosBaru">
                    <div class="mb-3">
                        <label class="form-label">Nama Pos <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_pos_baru" name="nama_pos_baru" placeholder="Nama pos penerimaan" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" id="keterangan_pos_baru" name="keterangan_pos_baru" rows="3" placeholder="Keterangan pos"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status_pos_baru" id="status_on" value="ON" checked>
                            <label class="form-check-label" for="status_on">Aktif</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status_pos_baru" id="status_off" value="OFF">
                            <label class="form-check-label" for="status_off">Tidak Aktif</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="simpanPosBaru()">
                    <i class="fas fa-save me-2"></i>Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Preview Kuitansi -->
<div class="modal fade" id="previewKuitansiModal" tabindex="-1" aria-labelledby="previewKuitansiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="previewKuitansiModalLabel">
                    <i class="fas fa-print me-2"></i>Preview Kuitansi Penerimaan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="kuitansiContent" class="bg-white p-4">
                    <!-- Content kuitansi akan diisi via JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-success" onclick="printKuitansi()">
                    <i class="fas fa-print me-1"></i>Cetak Kuitansi
                </button>
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

<style>
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

/* Custom Card Styles */
.card-header {
    border-bottom: 2px solid rgba(0,0,0,0.1);
}

.table th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
}

.btn {
    border-radius: 8px;
    font-weight: 500;
}

.form-control, .form-select {
    border-radius: 6px;
}

/* Kuitansi Print Styles */
@media print {
    .kuitansi-container {
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    
    .modal-header,
    .modal-footer,
    .btn {
        display: none !important;
    }
    
    .modal-body {
        padding: 0 !important;
    }
    
    .kuitansi-container {
        page-break-inside: avoid;
    }
    
    .table {
        page-break-inside: avoid;
    }
}

/* Kuitansi styling */
.kuitansi-container {
    background: white;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.kuitansi-container table {
    border-collapse: collapse;
    width: 100%;
}

.kuitansi-container th,
.kuitansi-container td {
    border: 1px solid #000;
    padding: 5px;
    font-size: 11px;
}

.kuitansi-container th {
    background-color: #f0f0f0;
    font-weight: bold;
}
</style>

<script>
// Data sekolah untuk header kuitansi
let schoolProfileData = @json($schoolProfile);

let currentRow = 1;
let selectedTransaksiId = null;

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

// Load data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadTransaksiData();
    setDefaultMetode();
});

// Load transaksi data
function loadTransaksiData() {
    // Data sudah di-load dari server via Blade
    // Tidak perlu simulasi lagi
}

// Render transaksi table
function renderTransaksiTable(data) {
    const tbody = document.getElementById('transaksiTable');
    tbody.innerHTML = '';
    
    data.forEach((item, index) => {
        const row = document.createElement('tr');
        row.className = 'cursor-pointer';
        row.onclick = () => selectTransaksi(item);
        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${item.tanggal}</td>
            <td>${item.no_transaksi}</td>
            <td>${item.keterangan}</td>
            <td class="text-end">Rp ${item.jumlah.toLocaleString('id-ID')}</td>
        `;
        tbody.appendChild(row);
    });
}

        // Data transaksi dari server
        const transaksiList = @json($transaksiList);
        
        // Debug: cek data yang tersedia
        console.log('TransaksiList data:', transaksiList);
        console.log('TransaksiList length:', transaksiList.length);
        
        // Tampilkan data yang tersedia di console untuk debugging
        if (transaksiList && transaksiList.length > 0) {
            console.log('Sample transaksi data:', transaksiList[0]);
            console.log('Available IDs:', transaksiList.map(t => t.id));
        }

        // Select transaksi
        function selectTransaksi(transaksiId) {
            console.log('Selecting transaksi ID:', transaksiId);
            console.log('Available transaksiList:', transaksiList);
            
            // Update selected transaksi ID
            selectedTransaksiId = transaksiId;
            
            // Hapus class active dari semua row
            document.querySelectorAll('#transaksiTable tr').forEach(tr => tr.classList.remove('table-active'));
            // Cari row yang sesuai dengan transaksiId
            const targetRow = document.querySelector(`#transaksiTable tr[data-transaksi-id="${transaksiId}"]`);
            if (targetRow) {
                targetRow.classList.add('table-active');
            }

            // Ambil data transaksi berdasarkan ID
            const transaksi = transaksiList.find(t => t.id == transaksiId); // Gunakan == untuk type coercion
            console.log('Found transaksi:', transaksi);
            
            if (transaksi) {
                // Update detail transaksi dengan field yang benar dari controller
                document.getElementById('infoTglNo').textContent = `${transaksi.tanggal_penerimaan} / ${transaksi.no_transaksi}`;
                document.getElementById('infoOperator').textContent = transaksi.operator || '-';
                document.getElementById('infoTerimaDari').textContent = transaksi.diterima_dari || '-';
                document.getElementById('infoTahunAjaran').textContent = transaksi.tahun_ajaran || '-';
                document.getElementById('infoCaraTransaksi').textContent = transaksi.cara_transaksi || '-';
                document.getElementById('infoKas').textContent = transaksi.kas_name || 'KAS BANK';
                document.getElementById('infoKeterangan').textContent = transaksi.keterangan_transaksi || '-';

                // Load detail item
                loadDetailItem(transaksiId);
            } else {
                console.error('Transaksi tidak ditemukan dengan ID:', transaksiId);
                console.log('Available IDs:', transaksiList.map(t => t.id));
            }
        }

// Load detail item
function loadDetailItem(transaksiId) {
    // Fetch detail item dari server
    fetch(`{{ route('manage.accounting.receipt-pos.show', ':id') }}`.replace(':id', transaksiId), {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.transaksi && data.transaksi.details) {
            renderItemDetailTable(data.transaksi.details);
        }
    })
    .catch(error => {
        console.error('Error loading detail item:', error);
    });
}

// Render item detail table
function renderItemDetailTable(items) {
    const tbody = document.getElementById('itemDetailTable');
    tbody.innerHTML = '';
    
    let total = 0;
    console.log('Items to render:', items);
    
    items.forEach((item, index) => {
        const row = document.createElement('tr');
        const jumlah = parseFloat(item.jumlah) || 0;
        const keterangan = item.keterangan_item || item.keterangan || '-';
        
        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${item.pos || item.pos_name || '-'}</td>
            <td>${keterangan}</td>
            <td class="text-end">Rp ${jumlah.toLocaleString('id-ID')}</td>
        `;
        tbody.appendChild(row);
        total += jumlah;
    });
    
    // Format total dengan benar
    const formattedTotal = total.toLocaleString('id-ID');
    document.getElementById('totalItemDetail').textContent = `Rp ${formattedTotal}`;
    document.getElementById('jumlahItem').textContent = `${items.length} item`;
    
    console.log('Total calculated:', total, 'Formatted:', formattedTotal);
    console.log('Tabel itemDetailTable setelah render:', document.getElementById('itemDetailTable').innerHTML);
}

// Update summary
function updateSummary(data) {
    const total = data.reduce((sum, item) => sum + item.jumlah, 0);
    document.getElementById('totalPenerimaan').textContent = `Rp ${total.toLocaleString('id-ID')}`;
    document.getElementById('jumlahTransaksi').textContent = `${data.length} transaksi`;
}

// Filter functions
function filterPeriode() {
    const tanggalAwal = document.getElementById('tanggal_awal').value;
    const tanggalAkhir = document.getElementById('tanggal_akhir').value;
    
    if (!tanggalAwal || !tanggalAkhir) {
        showModernToast('warning', 'Peringatan!', 'Pilih tanggal awal dan akhir');
        return;
    }
    
    showModernToast('info', 'Memfilter...', 'Menerapkan filter periode');
    // Implementasi filter sesuai kebutuhan
}

function multiFilter() {
    showModernToast('info', 'Multi Filter', 'Fitur multi filter akan segera tersedia');
}

// Action functions
function tambahTransaksi() {
    document.getElementById('formTambahTransaksi').reset();
    document.getElementById('tanggal_penerimaan').value = new Date().toISOString().split('T')[0];
    resetRincianTable();
    
    const modal = new bootstrap.Modal(document.getElementById('tambahTransaksiModal'));
    modal.show();
}

function tambahCopy() {
    if (!selectedTransaksiId) {
        showModernToast('warning', 'Peringatan!', 'Pilih transaksi yang akan di-copy');
        return;
    }
    showModernToast('info', 'Copy Transaksi', 'Fitur copy transaksi akan segera tersedia');
}

function ubahTransaksi() {
    if (!selectedTransaksiId) {
        showModernToast('warning', 'Peringatan!', 'Pilih transaksi yang akan diubah');
        return;
    }
    
    // Ambil data transaksi yang dipilih
    const transaksi = transaksiList.find(t => t.id == selectedTransaksiId);
    if (!transaksi) {
        showModernToast('error', 'Error!', 'Data transaksi tidak ditemukan');
        return;
    }
    
    // Isi form edit dengan data yang ada
    document.getElementById('editTransaksiId').value = selectedTransaksiId;
    document.getElementById('editTanggalPenerimaan').value = transaksi.tanggal_penerimaan;
    document.getElementById('editTahunAjaran').value = transaksi.tahun_ajaran || '';
    document.getElementById('editDiterimaDari').value = transaksi.diterima_dari || '';
    document.getElementById('editKeteranganTransaksi').value = transaksi.keterangan_transaksi || '';
    
    // Set metode pembayaran
    const metodeSelect = document.getElementById('editMetodePembayaran');
    if (metodeSelect) {
        metodeSelect.value = transaksi.metode_pembayaran_id || '';
    }
    
    // Load detail item untuk edit
    loadDetailItemForEdit(selectedTransaksiId);
    
    // Tampilkan modal edit
    const modal = new bootstrap.Modal(document.getElementById('editTransaksiModal'));
    modal.show();
}

// Load detail item untuk edit
function loadDetailItemForEdit(transaksiId) {
    fetch(`{{ route('manage.accounting.receipt-pos.show', ':id') }}`.replace(':id', transaksiId), {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.transaksi && data.transaksi.details) {
            renderEditRincianTable(data.transaksi.details);
        }
    })
    .catch(error => {
        console.error('Error loading detail item for edit:', error);
    });
}

// Render rincian table untuk edit
function renderEditRincianTable(items) {
    const tbody = document.querySelector('#editRincianTable tbody');
    tbody.innerHTML = '';
    
    let total = 0;
    
    items.forEach((item, index) => {
        const row = document.createElement('tr');
        const jumlah = parseFloat(item.jumlah) || 0;
        const keterangan = item.keterangan_item || item.keterangan || '-';
        
        row.innerHTML = `
            <td>${index + 1}</td>
            <td>
                <select class="form-select form-select-sm" name="pos_penerimaan[]" required>
                    <option value="">Pilih Pos</option>
                    @foreach($receiptPos as $pos)
                        <option value="{{ $pos->pos_id }}" ${item.pos_penerimaan_id == {{ $pos->pos_id }} ? 'selected' : ''}>{{ $pos->pos_name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="keterangan_item[]" value="${keterangan}" required>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm" name="jumlah_penerimaan[]" value="${jumlah}" min="0" step="1" required onchange="hitungTotalEdit()">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="hapusBarisEdit(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
        total += jumlah;
    });
    
    // Update total
    document.getElementById('editTotalPenerimaan').value = `Rp ${total.toLocaleString('id-ID')}`;
}

// Tambah baris untuk edit
function tambahBarisEdit() {
    const tbody = document.querySelector('#editRincianTable tbody');
    const currentRow = tbody.querySelectorAll('tr').length + 1;
    
    const row = document.createElement('tr');
    row.innerHTML = `
        <td>${currentRow}</td>
        <td>
            <select class="form-select form-select-sm" name="pos_penerimaan[]" required>
                <option value="">Pilih Pos</option>
                @foreach($receiptPos as $pos)
                    <option value="{{ $pos->pos_id }}">{{ $pos->pos_name }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm" name="keterangan_item[]" placeholder="Keterangan item" required>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm" name="jumlah_penerimaan[]" placeholder="0" min="0" step="1" required onchange="hitungTotalEdit()">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="hapusBarisEdit(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(row);
}

// Hapus baris untuk edit
function hapusBarisEdit(button) {
    const tbody = document.querySelector('#editRincianTable tbody');
    const rows = tbody.querySelectorAll('tr');
    
    if (rows.length > 1) {
        button.closest('tr').remove();
        updateNomorBarisEdit();
        hitungTotalEdit();
    } else {
        showModernToast('warning', 'Peringatan!', 'Minimal harus ada 1 baris rincian');
    }
}

// Update nomor baris untuk edit
function updateNomorBarisEdit() {
    const rows = document.querySelectorAll('#editRincianTable tbody tr');
    rows.forEach((row, index) => {
        row.cells[0].textContent = index + 1;
    });
}

// Hitung total untuk edit
function hitungTotalEdit() {
    const inputs = document.querySelectorAll('#editRincianTable input[name="jumlah_penerimaan[]"]');
    let total = 0;
    
    inputs.forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    
    document.getElementById('editTotalPenerimaan').value = `Rp ${total.toLocaleString('id-ID')}`;
}

// Simpan edit transaksi
function simpanEditTransaksi() {
    console.log('Memulai simpan edit transaksi...');
    console.log('Selected Transaksi ID:', selectedTransaksiId);
    
    const form = document.getElementById('formEditTransaksi');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Debug: cek data yang akan dikirim
    const formData = new FormData(form);
    console.log('Form data yang akan dikirim:');
    for (let [key, value] of formData.entries()) {
        console.log(key, value);
    }
    
    // Validasi tambahan
    const posPenerimaan = formData.getAll('pos_penerimaan[]');
    const keteranganItem = formData.getAll('keterangan_item[]');
    const jumlahPenerimaan = formData.getAll('jumlah_penerimaan[]');
    
    console.log('Validasi data:', { posPenerimaan, keteranganItem, jumlahPenerimaan });
    
    if (posPenerimaan.length === 0 || posPenerimaan.some(pos => pos === '')) {
        showModernToast('error', 'Error!', 'Semua pos penerimaan harus dipilih');
        return;
    }
    
    if (jumlahPenerimaan.some(jumlah => parseFloat(jumlah) <= 0)) {
        showModernToast('error', 'Error!', 'Semua jumlah penerimaan harus lebih dari 0');
        return;
    }
    
    // Cek CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    console.log('CSRF Token:', csrfToken);
    
    // Tampilkan loading
    showModernToast('info', 'Menyimpan...', 'Sedang menyimpan perubahan transaksi');
    
    fetch(`{{ route('manage.accounting.receipt-pos.update-transaksi', ':id') }}`.replace(':id', selectedTransaksiId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            showModernToast('success', 'Berhasil!', data.message);
            const modal = bootstrap.Modal.getInstance(document.getElementById('editTransaksiModal'));
            modal.hide();
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showModernToast('error', 'Error!', data.message || 'Gagal menyimpan perubahan');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showModernToast('error', 'Error!', 'Terjadi kesalahan saat menyimpan perubahan: ' + error.message);
    });
}

function hapusTransaksi() {
    if (!selectedTransaksiId) {
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
            deleteTransaction(selectedTransaksiId);
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
    fetch(`{{ route('manage.accounting.receipt-pos.destroy-transaksi', ':id') }}`.replace(':id', transactionId), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
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
            selectedTransaksiId = null;
            
            // Remove selection from table
            document.querySelectorAll('#transaksiTable tr').forEach(tr => {
                tr.classList.remove('table-active');
            });
            
            // Clear detail panel
            clearDetailPanel();
            
            // Reload data dari server
            setTimeout(() => {
                location.reload();
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
    // Reset semua field detail panel
    const detailFields = [
        'infoTglNo', 'infoOperator', 'infoTerimaDari', 
        'infoTahunAjaran', 'infoCaraTransaksi', 'infoKeterangan'
    ];
    
    detailFields.forEach(fieldId => {
        const element = document.getElementById(fieldId);
        if (element) {
            element.textContent = '-';
        }
    });
    
    // Reset total item
    const totalElement = document.getElementById('totalItemDetail');
    if (totalElement) {
        totalElement.textContent = 'Rp 0';
    }
    
    // Reset tabel item detail
    const tbody = document.getElementById('itemDetailTable');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-muted">Pilih transaksi untuk melihat detail</td>
            </tr>
        `;
    }
}

function cetakBukti() {
    if (!selectedTransaksiId) {
        showModernToast('warning', 'Peringatan!', 'Pilih transaksi yang akan dicetak');
        return;
    }
    
    // Ambil data dari panel detail transaksi yang sudah ada
    const tglNo = document.getElementById('infoTglNo')?.textContent || '-';
    const operator = document.getElementById('infoOperator')?.textContent || '-';
    const terimaDari = document.getElementById('infoTerimaDari')?.textContent || '-';
    const tahunAjaran = document.getElementById('infoTahunAjaran')?.textContent || '-';
    const caraTransaksi = document.getElementById('infoCaraTransaksi')?.textContent || '-';
    const keterangan = document.getElementById('infoKeterangan')?.textContent || '-';
    const totalItem = document.getElementById('totalItemDetail')?.textContent || 'Rp 0';
    
    // Debug: cek data yang tersedia
    console.log('Data untuk cetak:', { tglNo, operator, terimaDari, tahunAjaran, caraTransaksi, keterangan, totalItem });
    
    // Ambil data item detail dari tabel
    const itemRows = document.querySelectorAll('#itemDetailTable tr');
    console.log('Jumlah row item detail:', itemRows.length);
    
    const itemDetails = [];
    
    itemRows.forEach((row, index) => {
        const cells = row.cells;
        console.log(`Row ${index} cells:`, cells.length, cells);
        if (cells.length >= 4) {
            const item = {
                no: index + 1,
                pos: cells[1].textContent,
                keterangan: cells[2].textContent,
                jumlah: cells[3].textContent
            };
            itemDetails.push(item);
            console.log(`Item ${index + 1}:`, item);
        }
    });
    
    console.log('Item details yang dikumpulkan:', itemDetails);
    
    // Generate kuitansi dengan data yang sudah ada
    generateKuitansi({
        tglNo: tglNo,
        operator: operator,
        terimaDari: terimaDari,
        tahunAjaran: tahunAjaran,
        caraTransaksi: caraTransaksi,
        keterangan: keterangan,
        totalItem: totalItem,
        itemDetails: itemDetails
    });
    
    // Tampilkan modal preview
    const modal = new bootstrap.Modal(document.getElementById('previewKuitansiModal'));
    modal.show();
}

// Rincian table functions
function resetRincianTable() {
    const tbody = document.getElementById('rincianTable').querySelector('tbody');
    tbody.innerHTML = '';
    currentRow = 1;
    tambahBaris();
    hitungTotal();
}

function tambahBaris() {
    const tbody = document.getElementById('rincianTable').querySelector('tbody');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td>${currentRow}</td>
        <td>
            <select class="form-select form-select-sm" name="pos_penerimaan[]" required>
                <option value="">Pilih Pos</option>
                @foreach($receiptPos as $pos)
                    <option value="{{ $pos->pos_id }}">{{ $pos->pos_name }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm" name="keterangan_item[]" placeholder="Keterangan item" required>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm" name="jumlah_penerimaan[]" placeholder="0" min="0" step="1" required onchange="hitungTotal()">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="hapusBaris(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(row);
    currentRow++;
}

function hapusBaris(button) {
    if (document.getElementById('rincianTable').querySelectorAll('tbody tr').length > 1) {
        button.closest('tr').remove();
        updateNomorBaris();
        hitungTotal();
    } else {
        showModernToast('warning', 'Peringatan!', 'Minimal harus ada 1 baris');
    }
}

function updateNomorBaris() {
    const rows = document.getElementById('rincianTable').querySelectorAll('tbody tr');
    rows.forEach((row, index) => {
        row.cells[0].textContent = index + 1;
    });
    currentRow = rows.length + 1;
}

function hitungTotal() {
    const inputs = document.querySelectorAll('input[name="jumlah_penerimaan[]"]');
    let total = 0;
    let itemCount = 0;
    
    inputs.forEach(input => {
        const value = parseFloat(input.value) || 0;
        total += value;
        if (value > 0) itemCount++;
    });
    
    document.getElementById('totalPenerimaanModal').textContent = `Rp ${total.toLocaleString('id-ID')}`;
    
    // Debug: log total untuk memastikan perhitungan benar
    console.log('Total calculated:', total);
    console.log('Item count:', itemCount);
}

// Pos Penerimaan functions
function editPos(id, name, description) {
    document.getElementById('edit_pos_id').value = id;
    document.getElementById('edit_pos_name').value = name;
    document.getElementById('edit_pos_description').value = description;
    
    const modal = new bootstrap.Modal(document.getElementById('editPosModal'));
    modal.show();
}

function deletePos(id, name) {
    if (confirm(`Apakah Anda yakin ingin menghapus pos "${name}"?`)) {
        const url = `{{ route('manage.accounting.receipt-pos.destroy', ':id') }}`.replace(':id', id);
        console.log('Delete URL:', url);
        console.log('CSRF Token:', '{{ csrf_token() }}');
        
        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                showModernToast('success', 'Berhasil!', data.message);
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                // Hanya log error, tidak tampilkan toast
                console.error('Delete pos error:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Hapus toast error yang mengganggu
        });
    }
}

function updatePos() {
    const form = document.getElementById('formEditPos');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    const id = document.getElementById('edit_pos_id').value;
    
    const url = `{{ route('manage.accounting.receipt-pos.update', ':id') }}`.replace(':id', id);
    console.log('Update URL:', url);
    console.log('Form data:', Object.fromEntries(formData));
    
    fetch(url, {
        method: 'PUT',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Update response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Update response data:', data);
        if (data.success) {
            showModernToast('success', 'Berhasil!', data.message);
            bootstrap.Modal.getInstance(document.getElementById('editPosModal')).hide();
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showModernToast('error', 'Error!', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showModernToast('error', 'Error!', 'Terjadi kesalahan saat mengupdate data');
    });
}

// Form submission functions
function simpanTransaksi() {
    const form = document.getElementById('formTambahTransaksi');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Validasi minimal 1 item
    const itemRows = document.querySelectorAll('#rincianTable tbody tr');
    if (itemRows.length === 0) {
        showModernToast('warning', 'Peringatan!', 'Minimal harus ada 1 item');
        return;
    }
    
    // Buat FormData kosong (jangan gunakan form yang sudah ada karena ada field yang tidak diperlukan)
    const formData = new FormData();
    
    // Tambahkan data form utama
    formData.append('tanggal_penerimaan', document.getElementById('tanggal_penerimaan').value);
    formData.append('tahun_ajaran', document.getElementById('tahun_ajaran').value);
    formData.append('diterima_dari', document.getElementById('diterima_dari').value);
    formData.append('metode_pembayaran', document.getElementById('metode_pembayaran').value);
    formData.append('kas_id', document.getElementById('kas_id').value);
    formData.append('keterangan_transaksi', document.getElementById('keterangan_transaksi').value);
    
    // Tambahkan data rincian
    const posPenerimaan = [];
    const keteranganItem = [];
    const jumlahPenerimaan = [];
    
    itemRows.forEach(row => {
        const posSelect = row.querySelector('select[name="pos_penerimaan[]"]');
        const keteranganInput = row.querySelector('input[name="keterangan_item[]"]');
        const jumlahInput = row.querySelector('input[name="jumlah_penerimaan[]"]');
        
        if (posSelect && posSelect.value && jumlahInput && parseFloat(jumlahInput.value) > 0) {
            posPenerimaan.push(posSelect.value);
            keteranganItem.push(keteranganInput.value);
            jumlahPenerimaan.push(parseFloat(jumlahInput.value));
        }
    });
    
    // Tambahkan array data ke FormData
    posPenerimaan.forEach((pos, index) => {
        formData.append('pos_penerimaan[]', pos);
        formData.append('keterangan_item[]', keteranganItem[index]);
        formData.append('jumlah_penerimaan[]', jumlahPenerimaan[index]);
    });
    
    showModernToast('info', 'Menyimpan...', 'Menyimpan transaksi penerimaan');
    
    // Kirim data ke server
    fetch('{{ route("manage.accounting.receipt-pos.store-transaksi") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showModernToast('success', 'Berhasil!', data.message);
            bootstrap.Modal.getInstance(document.getElementById('tambahTransaksiModal')).hide();
            
            // Reload page untuk menampilkan data terbaru
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showModernToast('error', 'Error!', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showModernToast('error', 'Error!', 'Terjadi kesalahan saat menyimpan data');
    });
}

function simpanPosBaru() {
    const form = document.getElementById('formBuatPosBaru');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    
    // Show loading toast
    showModernToast('info', 'Menyimpan...', 'Menyimpan pos penerimaan baru');
    
    const url = '{{ route("manage.accounting.receipt-pos.store") }}';
    console.log('Store URL:', url);
    console.log('Form data:', Object.fromEntries(formData));
    
    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Store response status:', response.status);
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showModernToast('success', 'Berhasil!', 'Pos penerimaan baru berhasil dibuat!');
            bootstrap.Modal.getInstance(document.getElementById('buatPosBaruModal')).hide();
            
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
        showModernToast('error', 'Error!', 'Terjadi kesalahan saat menyimpan data');
    });
}

function setDefaultMetode() {
    const select = document.getElementById('metode_pembayaran');
    if (select.options.length > 1) {
        select.selectedIndex = 1;
    }
}

// Fungsi untuk konversi angka ke terbilang
function terbilang(angka) {
    if (angka === 0) return 'Nol Rupiah';
    
    const satuan = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh'];
    const belasan = ['', 'Sebelas', 'Dua Belas', 'Tiga Belas', 'Empat Belas', 'Lima Belas', 'Enam Belas', 'Tujuh Belas', 'Delapan Belas', 'Sembilan Belas'];
    const puluhan = ['', '', 'Dua Puluh', 'Tiga Puluh', 'Empat Puluh', 'Lima Puluh', 'Enam Puluh', 'Tujuh Puluh', 'Delapan Puluh', 'Sembilan Puluh'];
    
    function konversi(n) {
        if (n < 11) return satuan[n];
        if (n < 20) return belasan[n - 10];
        if (n < 100) return puluhan[Math.floor(n / 10)] + (n % 10 > 0 ? ' ' + satuan[n % 10] : '');
        if (n < 200) return 'Seratus ' + (n % 100 > 0 ? konversi(n % 100) : '');
        if (n < 1000) return satuan[Math.floor(n / 100)] + ' Ratus ' + (n % 100 > 0 ? konversi(n % 100) : '');
        if (n < 2000) return 'Seribu ' + (n % 1000 > 0 ? konversi(n % 1000) : '');
        if (n < 1000000) return konversi(Math.floor(n / 1000)) + ' Ribu ' + (n % 1000 > 0 ? konversi(n % 1000) : '');
        if (n < 2000000) return 'Satu Juta ' + (n % 1000000 > 0 ? konversi(n % 1000000) : '');
        if (n < 1000000000) return konversi(Math.floor(n / 1000000)) + ' Juta ' + (n % 1000000 > 0 ? konversi(n % 1000000) : '');
        return 'Angka terlalu besar';
    }
    
    return konversi(angka) + ' Rupiah';
}

// Fungsi untuk generate kuitansi
function generateKuitansi(data) {
    // Hitung total dari itemDetails
    let totalAmount = 0;
    if (data.itemDetails && data.itemDetails.length > 0) {
        totalAmount = data.itemDetails.reduce((sum, item) => {
            const amount = parseFloat(item.jumlah.replace(/[^\d]/g, '')) || 0;
            return sum + amount;
        }, 0);
    }
    
    const kuitansiContent = `
        <div style="font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; font-size: 12px; font-weight: normal;">
            <!-- Header Identitas Sekolah dan Judul Dokumen -->
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
                <!-- Identitas Sekolah -->
                <div style="flex: 1;">
                    <div style="font-weight: bold; font-size: 14px; margin-bottom: 5px;">${schoolProfileData ? schoolProfileData.nama_sekolah : 'NAMA LEMBAGA ANDA'}</div>
                    <div style="font-size: 12px; line-height: 1.2; margin-bottom: 5px;">
                        ${schoolProfileData ? schoolProfileData.alamat : 'Jl. Mohon Diisi Alamat Lembaga Anda RT.01/01 No.99 Desa Banjarmangu, Kecamatan Banjarmangu, Kabupaten Banjarnegara - Jawa Tengah'}
                    </div>
                    <div style="font-size: 12px; line-height: 1.1; margin-bottom: 2px;">
                        Telp: ${schoolProfileData ? schoolProfileData.no_telp : '0231-89988989'}
                    </div>
                </div>
                
                            <!-- Judul Dokumen dengan Border Solid -->
            <div style="flex: 0 0 auto; margin-left: 20px;">
                <div style="border: 2px solid #000; padding: 8px 15px; font-weight: bold; font-size: 14px; margin-bottom: 2px;">
                    BUKTI PENERIMAAN
                </div>
            </div>
        </div>
        
        <!-- Garis Lurus -->
        <div style="border-bottom: 2px solid #000; margin-bottom: 8px;"></div>
        
        <!-- Detail transaksi dalam 2 kolom -->
        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
            <div style="flex: 1;">
                <div style="margin-bottom: 7px;">
                    <span style="font-weight: normal;">Diterima dari:</span> ${data.terimaDari}
                </div>
                <div style="margin-bottom: 7px;">
                    <span style="font-weight: normal;">Cara Transaksi:</span> ${data.caraTransaksi}
                </div>
                <div style="margin-bottom: 2px;">
                    <span style="font-weight: normal;">Terbilang:</span> ${terbilang(totalAmount)}
                </div>
            </div>
            <div style="flex: 0 0 auto; margin-left: auto; margin-right: 70px; width: 250px;">
                <div style="margin-bottom: 7px;">
                    <span style="font-weight: normal;">Tgl. Transaksi:</span> ${data.tglNo.split(' / ')[0]}
                </div>
                <div style="margin-bottom: 7px;">
                    <span style="font-weight: normal;">Nomor Bukti:</span> ${data.tglNo.split(' / ')[1] || data.tglNo}
                </div>
                <div style="margin-bottom: 2px;">
                    <span style="font-weight: normal;">Petugas:</span> ${data.operator.split(' - ')[0] || data.operator}
                </div>
            </div>
        </div>
        
        <!-- Rincian transaksi dengan tabel -->
        <div style="margin-bottom: 2px;">
            <div style="margin-bottom: 2px; font-weight: bold;">Dengan rincian transaksi sebagai berikut:</div>
            <!-- Garis Lurus -->
            <div style="border-bottom: 2px solid #000; margin-bottom: 8px;"></div>
            
            <!-- Tabel Rincian -->
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
                <thead>
                    <tr style="background-color: #f5f5f5;">
                        <th style="padding: 8px; text-align: center; width: 60px; font-size: 12px; font-weight: bold;">NO</th>
                        <th style="padding: 8px; text-align: left; width: 200px; font-size: 12px; font-weight: bold;">POS PENERIMAAN</th>
                        <th style="padding: 8px; text-align: left; width: 250px; font-size: 12px; font-weight: bold;">KETERANGAN ITEM</th>
                        <th style="padding: 8px; text-align: right; width: 150px; font-size: 12px; font-weight: bold;">JUMLAH PENERIMAAN</th>
                    </tr>
                </thead>
                <tbody>
                    ${data.itemDetails.map((item, index) => `
                        <tr>
                            <td style="padding: 8px; text-align: center; font-size: 12px;">${item.no}</td>
                            <td style="padding: 8px; font-size: 12px;">${item.pos_penerimaan || 'Tes Pengeluaran'}</td>
                            <td style="padding: 8px; font-size: 12px;">${item.keterangan}</td>
                            <td style="padding: 8px; text-align: right; font-size: 12px;">${item.jumlah}</td>
                        </tr>
                    `).join('')}
                    
                    <!-- Total Row -->
                    <tr style="font-weight: bold; background-color: #f8f9fa;">
                        <td style="padding: 8px; text-align: center; font-size: 12px;" colspan="3">TOTAL</td>
                        <td style="padding: 8px; text-align: right; font-size: 12px;">${totalAmount.toLocaleString('id-ID')}</td>
                    </tr>
                </tbody>
            </table>
        </div>
            
            <!-- Tanda Tangan -->
            <div style="display: flex; justify-content: space-between; margin-top: 30px;">
                <div style="text-align: center;">
                    <div style="margin-bottom: 10px;"><span style="font-weight: bold;">Penyetor,</span></div>
                    <div style="margin-top: 20px;">_________________</div>
                </div>
                <div style="text-align: center;">
                    <div style="margin-bottom: 10px;"><span style="font-weight: bold;">Penerima,</span></div>
                    <div style="margin-top: 20px;">__________________</div>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('kuitansiContent').innerHTML = kuitansiContent;
}

// Fungsi untuk cetak kuitansi
function printKuitansi() {
    const printContent = document.getElementById('kuitansiContent').innerHTML;
    const originalContent = document.body.innerHTML;
    
    // Ganti konten body dengan kuitansi
    document.body.innerHTML = `
        <div style="padding: 20px;">
            ${printContent}
        </div>
    `;
    
    // Cetak
    window.print();
    
    // Kembalikan konten asli
    document.body.innerHTML = originalContent;
    
    // Re-initialize event listeners
    location.reload();
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey || e.altKey) return;
    
    switch(e.key) {
        case 'F2':
            e.preventDefault();
            tambahTransaksi();
            break;
        case 'F3':
            e.preventDefault();
            ubahTransaksi();
            break;
        case 'F4':
            e.preventDefault();
            hapusTransaksi();
            break;
        case 'F5':
            e.preventDefault();
            filterPeriode();
            break;
        case 'F6':
            e.preventDefault();
            cetakBukti();
            break;
        case 'F7':
            e.preventDefault();
            multiFilter();
            break;
        case 'F8':
            e.preventDefault();
            tambahCopy();
            break;
    }
});
</script>
@endsection