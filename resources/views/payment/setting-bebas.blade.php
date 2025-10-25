@extends('layouts.coreui')

@section('head')
<title>Setting Pembayaran Bebas</title>
<style>
/* Toast Notification Styles */
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
}

.toast {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    margin-bottom: 10px;
    min-width: 300px;
    border-left: 4px solid #28a745;
    animation: slideInRight 0.3s ease-out;
}

.toast.success {
    border-left-color: #28a745;
}

.toast.error {
    border-left-color: #dc3545;
}

.toast.warning {
    border-left-color: #ffc107;
}

.toast.info {
    border-left-color: #17a2b8;
}

.toast-header {
    background: transparent;
    border-bottom: 1px solid #e9ecef;
    padding: 12px 16px 8px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.toast-title {
    font-weight: 600;
    font-size: 14px;
    color: #333;
}

.toast-close {
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    color: #999;
    padding: 0;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.toast-close:hover {
    color: #333;
}

.toast-body {
    padding: 8px 16px 12px;
    color: #666;
    font-size: 13px;
    line-height: 1.4;
}

.toast-icon {
    margin-right: 8px;
    font-size: 16px;
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

.toast.hide {
    animation: slideOutRight 0.3s ease-in forwards;
}
</style>
@endsection

@section('content')
<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card mb-4">
                <div class="card-header bg-light border-bottom-0" style="border-top: 4px solid #2196f3;">
                    <h4 class="mb-0">Tarif Tagihan <small class="text-muted" style="font-size:1rem;">Detail</small></h4>
                </div>
                <div class="card-body pb-2">
                    <div class="mb-2">
                        <strong>Tarif - {{ $payment->pos->pos_name ?? '-' }} - T.A {{ $payment->period->period_start ?? '' }}/{{ $payment->period->period_end ?? '' }}</strong>
                    </div>
                    <form class="row g-3 align-items-end mb-3" method="GET" action="">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Tahun</label>
                            <input type="text" class="form-control" value="{{ $payment->period->period_start ?? '' }}/{{ $payment->period->period_end ?? '' }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Kelas</label>
                            <select class="form-select" name="class_id">
                                <option value="">-- Semua Kelas --</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->class_id }}" {{ (isset($selectedClass) && $selectedClass == $class->class_id) ? 'selected' : '' }}>{{ $class->class_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-success"><i class="fa fa-search me-1"></i> Tampilkan Data</button>
                        </div>
                    </form>
                    <div class="border-top pt-3 mb-3">
                        <div class="fw-bold mb-2">Pengaturan Tarif</div>
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            @if($payment->payment_type == 'BULAN')
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tarifBulananModal">
                                    <i class="fa fa-plus me-1"></i> Berdasarkan Kelas
                                </button>
                            @else
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahTarifModal">
                                    <i class="fa fa-plus me-1"></i> Berdasarkan Kelas
                                </button>
                            @endif
                            <button class="btn btn-warning text-white" id="btnEditMasal" disabled><i class="fa fa-edit me-1"></i> Edit Masal Per Kelas</button>
                            <button class="btn btn-danger text-white" id="btnDeleteMasal" disabled><i class="fa fa-trash me-1"></i> Hapus Masal</button>
                            <a href="{{ route('payment.index') }}" class="btn btn-light border"><i class="fa fa-undo me-1"></i> Kembali</a>
                        </div>
                    </div>
                    @if(request('class_id'))
                        @if(!$hasTarif)
                            <div class="alert alert-info mt-4">Belum ada data tarif tagihan untuk kelas ini. Silakan atur tarif terlebih dahulu.</div>
                        @elseif(isset($students))
                            <div class="table-responsive mt-2">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-success align-middle text-center">
                                        <tr>
                                            <th><input type="checkbox" id="selectAllTarif"></th>
                                            <th>No</th>
                                            <th>NIS</th>
                                            <th>Nama</th>
                                            <th>Kelas</th>
                                            <th>Tarif Tagihan</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="align-middle text-center">
                                        @forelse($students as $i => $student)
                                        @php
                                            $bebas = \App\Models\Bebas::where('student_student_id', $student->student_id)
                                                                       ->where('payment_payment_id', $payment->payment_id)
                                                                       ->first();
                                        @endphp
                                        <tr>
                                            <td>
                                                @if($bebas)
                                                    <input type="checkbox" class="selectTarif" value="{{ $bebas->bebas_id }}">
                                                @endif
                                            </td>
                                            <td>{{ $i+1 }}</td>
                                            <td>{{ $student->student_nis }}</td>
                                            <td>{{ $student->student_full_name }}</td>
                                            <td>{{ $student->class->class_name ?? '-' }}</td>
                                            <td>
                                                @if($bebas)
                                                    Rp. {{ number_format($bebas->bebas_bill, 0, ',', '.') }}
                                                @else
                                                    <span class="text-muted">Belum diatur</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($bebas)
                                                    <button type="button" class="btn btn-warning btn-sm btn-edit-tarif" 
                                                        data-id="{{ $bebas->bebas_id }}" 
                                                        data-bill="{{ $bebas->bebas_bill }}" 
                                                        data-desc="{{ $bebas->bebas_desc }}"
                                                        data-student-id="{{ $student->student_id }}"
                                                        data-student-nama="{{ $student->student_full_name }}"
                                                        data-nis="{{ $student->student_nis }}"
                                                        data-kelas="{{ $student->class->class_name ?? '-' }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form action="#" method="POST" class="d-inline delete-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-danger btn-sm btn-delete-tarif" data-id="{{ $bebas->bebas_id }}"><i class="fas fa-trash"></i></button>
                                                    </form>
                                                @else
                                                    <button type="button" class="btn btn-warning btn-sm btn-edit-tarif" data-id="" data-bill="" data-desc="" data-student-id="{{ $student->student_id }}" data-student-nama="{{ $student->student_full_name }}" data-nis="{{ $student->student_nis }}" data-kelas="{{ $student->class->class_name ?? '-' }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="7" class="text-center">Tidak ada data siswa</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Tarif Pembayaran -->
<div class="modal fade" id="tambahTarifModal" tabindex="-1" aria-labelledby="tambahTarifModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="tambahTarifModalLabel">
                    <i class="fa fa-plus me-2"></i>Tambah Tarif Pembayaran
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Left Section: Informasi Pembayaran -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 pb-0">
                                <h6 class="mb-0 text-dark" style="border-bottom: 3px solid #28a745; padding-bottom: 8px; display: inline-block;">
                                    Informasi Pembayaran
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Jenis Pembayaran</label>
                                    <input type="text" class="form-control" value="{{ $payment->pos->pos_name ?? '-' }} - T.A {{ $payment->period->period_start ?? '' }}/{{ $payment->period->period_end ?? '' }}" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Tahun Pelajaran</label>
                                    <input type="text" class="form-control" value="{{ $payment->period->period_start ?? '' }}/{{ $payment->period->period_end ?? '' }}" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Tipe Pembayaran</label>
                                    <input type="text" class="form-control" value="{{ $payment->payment_type == 'BEBAS' ? 'Bebas' : 'Bulanan' }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Section: Tarif Tagihan Per Kelas -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 pb-0">
                                <h6 class="mb-0 text-dark" style="border-bottom: 3px solid #28a745; padding-bottom: 8px; display: inline-block;">
                                    Tarif Tagihan Per Kelas
                                </h6>
                            </div>
                            <div class="card-body">
                                <form id="tambahTarifForm">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Kelas</label>
                                        <select class="form-select" name="class_id" required>
                                            <option value="">--Pilih Kelas--</option>
                                            @foreach($classes as $class)
                                                <option value="{{ $class->class_id }}">{{ $class->class_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Tarif (Rp.)</label>
                                        <input type="number" class="form-control" name="tarif" placeholder="Masukan Tarif" required>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">Keterangan (Rincian)</label>
                                        <textarea class="form-control" name="keterangan" rows="4" placeholder="Masukan keterangan atau rincian tarif"></textarea>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fa fa-times me-2"></i>Cancel
                </button>
                <button type="button" id="btnSimpanTarif" class="btn btn-success">
                    <i class="fa fa-save me-2"></i>Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Tarif Bebas -->
<div class="modal fade" id="editTarifModal" tabindex="-1" aria-labelledby="editTarifModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editTarifModalLabel">
                    <i class="fa fa-edit me-2"></i>Edit Tarif Bebas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editTarifForm">
                    <input type="hidden" name="bebas_id" id="editBebasId">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tarif (Rp.)</label>
                        <input type="number" class="form-control" name="bebas_bill" id="editBebasBill" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Keterangan (Rincian)</label>
                        <textarea class="form-control" name="bebas_desc" id="editBebasDesc" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="btnUpdateTarif" class="btn btn-warning">Update</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal Edit Masal Tarif Bebas -->
<div class="modal fade" id="editMasalTarifModal" tabindex="-1" aria-labelledby="editMasalTarifModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editMasalTarifModalLabel">
                    <i class="fa fa-edit me-2"></i>Edit Masal Tarif Bebas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editMasalTarifForm">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tarif (Rp.)</label>
                        <input type="number" class="form-control" name="bebas_bill" id="editMasalBebasBill" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Keterangan (Rincian)</label>
                        <textarea class="form-control" name="bebas_desc" id="editMasalBebasDesc" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="btnUpdateMasalTarif" class="btn btn-warning">Update</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal Tambah/Edit Tarif Per Siswa -->
<div class="modal fade" id="tarifSiswaModal" tabindex="-1" aria-labelledby="tarifSiswaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="tarifSiswaModalLabel">
                    <i class="fa fa-plus me-2"></i>Tambah Tarif Tagihan Per Siswa
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Kiri: Informasi Pembayaran -->
                    <div class="col-lg-6">
                        <div class="card border-0" style="border-top: 3px solid #d9534f;">
                            <div class="card-header bg-white border-0 pb-0">
                                <h6 class="mb-0 text-dark" style="border-bottom: 2px solid #d9534f; padding-bottom: 8px; display: inline-block;">
                                    Informasi Pembayaran
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Jenis Pembayaran</label>
                                    <input type="text" class="form-control" value="{{ $payment->pos->pos_name ?? '-' }} - T.A {{ $payment->period->period_start ?? '' }}/{{ $payment->period->period_end ?? '' }}" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Tahun Pelajaran</label>
                                    <input type="text" class="form-control" value="{{ $payment->period->period_start ?? '' }}/{{ $payment->period->period_end ?? '' }}" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Tipe Pembayaran</label>
                                    <input type="text" class="form-control" value="Bebas" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Kanan: Form Input -->
                    <div class="col-lg-6">
                        <div class="card border-0" style="border-top: 3px solid #28a745;">
                            <div class="card-header bg-white border-0 pb-0">
                                <h6 class="mb-0 text-dark" style="border-bottom: 2px solid #28a745; padding-bottom: 8px; display: inline-block;">
                                    Tarif Tagihan Per Siswa
                                </h6>
                            </div>
                            <div class="card-body">
                                <form id="tarifSiswaForm">
                                    <div class="mb-3 row">
                                        <label class="col-sm-4 col-form-label fw-bold">Kelas</label>
                                        <div class="col-sm-8">
                                            <select class="form-select" name="kelas_id" id="kelasSiswaSelectSiswa" required>
                                                <option value="">Pilih Kelas</option>
                                                @foreach($classes as $class)
                                                    <option value="{{ $class->class_id }}">{{ $class->class_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label class="col-sm-4 col-form-label fw-bold">Nama Siswa</label>
                                        <div class="col-sm-8">
                                            <select class="form-select" name="student_id" id="siswaSelectSiswa" required>
                                                <option value="">-- Pilih Siswa --</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label class="col-sm-4 col-form-label fw-bold">Tarif (Rp.)</label>
                                        <div class="col-sm-8">
                                            <input type="number" class="form-control" name="tarif" placeholder="Masukan Tarif" required>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label class="col-sm-4 col-form-label fw-bold">Keterangan (Rincian)</label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" name="keterangan" rows="2" placeholder="Masukan keterangan atau rincian tarif"></textarea>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button type="submit" class="btn btn-success px-4"><i class="fa fa-save me-2"></i>Simpan</button>
                                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"><i class="fa fa-undo me-2"></i>Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal Tarif Bulanan -->
<div class="modal fade" id="tarifBulananModal" tabindex="-1" aria-labelledby="tarifBulananModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="tarifBulananModalLabel">
                    <i class="fa fa-plus me-2"></i>Tambah Tarif Bulanan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formTarifBulananModal">
                    <div class="row">
                        <!-- Kiri: Info & Pilih Kelas -->
                        <div class="col-lg-5">
                            <div class="card mb-3" style="border-top: 3px solid #28a745;">
                                <div class="card-header bg-white border-0 pb-0">
                                    <h6 class="mb-0 text-dark" style="border-bottom: 2px solid #28a745; padding-bottom: 8px; display: inline-block;">Pilih Kelas</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Jenis Pembayaran</label>
                                        <input type="text" class="form-control" value="{{ $payment->pos->pos_name ?? '-' }} - T.A {{ $payment->period->period_start ?? '' }}/{{ $payment->period->period_end ?? '' }}" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Tahun Pelajaran</label>
                                        <input type="text" class="form-control" value="{{ $payment->period->period_start ?? '' }}/{{ $payment->period->period_end ?? '' }}" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Tipe Pembayaran</label>
                                        <input type="text" class="form-control" value="Bulanan" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Kelas</label>
                                        <select class="form-select" name="class_id" id="kelasBulananSelect" required>
                                            <option value="">---Pilih Kelas---</option>
                                            @foreach($classes as $class)
                                                <option value="{{ $class->class_id }}">{{ $class->class_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="card" style="border-top: 3px solid #28a745;">
                                <div class="card-header bg-white border-0 pb-0">
                                    <h6 class="mb-0 text-dark" style="border-bottom: 2px solid #28a745; padding-bottom: 8px; display: inline-block;">Tarif Setiap Bulan Sama</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2 row align-items-center">
                                        <label class="col-sm-4 col-form-label fw-bold">Tarif Bulanan (Rp.)</label>
                                        <div class="col-sm-8">
                                            <input type="number" class="form-control" id="tarifBulananSama" placeholder="Masukkan Nilai dan Tekan Enter">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Kanan: Tarif per Bulan -->
                        <div class="col-lg-7">
                            <div class="card" style="border-top: 3px solid #28a745;">
                                <div class="card-header bg-white border-0 pb-0">
                                    <h6 class="mb-0 text-dark" style="border-bottom: 2px solid #28a745; padding-bottom: 8px; display: inline-block;">Tarif Setiap Bulan Tidak Sama</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-2">
                                        @php $bulanList = ['Juli','Agustus','September','Oktober','November','Desember','Januari','Februari','Maret','April','Mei','Juni']; @endphp
                                        @foreach($bulanList as $bulan)
                                        <div class="col-4 text-end fw-bold pt-2">{{ $bulan }}</div>
                                        <div class="col-8 mb-2">
                                            <input type="number" class="form-control input-bulan" name="bulan[{{ $bulan }}]" placeholder="">
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2 justify-content-end mt-3">
                                <button type="submit" class="btn btn-success px-4"><i class="fa fa-save me-2"></i>Simpan</button>
                                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"><i class="fa fa-undo me-2"></i>Cancel</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Modal Buat/Edit Tarif Bebas Siswa (untuk siswa yang belum punya tarif) -->
<div class="modal fade" id="buatTarifBebasModal" tabindex="-1" aria-labelledby="buatTarifBebasModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="buatTarifBebasModalLabel">
                    <i class="fa fa-plus me-2"></i>Buat/Edit Tarif Bebas
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formBuatTarifBebas">
                    <input type="hidden" name="student_id" id="buatStudentId">
                    <div class="mb-2"><b id="buatStudentInfo"></b></div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tarif (Rp.)</label>
                        <input type="number" class="form-control" name="tarif" id="buatTarifBebas" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Keterangan</label>
                        <textarea class="form-control" name="keterangan" id="buatDescBebas" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="btnSimpanBuatTarifBebas" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-danger text-white border-0">
        <h5 class="modal-title" id="deleteModalLabel">
          <i class="fa fa-trash me-2"></i>Konfirmasi Hapus Tarif
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center py-4">
        <div class="mb-3">
          <i class="fa fa-question-circle text-warning" style="font-size: 3rem;"></i>
        </div>
        <h5 class="mb-3">Yakin ingin menghapus tarif ini?</h5>
        <p class="text-muted mb-0">Tindakan ini tidak dapat dibatalkan.</p>
      </div>
      <div class="modal-footer border-0 justify-content-center">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
          <i class="fa fa-times me-2"></i>Batal
        </button>
        <button type="button" class="btn btn-danger px-4" id="confirmDeleteBtn">
          <i class="fa fa-trash me-2"></i>Ya, Hapus
        </button>
      </div>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script>
// Toast notification functions
function showSuccessToast(message, title = 'Berhasil') {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: title,
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    } else {
        // Fallback to alert if SweetAlert is not available
        alert(title + ': ' + message);
    }
}

function showErrorToast(message, title = 'Gagal') {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: title,
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true
        });
    } else {
        // Fallback to alert if SweetAlert is not available
        alert(title + ': ' + message);
    }
}

function showWarningToast(message, title = 'Peringatan') {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'warning',
            title: title,
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    } else {
        // Fallback to alert if SweetAlert is not available
        alert(title + ': ' + message);
    }
}

document.addEventListener('DOMContentLoaded', function() {

    console.log('DOMContentLoaded: script berjalan');
    // Handle form submission tambah tarif per kelas
    var btnSimpanTarif = document.getElementById('btnSimpanTarif');
    if (btnSimpanTarif) {
        btnSimpanTarif.addEventListener('click', function() {
            const form = document.getElementById('tambahTarifForm');
            const formData = new FormData(form);
            if (!formData.get('class_id')) {
                showWarningToast('Silakan pilih kelas terlebih dahulu!', 'Peringatan');
                return;
            }
            if (!formData.get('tarif') || formData.get('tarif') <= 0) {
                showWarningToast('Silakan masukkan tarif yang valid!', 'Peringatan');
                return;
            }
            const originalText = btnSimpanTarif.innerHTML;
            btnSimpanTarif.disabled = true;
            btnSimpanTarif.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Menyimpan...';
            fetch('{{ route("payment.store-tarif-bebas", $payment->payment_id) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    class_id: formData.get('class_id'),
                    tarif: formData.get('tarif'),
                    keterangan: formData.get('keterangan')
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessToast(data.message, 'Berhasil');
                    // Reset form
                    form.reset();
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('tambahTarifModal'));
                    if (modal) modal.hide();
                    // Reload page after delay
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showErrorToast(data.message || 'Gagal menyimpan tarif!', 'Gagal');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorToast('Terjadi kesalahan saat menyimpan tarif!', 'Error');
            })
            .finally(() => {
                btnSimpanTarif.disabled = false;
                btnSimpanTarif.innerHTML = originalText;
            });
        });
    }

    // Event handler untuk modal tambah tarif per kelas
    var tambahTarifModal = document.getElementById('tambahTarifModal');
    if (tambahTarifModal) {
        tambahTarifModal.addEventListener('hidden.bs.modal', function () {
            // Reset form dan state ketika modal ditutup
            document.getElementById('tambahTarifForm').reset();
        });
        
        tambahTarifModal.addEventListener('show.bs.modal', function () {
            // Pastikan form bersih ketika modal dibuka
            document.getElementById('tambahTarifForm').reset();
        });
    }

    // Edit Tarif Satuan
    document.querySelectorAll('.btn-edit-tarif').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var bebasId = this.dataset.id;
            var bill = this.dataset.bill;
            var desc = this.dataset.desc;
            var studentId = this.dataset.studentId;
            var nama = this.dataset.studentNama;
            var nis = this.dataset.nis;
            var kelas = this.dataset.kelas;
            // Jika belum ada tarif, tampilkan modal input tarif baru
            if (!bebasId) {
                document.getElementById('buatStudentId').value = studentId;
                document.getElementById('buatStudentInfo').innerText = nama + ' (' + nis + ') - ' + kelas;
                document.getElementById('buatTarifBebas').value = '';
                document.getElementById('buatDescBebas').value = '';
                new bootstrap.Modal(document.getElementById('buatTarifBebasModal')).show();
            } else {
                document.getElementById('editBebasId').value = bebasId;
                document.getElementById('editBebasBill').value = bill;
                document.getElementById('editBebasDesc').value = desc || '';
                new bootstrap.Modal(document.getElementById('editTarifModal')).show();
            }
        });
    });
    var btnUpdateTarif = document.getElementById('btnUpdateTarif');
    if (btnUpdateTarif) {
        btnUpdateTarif.addEventListener('click', function() {
            const id = document.getElementById('editBebasId').value;
            const bill = document.getElementById('editBebasBill').value;
            const desc = document.getElementById('editBebasDesc').value;
            if (!bill || bill <= 0) { Swal.fire('Peringatan', 'Tarif harus diisi!', 'warning'); return; }
            this.disabled = true;
            this.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Updating...';
            fetch('/payment/' + id + '/update-tarif-bebas', {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ bebas_bill: bill, bebas_desc: desc })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showSuccessToast(data.message || 'Berhasil update!', 'Berhasil');
                    // Tutup modal sebelum reload
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editTarifModal'));
                    if (modal) modal.hide();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showErrorToast(data.message || 'Gagal update!', 'Gagal');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorToast('Gagal update!', 'Error');
            })
            .finally(() => { 
                this.disabled = false; 
                this.innerHTML = 'Update'; 
            });
        });
    }

    // Event handler untuk modal edit tarif bebas
    var editTarifModal = document.getElementById('editTarifModal');
    if (editTarifModal) {
        editTarifModal.addEventListener('hidden.bs.modal', function () {
            // Reset form dan state ketika modal ditutup
            document.getElementById('editTarifForm').reset();
            document.getElementById('editBebasId').value = '';
            document.getElementById('editBebasBill').value = '';
            document.getElementById('editBebasDesc').value = '';
        });
        
        editTarifModal.addEventListener('show.bs.modal', function () {
            // Pastikan form bersih ketika modal dibuka
            document.getElementById('editTarifForm').reset();
        });
    }
    // Delete Tarif Satuan
    let deleteForm = null;
    document.querySelectorAll('.btn-delete-tarif').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            deleteForm = this.closest('form');
            deleteForm.action = '/payment/' + id + '/delete-tarif-bebas-siswa';
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        });
    });

    // Event handler untuk modal delete
    var deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('hidden.bs.modal', function () {
            // Reset state ketika modal ditutup
            deleteForm = null;
        });
    }

    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (deleteForm) {
            fetch(deleteForm.action, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            })
            .then(r => r.json().then(data => ({ok: r.ok, data})))
            .then(({ok, data}) => {
                if (ok) {
                    Swal.fire('Berhasil', data.message || 'Berhasil dihapus!', 'success');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    Swal.fire('Gagal', data.message || 'Gagal hapus!', 'error');
                }
            })
            .catch(() => Swal.fire('Gagal', 'Terjadi kesalahan jaringan.', 'error'))
            .finally(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
                modal.hide();
            });
        }
    });

    // Bulk select, edit, delete
    let selectedTarif = [];
    function updateBulkButtons() {
        selectedTarif = Array.from(document.querySelectorAll('.selectTarif:checked')).map(cb => cb.value);
        var btnEditMasal = document.getElementById('btnEditMasal');
        if (btnEditMasal) {
            btnEditMasal.disabled = selectedTarif.length === 0;
        }
        var btnDeleteMasal = document.getElementById('btnDeleteMasal');
        if (btnDeleteMasal) {
            btnDeleteMasal.disabled = selectedTarif.length === 0;
        }
    }
    var selectAllTarif = document.getElementById('selectAllTarif');
    if (selectAllTarif) {
        selectAllTarif.addEventListener('change', function() {
            document.querySelectorAll('.selectTarif').forEach(cb => { cb.checked = this.checked; });
            updateBulkButtons();
        });
    }
    document.querySelectorAll('.selectTarif').forEach(cb => {
        cb.addEventListener('change', updateBulkButtons);
    });
    var btnEditMasal = document.getElementById('btnEditMasal');
    if (btnEditMasal) {
        btnEditMasal.addEventListener('click', function() {
            document.getElementById('editMasalBebasBill').value = '';
            document.getElementById('editMasalBebasDesc').value = '';
            new bootstrap.Modal(document.getElementById('editMasalTarifModal')).show();
        });
    }
    var btnUpdateMasalTarif = document.getElementById('btnUpdateMasalTarif');
    if (btnUpdateMasalTarif) {
        btnUpdateMasalTarif.addEventListener('click', function() {
            const bill = document.getElementById('editMasalBebasBill').value;
            const desc = document.getElementById('editMasalBebasDesc').value;
            if (!bill || bill <= 0) { Swal.fire('Peringatan', 'Tarif harus diisi!', 'warning'); return; }
            this.disabled = true;
            this.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Updating...';
            fetch('/payment/{{ $payment->payment_id }}/bulk-update-tarif-bebas', {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ ids: selectedTarif, bebas_bill: bill, bebas_desc: desc })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showSuccessToast(data.message || 'Berhasil update masal!', 'Berhasil');
                    // Tutup modal sebelum reload
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editMasalTarifModal'));
                    if (modal) modal.hide();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showErrorToast(data.message || 'Gagal update masal!', 'Gagal');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorToast('Gagal update masal!', 'Error');
            })
            .finally(() => { 
                this.disabled = false; 
                this.innerHTML = 'Update Masal'; 
            });
        });
    }

    // Event handler untuk modal edit masal tarif bebas
    var editMasalTarifModal = document.getElementById('editMasalTarifModal');
    if (editMasalTarifModal) {
        editMasalTarifModal.addEventListener('hidden.bs.modal', function () {
            // Reset form dan state ketika modal ditutup
            document.getElementById('editMasalTarifForm').reset();
            document.getElementById('editMasalBebasBill').value = '';
            document.getElementById('editMasalBebasDesc').value = '';
        });
        
        editMasalTarifModal.addEventListener('show.bs.modal', function () {
            // Pastikan form bersih ketika modal dibuka
            document.getElementById('editMasalTarifForm').reset();
        });
    }

    var btnDeleteMasal = document.getElementById('btnDeleteMasal');
    if (btnDeleteMasal) {
        btnDeleteMasal.addEventListener('click', function() {
            if (selectedTarif.length === 0) {
                Swal.fire('Peringatan', 'Tidak ada data yang dipilih untuk dihapus!', 'warning');
                return;
            }
            Swal.fire({
                title: 'Konfirmasi',
                text: 'Yakin ingin menghapus tarif yang dipilih?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.disabled = true;
                    this.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Deleting...';
                    fetch('/payment/{{ $payment->payment_id }}/delete-tarif-bebas', {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
                        body: JSON.stringify({ ids: selectedTarif })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Berhasil', data.message, 'success');
                            location.reload();
                        } else {
                            Swal.fire('Gagal', 'Gagal hapus: ' + (data.message || 'Unknown error'), 'error');
                        }
                    })
                    .catch(() => Swal.fire('Gagal', 'Gagal hapus!', 'error'))
                    .finally(() => { this.disabled = false; this.innerHTML = 'Hapus Masal'; });
                }
            });
        });
    }

    // Dropdown siswa dinamis berdasarkan kelas (khusus modal siswa)
    var kelasSiswa = document.getElementById('kelasSiswaSelectSiswa');
    if (kelasSiswa) {
        kelasSiswa.addEventListener('change', function() {
            console.log('Kelas dipilih:', this.value);
            const classId = this.value;
            const siswaSelect = document.getElementById('siswaSelectSiswa');
            siswaSelect.innerHTML = '<option value="">Memuat...</option>';
            if (!classId) {
                siswaSelect.innerHTML = '<option value="">-- Pilih Siswa --</option>';
                return;
            }
            fetch('/students-get-by-class', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ class_id: classId })
            })
            .then(r => r.json())
            .then(data => {
                console.log('AJAX siswa:', data); // Log detail
                let html = '<option value="">-- Pilih Siswa --</option>';
                if (data && data.students && data.students.length > 0) {
                    data.students.forEach(function(s) {
                        html += `<option value="${s.student_id}">${s.student_full_name} (${s.student_nis})</option>`;
                    });
                } else {
                    html += '<option value="">Tidak ada siswa aktif di kelas ini</option>';
                    Swal.fire('Info', 'Tidak ada siswa aktif di kelas ini', 'info');
                }
                siswaSelect.innerHTML = html;
            })
            .catch((err) => {
                console.error('AJAX siswa error:', err);
                siswaSelect.innerHTML = '<option value="">-- Pilih Siswa --</option>';
                Swal.fire('Gagal', 'Gagal mengambil data siswa!', 'error');
            });
        });
    }
    var modalSiswa = document.getElementById('tarifSiswaModal');
    if (modalSiswa) {
        modalSiswa.addEventListener('shown.bs.modal', function () {
            console.log('Modal siswa dibuka');
            var kelasSiswaSelect = document.getElementById('kelasSiswaSelectSiswa');
            if (kelasSiswaSelect) kelasSiswaSelect.selectedIndex = 0;
            const siswaSelect = document.getElementById('siswaSelectSiswa');
            if (siswaSelect) siswaSelect.innerHTML = '<option value="">-- Pilih Siswa --</option>';
        });
        
        modalSiswa.addEventListener('hidden.bs.modal', function () {
            // Reset form dan state ketika modal ditutup
            if (formSiswa) {
                formSiswa.reset();
            }
            var kelasSiswaSelect = document.getElementById('kelasSiswaSelectSiswa');
            if (kelasSiswaSelect) kelasSiswaSelect.selectedIndex = 0;
            const siswaSelect = document.getElementById('siswaSelectSiswa');
            if (siswaSelect) siswaSelect.innerHTML = '<option value="">-- Pilih Siswa --</option>';
        });
    }
    var formSiswa = document.getElementById('tarifSiswaForm');
    if (formSiswa) {
        formSiswa.addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const kelasId = form.kelas_id.value;
            const studentId = form.student_id.value;
            const tarif = form.tarif.value;
            const keterangan = form.keterangan.value;
            if (!kelasId || !studentId || !tarif || tarif <= 0) {
                showWarningToast('Semua field wajib diisi dan tarif harus lebih dari 0!', 'Peringatan');
                return;
            }
            const btn = form.querySelector('button[type=submit]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Menyimpan...';
            fetch(`{{ route('payment.store-tarif-bebas-siswa', $payment->payment_id) }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ kelas_id: kelasId, student_id: studentId, tarif: tarif, keterangan: keterangan })
            })
            .then(r => r.json())
                                .then(data => {
                if (data.success) {
                    showSuccessToast(data.message, 'Berhasil');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showErrorToast(data.message || 'Gagal simpan!', 'Gagal');
                }
            })
            .catch(() => showErrorToast('Gagal simpan!', 'Gagal'))
            .finally(() => { btn.disabled = false; btn.innerHTML = '<i class="fa fa-save me-2"></i>Simpan'; });
        });
    }

    // Buat tarif bebas per siswa (pakai modal Bootstrap)
    document.querySelectorAll('.btn-edit-tarif').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var bebasId = this.dataset.id;
            var bill = this.dataset.bill;
            var desc = this.dataset.desc;
            var studentId = this.dataset.studentId;
            var nama = this.dataset.studentNama;
            var nis = this.dataset.nis;
            var kelas = this.dataset.kelas;
            if (!bebasId) {
                document.getElementById('buatStudentId').value = studentId;
                document.getElementById('buatStudentInfo').innerText = nama + ' (' + nis + ') - ' + kelas;
                document.getElementById('buatTarifBebas').value = '';
                document.getElementById('buatDescBebas').value = '';
                new bootstrap.Modal(document.getElementById('buatTarifBebasModal')).show();
            } else {
                document.getElementById('editBebasId').value = bebasId;
                document.getElementById('editBebasBill').value = bill;
                document.getElementById('editBebasDesc').value = desc || '';
                new bootstrap.Modal(document.getElementById('editTarifModal')).show();
            }
        });
    });
    // Simpan tarif bebas baru dari modal
    document.getElementById('btnSimpanBuatTarifBebas').addEventListener('click', function() {
        var form = document.getElementById('formBuatTarifBebas');
        var studentId = form.buatStudentId.value;
        var tarif = form.buatTarifBebas.value;
        var keterangan = form.buatDescBebas.value;
        if (!tarif || tarif <= 0) {
            showWarningToast('Tarif harus diisi dan lebih dari 0!', 'Peringatan');
            return;
        }
        this.disabled = true;
        this.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Menyimpan...';
        fetch(`/payment/{{ $payment->payment_id }}/store-tarif-bebas-siswa`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ kelas_id: {{ $selectedClass ?? 'null' }}, student_id: studentId, tarif: tarif, keterangan: keterangan })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showSuccessToast(data.message, 'Berhasil');
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('buatTarifBebasModal'));
                if (modal) modal.hide();
                setTimeout(() => location.reload(), 1500);
            } else {
                showErrorToast(data.message || 'Gagal simpan!', 'Gagal');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorToast('Gagal simpan!', 'Error');
        })
        .finally(() => {
            this.disabled = false;
            this.innerHTML = 'Simpan';
        });
    });

    // Event handler untuk modal tambah tarif bebas
    var tambahTarifBebasModal = document.getElementById('tambahTarifBebasModal');
    if (tambahTarifBebasModal) {
        tambahTarifBebasModal.addEventListener('hidden.bs.modal', function () {
            // Reset form dan state ketika modal ditutup
            document.getElementById('tambahTarifForm').reset();
        });
        
        tambahTarifBebasModal.addEventListener('show.bs.modal', function () {
            // Pastikan form bersih ketika modal dibuka
            document.getElementById('tambahTarifForm').reset();
        });
    }

    // Event handler untuk modal buat tarif bebas
    var buatTarifBebasModal = document.getElementById('buatTarifBebasModal');
    if (buatTarifBebasModal) {
        buatTarifBebasModal.addEventListener('hidden.bs.modal', function () {
            // Reset form dan state ketika modal ditutup
            document.getElementById('formBuatTarifBebas').reset();
            document.getElementById('buatStudentId').value = '';
            document.getElementById('buatStudentInfo').innerText = '';
            document.getElementById('buatTarifBebas').value = '';
            document.getElementById('buatDescBebas').value = '';
        });
        
        buatTarifBebasModal.addEventListener('show.bs.modal', function () {
            // Pastikan form bersih ketika modal dibuka
            document.getElementById('formBuatTarifBebas').reset();
        });
    }

    // Handle form submission tambah tarif bulanan
    var formTarifBulanan = document.getElementById('formTarifBulananModal');
    if (formTarifBulanan) {
        formTarifBulanan.addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const classId = form.kelasBulananSelect.value;
            const tarifSama = form.tarifBulananSama.value;
            const bulanTarif = {};
            form.querySelectorAll('.input-bulan').forEach(function(input) {
                bulanTarif[input.name.split('[')[1].replace(']', '')] = input.value;
            });

            if (!classId) {
                showWarningToast('Silakan pilih kelas terlebih dahulu!', 'Peringatan');
                return;
            }
            if (!tarifSama || tarifSama <= 0) {
                showWarningToast('Silakan masukkan tarif bulanan yang valid!', 'Peringatan');
                return;
            }

            // Log data yang akan dikirim
            console.log('Submit tarif bulanan:', {
                class_id: classId,
                tarif_sama: tarifSama,
                bulan_tarif: bulanTarif
            });

            const btn = form.querySelector('button[type=submit]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Menyimpan...';

            fetch('{{ route("payment.store-tarif-bulanan", $payment->payment_id) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    class_id: classId,
                    tarif_sama: tarifSama,
                    bulan_tarif: bulanTarif
                })
            })
            .then(response => {
                if (!response.ok) throw new Error('HTTP error ' + response.status);
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showSuccessToast(data.message, 'Berhasil');
                    const modal = bootstrap.Modal.getInstance(document.getElementById('tarifBulananModal'));
                    modal.hide();
                    form.reset();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showErrorToast('Terjadi kesalahan: ' + (data.message || 'Unknown error'), 'Gagal');
                }
            })
            .catch(error => {
                console.error('Error simpan tarif bulanan:', error);
                showErrorToast('Terjadi kesalahan saat menyimpan data!', 'Gagal');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = 'Simpan';
            });
        });
    }

    // Event handler untuk modal tarif bulanan
    var tarifBulananModal = document.getElementById('tarifBulananModal');
    if (tarifBulananModal) {
        tarifBulananModal.addEventListener('hidden.bs.modal', function () {
            // Reset form dan state ketika modal ditutup
            if (formTarifBulanan) {
                formTarifBulanan.reset();
            }
        });
        
        tarifBulananModal.addEventListener('show.bs.modal', function () {
            // Pastikan form bersih ketika modal dibuka
            if (formTarifBulanan) {
                formTarifBulanan.reset();
            }
        });
    }

    // Autofill tarif bulanan sama ke semua bulan jika diisi dan tekan Enter
    var inputSama = document.getElementById('tarifBulananSama');
    if (inputSama) {
        inputSama.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                var val = this.value;
                document.querySelectorAll('.input-bulan').forEach(function(inp) {
                    inp.value = val;
                });
            }
        });
    }

    var btnKembali = document.getElementById('btnKembali');
    if (btnKembali) {
        btnKembali.addEventListener('click', function() {
            window.history.back();
        });
    }

    // Toast Notification Functions
    function showToast(type, title, message, duration = 5000) {
        const toastContainer = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        
        const iconMap = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };
        
        toast.innerHTML = `
            <div class="toast-header">
                <div class="toast-title">
                    <i class="fa ${iconMap[type]} toast-icon"></i>
                    ${title}
                </div>
                <button class="toast-close" onclick="this.parentElement.parentElement.remove()">&times;</button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        `;
        
        toastContainer.appendChild(toast);
        
        // Auto remove after duration
        setTimeout(() => {
            if (toast.parentElement) {
                toast.classList.add('hide');
                setTimeout(() => {
                    if (toast.parentElement) {
                        toast.remove();
                    }
                }, 300);
            }
        }, duration);
        
        return toast;
    }

    // Success toast shortcut
    function showSuccessToast(message, title = 'Berhasil') {
        return showToast('success', title, message);
    }

    // Error toast shortcut
    function showErrorToast(message, title = 'Error') {
        return showToast('error', title, message);
    }

    // Warning toast shortcut
    function showWarningToast(message, title = 'Peringatan') {
        return showToast('warning', title, message);
    }

    // Info toast shortcut
    function showInfoToast(message, title = 'Informasi') {
        return showToast('info', title, message);
    }
});
</script>
@endsection 