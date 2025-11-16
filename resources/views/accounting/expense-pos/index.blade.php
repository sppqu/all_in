@extends('layouts.adminty')

@section('title', 'Pos Pengeluaran')

@push('head')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

<style>
    .card-modern {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }
    
    .card-modern:hover {
        box-shadow: 0 4px 20px rgba(0,0,0,0.12);
    }
    
    .btn-action {
        border-radius: 8px;
        padding: 10px 20px;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
    }
    
    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .btn-success { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); }
    .btn-primary { background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); }
    .btn-warning { background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); }
    .btn-danger { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); }
    .btn-info { background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); }
    
    .table-modern {
        border-radius: 8px;
        overflow: hidden;
    }
    
    .table-modern thead {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .table-modern tbody tr {
        transition: all 0.2s ease;
    }
    
    .table-modern tbody tr:hover {
        background-color: #f8f9fa;
        transform: scale(1.01);
    }
    
    .table-modern tbody tr.selected {
        background-color: #e3f2fd;
        border-left: 4px solid #2196f3;
    }
    
    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .stat-card h3 {
        margin: 0;
        font-size: 2rem;
        font-weight: 700;
    }
    
    .stat-card p {
        margin: 5px 0 0 0;
        opacity: 0.9;
    }
    
    .modal-header-modern {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 0;
    }
    
    .form-control-modern {
        border-radius: 8px;
        border: 1px solid #e0e0e0;
        padding: 10px 15px;
        transition: all 0.3s ease;
    }
    
    .form-control-modern:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .badge-modern {
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 600;
    }
    
    /* Table Styling - Putih dan font lebih besar */
    #transaksiTableMain {
        font-size: 1rem;
    }
    
    #transaksiTableMain thead {
        background-color: #ffffff !important;
        border-bottom: 2px solid #dee2e6;
    }
    
    #transaksiTableMain thead th {
        background-color: #ffffff !important;
        color: #333 !important;
        font-weight: 600;
        font-size: 1rem;
        padding: 0.75rem;
        border-bottom: 2px solid #dee2e6;
    }
    
    #transaksiTableMain tbody tr {
        background-color: #ffffff !important;
    }
    
    #transaksiTableMain tbody td {
        background-color: #ffffff !important;
        color: #333 !important;
        font-size: 1rem;
        padding: 0.75rem;
        border-bottom: 1px solid #f0f0f0;
    }
    
    #transaksiTableMain tbody tr.table-row-hover:hover {
        background-color: rgba(0, 123, 255, 0.1) !important;
    }
    
    #transaksiTableMain tbody tr.selected {
        background-color: rgba(0, 123, 255, 0.2) !important;
        border-left: 4px solid #007bff;
    }
    
    /* Pastikan panel sejajar horizontal */
    .container-fluid > .row:first-child {
        display: flex;
        flex-wrap: wrap;
    }
    
    .container-fluid > .row:first-child > .col-md-6 {
        display: flex;
        flex-direction: column;
    }
    
    .container-fluid > .row:first-child > .col-md-6 > .card {
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .container-fluid > .row:first-child > .col-md-6 > .card > .card-body {
        flex: 1;
    }
</style>

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Panel Kiri: Daftar Transaksi -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white" style="color: #ffffff !important;">
                    <h5 class="mb-0" style="color: #ffffff !important;">
                        <span style="color: #ffffff !important;">Transaksi Pengeluaran Lain</span>
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Filter Section -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-primary" onclick="handleFilter()">
                                <i class="fas fa-filter me-1"></i>Filter Periode [F5]
                            </button>
                        </div>
                    </div>

                    <!-- Tabel Transaksi -->
                    <div class="table-responsive">
                        <table class="table table-hover" id="transaksiTableMain">
                            <thead>
                                <tr>
                                    <th>NO.</th>
                                    <th>TANGGAL</th>
                                    <th>NO. TRANSAKSI</th>
                                    <th>DIBAYAR KE</th>
                                    <th>KETERANGAN</th>
                                    <th>JUMLAH PENGELUARAN</th>
                                </tr>
                            </thead>
                            <tbody id="transaksiTable">
                                @foreach($transactions as $index => $transaksi)
                                <tr class="cursor-pointer table-row-hover" onclick="selectRow(this, {{ $transaksi->id }})" data-transaksi-id="{{ $transaksi->id }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ date('d-m-Y', strtotime($transaksi->tanggal)) }}</td>
                                    <td>{{ $transaksi->no_transaksi }}</td>
                                    <td>{{ $transaksi->dibayar_ke ?? '-' }}</td>
                                    <td>{{ $transaksi->keterangan ?? '-' }}</td>
                                    <td class="text-end"><strong class="text-danger">Rp {{ number_format($transaksi->jumlah_pengeluaran, 0, ',', '.') }}</strong></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Kanan: Detail Transaksi -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white" style="color: #ffffff !important;">
                    <h5 class="mb-0" style="color: #ffffff !important;">
                        <span style="color: #ffffff !important;">Detail Transaksi</span>
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
                            <div class="col-4"><strong class="text-dark">Dibayar Ke:</strong></div>
                            <div class="col-8" id="infoDibayarKe">-</div>
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
                            <thead>
                                <tr>
                                    <th>NO</th>
                                    <th>POS PENGELUARAN</th>
                                    <th>KETERANGAN ITEM</th>
                                    <th>JUMLAH</th>
                                </tr>
                            </thead>
                            <tbody id="itemDetailTable">
                                <!-- Data akan diisi via JavaScript -->
                            </tbody>
                        </table>
                    </div>
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
                        <button type="button" class="btn btn-success me-2" onclick="handleTambah()">
                            <i class="fa fa-plus me-1"></i>Tambah [F2]
                        </button>
                        <button type="button" class="btn btn-primary me-2" onclick="handleTambahCopy()">
                            <i class="fa fa-copy me-1"></i>Tambah + Copy [F8]
                        </button>
                        <button type="button" class="btn btn-warning me-2" onclick="handleUbah()">
                            <i class="fa fa-edit me-1"></i>Ubah [F3]
                        </button>
                        <button type="button" class="btn btn-danger me-2" onclick="handleHapus()">
                            <i class="fa fa-trash me-1"></i>Hapus [F4]
                        </button>
                        <button type="button" class="btn btn-info me-2" onclick="handleCetak()">
                            <i class="fa fa-print me-1"></i>Cetak Bukti [F6]
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Transaksi -->
<div class="modal fade" id="modalTambah" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-modern">
                <h5 class="modal-title"><i class="fa fa-plus me-2"></i>Tambah Transaksi Pengeluaran</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formTambah">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Tanggal Pengeluaran <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_pengeluaran" class="form-control form-control-modern" required>
                            </div>
                        <div class="col-md-6 mb-3">
                            <label>No. Transaksi <span class="text-danger">*</span></label>
                            <input type="text" name="no_transaksi" class="form-control form-control-modern" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Pos Pengeluaran <span class="text-danger">*</span></label>
                                <div class="input-group">
                                <select name="pos_pengeluaran_id" id="tambah_pos_pengeluaran_id" class="form-control form-control-modern" required>
                                    <option value="">Pilih Pos Pengeluaran</option>
                                    @foreach($expensePos as $pos)
                                    <option value="{{ $pos->pos_id ?? $pos->id }}">{{ $pos->pos_name }}</option>
                                        @endforeach
                                    </select>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-success" onclick="openModalTambahPos()" title="Tambah Pos Pengeluaran Baru">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Kas <span class="text-danger">*</span></label>
                            <select name="kas_id" class="form-control form-control-modern" required>
                                    <option value="">Pilih Kas</option>
                                @foreach($kasList as $kas)
                                        <option value="{{ $kas->id }}">{{ $kas->nama_kas }}</option>
                                    @endforeach
                                </select>
                            </div>
                        <div class="col-md-6 mb-3">
                            <label>Metode Pembayaran <span class="text-danger">*</span></label>
                            <select name="metode_pembayaran_id" class="form-control form-control-modern" required>
                                <option value="">Pilih Metode Pembayaran</option>
                                @foreach($paymentMethods as $method)
                                            <option value="{{ $method->id }}">{{ $method->nama_metode }}</option>
                                        @endforeach
                                    </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Dibayar Ke <span class="text-danger">*</span></label>
                            <input type="text" name="dibayar_ke" class="form-control form-control-modern" maxlength="100" placeholder="Masukkan nama penerima pembayaran" required>
                            </div>
                        <div class="col-md-6 mb-3">
                            <label>Tahun Ajaran <span class="text-danger">*</span></label>
                            <input type="text" name="tahun_ajaran" class="form-control form-control-modern" maxlength="20" placeholder="Contoh: 2024/2025" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label>Keterangan Transaksi</label>
                            <textarea name="keterangan_transaksi" class="form-control form-control-modern" rows="2" maxlength="500" placeholder="Masukkan keterangan transaksi"></textarea>
                        </div>
                    </div>

                    <!-- Tabel Rincian Pengeluaran -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="mb-3"><i class="fa fa-list me-2"></i>Rincian Pengeluaran</h6>
                        <div class="table-responsive">
                                <table class="table table-bordered table-sm" id="rincianTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 35px;">No</th>
                                            <th>POS Sumber Penerimaan</th>
                                            <th>Keterangan Item</th>
                                            <th style="width: 250px;">Nominal</th>
                                            <th style="width: 80px;">Aksi</th>
                                    </tr>
                                </thead>
                                    <tbody id="rincianTableBody">
                                        <!-- Baris akan ditambahkan via JavaScript -->
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-right"><strong>Total:</strong></td>
                                            <td><strong id="totalRincian" class="text-danger">Rp 0</strong></td>
                                            <td></td>
                                    </tr>
                                    </tfoot>
                            </table>
                        </div>
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-primary" onclick="tambahBarisRincian()">
                                    <i class="fa fa-plus me-1"></i>Tambah Baris
                                </button>
                            </div>
                        </div>
                    </div>
                    </div>
            
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save me-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Transaksi -->
<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-modern">
                <h5 class="modal-title"><i class="fa fa-edit me-2"></i>Ubah Transaksi Pengeluaran</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                                </button>
            </div>
            <form id="formEdit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Tanggal Pengeluaran <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_pengeluaran" id="edit_tanggal" class="form-control form-control-modern" required>
                            </div>
                        <div class="col-md-6 mb-3">
                            <label>No. Transaksi <span class="text-danger">*</span></label>
                            <input type="text" name="no_transaksi" id="edit_no_transaksi" class="form-control form-control-modern" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Pos Pengeluaran <span class="text-danger">*</span></label>
                                <div class="input-group">
                                <select name="pos_pengeluaran_id" id="edit_pos_id" class="form-control form-control-modern" required>
                                    <option value="">Pilih Pos Pengeluaran</option>
                                    @foreach($expensePos as $pos)
                                    <option value="{{ $pos->pos_id ?? $pos->id }}">{{ $pos->pos_name }}</option>
                                        @endforeach
                                    </select>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-success" onclick="openModalTambahPos()" title="Tambah Pos Pengeluaran Baru">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Kas <span class="text-danger">*</span></label>
                            <select name="kas_id" id="edit_kas_id" class="form-control form-control-modern" required>
                                    <option value="">Pilih Kas</option>
                                @foreach($kasList as $kas)
                                        <option value="{{ $kas->id }}">{{ $kas->nama_kas }}</option>
                                    @endforeach
                                </select>
                            </div>
                        <div class="col-md-6 mb-3">
                            <label>Metode Pembayaran <span class="text-danger">*</span></label>
                            <select name="metode_pembayaran_id" id="edit_metode_pembayaran_id" class="form-control form-control-modern" required>
                                <option value="">Pilih Metode Pembayaran</option>
                                @foreach($paymentMethods as $method)
                                <option value="{{ $method->id }}">{{ $method->nama_metode }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Dibayar Ke <span class="text-danger">*</span></label>
                            <input type="text" name="dibayar_ke" id="edit_dibayar_ke" class="form-control form-control-modern" maxlength="100" placeholder="Masukkan nama penerima pembayaran" required>
                    </div>
                        <div class="col-md-6 mb-3">
                            <label>Tahun Ajaran <span class="text-danger">*</span></label>
                            <input type="text" name="tahun_ajaran" id="edit_tahun_ajaran" class="form-control form-control-modern" maxlength="20" placeholder="Contoh: 2024/2025" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label>Keterangan Transaksi</label>
                            <textarea name="keterangan_transaksi" id="edit_keterangan_transaksi" class="form-control form-control-modern" rows="2" maxlength="500" placeholder="Masukkan keterangan transaksi"></textarea>
                        </div>
                    </div>

                    <!-- Tabel Rincian Pengeluaran -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="mb-3"><i class="fa fa-list me-2"></i>Rincian Pengeluaran</h6>
                        <div class="table-responsive">
                                <table class="table table-bordered table-sm" id="rincianTableEdit">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 35px;">No</th>
                                            <th>POS Sumber Penerimaan</th>
                                            <th>Keterangan Item</th>
                                            <th style="width: 250px;">Nominal</th>
                                            <th style="width: 80px;">Aksi</th>
                                    </tr>
                                </thead>
                                    <tbody id="rincianTableBodyEdit">
                                        <!-- Baris akan ditambahkan via JavaScript -->
                                </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-right"><strong>Total:</strong></td>
                                            <td><strong id="totalRincianEdit" class="text-danger">Rp 0</strong></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                            </table>
                        </div>
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-primary" onclick="tambahBarisRincianEdit()">
                                    <i class="fa fa-plus me-1"></i>Tambah Baris
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fa fa-save me-2"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tambah Pos Pengeluaran -->
<div class="modal fade" id="modalTambahPos" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-modern">
                <h5 class="modal-title"><i class="fa fa-plus-circle me-2"></i>Tambah Pos Pengeluaran Baru</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formTambahPos">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nama Pos Pengeluaran <span class="text-danger">*</span></label>
                        <input type="text" name="nama_pos_pengeluaran_baru" class="form-control form-control-modern" required maxlength="100" placeholder="Masukkan nama pos pengeluaran">
                    </div>
                    <div class="mb-3">
                        <label>Keterangan</label>
                        <textarea name="keterangan_pos_pengeluaran_baru" class="form-control form-control-modern" rows="3" maxlength="100" placeholder="Masukkan keterangan (opsional)"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Status <span class="text-danger">*</span></label>
                        <select name="status_pos_pengeluaran_baru" class="form-control form-control-modern" required>
                            <option value="1">Aktif</option>
                            <option value="0">Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save me-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Filter -->
<div class="modal fade" id="modalFilter" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-modern">
                <h5 class="modal-title"><i class="fa fa-filter me-2"></i>Filter Periode</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formFilter" method="GET" action="{{ route('manage.accounting.expense-pos.index') }}">
            <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Tanggal Mulai</label>
                            <input type="date" name="start_date" value="{{ $startDate }}" class="form-control form-control-modern" required>
                    </div>
                        <div class="col-md-6 mb-3">
                            <label>Tanggal Akhir</label>
                            <input type="date" name="end_date" value="{{ $endDate }}" class="form-control form-control-modern" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-filter me-2"></i>Terapkan Filter
                </button>
                    </div>
                </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let selectedTransaksiId = null;
let transactions = @json($transactions);
let rincianRowCount = 0;
let rincianRowCountEdit = 0;
let receiptPos = @json($receiptPos);
let expensePos = @json($expensePos);

// Initialize
$(document).ready(function() {
    updateStatistics();
    loadNextNumber();
    initRincianTable();
    
    // Initialize detail panel
    const tbody = document.getElementById('itemDetailTable');
    if (tbody && tbody.children.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-muted">Pilih transaksi untuk melihat detail</td>
            </tr>
        `;
    }
    
    // Form submit handlers
    $('#formTambah').on('submit', handleSimpanTambah);
    $('#formEdit').on('submit', handleSimpanEdit);
    $('#formTambahPos').on('submit', handleSimpanPos);
    
    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        if (e.ctrlKey || e.altKey) return;
        switch(e.key) {
            case 'F2': e.preventDefault(); handleTambah(); break;
            case 'F3': e.preventDefault(); handleUbah(); break;
            case 'F4': e.preventDefault(); handleHapus(); break;
            case 'F6': e.preventDefault(); handleCetak(); break;
            case 'F8': e.preventDefault(); handleTambahCopy(); break;
        }
    });
});

// Update statistics
function updateStatistics() {
    const total = transactions.length;
    const totalAmount = transactions.reduce((sum, t) => sum + parseFloat(t.jumlah_pengeluaran || 0), 0);
    
    $('#totalTransaksi').text(total);
    $('#totalPengeluaran').text(formatCurrency(totalAmount));
}

// Format currency
function formatCurrency(amount) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

// Load next transaction number
function loadNextNumber() {
    fetch('{{ route("manage.accounting.expense-transactions.get-next-number") }}', {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => {
        const contentType = res.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Response is not JSON');
        }
        return res.json();
    })
    .then(data => {
        if (data.success) {
            $('input[name="no_transaksi"]').val(data.next_number);
        }
    })
    .catch(err => {
        console.error('Error loading next number:', err);
    });
}

// Select row
function selectRow(element, id) {
    $('#transaksiTableMain tbody tr').removeClass('selected');
    $(element).addClass('selected');
    selectedTransaksiId = id;
    loadDetail(id);
}

// Load detail
function loadDetail(id) {
    fetch(`{{ route('manage.accounting.expense-transactions.details', ':id') }}`.replace(':id', id), {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => {
        const contentType = res.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return res.text().then(text => {
                console.error('Non-JSON response:', text);
                throw new Error('Server returned non-JSON response. Check console for details.');
            });
        }
        return res.json();
    })
    .then(data => {
        if (data.success) {
            const t = data.transaction;
            
            // Update informasi transaksi
            const tanggal = t.tanggal_pengeluaran || t.tanggal || '-';
            const tanggalFormatted = tanggal !== '-' ? formatDate(tanggal) : '-';
            document.getElementById('infoTglNo').textContent = `${tanggalFormatted} / ${t.no_transaksi || '-'}`;
            document.getElementById('infoOperator').textContent = t.operator || t.operator_name || '-';
            document.getElementById('infoDibayarKe').textContent = t.dibayar_ke || '-';
            document.getElementById('infoTahunAjaran').textContent = t.tahun_ajaran || '-';
            document.getElementById('infoCaraTransaksi').textContent = t.metode_pembayaran_name || t.metode_pembayaran || '-';
            document.getElementById('infoKas').textContent = t.kas_name || t.kas || '-';
            document.getElementById('infoKeterangan').textContent = t.keterangan || t.keterangan_transaksi || '-';

            // Load detail item
            if (data.details && data.details.length > 0) {
                renderItemDetailTable(data.details);
            } else {
                const tbody = document.getElementById('itemDetailTable');
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center text-muted">Tidak ada detail item</td>
                    </tr>
                `;
            }
        }
    })
    .catch(err => {
        console.error('Error loading detail:', err);
        // Reset semua field ke default
        document.getElementById('infoTglNo').textContent = '-';
        document.getElementById('infoOperator').textContent = '-';
        document.getElementById('infoDibayarKe').textContent = '-';
        document.getElementById('infoTahunAjaran').textContent = '-';
        document.getElementById('infoCaraTransaksi').textContent = '-';
        document.getElementById('infoKas').textContent = '-';
        document.getElementById('infoKeterangan').textContent = '-';
        
        const tbody = document.getElementById('itemDetailTable');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-muted">Gagal memuat detail transaksi</td>
                </tr>
            `;
        }
    });
}

// Render item detail table
function renderItemDetailTable(items) {
    const tbody = document.getElementById('itemDetailTable');
    tbody.innerHTML = '';
    
    items.forEach((item, index) => {
        const row = document.createElement('tr');
        const jumlah = parseFloat(item.jumlah_pengeluaran || item.jumlah || 0);
        const keterangan = item.keterangan_item || item.keterangan || '-';
        
        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${item.pos_pengeluaran_name || item.pos_sumber_dana_name || '-'}</td>
            <td>${keterangan}</td>
            <td class="text-end">Rp ${jumlah.toLocaleString('id-ID')}</td>
        `;
        tbody.appendChild(row);
    });
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
}

