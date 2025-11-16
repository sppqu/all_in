@extends('layouts.adminty')

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
                <div class="card-header bg-primary text-white border-0" style="color: #ffffff !important;">
                    <h4 class="mb-0" style="color: #ffffff !important;">
                        <i class="fa fa-exchange-alt me-2" style="color: #ffffff !important;"></i><span style="color: #ffffff !important;">PINDAH BUKU</span>
                    </h4>
                </div>
            </div>

            <!-- Main Content - Two Panels -->
            <div class="row">
                <!-- Left Panel - Transaction List -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-primary text-white border-0" style="color: #ffffff !important;">
                            <h5 class="mb-0" style="color: #ffffff !important;">
                                <i class="fa fa-list me-2" style="color: #ffffff !important;"></i><span style="color: #ffffff !important;">Daftar Transaksi Pindah Buku</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Filter Tanggal -->
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold text-info mb-2">
                                        <i class="fa fa-filter me-1"></i>Filter Tanggal
                                    </label>
                                    <div class="input-group">
                                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate ?? date('Y-m-d', strtotime('-3 months')) }}">
                                        <span class="input-group-text bg-info text-white">s/d</span>
                                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate ?? date('Y-m-d') }}">
                                        <button type="button" class="btn btn-info" onclick="tampilkanData()">
                                            <i class="fa fa-eye me-1"></i>Tampilkan [F5]
                                        </button>
                                    </div>
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
                        <div class="card-header bg-primary text-white border-0" style="color: #ffffff !important;">
                            <h5 class="mb-0" style="color: #ffffff !important;">
                                <i class="fa fa-info-circle me-2" style="color: #ffffff !important;"></i><span style="color: #ffffff !important;">Detail Transaksi</span>
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
<div class="modal fade" id="tambahTransferModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white" style="color: #ffffff !important;">
                <h5 class="modal-title" id="tambahTransferModalLabel" style="color: #ffffff !important;">
                    <i class="fa fa-plus me-2" style="color: #ffffff !important;"></i><span style="color: #ffffff !important;">Tambah Pindah Buku</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formTambahTransfer">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tanggal_transfer" class="form-label">Tanggal Transfer <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggal_transfer" name="tanggal_transfer" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="no_transaksi" class="form-label">No. Transaksi</label>
                                <input type="text" class="form-control" id="no_transaksi" name="no_transaksi" placeholder="Auto generate" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="kas_asal_id" class="form-label">Kas Asal <span class="text-danger">*</span></label>
                                <select class="form-control" id="kas_asal_id" name="kas_asal_id" required>
                                    <option value="">Pilih Kas Asal</option>
                                    @foreach($kasList as $kas)
                                        <option value="{{ $kas->id }}">{{ $kas->nama_kas }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="kas_tujuan_id" class="form-label">Kas Tujuan <span class="text-danger">*</span></label>
                                <select class="form-control" id="kas_tujuan_id" name="kas_tujuan_id" required>
                                    <option value="">Pilih Kas Tujuan</option>
                                    @foreach($kasList as $kas)
                                        <option value="{{ $kas->id }}">{{ $kas->nama_kas }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="3" placeholder="Masukkan keterangan transfer"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nama_penyetor" class="form-label">Nama Penyetor</label>
                                <input type="text" class="form-control" id="nama_penyetor" name="nama_penyetor" placeholder="Nama penyetor">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nama_penerima" class="form-label">Nama Penerima</label>
                                <input type="text" class="form-control" id="nama_penerima" name="nama_penerima" placeholder="Nama penerima">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="jumlah_transfer" class="form-label">Jumlah Transfer <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="jumlah_transfer" name="jumlah_transfer" placeholder="0" min="0" step="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
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

@section('scripts')
<script>
// Data dari controller
const kasList = @json($kasList ?? []);
const transferData = @json($transfers ?? []);
let selectedTransferId = null;

// Inisialisasi halaman
$(document).ready(function() {
    // Set tanggal default
    $('#start_date').val('{{ $startDate ?? date("Y-m-d", strtotime("-3 months")) }}');
    $('#end_date').val('{{ $endDate ?? date("Y-m-d") }}');
    
    // Load data awal
    loadTransferData();
    
    // Form submit handler
    $('#formTambahTransfer').on('submit', handleSimpanTransfer);
});

// Filter dan tampilkan data
function tampilkanData() {
    loadTransferData();
}
window.tampilkanData = tampilkanData;

// Load data transfer
function loadTransferData() {
    const startDate = $('#start_date').val();
    const endDate = $('#end_date').val();
    
    // Filter data berdasarkan periode yang dipilih
    const filteredData = transferData.filter(transfer => {
        const transferDate = new Date(transfer.tanggal_transfer || transfer.tanggal);
        const start = new Date(startDate);
        const end = new Date(endDate);
        end.setHours(23, 59, 59, 999); // Set to end of day
        return transferDate >= start && transferDate <= end;
    });
    
    renderTransferTable(filteredData);
    
    // Reset selected
    selectedTransferId = null;
    $('#btnTambahCopy').prop('disabled', true);
    $('#btnHapus').prop('disabled', true);
    $('#btnCetak').prop('disabled', true);
    $('#selectedCount').text('0');
}
window.loadTransferData = loadTransferData;

// Render tabel transfer
function renderTransferTable(data) {
    const tbody = $('#transferTableBody');
    
    if (data.length === 0) {
        tbody.html(`
            <tr>
                <td colspan="7" class="text-center py-4 text-muted">
                    <i class="fa fa-inbox fa-2x mb-2 text-info"></i>
                    <br>Tidak ada data transfer
                </td>
            </tr>
        `);
        return;
    }
    
    tbody.html(data.map((transfer, index) => `
        <tr class="transfer-row cursor-pointer" data-id="${transfer.id}" onclick="selectTransfer(${transfer.id})">
            <td class="text-center">${index + 1}</td>
            <td>${formatDate(transfer.tanggal_transfer)}</td>
            <td>${transfer.no_transaksi || '-'}</td>
            <td>${transfer.petugas || transfer.operator || '-'}</td>
            <td>${transfer.kas_asal_nama || '-'}</td>
            <td>${transfer.kas_tujuan_nama || '-'}</td>
            <td class="text-end">Rp ${(transfer.jumlah_transfer || 0).toLocaleString('id-ID')}</td>
        </tr>
    `).join(''));
}
window.renderTransferTable = renderTransferTable;

// Select transfer untuk detail
function selectTransfer(transferId) {
    selectedTransferId = transferId;
    
    // Remove active class from all rows
    $('.transfer-row').removeClass('table-active');
    
    // Add active class to selected row
    $(`.transfer-row[data-id="${transferId}"]`).addClass('table-active');
    
    // Enable action buttons
    $('#btnTambahCopy').prop('disabled', false);
    $('#btnHapus').prop('disabled', false);
    $('#btnCetak').prop('disabled', false);
    
    // Update selected count
    $('#selectedCount').text('1');
    
    // Load transfer details
    loadTransferDetails(transferId);
}
window.selectTransfer = selectTransfer;

// Load detail transfer
function loadTransferDetails(transferId) {
    const transfer = transferData.find(t => t.id === transferId);
    if (!transfer) return;
    
    // Populate detail fields
    $('#detailTglNo').text(`${formatDate(transfer.tanggal_transfer || transfer.tanggal)} / ${transfer.no_transaksi || '-'}`);
    $('#detailOperator').text(transfer.petugas || transfer.operator || '-');
    $('#detailKasAsal').text(transfer.kas_asal_nama || '-');
    $('#detailKasTujuan').text(transfer.kas_tujuan_nama || '-');
    $('#detailKeterangan').text(transfer.keterangan || '-');
    $('#detailPenyetor').text(transfer.nama_penyetor || '-');
    $('#detailPenerima').text(transfer.nama_penerima || '-');
    
    // Populate detail item table
    renderDetailItemTable(transfer.detail_items || []);
    
    // Update total
    $('#detailTotal').text((transfer.jumlah_transfer || 0).toLocaleString('id-ID'));
}
window.loadTransferDetails = loadTransferDetails;

// Render detail item table
function renderDetailItemTable(items) {
    const tbody = $('#detailItemTable tbody');
    
    if (items.length === 0) {
        tbody.html(`
            <tr>
                <td colspan="2" class="text-center py-3 text-muted">
                    Tidak ada detail item
                </td>
            </tr>
        `);
        return;
    }
    
    tbody.html(items.map((item, index) => `
        <tr>
            <td class="text-center">${item.pos_name || '-'}</td>
            <td class="text-end">Rp ${(item.jumlah || 0).toLocaleString('id-ID')}</td>
        </tr>
    `).join(''));
}
window.renderDetailItemTable = renderDetailItemTable;

// Tambah transfer baru
function tambahTransfer() {
    selectedTransferId = null;
    
    // Reset form
    $('#formTambahTransfer')[0].reset();
    
    // Set tanggal default
    const today = new Date().toISOString().split('T')[0];
    $('#tanggal_transfer').val(today);
    
    // Generate no transaksi
    const date = new Date();
    const year = date.getFullYear().toString().substr(-2);
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const day = date.getDate().toString().padStart(2, '0');
    const noTransaksi = `TRF-${year}${month}${day}-001`;
    $('#no_transaksi').val(noTransaksi);
    
    // Show modal
    $('#tambahTransferModal').modal('show');
}
window.tambahTransfer = tambahTransfer;

// Tambah + Copy
function tambahCopy() {
    if (!selectedTransferId) {
        if (typeof showToast !== 'undefined') {
            showToast('warning', 'Peringatan', 'Pilih transaksi yang akan di-copy terlebih dahulu');
        } else {
            alert('Pilih transaksi yang akan di-copy terlebih dahulu');
        }
        return;
    }
    
    const transfer = transferData.find(t => t.id === selectedTransferId);
    if (!transfer) return;
    
    // Reset form
    $('#formTambahTransfer')[0].reset();
    
    // Set tanggal default
    const today = new Date().toISOString().split('T')[0];
    $('#tanggal_transfer').val(today);
    
    // Generate no transaksi baru
    const date = new Date();
    const year = date.getFullYear().toString().substr(-2);
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const day = date.getDate().toString().padStart(2, '0');
    const noTransaksi = `TRF-${year}${month}${day}-001`;
    $('#no_transaksi').val(noTransaksi);
    
    // Copy data dari transfer yang dipilih
    $('#kas_asal_id').val(transfer.kas_asal_id || '');
    $('#kas_tujuan_id').val(transfer.kas_tujuan_id || '');
    $('#keterangan').val(transfer.keterangan || '');
    $('#nama_penyetor').val(transfer.nama_penyetor || '');
    $('#nama_penerima').val(transfer.nama_penerima || '');
    $('#jumlah_transfer').val(transfer.jumlah_transfer || '');
    
    // Show modal
    $('#tambahTransferModal').modal('show');
}
window.tambahCopy = tambahCopy;

// Hapus transfer
function hapusTransfer() {
    if (!selectedTransferId) {
        if (typeof showToast !== 'undefined') {
            showToast('warning', 'Peringatan', 'Pilih transaksi yang akan dihapus terlebih dahulu');
        } else {
            alert('Pilih transaksi yang akan dihapus terlebih dahulu');
        }
        return;
    }
    
    if (confirm('Apakah Anda yakin ingin menghapus transaksi ini?')) {
        // TODO: Implementasi hapus transfer via AJAX
        if (typeof showToast !== 'undefined') {
            showToast('info', 'Info', 'Fitur hapus akan diimplementasikan');
        } else {
            alert('Fitur hapus akan diimplementasikan');
        }
    }
}
window.hapusTransfer = hapusTransfer;

// Cetak bukti
function cetakBukti() {
    if (!selectedTransferId) {
        if (typeof showToast !== 'undefined') {
            showToast('warning', 'Peringatan', 'Pilih transaksi yang akan dicetak terlebih dahulu');
        } else {
            alert('Pilih transaksi yang akan dicetak terlebih dahulu');
        }
        return;
    }
    
    // TODO: Implementasi cetak bukti
    if (typeof showToast !== 'undefined') {
        showToast('info', 'Info', 'Fitur cetak bukti akan diimplementasikan');
    } else {
        alert('Fitur cetak bukti akan diimplementasikan');
    }
}
window.cetakBukti = cetakBukti;

// Handle Simpan Transfer
function handleSimpanTransfer(e) {
    e.preventDefault();
    
    // Validasi kas asal dan tujuan tidak boleh sama
    const kasAsal = $('#kas_asal_id').val();
    const kasTujuan = $('#kas_tujuan_id').val();
    
    if (!kasAsal || !kasTujuan) {
        if (typeof showToast !== 'undefined') {
            showToast('warning', 'Peringatan', 'Pilih Kas Asal dan Kas Tujuan terlebih dahulu');
        } else {
            alert('Pilih Kas Asal dan Kas Tujuan terlebih dahulu');
        }
        return;
    }
    
    if (kasAsal === kasTujuan) {
        if (typeof showToast !== 'undefined') {
            showToast('warning', 'Peringatan', 'Kas Asal dan Kas Tujuan tidak boleh sama');
        } else {
            alert('Kas Asal dan Kas Tujuan tidak boleh sama');
        }
        return;
    }
    
    const formData = new FormData(e.target);
    
    fetch('{{ route("manage.accounting.cash-transfer.store") }}', {
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
            // Close modal
            $('#tambahTransferModal').modal('hide');
            
            // Show success notification
            if (typeof showToast !== 'undefined') {
                showToast('success', 'Berhasil', data.message || 'Transfer kas berhasil dilakukan');
            } else {
                alert(data.message || 'Transfer kas berhasil dilakukan');
            }
            
            // Reload page after 1 second
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            const errorMessage = data.message || 'Gagal melakukan transfer kas';
            if (typeof showToast !== 'undefined') {
                showToast('error', 'Error', errorMessage);
            } else {
                alert(errorMessage);
            }
        }
    })
    .catch(err => {
        console.error('Error saving transfer:', err);
        const errorMessage = err.message || 'Terjadi kesalahan saat menyimpan transfer kas';
        if (typeof showToast !== 'undefined') {
            showToast('error', 'Error', errorMessage);
        } else {
            alert(errorMessage);
        }
    });
}
window.handleSimpanTransfer = handleSimpanTransfer;

// Format date helper
function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}
window.formatDate = formatDate;

