@extends('layouts.adminty')

@section('head')
<title>Setting Pembayaran Bulanan</title>
<style>
    /* Custom width untuk modal tarif bulanan */
    #tarifBulananModal .modal-dialog {
        max-width: 95%;
        width: 95%;
    }
    
    @media (min-width: 1200px) {
        #tarifBulananModal .modal-dialog {
            max-width: 1400px;
            width: 1400px;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card mb-4">
                <div class="card-header bg-light border-bottom-0" style="border-top: 4px solid #2196f3;">
                    <h4 class="mb-0">Tarif Tagihan Bulanan <small class="text-muted" style="font-size:1rem;">Detail</small></h4>
                </div>
                <div class="card-body pb-2">
                    <form class="row g-3 align-items-end mb-3" method="GET" action="">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Tahun</label>
                            <input type="text" class="form-control" value="{{ $payment->period->period_start ?? '' }}/{{ $payment->period->period_end ?? '' }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Kelas</label>
                            <select class="form-control select-primary" name="class_id">
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
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <button type="button" class="btn btn-primary" onclick="openTarifBulananModal()">
                                <i class="fa fa-plus me-1"></i> Berdasarkan Kelas
                            </button>

                            <button type="button" class="btn btn-danger text-white" id="btnDeleteMasalBulanan" disabled>
                                <i class="fa fa-trash me-1"></i> Hapus Masal
                            </button>
                            <a href="{{ route('payment.index') }}" class="btn btn-light border">
                                <i class="fa fa-undo me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                    @if(request('class_id'))
                        @if(!$hasTarif)
                            <div class="alert alert-info mt-4">Belum ada data tarif bulanan untuk kelas ini. Silakan atur tarif terlebih dahulu.</div>
                        @elseif(isset($students))
                            <div class="table-responsive mt-2">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-success align-middle text-center">
                                        <tr>
                                            <th><input type="checkbox" id="selectAllBulanan"></th>
                                            <th>No</th>
                                            <th>NIS</th>
                                            <th>Nama</th>
                                            <th>Kelas</th>
                                            <th>Total</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="align-middle text-center">
                                        @forelse($students as $i => $student)
                                        @php
                                            $bulanData = DB::table('bulan')
                                                ->where('student_student_id', $student->student_id)
                                                ->where('payment_payment_id', $payment->payment_id)
                                                ->orderBy('month_month_id')
                                                ->get();
                                            $total = $bulanData->sum('bulan_bill');
                                        @endphp
                                        <tr>
                                            <td><input type="checkbox" class="selectBulanan" value="{{ $student->student_id }}"></td>
                                            <td>{{ $i+1 }}</td>
                                            <td>{{ $student->student_nis }}</td>
                                            <td>{{ $student->student_full_name }}</td>
                                            <td>{{ $student->class->class_name ?? '-' }}</td>
                                            <td>
                                                @if($total > 0)
                                                    {{ 'Rp. '.number_format($total,0,',','.') }}
                                                @else
                                                    <span class="text-muted">Belum diatur</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-warning btn-sm btn-edit-bulanan" 
                                                    data-student-id="{{ $student->student_id }}" 
                                                    data-student-nama="{{ $student->student_full_name }}"
                                                    data-nis="{{ $student->student_nis }}"
                                                    data-kelas="{{ $student->class->class_name ?? '-' }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="#" method="POST" class="d-inline delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-danger btn-sm btn-delete-bulanan" 
                                                        data-student-id="{{ $student->student_id }}" 
                                                        data-student-nama="{{ $student->student_full_name }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
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
<!-- Modal Tarif Bulanan -->
<div class="modal fade" id="tarifBulananModal" tabindex="-1" role="dialog" aria-labelledby="tarifBulananModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="tarifBulananModalLabel">
                    <i class="fa fa-plus me-2"></i>Tambah Tarif Bulanan
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" onclick="closeTarifBulananModal()" style="opacity: 1; font-size: 1.5rem; padding: 0.5rem;">
                    <span aria-hidden="true">&times;</span>
                </button>
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
                                        <select class="form-control select-primary" name="class_id" id="kelasBulananSelect" required>
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
                                <button type="button" class="btn btn-secondary px-4" data-dismiss="modal" onclick="closeTarifBulananModal()"><i class="fa fa-undo me-2"></i>Cancel</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Modal Edit Tarif Bulanan Satuan -->
<div class="modal fade" id="editBulananModal" tabindex="-1" role="dialog" aria-labelledby="editBulananModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editBulananModalLabel">
                    <i class="fa fa-edit me-2"></i>Edit Tarif Bulanan Siswa
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="closeEditBulananModal()" style="opacity: 1; font-size: 1.5rem; padding: 0.5rem;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formEditBulanan">
                    <input type="hidden" name="student_id" id="editStudentId">
                    <div class="mb-2"><b id="editStudentInfo"></b></div>
                    <div class="row g-2">
                        @foreach($bulanList as $idx => $bulan)
                        <div class="col-4 text-end fw-bold pt-2">{{ $bulan }}</div>
                        <div class="col-8 mb-2">
                            <input type="number" class="form-control edit-input-bulan" name="bulan[{{ $bulan }}]" id="editBulan{{ $idx+1 }}" placeholder="">
                        </div>
                        @endforeach
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="closeEditBulananModal()">Batal</button>
                <button type="button" id="btnUpdateBulanan" class="btn btn-warning">Update</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal Tarif Bulanan Per Siswa -->
<div class="modal fade" id="tarifSiswaModal" tabindex="-1" aria-labelledby="tarifSiswaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="tarifSiswaModalLabel">
                    <i class="fa fa-plus me-2"></i>Tambah Tarif Bulanan Per Siswa
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" onclick="closeTarifBulananModal()" style="opacity: 1; font-size: 1.5rem; padding: 0.5rem;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formTarifBulananSiswa">
                    <div class="row g-3 align-items-end mb-3">
                        <div class="col-md-5">
                            <label class="form-label fw-bold">Kelas</label>
                            <select class="form-control select-primary" name="kelas_id" id="kelasSiswaSelectBulanan" required>
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->class_id }}">{{ $class->class_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label fw-bold">Siswa</label>
                            <select class="form-control select-primary" name="student_id" id="siswaSelectBulanan" required>
                                <option value="">-- Pilih Siswa --</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2">
                        @php $bulanList = ['Juli','Agustus','September','Oktober','November','Desember','Januari','Februari','Maret','April','Mei','Juni']; @endphp
                        @foreach($bulanList as $idx => $bulan)
                        <div class="col-4 text-end fw-bold pt-2">{{ $bulan }}</div>
                        <div class="col-8 mb-2">
                            <input type="number" class="form-control input-bulan-siswa" name="bulan[{{ $bulan }}]" placeholder="">
                        </div>
                        @endforeach
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="closeEditBulananModal()">Batal</button>
                <button type="button" id="btnSimpanTarifBulananSiswa" class="btn btn-info text-white">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-danger text-white border-0">
        <h5 class="modal-title" id="deleteModalLabel">
          <i class="fa fa-trash me-2"></i>Konfirmasi Hapus Tarif
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" onclick="closeDeleteModalBulanan()" style="opacity: 1; font-size: 1.5rem; padding: 0.5rem;">
            <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center py-4">
        <div class="mb-3">
          <i class="fa fa-question-circle text-warning" style="font-size: 3rem;"></i>
        </div>
        <h5 class="mb-3">Yakin ingin menghapus tarif bulanan untuk siswa <span id="modalStudentName"></span>?</h5>
        <p class="text-muted mb-0">Tindakan ini tidak dapat dibatalkan.</p>
      </div>
      <div class="modal-footer border-0 justify-content-center">
        <button type="button" class="btn btn-secondary px-4" data-dismiss="modal" onclick="closeDeleteModalBulanan()">
          <i class="fa fa-times me-2"></i>Batal
        </button>
        <button type="button" class="btn btn-danger px-4" id="confirmDeleteBtn" onclick="confirmDeleteBulanan()">
          <i class="fa fa-trash me-2"></i>Ya, Hapus
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')

