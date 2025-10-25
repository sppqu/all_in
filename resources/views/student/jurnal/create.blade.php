@extends('layouts.student')

@section('title', 'Isi Jurnal Harian')

@section('content')
<div class="container-fluid px-3">
    <!-- Page Header -->
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('student.jurnal.index') }}" class="btn btn-sm btn-outline-secondary me-3" style="border-radius: 10px;">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h5 class="fw-bold mb-1" style="color: #6f42c1;">✍️ Isi Jurnal Harian</h5>
            <p class="text-muted small mb-0">{{ now()->format('d F Y') }}</p>
        </div>
    </div>

    <!-- Error/Success Messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 12px;">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 12px;">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('info'))
    <div class="alert alert-info alert-dismissible fade show" role="alert" style="border-radius: 12px;">
        <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 12px;">
        <h6 class="fw-bold mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Terdapat Kesalahan:</h6>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Form -->
    <form action="{{ route('student.jurnal.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Hidden Tanggal (today) -->
        <input type="hidden" name="tanggal" value="{{ now()->format('Y-m-d') }}">

        <!-- 7 Kategori KAIH -->
        @foreach($categories as $category)
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 15px;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                         style="width: 50px; height: 50px; background: {{ $category->warna }}; background: linear-gradient(135deg, {{ $category->warna }} 0%, {{ $category->warna }}dd 100%);">
                        <i class="{{ $category->icon }} text-white" style="font-size: 1.3rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1 fw-bold">{{ $category->nama_kategori }}</h6>
                        <small class="text-muted">{{ $category->deskripsi }}</small>
                    </div>
                </div>

                <!-- Dynamic Input Based on Category -->
                @if($category->kode == 'BANGUN')
                    <!-- Bangun Pagi: Jam + Keterangan -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold">
                            <i class="fas fa-clock me-1"></i>Jam Bangun
                        </label>
                        <input type="time" 
                               name="kategori[{{ $category->kategori_id }}][jam]" 
                               class="form-control" 
                               style="border-radius: 10px;"
                               required>
                    </div>
                    <div>
                        <label class="form-label small fw-bold">Keterangan</label>
                        <textarea name="kategori[{{ $category->kategori_id }}][keterangan]" 
                                  class="form-control" 
                                  rows="2" 
                                  placeholder="Misal: Tidur jam 22.00 semalam, bangun untuk sholat subuh"
                                  style="border-radius: 10px; font-size: 0.9rem;"></textarea>
                    </div>

                @elseif($category->kode == 'IBADAH')
                    <!-- Beribadah: Checklist 5 Sholat -->
                    <label class="form-label small fw-bold mb-2">
                        <i class="fas fa-check-square me-1"></i>Sholat Hari Ini
                    </label>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" 
                               name="kategori[{{ $category->kategori_id }}][checklist][subuh]" 
                               value="1" 
                               id="subuh-{{ $category->kategori_id }}">
                        <label class="form-check-label" for="subuh-{{ $category->kategori_id }}">
                            🌅 Subuh
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" 
                               name="kategori[{{ $category->kategori_id }}][checklist][dzuhur]" 
                               value="1" 
                               id="dzuhur-{{ $category->kategori_id }}">
                        <label class="form-check-label" for="dzuhur-{{ $category->kategori_id }}">
                            ☀️ Dzuhur
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" 
                               name="kategori[{{ $category->kategori_id }}][checklist][asar]" 
                               value="1" 
                               id="asar-{{ $category->kategori_id }}">
                        <label class="form-check-label" for="asar-{{ $category->kategori_id }}">
                            🌤️ Asar
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" 
                               name="kategori[{{ $category->kategori_id }}][checklist][magrib]" 
                               value="1" 
                               id="magrib-{{ $category->kategori_id }}">
                        <label class="form-check-label" for="magrib-{{ $category->kategori_id }}">
                            🌆 Magrib
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" 
                               name="kategori[{{ $category->kategori_id }}][checklist][isya]" 
                               value="1" 
                               id="isya-{{ $category->kategori_id }}">
                        <label class="form-check-label" for="isya-{{ $category->kategori_id }}">
                            🌙 Isya
                        </label>
                    </div>

                @elseif($category->kode == 'OLAHRAGA')
                    <!-- Berolahraga: Ya/Tidak + Jam -->
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" 
                               name="kategori[{{ $category->kategori_id }}][checklist][berolahraga]" 
                               value="1" 
                               id="olahraga-{{ $category->kategori_id }}"
                               onchange="document.getElementById('jam-olahraga-{{ $category->kategori_id }}').disabled = !this.checked">
                        <label class="form-check-label fw-bold" for="olahraga-{{ $category->kategori_id }}">
                            <i class="fas fa-running me-1"></i>Apakah berolahraga hari ini?
                        </label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">
                            <i class="fas fa-clock me-1"></i>Jam Olahraga (jika ada)
                        </label>
                        <input type="time" 
                               name="kategori[{{ $category->kategori_id }}][jam]" 
                               id="jam-olahraga-{{ $category->kategori_id }}"
                               class="form-control" 
                               style="border-radius: 10px;"
                               disabled>
                    </div>
                    <div>
                        <label class="form-label small fw-bold">Jenis Olahraga</label>
                        <textarea name="kategori[{{ $category->kategori_id }}][keterangan]" 
                                  class="form-control" 
                                  rows="2" 
                                  placeholder="Misal: Lari pagi, push up, main bola, dll"
                                  style="border-radius: 10px; font-size: 0.9rem;"></textarea>
                    </div>

                @elseif($category->kode == 'MAKAN')
                    <!-- Makan Sehat: Checklist 3x + Menu -->
                    <label class="form-label small fw-bold mb-2">
                        <i class="fas fa-check-square me-1"></i>Makan Hari Ini
                    </label>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" 
                               name="kategori[{{ $category->kategori_id }}][checklist][pagi]" 
                               value="1" 
                               id="pagi-{{ $category->kategori_id }}">
                        <label class="form-check-label" for="pagi-{{ $category->kategori_id }}">
                            🌅 Sarapan Pagi
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" 
                               name="kategori[{{ $category->kategori_id }}][checklist][siang]" 
                               value="1" 
                               id="siang-{{ $category->kategori_id }}">
                        <label class="form-check-label" for="siang-{{ $category->kategori_id }}">
                            ☀️ Makan Siang
                        </label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" 
                               name="kategori[{{ $category->kategori_id }}][checklist][malam]" 
                               value="1" 
                               id="malam-{{ $category->kategori_id }}">
                        <label class="form-check-label" for="malam-{{ $category->kategori_id }}">
                            🌙 Makan Malam
                        </label>
                    </div>
                    <div>
                        <label class="form-label small fw-bold">Menu Makanan</label>
                        <textarea name="kategori[{{ $category->kategori_id }}][keterangan]" 
                                  class="form-control" 
                                  rows="2" 
                                  placeholder="Ceritakan menu makanan yang dimakan hari ini..."
                                  style="border-radius: 10px; font-size: 0.9rem;"></textarea>
                    </div>

                @elseif($category->kode == 'MEMBACA')
                    <!-- Gemar Membaca: Ya/Tidak + Apa yang dibaca -->
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" 
                               name="kategori[{{ $category->kategori_id }}][checklist][membaca]" 
                               value="1" 
                               id="membaca-{{ $category->kategori_id }}">
                        <label class="form-check-label fw-bold" for="membaca-{{ $category->kategori_id }}">
                            <i class="fas fa-book-reader me-1"></i>Apakah belajar/membaca hari ini?
                        </label>
                    </div>
                    <div>
                        <label class="form-label small fw-bold">Apa yang Dipelajari/Dibaca?</label>
                        <textarea name="kategori[{{ $category->kategori_id }}][keterangan]" 
                                  class="form-control" 
                                  rows="2" 
                                  placeholder="Ceritakan apa yang dipelajari atau dibaca hari ini..."
                                  style="border-radius: 10px; font-size: 0.9rem;"></textarea>
                    </div>

                @elseif($category->kode == 'SOSIAL')
                    <!-- Bermasyarakat: Kegiatan + Checklist -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Kegiatan Hari Ini</label>
                        <textarea name="kategori[{{ $category->kategori_id }}][keterangan]" 
                                  class="form-control" 
                                  rows="2" 
                                  placeholder="Ceritakan kegiatan dengan keluarga, teman, atau tetangga..."
                                  style="border-radius: 10px; font-size: 0.9rem;"
                                  required></textarea>
                    </div>
                    <label class="form-label small fw-bold mb-2">
                        <i class="fas fa-users me-1"></i>Bersama Siapa?
                    </label>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" 
                               name="kategori[{{ $category->kategori_id }}][checklist][keluarga]" 
                               value="1" 
                               id="keluarga-{{ $category->kategori_id }}">
                        <label class="form-check-label" for="keluarga-{{ $category->kategori_id }}">
                            👨‍👩‍👧‍👦 Keluarga
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" 
                               name="kategori[{{ $category->kategori_id }}][checklist][teman]" 
                               value="1" 
                               id="teman-{{ $category->kategori_id }}">
                        <label class="form-check-label" for="teman-{{ $category->kategori_id }}">
                            👥 Teman
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" 
                               name="kategori[{{ $category->kategori_id }}][checklist][tetangga]" 
                               value="1" 
                               id="tetangga-{{ $category->kategori_id }}">
                        <label class="form-check-label" for="tetangga-{{ $category->kategori_id }}">
                            🏘️ Tetangga
                        </label>
                    </div>

                @elseif($category->kode == 'TIDUR')
                    <!-- Tidur Cepat: Jam + Keterangan -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold">
                            <i class="fas fa-clock me-1"></i>Jam Tidur Malam
                        </label>
                        <input type="time" 
                               name="kategori[{{ $category->kategori_id }}][jam]" 
                               class="form-control" 
                               style="border-radius: 10px;"
                               required>
                    </div>
                    <div>
                        <label class="form-label small fw-bold">Keterangan</label>
                        <textarea name="kategori[{{ $category->kategori_id }}][keterangan]" 
                                  class="form-control" 
                                  rows="2" 
                                  placeholder="Misal: Tidur setelah belajar dan menonton TV"
                                  style="border-radius: 10px; font-size: 0.9rem;"></textarea>
                    </div>
                @endif
            </div>
        </div>
        @endforeach

        <!-- Catatan Umum -->
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 15px;">
            <div class="card-body p-3">
                <label class="form-label fw-bold">
                    <i class="fas fa-sticky-note me-2" style="color: #6f42c1;"></i>Catatan Umum (Opsional)
                </label>
                <textarea name="catatan_umum" 
                          class="form-control" 
                          rows="3" 
                          placeholder="Tulis catatan umum atau refleksi harian Anda..."
                          style="border-radius: 10px;"></textarea>
            </div>
        </div>

        <!-- Upload Foto -->
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 15px;">
            <div class="card-body p-3">
                <label class="form-label fw-bold">
                    <i class="fas fa-camera me-2" style="color: #6f42c1;"></i>Foto Dokumentasi (Opsional)
                </label>
                <input type="file" 
                       name="foto" 
                       class="form-control" 
                       accept="image/*" 
                       id="fotoInput"
                       style="border-radius: 10px;">
                <small class="text-muted d-block mt-2">
                    <i class="fas fa-info-circle me-1"></i>Maksimal 2MB, format: JPG, PNG
                </small>
                <div id="preview" class="mt-3" style="display: none;">
                    <img id="previewImg" src="" alt="Preview" class="img-fluid rounded" style="max-height: 200px; border-radius: 10px;">
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="d-grid gap-2 mb-4">
            <button type="submit" class="btn btn-primary btn-lg fw-bold" style="background: linear-gradient(135deg, #6f42c1, #9d5bd2); border: none; border-radius: 12px; box-shadow: 0 6px 20px rgba(111, 66, 193, 0.3);">
                <i class="fas fa-paper-plane me-2"></i>Simpan Jurnal
            </button>
            <a href="{{ route('student.jurnal.index') }}" class="btn btn-outline-secondary btn-lg" style="border-radius: 12px;">
                <i class="fas fa-times me-2"></i>Batal
            </a>
        </div>
    </form>