// Keyboard shortcuts
$(document).on('keydown', function(e) {
    if (e.ctrlKey || e.altKey) return;
    
    switch(e.key) {
        case 'F2':
            e.preventDefault();
            tambahTransfer();
            break;
        case 'F4':
            e.preventDefault();
            hapusTransfer();
            break;
        case 'F5':
            e.preventDefault();
            tampilkanData();
            break;
        case 'F6':
            e.preventDefault();
            cetakBukti();
            break;
        case 'F8':
            e.preventDefault();
            tambahCopy();
            break;
    }
});
</script>
@endsection

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
    transition: all 0.2s ease;
}

.cursor-pointer {
    cursor: pointer;
}

.table-active {
    background-color: rgba(79, 172, 254, 0.2) !important;
    border-left: 4px solid #4facfe;
}

/* Force white text untuk semua header */
.card-header.bg-primary,
.card-header.bg-primary h4,
.card-header.bg-primary h5,
.card-header.bg-primary h4 span,
.card-header.bg-primary h5 span,
.card-header.bg-primary i,
.modal-header.bg-primary,
.modal-header.bg-primary h5,
.modal-header.bg-primary h5 span,
.modal-header.bg-primary i {
    color: #ffffff !important;
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

/* Input group styling untuk filter tanggal */
.input-group > .form-control:first-child {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    border-right: none;
}

.input-group > .form-control:not(:first-child):not(:last-of-type) {
    border-left: none;
    border-right: none;
    border-radius: 0;
}

.input-group > .form-control:last-of-type {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    border-left: none;
}

.input-group > .input-group-text {
    border-left: none;
    border-right: none;
    border-radius: 0;
    padding: 0.375rem 0.75rem;
    background-color: #17a2b8;
    color: white;
}

.input-group > .btn {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    border-left: none;
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