// Initialize Rincian Table
function initRincianTable() {
    rincianRowCount = 0;
    $('#rincianTableBody').empty();
    tambahBarisRincian();
}

// Tambah Baris Rincian
function tambahBarisRincian() {
    rincianRowCount++;
    const row = `
        <tr data-row="${rincianRowCount}">
            <td>${rincianRowCount}</td>
            <td>
                <select name="pos_sumber_dana[]" class="form-control form-control-sm" required>
                    <option value="">Pilih Pos Sumber Penerimaan</option>
                    ${receiptPos.map(pos => `<option value="${pos.pos_id}">${pos.pos_name}</option>`).join('')}
                </select>
            </td>
            <td>
                <input type="text" name="keterangan_item[]" class="form-control form-control-sm" placeholder="Keterangan item" required>
            </td>
            <td>
                <input type="number" name="jumlah_pengeluaran[]" class="form-control form-control-sm jumlah-input" step="0.01" min="0" value="0" required onchange="hitungTotalRincian()">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger" onclick="hapusBarisRincian(this)" ${rincianRowCount === 1 ? 'disabled' : ''}>
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        </tr>
    `;
    $('#rincianTableBody').append(row);
    hitungTotalRincian();
}

// Hapus Baris Rincian
function hapusBarisRincian(button) {
    $(button).closest('tr').remove();
    updateNomorBarisRincian();
    hitungTotalRincian();
}

