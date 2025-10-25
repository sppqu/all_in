@extends('layouts.coreui')

@section('head')
<title>Kelulusan Siswa - SPPQU</title>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Kelulusan Siswa</h4>
                    <a href="{{ route('students.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> Terdapat kesalahan:
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('students.process-graduate') }}" method="POST" id="graduateForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-school"></i> Pilih Kelas</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="class_id" class="form-label">Kelas <span class="text-danger">*</span></label>
                                            <select class="form-select" id="class_id" name="class_id" required>
                                                <option value="">Pilih Kelas</option>
                                                @foreach($classes as $class)
                                                    <option value="{{ $class->class_id }}" {{ old('class_id') == $class->class_id ? 'selected' : '' }}>
                                                        {{ $class->class_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div id="studentsContainer" style="display: none;">
                                            <h6>Daftar Siswa di Kelas Ini:</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th width="50">
                                                                <input type="checkbox" id="selectAllStudents" class="form-check-input">
                                                            </th>
                                                            <th>NIS</th>
                                                            <th>Nama</th>
                                                            <th>JK</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="studentsList">
                                                        <!-- Students will be loaded here -->
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    <span id="selectedCount">0</span> siswa dipilih
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-info mt-3">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Informasi:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Pilih kelas untuk melihat daftar siswa</li>
                                        <li>Centang siswa yang akan diluluskan</li>
                                        <li>Klik tombol "Luluskan Siswa" untuk memproses</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                        <i class="fas fa-undo"></i> Reset
                                    </button>
                                    <button type="submit" class="btn btn-success" id="submitBtn" disabled>
                                        <i class="fas fa-graduation-cap"></i> Luluskan Siswa
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).ready(function() {
    $('#class_id').change(function() {
        const classId = $(this).val();
        if (classId) {
            loadStudents(classId);
        } else {
            $('#studentsContainer').hide();
            $('#studentsList').empty();
            updateSubmitButton();
        }
    });

    $('#selectAllStudents').change(function() {
        const isChecked = $(this).is(':checked');
        $('.student-checkbox').prop('checked', isChecked);
        updateSelectedCount();
        updateSubmitButton();
    });

    $(document).on('change', '.student-checkbox', function() {
        updateSelectedCount();
        updateSelectAllCheckbox();
        updateSubmitButton();
    });

    $('#graduateForm').submit(function(e) {
        const classId = $('#class_id').val();
        const selectedStudents = $('.student-checkbox:checked').length;

        if (!classId) {
            alert('Pilih kelas terlebih dahulu');
            e.preventDefault();
            return false;
        }
        if (selectedStudents === 0) {
            alert('Pilih minimal satu siswa untuk diluluskan');
            e.preventDefault();
            return false;
        }
        const confirmMessage = `Apakah Anda yakin ingin meluluskan ${selectedStudents} siswa dari kelas yang dipilih?`;
        if (!confirm(confirmMessage)) {
            e.preventDefault();
            return false;
        }
    });
});

function loadStudents(classId) {
    $('#studentsList').html('<tr><td colspan="4" class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat data siswa...</td></tr>');
    $('#studentsContainer').show();
    $.ajax({
        url: '{{ route("students.get-by-class") }}',
        method: 'POST',
        data: { class_id: classId },
        dataType: 'json',
        success: function(response) {
            const studentsList = $('#studentsList');
            studentsList.empty();
            if (response.length === 0) {
                studentsList.html('<tr><td colspan="4" class="text-center text-muted">Tidak ada siswa di kelas ini</td></tr>');
            } else {
                response.forEach(function(student) {
                    const row = `
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" name="student_ids[]" value="${student.student_id}"  
                                       class="form-check-input student-checkbox">
                            </td>
                            <td>${student.student_nis || '-'}</td>
                            <td>${student.student_full_name || '-'}</td>
                            <td>${student.student_gender || '-'}</td>
                        </tr>
                    `;
                    studentsList.append(row);
                });
            }
            updateSelectedCount();
            updateSelectAllCheckbox();
            updateSubmitButton();
        },
        error: function(xhr, status, error) {
            alert('Gagal memuat data siswa: ' + error);
            $('#studentsList').html('<tr><td colspan="4" class="text-center text-muted">Gagal memuat data siswa.</td></tr>');
        }
    });
}

function updateSelectedCount() {
    const selectedCount = $('.student-checkbox:checked').length;
    $('#selectedCount').text(selectedCount);
}

function updateSelectAllCheckbox() {
    const allChecked = $('.student-checkbox').length === $('.student-checkbox:checked').length;
    $('#selectAllStudents').prop('indeterminate', !allChecked && $('.student-checkbox').length > 0);
    $('#selectAllStudents').prop('checked', allChecked);
}

function updateSubmitButton() {
    const classId = $('#class_id').val();
    const selectedStudents = $('.student-checkbox:checked').length;
    if (classId && selectedStudents > 0) {
        $('#submitBtn').prop('disabled', false);
    } else {
        $('#submitBtn').prop('disabled', true);
    }
}

function resetForm() {
    $('#graduateForm')[0].reset();
    $('#studentsContainer').hide();
    $('#studentsList').empty();
    $('#selectedCount').text('0');
    $('#selectAllStudents').prop('indeterminate', false).prop('checked', false);
    updateSubmitButton();
}
</script>
@endsection 