@extends('layouts.coreui')

@section('title', 'Pindah Buku')
@section('content-header', 'Pindah Buku')

@push('meta')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-gradient-primary text-white border-0">
                    <h4 class="mb-0">
                        <i class="fa fa-exchange-alt me-2"></i>PINDAH BUKU
                    </h4>
                </div>
            </div>

            <!-- Main Content - Two Panels -->
            <div class="row">
                <!-- Left Panel - Transaction List -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-gradient-info text-white border-0">
                            <h5 class="mb-0">
                                <i class="fa fa-list me-2"></i>Daftar Transaksi Pindah Buku
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Filter Tanggal -->
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label for="start_date" class="form-label fw-bold text-info">Filter Tanggal</label>
                                    <div class="input-group">
                                        <input type="date" class="form-control border-info" id="start_date" name="start_date" value="{{ $startDate ?? date('Y-m-d', strtotime('-3 months')) }}">
                                        <span class="input-group-text bg-info text-white">-</span>
                                        <input type="date" class="form-control border-info" id="end_date" name="end_date" value="{{ $endDate ?? date('Y-m-d') }}">
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="button" class="btn btn-info w-100 shadow-sm" onclick="tampilkanData()">
                                        <i class="fa fa-eye me-1"></i>Tampilkan [F5]
                                    </button>
                                </div>
                            </div>

                            <!-- Tabel Transaksi -->
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light-info">
                                        <tr>
                                            <th class="border-0 text-center" style="width: 8%;">NO.</th>
                                            <th class="border-0" style="width: 12%;">TANGGAL</th>
                                            <th class="border-0" style="width: 18%;">NO. TRANSAKSI</th>
                                            <th class="border-0" style="width: 15%;">PETUGAS</th>
                                            <th class="border-0" style="width: 15%;">KAS ASAL</th>
                                            <th class="border-0" style="width: 15%;">KAS TUJUAN</th>
                                            <th class="border-0 text-end" style="width: 17%;">JUMLAH</th>
                                        </tr>
                                    </thead>
                                    <tbody id="transferTableBody">
                                        <!-- Data akan di-load melalui JavaScript -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="text-muted">
                                    <span id="selectedCount">0</span> item dipilih
                                </div>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-success" onclick="tambahTransfer()" id="btnTambah">
                                        <i class="fa fa-plus me-1"></i>Tambah [F2]
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="tambahCopy()" id="btnTambahCopy" disabled>
                                        <i class="fa fa-copy me-1"></i>Tambah + Copy [F8]
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="hapusTransfer()" id="btnHapus" disabled>
                                        <i class="fa fa-trash me-1"></i>Hapus [F4]
                                    </button>
                                </div>
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
                            <!-- Informasi Transaksi -->
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
                                    <div class="col-4"><strong class="text-dark">Kas Asal:</strong></div>
                                    <div class="col-8" id="detailKasAsal">-</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong class="text-dark">Kas Tujuan:</strong></div>
                                    <div class="col-8" id="detailKasTujuan">-</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong class="text-dark">Keterangan:</strong></div>
                                    <div class="col-8" id="detailKeterangan">-</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong class="text-dark">Nama Penyetor:</strong></div>
                                    <div class="col-8" id="detailPenyetor">-</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong class="text-dark">Nama Penerima:</strong></div>
                                    <div class="col-8" id="detailPenerima">-</div>
                                </div>
                            </div>

                            <!-- Tabel Detail Item -->
                            <div class="mb-4">
                                <h6 class="fw-bold text-success mb-3">Detail Item:</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="detailItemTable">
                                        <thead class="bg-light-success">
                                            <tr>
                                                <th style="width: 50%;" class="text-center">NO POS</th>
                                                <th style="width: 50%;" class="text-center">JUMLAH (RP)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Data akan di-populate melalui JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Footer Actions -->
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-warning w-100" onclick="cetakBukti()" id="btnCetak" disabled>
                                        <i class="fa fa-print me-1"></i>Cetak Bukti [F6]
                                    </button>
                                </div>
                                <div class="col-md-6 text-end">
                                    <div class="mb-2">
                                        <strong class="text-primary">Total (Rp) <span id="detailTotal">0</span></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Pindah Buku -->
