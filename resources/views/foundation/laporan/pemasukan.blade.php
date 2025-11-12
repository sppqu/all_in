@extends('layouts.coreui')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Laporan Pemasukan</h2>
    </div>

    <!-- Rekapitulasi per Jenis Biaya -->
    <div class="card mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fa fa-chevron-up me-2"></i>
                Rekapitulasi per Jenis Biaya
            </h5>
            <div class="d-flex gap-2 align-items-center">
                <!-- School Filter -->
                <select name="school_id" id="school_id" class="form-select form-select-sm" style="width: auto;" onchange="filterReport()">
                    <option value="">Semua Sekolah</option>
                    @foreach($schools as $school)
                        <option value="{{ $school->id }}" {{ $selectedSchoolId == $school->id ? 'selected' : '' }}>
                            {{ $school->nama_sekolah }}
                        </option>
                    @endforeach
                </select>
                
                <!-- Time Range Filter -->
                <div class="btn-group btn-group-sm" role="group">
                    <input type="radio" class="btn-check" name="time_range" id="today" value="today" {{ $timeRange == 'today' ? 'checked' : '' }} onchange="filterReport()">
                    <label class="btn btn-outline-primary" for="today">Hari Ini</label>
                    
                    <input type="radio" class="btn-check" name="time_range" id="7days" value="7days" {{ $timeRange == '7days' ? 'checked' : '' }} onchange="filterReport()">
                    <label class="btn btn-outline-primary" for="7days">7 Hari</label>
                    
                    <input type="radio" class="btn-check" name="time_range" id="month" value="month" {{ $timeRange == 'month' ? 'checked' : '' }} onchange="filterReport()">
                    <label class="btn btn-outline-primary" for="month">Bulan Ini</label>
                    
                    <input type="radio" class="btn-check" name="time_range" id="6months" value="6months" {{ $timeRange == '6months' ? 'checked' : '' }} onchange="filterReport()">
                    <label class="btn btn-outline-primary" for="6months">6 Bulan</label>
                    
                    <input type="radio" class="btn-check" name="time_range" id="year" value="year" {{ $timeRange == 'year' ? 'checked' : '' }} onchange="filterReport()">
                    <label class="btn btn-outline-primary" for="year">Tahun Ini</label>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>JENIS BIAYA</th>
                            <th class="text-end">TUNAI</th>
                            <th class="text-end">TRANSFER</th>
                            <th class="text-end">JML. TRANSAKSI</th>
                            <th class="text-end" style="background-color: #fff3cd;">PEMB. HISTORIS</th>
                            <th class="text-end" style="background-color: #cfe2ff;">JUMLAH TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recapData as $item)
                        <tr>
                            <td><strong>{{ $item->pos_name }}</strong></td>
                            <td class="text-end">Rp {{ number_format($item->tunai, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($item->transfer, 0, ',', '.') }}</td>
                            <td class="text-end">{{ $item->jumlah_transaksi }}</td>
                            <td class="text-end" style="background-color: #fff3cd;">Rp {{ number_format($item->pembayaran_historis, 0, ',', '.') }}</td>
                            <td class="text-end" style="background-color: #cfe2ff;"><strong>Rp {{ number_format($item->jumlah_total, 0, ',', '.') }}</strong></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                Tidak ada data untuk rentang waktu yang dipilih.
                            </td>
                        </tr>
                        @endforelse
                        @if($recapData->count() > 0)
                        <tr class="table-secondary fw-bold">
                            <td>TOTAL</td>
                            <td class="text-end">Rp {{ number_format($recapData->sum('tunai'), 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($recapData->sum('transfer'), 0, ',', '.') }}</td>
                            <td class="text-end">{{ $recapData->sum('jumlah_transaksi') }}</td>
                            <td class="text-end" style="background-color: #fff3cd;">Rp {{ number_format($recapData->sum('pembayaran_historis'), 0, ',', '.') }}</td>
                            <td class="text-end" style="background-color: #cfe2ff;">Rp {{ number_format($recapData->sum('jumlah_total'), 0, ',', '.') }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <small class="text-muted">
                * Pembayaran Historis adalah data pembayaran manual dari sebelum sistem ini digunakan.
            </small>
        </div>
    </div>

    <!-- Rincian Transaksi (Default Tersembunyi) -->
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fa fa-chevron-down me-2"></i>
                Rincian Transaksi (Default Tersembunyi)
            </h5>
            <input type="text" class="form-control form-control-sm" placeholder="Cari Nama / NIS / No. k" style="width: 250px;" id="searchTransaction">
        </div>
        <div class="card-body collapse" id="transactionDetails">
            <p class="text-muted text-center py-4">Detail transaksi akan ditampilkan di sini</p>
        </div>
    </div>
</div>

<script>
function filterReport() {
    const schoolId = document.getElementById('school_id').value;
    const timeRange = document.querySelector('input[name="time_range"]:checked').value;
    
    const url = new URL(window.location.href);
    url.searchParams.set('school_id', schoolId);
    url.searchParams.set('time_range', timeRange);
    
    window.location.href = url.toString();
}

// Toggle transaction details
document.addEventListener('DOMContentLoaded', function() {
    const transactionHeader = document.querySelector('.card-header:has(#transactionDetails)');
    if (transactionHeader) {
        transactionHeader.addEventListener('click', function(e) {
            if (!e.target.closest('.form-control')) {
                const details = document.getElementById('transactionDetails');
                details.classList.toggle('collapse');
                const icon = this.querySelector('.fa-chevron-down, .fa-chevron-up');
                if (icon) {
                    icon.classList.toggle('fa-chevron-down');
                    icon.classList.toggle('fa-chevron-up');
                }
            }
        });
    }
});
</script>
@endsection