// Update Nomor Baris
function updateNomorBarisRincian() {
    $('#rincianTableBody tr').each(function(index) {
        $(this).find('td:first').text(index + 1);
        $(this).attr('data-row', index + 1);
        
        // Enable/disable tombol hapus
        const deleteBtn = $(this).find('button');
        if ($('#rincianTableBody tr').length === 1) {
            deleteBtn.prop('disabled', true);
    } else {
            deleteBtn.prop('disabled', false);
        }
    });
    rincianRowCount = $('#rincianTableBody tr').length;
}

// Hitung Total Rincian
function hitungTotalRincian() {
    let total = 0;
    $('.jumlah-input').each(function() {
        const value = parseFloat($(this).val()) || 0;
            total += value;
    });
    $('#totalRincian').text(formatCurrency(total));
}

// Handle Tambah
function handleTambah() {
    $('#formTambah')[0].reset();
    loadNextNumber();
    $('input[name="tanggal_pengeluaran"]').val(new Date().toISOString().split('T')[0]);
    initRincianTable();
    $('#modalTambah').modal('show');
}

// Handle Tambah Copy
function handleTambahCopy() {
    if (!selectedTransaksiId) {
        Swal.fire('Peringatan', 'Pilih transaksi yang akan di-copy terlebih dahulu', 'warning');
        return;
    }
    Swal.fire('Info', 'Fitur copy transaksi akan segera tersedia', 'info');
}

