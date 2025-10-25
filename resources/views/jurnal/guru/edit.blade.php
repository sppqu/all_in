@extends('layouts.coreui')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('jurnal.guru.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
            <h1 class="h3 mb-1 fw-bold" style="color: #6f42c1;">
                <i class="fas fa-edit me-2"></i>Edit Jurnal Harian
            </h1>
            <p class="text-muted mb-0">Siswa: {{ $jurnal->siswa->student_full_name }} | Tanggal: {{ \Carbon\Carbon::parse($jurnal->tanggal)->format('d M Y') }}</p>
        </div>
    </div>

    <!-- Messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <h6 class="fw-bold mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Terdapat Kesalahan:</h6>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Form -->
    <form action="{{ route('jurnal.guru.update', $jurnal->jurnal_id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <!-- Left Column: Student Info -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-user me-2"></i>Informasi Siswa
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <span class="fw-bold fs-1">{{ substr($jurnal->siswa->student_full_name, 0, 1) }}</span>
                            </div>
                        </div>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="fw-semibold">Nama:</td>
                                <td>{{ $jurnal->siswa->student_full_name }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">NIS:</td>
                                <td>{{ $jurnal->siswa->student_nis }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Kelas:</td>
                                <td><span class="badge bg-info">{{ $jurnal->siswa->class->class_name ?? '-' }}</span></td>
                            </tr>
                        </table>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" value="{{ $jurnal->tanggal }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Catatan Umum</label>
                            <textarea name="catatan_umum" class="form-control" rows="3" placeholder="Catatan umum dari siswa...">{{ $jurnal->catatan_umum }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Foto</label>
                            @if($jurnal->foto)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $jurnal->foto) }}" alt="Foto Jurnal" class="img-fluid rounded">
                                    <small class="text-muted d-block mt-1">Foto saat ini</small>
                                </div>
                            @endif
                            <input type="file" name="foto" class="form-control" accept="image/*">
                            <small class="text-muted">Upload foto baru jika ingin mengganti</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Jurnal Entries -->
            <div class="col-lg-8">
                <!-- 7 Kategori KAIH -->
                @foreach($kategori as $category)
                    @php
                        $entry = $jurnal->entries->where('kategori_id', $category->kategori_id)->first();
                        $checklistData = $entry && $entry->checklist_data ? json_decode($entry->checklist_data, true) : [];
                    @endphp

                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                                     style="width: 50px; height: 50px; background: {{ $category->warna }}20;">
                                    <i class="{{ $category->icon }} fs-4" style="color: {{ $category->warna }};"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">{{ $category->nama_kategori }}</h6>
                                    <small class="text-muted">{{ $category->deskripsi }}</small>
                                </div>
                            </div>

                            <!-- Dynamic Input Based on Category -->
                            @if($category->kode == 'BANGUN' || $category->kode == 'TIDUR')
                                <!-- Jam + Keterangan -->
                                <div class="mb-3">
                                    <label class="form-label fw-semibold"><i class="fas fa-clock me-1"></i>Jam</label>
                                    <input type="time" name="kategori[{{ $category->kategori_id }}][jam]" 
                                           class="form-control" value="{{ $entry->jam ?? '' }}" required>
                                </div>
                                <div>
                                    <label class="form-label fw-semibold">Keterangan</label>
                                    <textarea name="kategori[{{ $category->kategori_id }}][keterangan]" 
                                              class="form-control" rows="2">{{ $entry->keterangan ?? '' }}</textarea>
                                </div>

                            @elseif($category->kode == 'IBADAH')
                                <!-- Sholat Checklist -->
                                <label class="form-label fw-semibold mb-2"><i class="fas fa-check-square me-1"></i>Sholat Hari Ini</label>
                                <div class="row g-2">
                                    @foreach(['subuh' => 'Subuh', 'dzuhur' => 'Dzuhur', 'asar' => 'Asar', 'magrib' => 'Magrib', 'isya' => 'Isya'] as $key => $label)
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input type="checkbox" name="kategori[{{ $category->kategori_id }}][checklist][{{ $key }}]" 
                                                   value="1" id="{{ $key }}-{{ $category->kategori_id }}" class="form-check-input"
                                                   {{ isset($checklistData[$key]) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="{{ $key }}-{{ $category->kategori_id }}">{{ $label }}</label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                            @elseif($category->kode == 'OLAHRAGA')
                                <!-- Olahraga -->
                                <div class="form-check mb-3">
                                    <input type="checkbox" name="kategori[{{ $category->kategori_id }}][checklist][berolahraga]" 
                                           value="1" id="berolahraga-{{ $category->kategori_id }}" class="form-check-input"
                                           {{ isset($checklistData['berolahraga']) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="berolahraga-{{ $category->kategori_id }}">Sudah Berolahraga</label>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold"><i class="fas fa-clock me-1"></i>Jam</label>
                                    <input type="time" name="kategori[{{ $category->kategori_id }}][jam]" 
                                           class="form-control" value="{{ $entry->jam ?? '' }}">
                                </div>
                                <div>
                                    <label class="form-label fw-semibold">Keterangan</label>
                                    <textarea name="kategori[{{ $category->kategori_id }}][keterangan]" 
                                              class="form-control" rows="2">{{ $entry->keterangan ?? '' }}</textarea>
                                </div>

                            @elseif($category->kode == 'MAKAN')
                                <!-- Makan -->
                                <label class="form-label fw-semibold mb-2"><i class="fas fa-utensils me-1"></i>Waktu Makan</label>
                                <div class="row g-2 mb-3">
                                    @foreach(['pagi' => 'Pagi', 'siang' => 'Siang', 'malam' => 'Malam'] as $key => $label)
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input type="checkbox" name="kategori[{{ $category->kategori_id }}][checklist][{{ $key }}]" 
                                                   value="1" id="{{ $key }}-{{ $category->kategori_id }}" class="form-check-input"
                                                   {{ isset($checklistData[$key]) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="{{ $key }}-{{ $category->kategori_id }}">{{ $label }}</label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <div>
                                    <label class="form-label fw-semibold">Menu Makanan</label>
                                    <textarea name="kategori[{{ $category->kategori_id }}][keterangan]" 
                                              class="form-control" rows="2">{{ $entry->keterangan ?? '' }}</textarea>
                                </div>

                            @elseif($category->kode == 'MEMBACA')
                                <!-- Membaca -->
                                <div class="form-check mb-3">
                                    <input type="checkbox" name="kategori[{{ $category->kategori_id }}][checklist][belajar]" 
                                           value="1" id="belajar-{{ $category->kategori_id }}" class="form-check-input"
                                           {{ isset($checklistData['belajar']) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="belajar-{{ $category->kategori_id }}">Sudah Belajar/Membaca</label>
                                </div>
                                <div>
                                    <label class="form-label fw-semibold">Yang Dipelajari</label>
                                    <textarea name="kategori[{{ $category->kategori_id }}][keterangan]" 
                                              class="form-control" rows="2" required>{{ $entry->keterangan ?? '' }}</textarea>
                                </div>

                            @elseif($category->kode == 'SOSIAL')
                                <!-- Bermasyarakat -->
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Kegiatan</label>
                                    <textarea name="kategori[{{ $category->kategori_id }}][keterangan]" 
                                              class="form-control" rows="2" required>{{ $entry->keterangan ?? '' }}</textarea>
                                </div>
                                <label class="form-label fw-semibold mb-2"><i class="fas fa-users me-1"></i>Dengan Siapa</label>
                                <div class="row g-2">
                                    @foreach(['keluarga' => 'Keluarga', 'teman' => 'Teman', 'tetangga' => 'Tetangga'] as $key => $label)
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input type="checkbox" name="kategori[{{ $category->kategori_id }}][checklist][{{ $key }}]" 
                                                   value="1" id="{{ $key }}-{{ $category->kategori_id }}" class="form-check-input"
                                                   {{ isset($checklistData[$key]) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="{{ $key }}-{{ $category->kategori_id }}">{{ $label }}</label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach

                <!-- Submit Buttons -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('jurnal.guru.show', $jurnal->jurnal_id) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
.form-check-input:checked {
    background-color: #6f42c1;
    border-color: #6f42c1;
}
</style>
@endsection