<script>
    // Fungsi untuk membuka modal tarif bulanan - Global
    window.openTarifBulananModal = function() {
        if (typeof $ !== 'undefined' && $.fn.modal) {
            $('#tarifBulananModal').modal('show');
        } else {
            console.error('jQuery modal not available');
        }
    };
    
    // Fungsi untuk menutup modal tarif bulanan - Global
    window.closeTarifBulananModal = function() {
        if (typeof $ !== 'undefined' && $.fn.modal) {
            $('#tarifBulananModal').modal('hide');
        } else {
            const modal = document.getElementById('tarifBulananModal');
            if (modal) {
                modal.style.display = 'none';
                modal.classList.remove('show');
                document.body.classList.remove('modal-open');
            }
        }
    };
    
    // Fungsi untuk menutup modal edit bulanan - Global
    window.closeEditBulananModal = function() {
        if (typeof $ !== 'undefined' && $.fn.modal) {
            $('#editBulananModal').modal('hide');
        } else {
            const modal = document.getElementById('editBulananModal');
            if (modal) {
                modal.style.display = 'none';
                modal.classList.remove('show');
                document.body.classList.remove('modal-open');
            }
        }
    };
    
    // Fungsi untuk menutup modal tarif siswa - Global
    window.closeTarifSiswaModal = function() {
        if (typeof $ !== 'undefined' && $.fn.modal) {
            $('#tarifSiswaModal').modal('hide');
        } else {
            const modal = document.getElementById('tarifSiswaModal');
            if (modal) {
                modal.style.display = 'none';
                modal.classList.remove('show');
                document.body.classList.remove('modal-open');
            }
        }
    };
    
    // Fungsi untuk menutup modal delete bulanan - Global
    window.closeDeleteModalBulanan = function() {
        if (typeof $ !== 'undefined' && $.fn.modal) {
            $('#deleteModal').modal('hide');
        } else {
            const modal = document.getElementById('deleteModal');
            if (modal) {
                modal.style.display = 'none';
                modal.classList.remove('show');
                document.body.classList.remove('modal-open');
            }
        }
    };
    
    // Fungsi untuk konfirmasi delete bulanan - Global
    window.confirmDeleteBulanan = function() {
        if (deleteForm) {
            fetch(deleteForm.action, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            })
            .then(r => r.json().then(data => ({ok: r.ok, data})))
            .then(({ok, data}) => {
                if (ok) {
                    showToast(data.message || 'Berhasil dihapus!');
                    setTimeout(() => {
                        const currentUrl = new URL(window.location.href);
                        const classId = currentUrl.searchParams.get('class_id');
                        if (classId) {
                            window.location.href = currentUrl.pathname + '?class_id=' + classId;
                        } else {
                            location.reload();
                        }
                    }, 2000);
                } else {
                    showToast(data.message || 'Gagal hapus!', 'danger');
                }
            })
            .catch(() => showToast('Terjadi kesalahan jaringan.', 'danger'))
            .finally(() => {
                if (typeof $ !== 'undefined' && $.fn.modal) {
                    $('#deleteModal').modal('hide');
                } else {
                    closeDeleteModalBulanan();
                }
            });
        }
    };