</div>

<style>
    .form-control, .form-check-input {
        font-size: 0.9rem;
    }
    
    .form-check-input {
        cursor: pointer;
        width: 1.2rem;
        height: 1.2rem;
    }
    
    .form-check-input:checked {
        background-color: #6f42c1;
        border-color: #6f42c1;
    }
    
    .form-check-label {
        cursor: pointer;
        margin-left: 0.5rem;
    }
    
    input[type="time"] {
        font-size: 1rem;
        padding: 0.5rem;
    }
    
    .card {
        transition: all 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.12) !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Jurnal Create Page Loaded - New Format');
    
    // Preview foto
    const fotoInput = document.getElementById('fotoInput');
    const preview = document.getElementById('preview');
    const previewImg = document.getElementById('previewImg');
    
    if (fotoInput) {
        fotoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                console.log('Photo selected:', file.name, file.size);
                
                // Validate file size (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file terlalu besar! Maksimal 2MB');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                    console.log('Photo preview loaded');
                };
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        });
    }
    
    // Form submit logging
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('Form submitting...');
            
            // Count filled inputs
            const timeInputs = document.querySelectorAll('input[type="time"]');
            const textareas = document.querySelectorAll('textarea[name^="kategori"]');
            const checkboxes = document.querySelectorAll('input[type="checkbox"][name^="kategori"]');
            
            console.log('Time inputs:', timeInputs.length);
            console.log('Textareas:', textareas.length);
            console.log('Checkboxes checked:', Array.from(checkboxes).filter(cb => cb.checked).length);
            
            // Form will be submitted (HTML5 validation handles required fields)
            console.log('Submitting form...');
            return true;
        });
    }
});
</script>
@endsection

