@extends('layouts.adminty')

@section('title', 'Laporan Perkelas')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Laporan Perkelas</h4>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form id="filterForm" method="GET" action="{{ route('manage.laporan-perkelas') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">Tahun Ajaran</label>
                                <select name="period_id" class="form-control select-primary" required>
                                    <option value="">Pilih Tahun Ajaran</option>
                                    @foreach($periods as $period)
                                        <option value="{{ $period->period_id }}" {{ $selectedPeriod == $period->period_id ? 'selected' : '' }}>
                                            {{ $period->period_start }}/{{ $period->period_end }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Kelas</label>
                                <select name="class_id" class="form-control select-primary" required>
                                    <option value="">Pilih Kelas</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->class_id }}" {{ $selectedClass == $class->class_id ? 'selected' : '' }}>
                                            {{ $class->class_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Bulan</label>
                                <select name="month" class="form-control select-primary" required>
                                    <option value="">Pilih Bulan</option>
                                    @foreach($months as $key => $month)
                                        <option value="{{ $key }}" {{ $selectedMonth == $key ? 'selected' : '' }}>
                                            {{ $month }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status Siswa</label>
                                <select name="student_status" class="form-control select-primary">
                                    <option value="">Semua Status</option>
                                    <option value="1" {{ $selectedStudentStatus == '1' ? 'selected' : '' }}>Aktif</option>
                                    <option value="0" {{ $selectedStudentStatus == '0' ? 'selected' : '' }}>Tidak Aktif</option>
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
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>

                    @if($data && $data->count() > 0)
                        @php
                            $classesCollection = $classes instanceof \Illuminate\Support\Collection ? $classes : collect($classes);
                            $periodsCollection = $periods instanceof \Illuminate\Support\Collection ? $periods : collect($periods);
                            $selectedClassObj = $classesCollection->firstWhere('class_id', $selectedClass);
                            $selectedPeriodObj = $periodsCollection->firstWhere('period_id', $selectedPeriod);
                            $displayClass = $selectedClassObj->class_name ?? 'Kelas';
                            $displayPeriod = $selectedPeriodObj ? ($selectedPeriodObj->period_start . '/' . $selectedPeriodObj->period_end) : 'Tahun Ajaran';
                        @endphp
                        <div class="alert alert-info">
                            <strong>Laporan Perkelas - {{ $displayClass }}</strong><br>
                            Tahun Ajaran: {{ $displayPeriod }}<br>
                            Bulan: {{ $months[$selectedMonth] ?? 'Bulan' }}<br>
                            Total Data: {{ $data->count() }} item
                        </div>

                        <!-- Tabel Laporan Perkelas -->
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kelas</th>
                                        <th>NIS</th>
                                        <th>Nama</th>
                                        @foreach($posList as $pos)
                                            <th>{{ $pos->pos_name }} - T.A {{ $period->period_start }}/{{ $period->period_end }}</th>
                                        @endforeach
                                        <th>Sub Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item['class_name'] }}</td>
                                            <td>{{ $item['student_nis'] }}</td>
                                            <td>{{ $item['student_full_name'] }}</td>
                                            @foreach($posList as $pos)
                                                @php
                                                    $posData = $item['pos_data'][$pos->pos_id] ?? null;
                                                    $amount = $posData ? $posData['amount'] : 0;
                                                @endphp
                                                <td class="text-center">
                                                    @if($amount > 0)
                                                        <span class="text-danger">Rp {{ number_format($amount, 0, ',', '.') }}</span>
                                                    @else
                                                        <span class="badge bg-success">Lunas</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td class="text-end">
                                                @if($item['subtotal'] > 0)
                                                    <span class="text-danger">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                                                @else
                                                    <span class="badge bg-success">Lunas</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    @if($data->count() > 0)
                                        <tr class="table-info">
                                            <td colspan="4" class="text-end fw-bold">TOTAL</td>
                                            @foreach($posList as $pos)
                                                @php
                                                    $posTotal = 0;
                                                    foreach($data as $item) {
                                                        $posData = $item['pos_data'][$pos->pos_id] ?? null;
                                                        if ($posData && $posData['amount'] > 0) {
                                                            $posTotal += $posData['amount'];
                                                        }
                                                    }
                                                @endphp
                                                <td class="text-end fw-bold">
                                                    @if($posTotal > 0)
                                                        <span class="text-danger">Rp {{ number_format($posTotal, 0, ',', '.') }}</span>
                                                    @else
                                                        <span class="badge bg-success">Lunas</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td class="text-end fw-bold">
                                                <span class="text-danger">Rp {{ number_format($data->sum('subtotal'), 0, ',', '.') }}</span>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    @elseif($selectedPeriod && $selectedClass && $selectedMonth && $data && $data->count() == 0)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Tidak ada data kekurangan pembayaran untuk kriteria yang dipilih.
                        </div>
                    @elseif($selectedPeriod && $selectedClass && $selectedMonth)
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
    const pick = (name) => fd.has(name) && String(fd.get(name)).trim() !== '' ? fd.get(name) : (form.querySelector(`[name="${name}"]`)?.value ?? null);
    return {
        period_id: pick('period_id'),
        class_id: pick('class_id'),
        month: pick('month'),
        student_status: pick('student_status')
    };
}
function validateRequired() {
    const v = getFilterValues();
    const isFilled = (x) => x !== null && String(x).trim() !== '';
    if (!isFilled(v.period_id) || !isFilled(v.class_id) || !isFilled(v.month)) {
        alert('Mohon pilih Tahun Ajaran, Kelas, dan Bulan.');
        return false;
    }
    return true;
}
function exportPDF() {
    if (!validateRequired()) return;
    const values = getFilterValues();
    
    // Buat form untuk export PDF
    const exportForm = document.createElement('form');
    exportForm.method = 'POST';
    exportForm.action = '{{ route("manage.export-laporan-perkelas") }}';
    
    // Tambahkan CSRF token
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    exportForm.appendChild(csrfToken);
    
    // Tambahkan data form yang dipastikan terisi
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