<div class="modal fade" id="tambahTransferModal" tabindex="-1" aria-labelledby="tambahTransferModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-success text-white">
                <h5 class="modal-title" id="tambahTransferModalLabel">
                    <i class="fa fa-plus me-2"></i>Tambah Pindah Buku
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTambahTransfer">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tanggal_transfer" class="form-label fw-bold text-success">Tanggal Transfer <span class="text-danger">*</span></label>
                                <input type="date" class="form-control border-success" id="tanggal_transfer" name="tanggal_transfer" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="no_transaksi" class="form-label fw-bold text-success">No. Transaksi</label>
                                <input type="text" class="form-control border-success" id="no_transaksi" name="no_transaksi" placeholder="Auto generate" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="kas_asal" class="form-label fw-bold text-success">Kas Asal <span class="text-danger">*</span></label>
                                <select class="form-select border-success" id="kas_asal" name="kas_asal" required>
                                    <option value="">Pilih Kas Asal</option>
                                    @foreach($kasList as $kas)
                                        <option value="{{ $kas->id }}">{{ $kas->nama_kas }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="kas_tujuan" class="form-label fw-bold text-success">Kas Tujuan <span class="text-danger">*</span></label>
                                <select class="form-select border-success" id="kas_tujuan" name="kas_tujuan" required>
                                    <option value="">Pilih Kas Tujuan</option>
                                    @foreach($kasList as $kas)
                                        <option value="{{ $kas->id }}">{{ $kas->nama_kas }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="keterangan" class="form-label fw-bold text-success">Keterangan</label>
                        <textarea class="form-control border-success" id="keterangan" name="keterangan" rows="3" placeholder="Masukkan keterangan transfer"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nama_penyetor" class="form-label fw-bold text-success">Nama Penyetor</label>
                                <input type="text" class="form-control border-success" id="nama_penyetor" name="nama_penyetor" placeholder="Nama penyetor">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nama_penerima" class="form-label fw-bold text-success">Nama Penerima</label>
                                <input type="text" class="form-control border-success" id="nama_penerima" name="nama_penerima" placeholder="Nama penerima">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="jumlah_transfer" class="form-label fw-bold text-success">Jumlah Transfer <span class="text-danger">*</span></label>
                        <input type="number" class="form-control border-success" id="jumlah_transfer" name="jumlah_transfer" placeholder="0" min="0" step="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fa fa-times me-2"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save me-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Data dari controller
const kasList = @json($kasList ?? []);
const transferData = @json($transfers ?? []);

// Inisialisasi halaman
document.addEventListener('DOMContentLoaded', function() {
    // Set tanggal default
    document.getElementById('start_date').value = '{{ $startDate ?? date("Y-m-d", strtotime("-3 months")) }}';
    document.getElementById('end_date').value = '{{ $endDate ?? date("Y-m-d") }}';
    
    // Load data awal
    loadTransferData();
});

// Filter dan tampilkan data
function tampilkanData() {
    loadTransferData();
}

// Load data transfer
function loadTransferData() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    // Filter data berdasarkan periode yang dipilih
    const filteredData = transferData.filter(transfer => {
        const transferDate = new Date(transfer.tanggal_transfer);
        const start = new Date(startDate);
        const end = new Date(endDate);
        return transferDate >= start && transferDate <= end;
    });
    
    renderTransferTable(filteredData);
}

// Render tabel transfer
function renderTransferTable(data) {
    const tbody = document.getElementById('transferTableBody');
    
    if (data.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4 text-muted">
                    <i class="fa fa-inbox fa-2x mb-2 text-info"></i>
                    <br>Tidak ada data transfer
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = data.map((transfer, index) => `
        <tr class="transfer-row" data-id="${transfer.id}" onclick="selectTransfer(${transfer.id})">
            <td class="text-center">${index + 1}</td>
            <td>${formatDate(transfer.tanggal_transfer)}</td>
            <td>${transfer.no_transaksi}</td>
            <td>${transfer.petugas || '-'}</td>
            <td>${transfer.kas_asal_nama || '-'}</td>
            <td>${transfer.kas_tujuan_nama || '-'}</td>
            <td class="text-end">Rp ${transfer.jumlah_transfer?.toLocaleString('id-ID') || '0'}</td>
        </tr>
    `).join('');
}

// Select transfer untuk detail
function selectTransfer(transferId) {
    // Remove active class from all rows
    document.querySelectorAll('.transfer-row').forEach(row => {
        row.classList.remove('table-active');
    });
    
    // Add active class to selected row
    const selectedRow = document.querySelector(`[data-id="${transferId}"]`);
    if (selectedRow) {
        selectedRow.classList.add('table-active');
    }
    
    // Enable action buttons
    document.getElementById('btnTambahCopy').disabled = false;
    document.getElementById('btnHapus').disabled = false;
    document.getElementById('btnCetak').disabled = false;
    
    // Update selected count
    document.getElementById('selectedCount').textContent = '1';
    
    // Load transfer details
    loadTransferDetails(transferId);
}

// Load detail transfer
function loadTransferDetails(transferId) {
    const transfer = transferData.find(t => t.id === transferId);
    if (!transfer) return;
    
    // Populate detail fields
    document.getElementById('detailTglNo').textContent = `${formatDate(transfer.tanggal_transfer)} / ${transfer.no_transaksi}`;
    document.getElementById('detailOperator').textContent = transfer.petugas || '-';
    document.getElementById('detailKasAsal').textContent = transfer.kas_asal_nama || '-';
    document.getElementById('detailKasTujuan').textContent = transfer.kas_tujuan_nama || '-';
    document.getElementById('detailKeterangan').textContent = transfer.keterangan || '-';
    document.getElementById('detailPenyetor').textContent = transfer.nama_penyetor || '-';
    document.getElementById('detailPenerima').textContent = transfer.nama_penerima || '-';
    
    // Populate detail item table
    renderDetailItemTable(transfer.detail_items || []);
    
    // Update total
    document.getElementById('detailTotal').textContent = transfer.jumlah_transfer?.toLocaleString('id-ID') || '0';
}

// Render detail item table
function renderDetailItemTable(items) {
    const tbody = document.querySelector('#detailItemTable tbody');
    
    if (items.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="2" class="text-center py-3 text-muted">
                    Tidak ada detail item
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = items.map((item, index) => `
        <tr>
            <td class="text-center">${item.pos_name || '-'}</td>
            <td class="text-end">Rp ${item.jumlah?.toLocaleString('id-ID') || '0'}</td>
        </tr>
    `).join('');
}

// Tambah transfer baru
function tambahTransfer() {
    // Reset form
    document.getElementById('formTambahTransfer').reset();
    
    // Set tanggal default
    document.getElementById('tanggal_transfer').value = new Date().toISOString().split('T')[0];
    
    // Generate no transaksi
    const today = new Date();
    const noTransaksi = 'TRF-' + today.getFullYear().toString().substr(-2) + 
                        (today.getMonth() + 1).toString().padStart(2, '0') + 
                        today.getDate().toString().padStart(2, '0') + '-001';
    document.getElementById('no_transaksi').value = noTransaksi;
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('tambahTransferModal'));
    modal.show();
}

// Tambah + Copy
function tambahCopy() {
    alert('Fitur tambah + copy akan diimplementasikan');
}

// Hapus transfer
function hapusTransfer() {
    alert('Fitur hapus akan diimplementasikan');
}

// Cetak bukti
function cetakBukti() {
    alert('Fitur cetak bukti akan diimplementasikan');
}

// Format date helper
function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID');
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.key === 'F2') {
        e.preventDefault();
        tambahTransfer();
    } else if (e.key === 'F4') {
        e.preventDefault();
        hapusTransfer();
    } else if (e.key === 'F5') {
        e.preventDefault();
        tampilkanData();
    } else if (e.key === 'F6') {
        e.preventDefault();
        cetakBukti();
    } else if (e.key === 'F8') {
        e.preventDefault();
        tambahCopy();
    }
});
</script>
@endpush

<style>
/* Gradient Backgrounds */
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-gradient-info {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.bg-light-info {
    background-color: #e6f3ff !important;
}

.bg-light-success {
    background-color: #d1edff !important;
}

/* Table Styling */
.table-hover tbody tr:hover {
    background-color: rgba(79, 172, 254, 0.1) !important;
    transform: scale(1.01);
    transition: all 0.2s ease;
    cursor: pointer;
}

.table-active {
    background-color: rgba(79, 172, 254, 0.2) !important;
    border-left: 4px solid #4facfe;
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

/* Form control styling */
.form-control:focus,
.form-select:focus {
    border-color: #4facfe;
    box-shadow: 0 0 0 0.2rem rgba(79, 172, 254, 0.25);
}

/* Modal styling */
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

/* Responsive adjustments */
@media (max-width: 768px) {
    .btn-group .btn {
        margin-bottom: 10px;
        width: 100%;
    }
    
    .col-md-6 {
        margin-bottom: 20px;
    }
}
</style>