// Initialize Rincian Table Edit
function initRincianTableEdit() {
    rincianRowCountEdit = 0;
    $('#rincianTableBodyEdit').empty();
}

// Tambah Baris Rincian Edit
function tambahBarisRincianEdit() {
    rincianRowCountEdit++;
    const row = `
        <tr data-row="${rincianRowCountEdit}">
            <td>${rincianRowCountEdit}</td>
            <td>
                <select name="pos_sumber_dana[]" class="form-control form-control-sm" required>
                    <option value="">Pilih Pos Sumber Penerimaan</option>
                    ${receiptPos.map(pos => `<option value="${pos.pos_id}">${pos.pos_name}</option>`).join('')}
                </select>
            </td>
            <td>
                <input type="text" name="keterangan_item[]" class="form-control form-control-sm" placeholder="Keterangan item" required>
            </td>
            <td>
                <input type="number" name="jumlah_pengeluaran[]" class="form-control form-control-sm jumlah-input-edit" step="0.01" min="0" value="0" required onchange="hitungTotalRincianEdit()">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger" onclick="hapusBarisRincianEdit(this)" ${rincianRowCountEdit === 1 ? 'disabled' : ''}>
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        </tr>
    `;
    $('#rincianTableBodyEdit').append(row);
    hitungTotalRincianEdit();
}

