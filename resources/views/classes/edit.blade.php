@extends('layouts.adminty')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Edit Kelas</h4>
                    <a href="{{ route('classes.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('classes.update', $class) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="class_name" class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('class_name') is-invalid @enderror" 
                                   id="class_name" 
                                   name="class_name" 
                                   value="{{ old('class_name', $class->class_name) }}" 
                                   maxlength="45"
                                   placeholder="Contoh: X IPA 1, XI IPS 2, XII MIPA 3"
                                   required>
                            @error('class_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Maksimal 45 karakter. Contoh: X IPA 1, XI IPS 2, XII MIPA 3
                            </small>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 