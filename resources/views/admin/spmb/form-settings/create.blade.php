@extends('layouts.adminty')

@push('styles')
<style>
    .form-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .section-header {
        background: #01a9ac;
        color: white;
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
    }
    .options-container {
        border: 1px solid #e9ecef;
        border-radius: 10px;
        padding: 15px;
        background: #f8f9fa;
    }
    .option-item {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }
    .option-item:last-child {
        margin-bottom: 0;
    }
    .btn-save {
        background-color: #01a9ac;
        border-color: #01a9ac;
        color: white;
    }
    .btn-save:hover {
        background-color: #018a8d;
        border-color: #018a8d;
        color: white;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1" style="font-size: 1.5rem;">
                        <i class="fas fa-plus me-2"></i>Tambah Field Form SPMB
                    </h4>
                    <p class="text-muted mb-0" style="font-size: 0.9rem;">Buat field baru untuk form pendaftaran</p>
                </div>
                <a href="{{ route('manage.spmb.form-settings.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
            </div>

            <div class="form-card">
                <div class="p-4">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('manage.spmb.form-settings.store') }}">
                        @csrf
                        
                        <!-- Basic Information -->
                        <div class="section-header">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>Informasi Dasar
                            </h5>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="field_name" class="form-label">
                                    <i class="fas fa-tag me-1"></i>Nama Field *
                                </label>
                                <input type="text" 
                                       class="form-control @error('field_name') is-invalid @enderror" 
                                       id="field_name" 
                                       name="field_name" 
                                       value="{{ old('field_name') }}"
                                       placeholder="contoh: parent_occupation"
                                       required>
                                @error('field_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Nama field harus unik dan menggunakan underscore (snake_case)</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="field_label" class="form-label">
                                    <i class="fas fa-tag me-1"></i>Label Field *
                                </label>
                                <input type="text" 
                                       class="form-control @error('field_label') is-invalid @enderror" 
                                       id="field_label" 
                                       name="field_label" 
                                       value="{{ old('field_label') }}"
                                       placeholder="contoh: Pekerjaan Orang Tua"
                                       required>
                                @error('field_label')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Label yang akan ditampilkan di form</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="field_type" class="form-label">
                                    <i class="fas fa-list me-1"></i>Tipe Field *
                                </label>
                                <select class="form-control @error('field_type') is-invalid @enderror" 
                                        id="field_type" 
                                        name="field_type" 
                                        required>
                                    <option value="">Pilih Tipe Field</option>
                                    @foreach($fieldTypes as $value => $label)
                                        <option value="{{ $value }}" {{ old('field_type') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('field_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="field_section" class="form-label">
                                    <i class="fas fa-folder me-1"></i>Section *
                                </label>
                                <select class="form-control @error('field_section') is-invalid @enderror" 
                                        id="field_section" 
                                        name="field_section" 
                                        required>
                                    <option value="">Pilih Section</option>
                                    @foreach($sections as $value => $label)
                                        <option value="{{ $value }}" {{ old('field_section') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('field_section')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Field Options -->
                        <div class="section-header">
                            <h5 class="mb-0">
                                <i class="fas fa-cog me-2"></i>Opsi Field
                            </h5>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="field_placeholder" class="form-label">
                                    <i class="fas fa-quote-left me-1"></i>Placeholder
                                </label>
                                <input type="text" 
                                       class="form-control @error('field_placeholder') is-invalid @enderror" 
                                       id="field_placeholder" 
                                       name="field_placeholder" 
                                       value="{{ old('field_placeholder') }}"
                                       placeholder="contoh: Masukkan pekerjaan orang tua">
                                @error('field_placeholder')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="field_order" class="form-label">
                                    <i class="fas fa-sort me-1"></i>Urutan
                                </label>
                                <input type="number" 
                                       class="form-control @error('field_order') is-invalid @enderror" 
                                       id="field_order" 
                                       name="field_order" 
                                       value="{{ old('field_order', 0) }}"
                                       min="0">
                                @error('field_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Urutan tampil di form (0 = pertama)</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="field_help_text" class="form-label">
                                <i class="fas fa-question-circle me-1"></i>Help Text
                            </label>
                            <textarea class="form-control @error('field_help_text') is-invalid @enderror" 
                                      id="field_help_text" 
                                      name="field_help_text" 
                                      rows="2" 
                                      placeholder="contoh: Masukkan pekerjaan orang tua dengan lengkap">{{ old('field_help_text') }}</textarea>
                            @error('field_help_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Select Options (for select type) -->
                        <div id="select-options" style="display: none;">
                            <div class="section-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-list me-2"></i>Opsi Select
                                </h5>
                            </div>
                            <div class="options-container">
                                <div id="options-list">
                                    <div class="option-item">
                                        <input type="text" name="field_options[]" class="form-control" placeholder="Opsi 1" value="">
                                        <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="removeOption(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addOption()">
                                    <i class="fas fa-plus me-1"></i>Tambah Opsi
                                </button>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="section-header">
                            <h5 class="mb-0">
                                <i class="fas fa-toggle-on me-2"></i>Status
                            </h5>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="form-check d-flex align-items-center">
                                    <input class="form-check-input @error('is_required') is-invalid @enderror" 
                                           type="checkbox" 
                                           id="is_required" 
                                           name="is_required" 
                                           value="1"
                                           style="width: 18px; height: 18px; margin-right: 10px; margin-top: 0; cursor: pointer;"
                                           {{ old('is_required') ? 'checked' : '' }}>
                                    <label class="form-check-label mb-0" for="is_required" style="cursor: pointer; font-size: 0.95rem;">
                                        Field Wajib
                                    </label>
                                    @error('is_required')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="form-check d-flex align-items-center">
                                    <input class="form-check-input @error('is_active') is-invalid @enderror" 
                                           type="checkbox" 
                                           id="is_active" 
                                           name="is_active" 
                                           value="1"
                                           style="width: 18px; height: 18px; margin-right: 10px; margin-top: 0; cursor: pointer;"
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label mb-0" for="is_active" style="cursor: pointer; font-size: 0.95rem;">
                                        <i class="fas fa-eye me-1"></i>Field Aktif
                                    </label>
                                    @error('is_active')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="form-check d-flex align-items-center">
                                    <input class="form-check-input @error('show_in_print') is-invalid @enderror" 
                                           type="checkbox" 
                                           id="show_in_print" 
                                           name="show_in_print" 
                                           value="1"
                                           style="width: 18px; height: 18px; margin-right: 10px; margin-top: 0; cursor: pointer;"
                                           {{ old('show_in_print', true) ? 'checked' : '' }}>
                                    <label class="form-check-label mb-0" for="show_in_print" style="cursor: pointer; font-size: 0.95rem;">
                                        <i class="fas fa-print me-1"></i>Tampil di Cetak
                                    </label>
                                    @error('show_in_print')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('manage.spmb.form-settings.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-save">
                                <i class="fas fa-save me-1"></i>Simpan Field
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Show/hide select options based on field type
    document.getElementById('field_type').addEventListener('change', function() {
        const selectOptions = document.getElementById('select-options');
        if (this.value === 'select') {
            selectOptions.style.display = 'block';
        } else {
            selectOptions.style.display = 'none';
        }
    });

    // Add option function
    function addOption() {
        const optionsList = document.getElementById('options-list');
        const optionCount = optionsList.children.length + 1;
        
        const optionItem = document.createElement('div');
        optionItem.className = 'option-item';
        optionItem.innerHTML = `
            <input type="text" name="field_options[]" class="form-control" placeholder="Opsi ${optionCount}" value="">
            <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="removeOption(this)">
                <i class="fas fa-trash"></i>
            </button>
        `;
        
        optionsList.appendChild(optionItem);
    }

    // Remove option function
    function removeOption(button) {
        button.parentElement.remove();
    }

    // Auto generate field name from label
    document.getElementById('field_label').addEventListener('input', function() {
        const fieldName = document.getElementById('field_name');
        if (!fieldName.value) {
            const generatedName = this.value
                .toLowerCase()
                .replace(/[^a-z0-9\s]/g, '')
                .replace(/\s+/g, '_');
            fieldName.value = generatedName;
        }
    });
</script>
@endpush
