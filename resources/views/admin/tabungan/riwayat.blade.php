@extends('layouts.adminty')

@section('title', 'Riwayat Transaksi Tabungan')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Riwayat Transaksi Tabungan</h4>
                    <div class="btn-group" role="group">
                        <a href="{{ route('manage.tabungan.export-mutasi', $tabungan->tabungan_id) }}" 
                           class="btn btn-success btn-sm text-white">
                            <i class="fas fa-download me-2 text-white"></i>Unduh Mutasi Tabungan
                        </a>
                        <button type="button" class="btn btn-primary btn-sm text-white" onclick="cetakKuitansi()">
                            <i class="fas fa-print me-2 text-white"></i>Cetak Kuitansi
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h5>Informasi Siswa</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="150"><strong>Nama Siswa:</strong></td>
                                    <td>{{ $tabungan->student_full_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>NIS:</strong></td>
                                    <td>{{ $tabungan->student_nis }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Kelas:</strong></td>
                                    <td>{{ $tabungan->class_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Saldo Saat Ini:</strong></td>
                                    <td><span class="badge bg-success">Rp {{ number_format($tabungan->saldo, 0, ',', '.') }}</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($riwayat->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Jenis</th>
                                        <th>Jumlah</th>
                                        <th>Saldo Setelah Transaksi</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($riwayat as $index => $transaksi)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ \Carbon\Carbon::parse($transaksi->log_tabungan_input_date)->format('d/m/Y H:i') }}</td>
                                            <td>
                                                @if($transaksi->kredit > 0)
                                                    <span class="badge bg-success">Setoran</span>
                                                @elseif($transaksi->debit > 0)
                                                    <span class="badge bg-warning">Penarikan</span>
                                                @else
                                                    <span class="badge bg-secondary">Lainnya</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($transaksi->kredit > 0)
                                                    <span class="text-success">+ Rp {{ number_format($transaksi->kredit, 0, ',', '.') }}</span>
                                                @elseif($transaksi->debit > 0)
                                                    <span class="text-danger">- Rp {{ number_format($transaksi->debit, 0, ',', '.') }}</span>
                                                @else
                                                    Rp 0
                                                @endif
                                            </td>
                                            <td>
                                                <strong>Rp {{ number_format($transaksi->saldo, 0, ',', '.') }}</strong>
                                            </td>
                                            <td>{{ $transaksi->keterangan ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Belum ada transaksi tabungan untuk siswa ini.
                        </div>
                    @endif

                    <div class="mt-3">
                        <a href="{{ route('manage.tabungan.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cetak Kuitansi -->
<div class="modal fade" id="modalCetakKuitansi" tabindex="-1" aria-labelledby="modalCetakKuitansiLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCetakKuitansiLabel">Cetak Kuitansi Tabungan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formCetakKuitansi">
                    <div class="mb-3">
                        <label for="tanggal_cetak" class="form-label">Tanggal Cetak</label>
                        <input type="date" class="form-control" id="tanggal_cetak" name="tanggal_cetak" 
                               value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="jenis_kuitansi" class="form-label">Jenis Kuitansi</label>
                        <select class="form-control" id="jenis_kuitansi" name="jenis_kuitansi" required>
                            <option value="">Pilih Jenis Kuitansi</option>
                            <option value="setoran">Kuitansi Setoran</option>
                            <option value="penarikan">Kuitansi Penarikan</option>
                            <option value="semua">Semua Transaksi</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="periode_awal" class="form-label">Periode Awal</label>
                        <input type="date" class="form-control" id="periode_awal" name="periode_awal" 
                               value="{{ date('Y-m-01') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="periode_akhir" class="form-label">Periode Akhir</label>
                        <input type="date" class="form-control" id="periode_akhir" name="periode_akhir" 
                               value="{{ date('Y-m-d') }}" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="prosesCetakKuitansi()">
                    <i class="fas fa-print me-2"></i>Cetak
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function cetakKuitansi() {
    // Gunakan jQuery modal untuk Bootstrap 4
    if (typeof $ !== 'undefined' && $.fn.modal) {
        $('#modalCetakKuitansi').modal('show');
    } else {
        // Fallback jika jQuery tidak tersedia
        const modal = document.getElementById('modalCetakKuitansi');
        if (modal) {
            modal.style.display = 'block';
            modal.classList.add('show');
            document.body.classList.add('modal-open');
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
        }
    }
}

function prosesCetakKuitansi() {
    const form = document.getElementById('formCetakKuitansi');
    if (!form) {
        alert('Form tidak ditemukan!');
        return;
    }
    
    const formData = new FormData(form);
    
    // Validasi form
    if (!formData.get('tanggal_cetak') || !formData.get('jenis_kuitansi') || 
        !formData.get('periode_awal') || !formData.get('periode_akhir')) {
        alert('Mohon lengkapi semua field yang diperlukan!');
        return;
    }
    
    // Validasi periode
    const periodeAwal = new Date(formData.get('periode_awal'));
    const periodeAkhir = new Date(formData.get('periode_akhir'));
    
    if (periodeAwal > periodeAkhir) {
        alert('Periode awal tidak boleh lebih besar dari periode akhir!');
        return;
    }
    
    // Buat URL untuk cetak kuitansi
    const params = new URLSearchParams({
        tanggal_cetak: formData.get('tanggal_cetak'),
        jenis_kuitansi: formData.get('jenis_kuitansi'),
        periode_awal: formData.get('periode_awal'),
        periode_akhir: formData.get('periode_akhir')
    });
    
    const url = `{{ route('manage.tabungan.cetak-kuitansi', $tabungan->tabungan_id) }}?${params.toString()}`;
    
    // Buka di tab baru untuk cetak
    window.open(url, '_blank');
    
    // Tutup modal
    if (typeof $ !== 'undefined' && $.fn.modal) {
        $('#modalCetakKuitansi').modal('hide');
    } else {
        // Fallback jika jQuery tidak tersedia
        const modal = document.getElementById('modalCetakKuitansi');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
            document.body.classList.remove('modal-open');
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        }
    }
}

// Set default periode ke bulan ini
$(document).ready(function() {
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    
    const periodeAwal = document.getElementById('periode_awal');
    const periodeAkhir = document.getElementById('periode_akhir');
    
    if (periodeAwal) {
        periodeAwal.value = firstDay.toISOString().split('T')[0];
    }
    if (periodeAkhir) {
        periodeAkhir.value = today.toISOString().split('T')[0];
    }
});
</script>
@endpush 