// Hapus Baris Rincian Edit
function hapusBarisRincianEdit(button) {
    $(button).closest('tr').remove();
    updateNomorBarisRincianEdit();
    hitungTotalRincianEdit();
}

// Update Nomor Baris Edit
function updateNomorBarisRincianEdit() {
    $('#rincianTableBodyEdit tr').each(function(index) {
        $(this).find('td:first').text(index + 1);
        $(this).attr('data-row', index + 1);
        
        // Enable/disable tombol hapus
        const deleteBtn = $(this).find('button');
        if ($('#rincianTableBodyEdit tr').length === 1) {
            deleteBtn.prop('disabled', true);
        } else {
            deleteBtn.prop('disabled', false);
        }
    });
    rincianRowCountEdit = $('#rincianTableBodyEdit tr').length;
}

// Hitung Total Rincian Edit
function hitungTotalRincianEdit() {
    let total = 0;
    $('.jumlah-input-edit').each(function() {
        const value = parseFloat($(this).val()) || 0;
        total += value;
    });
    $('#totalRincianEdit').text(formatCurrency(total));
}

// Load Rincian untuk Edit
function loadRincianForEdit(details) {
    initRincianTableEdit();
    if (details && details.length > 0) {
        details.forEach((detail, index) => {
            rincianRowCountEdit++;
            const row = `
                <tr data-row="${rincianRowCountEdit}">
                    <td>${rincianRowCountEdit}</td>
                    <td>
                        <select name="pos_sumber_dana[]" class="form-control form-control-sm" required>
                            <option value="">Pilih Pos Sumber Penerimaan</option>
                            ${receiptPos.map(pos => `<option value="${pos.pos_id}" ${pos.pos_id == detail.pos_sumber_dana_id ? 'selected' : ''}>${pos.pos_name}</option>`).join('')}
                        </select>
                    </td>
                    <td>
                        <input type="text" name="keterangan_item[]" class="form-control form-control-sm" placeholder="Keterangan item" value="${detail.keterangan_item || ''}" required>
                    </td>
                    <td>
                        <input type="number" name="jumlah_pengeluaran[]" class="form-control form-control-sm jumlah-input-edit" step="0.01" min="0" value="${detail.jumlah || 0}" required onchange="hitungTotalRincianEdit()">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger" onclick="hapusBarisRincianEdit(this)">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
            $('#rincianTableBodyEdit').append(row);
        });
            } else {
        tambahBarisRincianEdit();
    }
    hitungTotalRincianEdit();
    updateNomorBarisRincianEdit();
}

// Handle Ubah
function handleUbah() {
    if (!selectedTransaksiId) {
        Swal.fire('Peringatan', 'Pilih transaksi yang akan diubah terlebih dahulu', 'warning');
        return;
    }
    
    fetch(`{{ route('manage.accounting.expense-transactions.details', ':id') }}`.replace(':id', selectedTransaksiId), {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => {
        const contentType = res.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return res.text().then(text => {
                console.error('Non-JSON response:', text);
                throw new Error('Server returned non-JSON response. Check console for details.');
            });
        }
        return res.json();
    })
    .then(data => {
        if (data.success) {
            const t = data.transaction;
            $('#edit_id').val(t.id);
            $('#edit_tanggal').val(t.tanggal_pengeluaran || t.tanggal);
            $('#edit_no_transaksi').val(t.no_transaksi);
            $('#edit_pos_id').val(t.pos_pengeluaran_id || '');
            $('#edit_kas_id').val(t.kas_id || '');
            $('#edit_metode_pembayaran_id').val(t.metode_pembayaran_id || '');
            $('#edit_dibayar_ke').val(t.dibayar_ke || '');
            $('#edit_tahun_ajaran').val(t.tahun_ajaran || '');
            $('#edit_keterangan_transaksi').val(t.keterangan_transaksi || t.keterangan || '');
            
            // Load rincian
            loadRincianForEdit(data.details || []);
            
            $('#modalEdit').modal('show');
        }
    })
    .catch(err => {
        console.error('Error loading transaction:', err);
        Swal.fire('Error', 'Gagal memuat data transaksi', 'error');
    });
}

// Handle Hapus
function handleHapus() {
    if (!selectedTransaksiId) {
        Swal.fire('Peringatan', 'Pilih transaksi yang akan dihapus terlebih dahulu', 'warning');
        return;
    }
    
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: 'Apakah Anda yakin ingin menghapus transaksi ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            deleteTransaction(selectedTransaksiId);
        }
    });
}

// Delete transaction
function deleteTransaction(id) {
    fetch(`{{ route('manage.accounting.expense-transactions.destroy', ':id') }}`.replace(':id', id), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(res => {
        const contentType = res.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return res.text().then(text => {
                console.error('Non-JSON response:', text);
                throw new Error('Server returned non-JSON response. Check console for details.');
            });
        }
        return res.json();
    })
    .then(data => {
        if (data.success) {
            Swal.fire('Berhasil', 'Transaksi berhasil dihapus', 'success').then(() => {
                location.reload();
            });
        } else {
            Swal.fire('Error', data.message || 'Gagal menghapus transaksi', 'error');
        }
    })
    .catch(err => {
        console.error('Error deleting transaction:', err);
        Swal.fire('Error', 'Terjadi kesalahan saat menghapus transaksi', 'error');
    });
}

// Handle Cetak
function handleCetak() {
    if (!selectedTransaksiId) {
        Swal.fire('Peringatan', 'Pilih transaksi yang akan dicetak terlebih dahulu', 'warning');
        return;
    }
    window.open(`{{ route('manage.accounting.expense-transactions.print', ':id') }}`.replace(':id', selectedTransaksiId), '_blank');
}

// Handle Filter
function handleFilter() {
    $('#modalFilter').modal('show');
}

// Handle Simpan Tambah
function handleSimpanTambah(e) {
    e.preventDefault();
    
    // Validasi minimal 1 baris rincian
    if ($('#rincianTableBody tr').length === 0) {
        if (typeof showToast !== 'undefined') {
            showToast('warning', 'Peringatan', 'Minimal harus ada 1 baris rincian');
                } else {
            alert('Minimal harus ada 1 baris rincian');
        }
        return;
    }
    
    // Validasi pos pengeluaran harus dipilih
    const posPengeluaranSelect = $('#tambah_pos_pengeluaran_id');
    const selectedPosPengeluaran = posPengeluaranSelect.val();
    if (!selectedPosPengeluaran) {
        if (typeof showToast !== 'undefined') {
            showToast('warning', 'Peringatan', 'Pilih Pos Pengeluaran terlebih dahulu');
    } else {
            alert('Pilih Pos Pengeluaran terlebih dahulu');
        }
        return;
    }
    
    // Validasi semua baris rincian harus lengkap
    let isValid = true;
    let errorMsg = '';
    $('#rincianTableBody tr').each(function(index) {
        const posSumberDana = $(this).find('select[name="pos_sumber_dana[]"]').val();
        const keterangan = $(this).find('input[name="keterangan_item[]"]').val();
        const jumlah = $(this).find('input[name="jumlah_pengeluaran[]"]').val();
        
        if (!posSumberDana) {
            isValid = false;
            errorMsg = `Baris ${index + 1}: Pilih POS Sumber Penerimaan`;
            return false;
        }
        if (!keterangan || keterangan.trim() === '') {
            isValid = false;
            errorMsg = `Baris ${index + 1}: Isi keterangan item`;
            return false;
        }
        if (!jumlah || parseFloat(jumlah) <= 0) {
            isValid = false;
            errorMsg = `Baris ${index + 1}: Nominal harus lebih dari 0`;
            return false;
        }
    });
    
    if (!isValid) {
        if (typeof showToast !== 'undefined') {
            showToast('warning', 'Peringatan', errorMsg);
        } else {
            alert(errorMsg);
        }
        return;
    }
    
    const formData = new FormData(e.target);
    
    // Tambahkan pos_pengeluaran untuk setiap baris rincian (dari dropdown utama)
    $('#rincianTableBody tr').each(function() {
        formData.append('pos_pengeluaran[]', selectedPosPengeluaran);
    });
    
    // Debug: Log form data
    console.log('Form Data:');
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
    
    fetch('{{ route("manage.accounting.expense-transactions.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(res => {
        const contentType = res.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return res.text().then(text => {
                console.error('Non-JSON response:', text);
                throw new Error('Server returned non-JSON response. Check console for details.');
            });
        }
        if (!res.ok) {
            return res.json().then(data => {
                throw new Error(data.message || 'Network response was not ok');
            });
        }
        return res.json();
    })
    .then(data => {
        console.log('Response:', data);
        if (data.success) {
            if (typeof showToast !== 'undefined') {
                showToast('success', 'Berhasil', data.message || 'Transaksi berhasil ditambahkan');
            }
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            const errorMessage = data.message || 'Gagal menambahkan transaksi';
            console.error('Error:', errorMessage);
            if (typeof showToast !== 'undefined') {
                showToast('error', 'Error', errorMessage);
            } else {
                alert(errorMessage);
            }
        }
    })
    .catch(err => {
        console.error('Error saving transaction:', err);
        const errorMessage = err.message || 'Terjadi kesalahan saat menyimpan transaksi';
        if (typeof showToast !== 'undefined') {
            showToast('error', 'Error', errorMessage);
    } else {
            alert(errorMessage);
        }
    });
}

// Handle Simpan Edit
function handleSimpanEdit(e) {
    e.preventDefault();
    
    // Validasi minimal 1 baris rincian
    if ($('#rincianTableBodyEdit tr').length === 0) {
        if (typeof showToast !== 'undefined') {
            showToast('warning', 'Peringatan', 'Minimal harus ada 1 baris rincian');
        } else {
            alert('Minimal harus ada 1 baris rincian');
        }
        return;
    }
    
    // Validasi pos pengeluaran harus dipilih
    const posPengeluaranSelect = $('#edit_pos_id');
    const selectedPosPengeluaran = posPengeluaranSelect.val();
    if (!selectedPosPengeluaran) {
        if (typeof showToast !== 'undefined') {
            showToast('warning', 'Peringatan', 'Pilih Pos Pengeluaran terlebih dahulu');
        } else {
            alert('Pilih Pos Pengeluaran terlebih dahulu');
        }
        return;
    }
    
    // Validasi semua baris rincian harus lengkap
    let isValid = true;
    let errorMsg = '';
    $('#rincianTableBodyEdit tr').each(function(index) {
        const posSumberDana = $(this).find('select[name="pos_sumber_dana[]"]').val();
        const keterangan = $(this).find('input[name="keterangan_item[]"]').val();
        const jumlah = $(this).find('input[name="jumlah_pengeluaran[]"]').val();
        
        if (!posSumberDana) {
            isValid = false;
            errorMsg = `Baris ${index + 1}: Pilih POS Sumber Penerimaan`;
            return false;
        }
        if (!keterangan || keterangan.trim() === '') {
            isValid = false;
            errorMsg = `Baris ${index + 1}: Isi keterangan item`;
            return false;
        }
        if (!jumlah || parseFloat(jumlah) <= 0) {
            isValid = false;
            errorMsg = `Baris ${index + 1}: Nominal harus lebih dari 0`;
            return false;
        }
    });
    
    if (!isValid) {
        if (typeof showToast !== 'undefined') {
            showToast('warning', 'Peringatan', errorMsg);
        } else {
            alert(errorMsg);
        }
        return;
    }
    
    const formData = new FormData(e.target);
    const id = $('#edit_id').val();
    
    // Tambahkan pos_pengeluaran untuk setiap baris rincian (dari dropdown utama)
    $('#rincianTableBodyEdit tr').each(function() {
        formData.append('pos_pengeluaran[]', selectedPosPengeluaran);
    });
    
    fetch(`{{ route('manage.accounting.expense-transactions.update', ':id') }}`.replace(':id', id), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-HTTP-Method-Override': 'PUT',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(res => {
        const contentType = res.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return res.text().then(text => {
                console.error('Non-JSON response:', text);
                throw new Error('Server returned non-JSON response. Check console for details.');
            });
        }
        return res.json();
    })
    .then(data => {
        if (data.success) {
            if (typeof showToast !== 'undefined') {
                showToast('success', 'Berhasil', data.message || 'Transaksi berhasil diubah');
            }
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            const errorMessage = data.message || 'Gagal mengubah transaksi';
            if (typeof showToast !== 'undefined') {
                showToast('error', 'Error', errorMessage);
                } else {
                alert(errorMessage);
            }
        }
    })
    .catch(err => {
        console.error('Error updating transaction:', err);
        const errorMessage = err.message || 'Terjadi kesalahan saat mengubah transaksi';
        if (typeof showToast !== 'undefined') {
            showToast('error', 'Error', errorMessage);
        } else {
            alert(errorMessage);
        }
    });
}

// Open Modal Tambah Pos
function openModalTambahPos() {
    $('#formTambahPos')[0].reset();
    $('#formTambahPos select[name="status_pos_pengeluaran_baru"]').val('1');
    $('#modalTambahPos').modal('show');
}

// Handle Simpan Pos
function handleSimpanPos(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    
    fetch('{{ route("manage.accounting.expense-transactions.store-expense-pos") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(res => {
        const contentType = res.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return res.text().then(text => {
                console.error('Non-JSON response:', text);
                throw new Error('Server returned non-JSON response. Check console for details.');
            });
        }
        return res.json();
    })
    .then(data => {
        if (data.success) {
            const newPosId = data.pos_id || data.id;
            // Refresh dropdown pos pengeluaran dan select pos yang baru
            refreshExpensePosDropdown(newPosId);
            $('#modalTambahPos').modal('hide');
            // Show global toast notification
            if (typeof showToast !== 'undefined') {
                showToast('success', 'Berhasil', data.message || 'Pos pengeluaran berhasil ditambahkan');
            }
        } else {
            if (typeof showToast !== 'undefined') {
                showToast('error', 'Error', data.message || 'Gagal menambahkan pos pengeluaran');
        } else {
                alert(data.message || 'Gagal menambahkan pos pengeluaran');
            }
        }
    })
    .catch(err => {
        console.error('Error saving expense pos:', err);
        if (typeof showToast !== 'undefined') {
            showToast('error', 'Error', 'Terjadi kesalahan saat menyimpan pos pengeluaran');
        } else {
            alert('Terjadi kesalahan saat menyimpan pos pengeluaran');
        }
    });
}

// Refresh Expense Pos Dropdown
function refreshExpensePosDropdown(selectId = null) {
    fetch('{{ route("manage.accounting.expense-transactions.get-expense-pos") }}', {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => {
        const contentType = res.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return res.text().then(text => {
                console.error('Non-JSON response:', text);
                throw new Error('Server returned non-JSON response. Check console for details.');
            });
        }
        return res.json();
    })
        .then(data => {
            if (data.success && data.pos_pengeluaran) {
                // Update dropdown di modal Tambah
                const selectTambah = $('#tambah_pos_pengeluaran_id');
                const currentValue = selectTambah.val();
                selectTambah.empty().append('<option value="">Pilih Pos Pengeluaran</option>');
                
                data.pos_pengeluaran.forEach(pos => {
                    const value = pos.pos_id || pos.id;
                    const text = pos.pos_name;
                    selectTambah.append(`<option value="${value}">${text}</option>`);
                });
                
                // Select pos yang baru ditambah atau yang terakhir
                if (selectId) {
                    selectTambah.val(selectId);
                } else if (data.pos_pengeluaran.length > 0) {
                    const lastPos = data.pos_pengeluaran[data.pos_pengeluaran.length - 1];
                    const lastValue = lastPos.pos_id || lastPos.id;
                    selectTambah.val(lastValue);
                } else if (currentValue) {
                    selectTambah.val(currentValue);
                }
                
                // Update dropdown di modal Edit
                const selectEdit = $('#edit_pos_id');
                const currentEditValue = selectEdit.val();
                selectEdit.empty().append('<option value="">Pilih Pos Pengeluaran</option>');
                
                data.pos_pengeluaran.forEach(pos => {
                    const value = pos.pos_id || pos.id;
                    const text = pos.pos_name;
                    selectEdit.append(`<option value="${value}">${text}</option>`);
                });
                
                if (currentEditValue) {
                    selectEdit.val(currentEditValue);
                }
            }
        })
        .catch(err => {
            console.error('Error refreshing expense pos dropdown:', err);
        });
}

// Make functions global
window.handleTambah = handleTambah;
window.handleTambahCopy = handleTambahCopy;
window.handleUbah = handleUbah;
window.handleHapus = handleHapus;
window.handleCetak = handleCetak;
window.handleFilter = handleFilter;
window.selectRow = selectRow;
window.openModalTambahPos = openModalTambahPos;
window.tambahBarisRincian = tambahBarisRincian;
window.hapusBarisRincian = hapusBarisRincian;
window.hitungTotalRincian = hitungTotalRincian;
window.tambahBarisRincianEdit = tambahBarisRincianEdit;
window.hapusBarisRincianEdit = hapusBarisRincianEdit;
window.hitungTotalRincianEdit = hitungTotalRincianEdit;
</script>
@endsection