// Pastikan SweetAlert tersedia
document.addEventListener('DOMContentLoaded', function() {
    // Fallback untuk SweetAlert jika tidak tersedia
    if (typeof Swal === 'undefined') {
        console.warn('SweetAlert not loaded, using fallback');
        window.Swal = {
            fire: function(title, text, icon) {
                alert(title + ': ' + text);
                return Promise.resolve({ isConfirmed: true });
            }
        };
    } else {
        console.log('SweetAlert loaded successfully');
    }

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
                Swal.fire('Peringatan', 'Silakan pilih kelas terlebih dahulu!', 'warning');
                return;
            }
            if (!tarifSama || tarifSama <= 0) {
                Swal.fire('Peringatan', 'Silakan masukkan tarif bulanan yang valid!', 'warning');
                return;
            }
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
                    showToast(data.message, 'success');
                    if (typeof $ !== 'undefined' && $.fn.modal) {
                        $('#tarifBulananModal').modal('hide');
                    } else {
                        closeTarifBulananModal();
                    }
                    form.reset();
                    // Redirect ke halaman sebelumnya dengan parameter yang sama
                    const currentUrl = new URL(window.location.href);
                    const classId = currentUrl.searchParams.get('class_id');
                    if (classId) {
                        window.location.href = currentUrl.pathname + '?class_id=' + classId;
                    } else {
                        location.reload();
                    }
                } else {
                    Swal.fire('Gagal', 'Terjadi kesalahan: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan data!', 'error');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = 'Simpan';
            });
        });
    }
    // Edit tarif bulanan satuan
    document.querySelectorAll('.btn-edit-bulanan').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var studentId = this.dataset.studentId;
            var nama = this.dataset.studentNama;
            var nis = this.dataset.nis;
            var kelas = this.dataset.kelas;
            document.getElementById('editStudentId').value = studentId;
            document.getElementById('editStudentInfo').innerText = nama + ' (' + nis + ') - ' + kelas;
            // Ambil data tarif per bulan via AJAX
            fetch(`/api/bulanan/${studentId}/{{ $payment->payment_id }}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        var bulan = data.bulan;
                        @foreach($bulanList as $idx => $bulan)
                        document.getElementById('editBulan{{ $idx+1 }}').value = bulan['{{ $bulan }}'] ?? '';
                        @endforeach
                    }
                })
                .finally(function() {
                    // Modal edit siap digunakan tanpa autofill
                });
            if (typeof $ !== 'undefined' && $.fn.modal) {
                $('#editBulananModal').modal('show');
            } else {
                console.error('jQuery modal not available');
            }
        });
    });
    // Update tarif bulanan satuan
    document.getElementById('btnUpdateBulanan').addEventListener('click', function() {
        var form = document.getElementById('formEditBulanan');
        var studentId = form.editStudentId.value;
        var bulan = {};
        @foreach($bulanList as $idx => $bulan)
        bulan['{{ $bulan }}'] = form['bulan[{{ $bulan }}]'].value;
        @endforeach
        fetch(`/api/bulanan/${studentId}/{{ $payment->payment_id }}`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ bulan: bulan })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(data.message || 'Berhasil update!');
                setTimeout(() => {
                    // Redirect ke halaman sebelumnya dengan parameter yang sama
                    const currentUrl = new URL(window.location.href);
                    const classId = currentUrl.searchParams.get('class_id');
                    if (classId) {
                        window.location.href = currentUrl.pathname + '?class_id=' + classId;
                    } else {
                        location.reload();
                    }
                }, 1500);
            } else {
                showToast(data.message || 'Gagal update!', 'danger');
            }
        });
    });
    // Hapus tarif bulanan satuan
    let deleteForm = null;
    document.querySelectorAll('.btn-delete-bulanan').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var studentId = this.dataset.studentId;
            var nama = this.dataset.studentNama;
            deleteForm = this.closest('form');
            deleteForm.action = `/api/bulanan/${studentId}/{{ $payment->payment_id }}`;
            document.getElementById('modalStudentName').textContent = nama;
            if (typeof $ !== 'undefined' && $.fn.modal) {
                $('#deleteModal').modal('show');
            } else {
                console.error('jQuery modal not available');
            }
        });
    });

    // Event listener untuk confirmDeleteBtn sudah dihandle oleh onclick="confirmDeleteBulanan()" di HTML
    // Bulk select, enable/disable hapus masal
    let selectedBulanan = [];
    function updateBulkBulananButtons() {
        selectedBulanan = Array.from(document.querySelectorAll('.selectBulanan:checked')).map(cb => cb.value);
        var btnDeleteMasal = document.getElementById('btnDeleteMasalBulanan');
        if (btnDeleteMasal) {
            btnDeleteMasal.disabled = selectedBulanan.length === 0;
        }
    }
    var selectAllBulanan = document.getElementById('selectAllBulanan');
    if (selectAllBulanan) {
        selectAllBulanan.addEventListener('change', function() {
            document.querySelectorAll('.selectBulanan').forEach(cb => { cb.checked = this.checked; });
            updateBulkBulananButtons();
        });
    }
    document.querySelectorAll('.selectBulanan').forEach(cb => {
        cb.addEventListener('change', updateBulkBulananButtons);
    });
    // Hapus masal
    var btnDeleteMasal = document.getElementById('btnDeleteMasalBulanan');
    console.log('btnDeleteMasal element:', btnDeleteMasal);
    if (btnDeleteMasal) {
        console.log('Adding click event to btnDeleteMasal');
        btnDeleteMasal.addEventListener('click', function() {
            if (selectedBulanan.length === 0) {
                Swal.fire('Peringatan', 'Tidak ada data yang dipilih untuk dihapus!', 'warning');
                return;
            }
            
            // Tampilkan loading state
            const btn = this;
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Memproses...';
            
            console.log('Showing SweetAlert confirmation...');
            console.log('Swal object:', Swal);
            Swal.fire({
                title: 'Konfirmasi',
                text: 'Yakin ingin menghapus tarif bulanan untuk siswa terpilih?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    console.log('Sending bulk delete request:', {
                        student_ids: selectedBulanan,
                        payment_id: {{ $payment->payment_id }},
                        csrf_token: '{{ csrf_token() }}'
                    });
                    
                    const requestData = { 
                        student_ids: selectedBulanan, 
                        payment_id: {{ $payment->payment_id }} 
                    };
                    
                    console.log('Request URL:', '/api/bulanan/bulk-delete');
                    console.log('Request data:', requestData);
                    
                    fetch(`/api/bulanan/bulk-delete`, {
                        method: 'POST',
                        headers: { 
                            'X-CSRF-TOKEN': '{{ csrf_token() }}', 
                            'Accept': 'application/json', 
                            'Content-Type': 'application/json' 
                        },
                        body: JSON.stringify(requestData)
                    })
                    .then(response => {
                        console.log('Bulk delete response status:', response.status);
                        console.log('Bulk delete response headers:', response.headers);
                        
                        if (!response.ok) {
                            return response.text().then(text => {
                                console.log('Error response text:', text);
                                throw new Error('HTTP error ' + response.status + ': ' + text);
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Bulk delete response data:', data);
                        if (data.success) {
                            showToast(data.message, 'success');
                            // Redirect ke halaman sebelumnya dengan parameter yang sama
                            const currentUrl = new URL(window.location.href);
                            const classId = currentUrl.searchParams.get('class_id');
                            if (classId) {
                                window.location.href = currentUrl.pathname + '?class_id=' + classId;
                            } else {
                                location.reload();
                            }
                        } else {
                            Swal.fire('Gagal', data.message || 'Gagal hapus!', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                        console.error('Error message:', error.message);
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus data: ' + error.message, 'error');
                    })
                    .finally(() => {
                        // Reset button state
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    });
                } else {
                    // Reset button state jika dibatalkan
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            });
        });
    }
    
    // Dropdown siswa dinamis berdasarkan kelas (modal siswa)
    var kelasSiswa = document.getElementById('kelasSiswaSelectBulanan');
    if (kelasSiswa) {
        kelasSiswa.addEventListener('change', function() {
            var classId = this.value;
            var siswaSelect = document.getElementById('siswaSelectBulanan');
            siswaSelect.innerHTML = '<option value="">Memuat...</option>';
            if (!classId) {
                siswaSelect.innerHTML = '<option value="">-- Pilih Siswa --</option>';
                return;
            }
            fetch('/students-get-by-class', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ class_id: classId })
            })
            .then(r => r.json())
            .then(data => {
                var html = '<option value="">-- Pilih Siswa --</option>';
                var siswaArr = Array.isArray(data) ? data : (data.students || []);
                if (siswaArr.length) {
                    siswaArr.forEach(function(s) {
                        if (s.student_id && !isNaN(Number(s.student_id))) {
                            html += `<option value="${s.student_id}">${s.student_full_name} (${s.student_nis})</option>`;
                        }
                    });
                } else {
                    html += '<option value="">Tidak ada siswa aktif di kelas ini</option>';
                }
                siswaSelect.innerHTML = html;
            })
            .catch(() => {
                siswaSelect.innerHTML = '<option value="">-- Pilih Siswa --</option>';
            });
        });
    }
    // Simpan tarif bulanan per siswa
    document.getElementById('btnSimpanTarifBulananSiswa').addEventListener('click', function() {
        var form = document.getElementById('formTarifBulananSiswa');
        var kelasId = form.kelas_id.value;
        var studentId = form.student_id.value;
        var bulan = {};
        @foreach($bulanList as $idx => $bulan)
        bulan['{{ $bulan }}'] = form['bulan[{{ $bulan }}]'].value;
        @endforeach
        if (!kelasId || !studentId || isNaN(Number(studentId)) || studentId === '' || Number(studentId) <= 0) {
            Swal.fire('Peringatan', 'Kelas dan siswa wajib dipilih dengan benar!', 'warning');
            return;
        }
        this.disabled = true;
        this.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Menyimpan...';
        fetch(`/api/bulanan/siswa/{{ $payment->payment_id }}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ kelas_id: kelasId, student_id: studentId, bulan: bulan })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                // Redirect ke halaman sebelumnya dengan parameter yang sama
                const currentUrl = new URL(window.location.href);
                const classId = currentUrl.searchParams.get('class_id');
                if (classId) {
                    window.location.href = currentUrl.pathname + '?class_id=' + classId;
                } else {
                    location.reload();
                }
            } else {
                Swal.fire('Gagal', data.message || 'Gagal simpan!', 'error');
            }
        })
        .finally(() => {
            this.disabled = false;
            this.innerHTML = 'Simpan';
        });
    });

    // Input bulan pada modal edit dapat diisi secara individual tanpa autofill

});
</script>
@endsection 