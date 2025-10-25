@extends('layouts.coreui')

@section('title', 'Laporan Perpos')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Laporan Perpos</h4>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form id="filterForm" method="GET" action="{{ route('manage.laporan-perpos') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-2">
                                <label class="form-label">Tahun Ajaran</label>
                                <select name="period_id" class="form-select" required>
                                    <option value="">Pilih Tahun Ajaran</option>
                                    @foreach($periods as $period)
                                        <option value="{{ $period->period_id }}" {{ $selectedPeriod == $period->period_id ? 'selected' : '' }}>
                                            {{ $period->period_start }}/{{ $period->period_end }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Pos Pembayaran</label>
                                <select name="pos_id" class="form-select" required>
                                    <option value="">Pilih Pos Pembayaran</option>
                                    @foreach($posList as $pos)
                                        <option value="{{ $pos->pos_id }}" {{ $selectedPos == $pos->pos_id ? 'selected' : '' }}>
                                            {{ $pos->pos_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Kelas</label>
                                <select name="class_id" class="form-select">
                                    <option value="">Semua Kelas</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->class_id }}" {{ $selectedClass == $class->class_id ? 'selected' : '' }}>
                                            {{ $class->class_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Status Pembayaran</label>
                                <select name="status" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="lunas" {{ $selectedStatus == 'lunas' ? 'selected' : '' }}>Lunas</option>
                                    <option value="belum_lunas" {{ $selectedStatus == 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Status Siswa</label>
                                <select name="student_status" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="1" {{ $selectedStudentStatus == '1' ? 'selected' : '' }}>Aktif</option>
                                    <option value="0" {{ $selectedStudentStatus == '0' ? 'selected' : '' }}>Tidak Aktif</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Jenis Pembayaran</label>
                                <select name="type" class="form-select" required>
                                    <option value="bulanan" {{ $selectedType == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                                    <option value="bebas" {{ $selectedType == 'bebas' ? 'selected' : '' }}>Bebas</option>
                                </select>
                            </div>
                            <div class="col-md-12 mt-3">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i>Filter
                                    </button>
                                    @if($data && $data->count() > 0)
                                        <button type="button" class="btn btn-danger ms-2" onclick="exportPDF()" style="color: white;">
                                            <i class="fas fa-file-pdf me-2" style="color: white;"></i>Export PDF
                                        </button>
                                        <button type="button" class="btn btn-success ms-2" onclick="exportExcel()" style="color: white;">
                                            <i class="fas fa-file-excel me-2" style="color: white;"></i>Export Excel
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>

                    @if($data && $data->count() > 0)
                        <div class="alert alert-info">
                            <strong>Laporan {{ ucfirst($selectedType) }} - {{ $selectedType == 'bulanan' ? ($data->first()->pos_name ?? '-') : $pos->pos_name }}</strong><br>
                            Tahun Ajaran: {{ $selectedPeriodName ?? '-' }}<br>
                            Total Siswa: {{ $data->unique('student_nis')->count() }} siswa<br>
                            @if($selectedType == 'bulanan')
                                Total Belum Lunas: Rp {{ number_format($data->where('bulan_date_pay', null)->sum('bulan_bill'), 0, ',', '.') }}
                            @else
                                Total Belum Lunas: Rp {{ number_format($data->sum('bebas_bill') - $data->sum('bebas_total_pay'), 0, ',', '.') }}
                            @endif
                        </div>

                        @if($selectedType == 'bulanan')
                            <!-- Tabel Laporan Bulanan -->
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>NIS</th>
                                            <th>Nama Siswa</th>
                                            <th>Kelas</th>
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
                                    <tbody>
                                        @php
                                            $groupedData = $data->groupBy('student_nis');
                                        @endphp
                                        @foreach($groupedData as $studentNis => $studentData)
                                            @php
                                                $firstRecord = $studentData->first();
                                                $monthlyData = $studentData->keyBy('month_month_id');
                                            @endphp
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $firstRecord->student_nis }}</td>
                                                <td>{{ $firstRecord->student_full_name }}</td>
                                                <td>{{ $firstRecord->class_name }}</td>
                                                @for($month = 1; $month <= 12; $month++)
                                                    @php
                                                        $monthData = $monthlyData->get($month);
                                                    @endphp
                                                    <td class="text-center">
                                                        @if($monthData)
                                                            @if($monthData->bulan_date_pay)
                                                                <span class="badge bg-success">Lunas</span>
                                                            @else
                                                                <span class="text-danger">Rp {{ number_format($monthData->bulan_bill, 0, ',', '.') }}</span>
                                                            @endif
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                @endfor
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @php
                                $totalSiswa = $data->groupBy('student_nis')->count();
                                $totalBelumLunas = $data->where('bulan_date_pay', null)->sum('bulan_bill');
                                $totalTagihan = $data->sum('bulan_bill');
                                $totalPenerimaan = $data->where('bulan_date_pay', '!=', null)->sum('bulan_bill');
                            @endphp
                            <div class="card mt-3">
                                <div class="card-body">
                                    <div class="mb-2 fw-bold">Ringkasan:</div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0">
                                            <tbody>
                                                <tr>
                                                    <td style="width:220px;">Total Siswa</td>
                                                    <td>{{ $totalSiswa }} siswa</td>
                                                </tr>
                                                <tr>
                                                    <td>Total Belum Lunas</td>
                                                    <td>Rp {{ number_format($totalBelumLunas, 0, ',', '.') }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Total Tagihan</td>
                                                    <td>Rp {{ number_format($totalTagihan, 0, ',', '.') }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Total Penerimaan</td>
                                                    <td>Rp {{ number_format($totalPenerimaan, 0, ',', '.') }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Tabel Laporan Bebas -->
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>NIS</th>
                                            <th>Nama Siswa</th>
                                            <th>Kelas</th>
                                            <th>Jumlah Tagihan</th>
                                            <th>Total Bayar</th>
                                            <th>Sisa</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data as $index => $item)
                                            @php
                                                $sisa = $item->bebas_bill - $item->bebas_total_pay;
                                                $status = $sisa <= 0 ? 'Lunas' : 'Belum Lunas';
                                                $statusClass = $sisa <= 0 ? 'success' : 'warning';
                                            @endphp
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $item->student_nis }}</td>
                                                <td>{{ $item->student_full_name }}</td>
                                                <td>{{ $item->class_name }}</td>
                                                <td>Rp {{ number_format($item->bebas_bill, 0, ',', '.') }}</td>
                                                <td>Rp {{ number_format($item->bebas_total_pay, 0, ',', '.') }}</td>
                                                <td>Rp {{ number_format($sisa, 0, ',', '.') }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $statusClass }}">{{ $status }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @php
                                $totalSiswa = $data->unique('student_nis')->count();
                                $totalBelumLunas = $data->sum(function($r){ return ($r->bebas_bill - $r->bebas_total_pay); });
                                $totalTagihan = $data->sum('bebas_bill');
                                $totalPenerimaan = $data->sum('bebas_total_pay');
                            @endphp
                            <div class="card mt-3">
                                <div class="card-body">
                                    <div class="mb-2 fw-bold">Ringkasan:</div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0">
                                            <tbody>
                                                <tr>
                                                    <td style="width:220px;">Total Siswa</td>
                                                    <td>{{ $totalSiswa }} siswa</td>
                                                </tr>
                                                <tr>
                                                    <td>Total Belum Lunas</td>
                                                    <td>Rp {{ number_format($totalBelumLunas, 0, ',', '.') }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Total Tagihan</td>
                                                    <td>Rp {{ number_format($totalTagihan, 0, ',', '.') }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Total Penerimaan</td>
                                                    <td>Rp {{ number_format($totalPenerimaan, 0, ',', '.') }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @elseif($selectedPeriod && $selectedPos && $data && $data->count() == 0)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Tidak ada data pembayaran untuk kriteria yang dipilih.
                        </div>
                    @elseif($selectedPeriod && $selectedPos)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Silakan pilih filter di atas untuk melihat data laporan.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function getFilterValues() {
    const form = document.getElementById('filterForm');
    const fd = new FormData(form);
    const qs = new URLSearchParams(window.location.search);
    const pick = (name) => {
        if (fd.has(name) && String(fd.get(name)).trim() !== '') return fd.get(name);
        const el = form.querySelector(`[name="${name}"]`);
        if (el && String(el.value).trim() !== '') return el.value;
        if (qs.has(name) && String(qs.get(name)).trim() !== '') return qs.get(name);
        return null;
    };
    return {
        period_id: pick('period_id'),
        pos_id: pick('pos_id'),
        class_id: pick('class_id'),
        status: pick('status'),
        student_status: pick('student_status'),
        type: pick('type')
    };
}

function validateRequired(form) {
    const v = getFilterValues();
    console.log('Export validation values:', { periodId: v.period_id, posId: v.pos_id, type: v.type });
    const isFilled = (x) => x !== null && x !== undefined && String(x).trim() !== '';
    if (!isFilled(v.period_id) || !isFilled(v.pos_id) || !isFilled(v.type)) {
        alert('Mohon pilih Tahun Ajaran, Pos Pembayaran, dan Jenis Pembayaran.');
        return false;
    }
    return true;
}
function exportPDF() {
    const form = document.getElementById('filterForm');
    if (!validateRequired(form)) return;
    const values = getFilterValues();
    
    // Buat form untuk export PDF
    const exportForm = document.createElement('form');
    exportForm.method = 'POST';
    exportForm.action = '{{ route("manage.export-laporan-perpos") }}';
    
    // Tambahkan CSRF token
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    exportForm.appendChild(csrfToken);
    
    // Tambahkan data form (gunakan nilai yang dipastikan)
    Object.entries(values).forEach(([key, value]) => {
        if (value !== null && String(value).trim() !== '') {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            exportForm.appendChild(input);
        }
    });
    
    document.body.appendChild(exportForm);
    exportForm.submit();
    document.body.removeChild(exportForm);
}

function exportExcel() {
    const form = document.getElementById('filterForm');
    if (!validateRequired(form)) return;
    const values = getFilterValues();
    const exportForm = document.createElement('form');
    exportForm.method = 'POST';
    exportForm.action = '{{ route("manage.export-laporan-perpos-excel") }}';
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    exportForm.appendChild(csrfToken);
    Object.entries(values).forEach(([key, value]) => {
        if (value !== null && String(value).trim() !== '') {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            exportForm.appendChild(input);
        }
    });
    document.body.appendChild(exportForm);
    exportForm.submit();
    document.body.removeChild(exportForm);
}
</script>
@endsection 