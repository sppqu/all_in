@extends('layouts.adminty')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Edit Tahun Pelajaran</h4>
                    <a href="{{ route('periods.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('periods.update', $period) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="period_start" class="form-label">Tahun Mulai <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control @error('period_start') is-invalid @enderror" 
                                           id="period_start" 
                                           name="period_start" 
                                           value="{{ old('period_start', $period->period_start) }}" 
                                           min="2000" 
                                           max="2100" 
                                           required>
                                    @error('period_start')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="period_end" class="form-label">Tahun Akhir <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control @error('period_end') is-invalid @enderror" 
                                           id="period_end" 
                                           name="period_end" 
                                           value="{{ old('period_end', $period->period_end) }}" 
                                           min="2000" 
                                           max="2100" 
                                           required>
                                    @error('period_end')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label d-block">Status Tahun Pelajaran</label>
                            <div class="form-check">
                                <input class="form-control form-check-input checkbox-primary" 
                                       type="checkbox" 
                                       id="period_status" 
                                       name="period_status" 
                                       value="1" 
                                       {{ old('period_status', $period->period_status) ? 'checked' : '' }}>
                                <label class="form-check-label" for="period_status">
                                    Aktifkan tahun pelajaran ini
                                </label>
                            </div>
                            <small class="form-text text-muted" style="display: block; margin-top: 0.5rem;">
                                <i class="fas fa-info-circle me-1"></i>
                                Jika dicentang, tahun pelajaran lain akan otomatis dinonaktifkan
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const startYear = document.getElementById('period_start');
    const endYear = document.getElementById('period_end');
    
    // Auto-fill end year when start year changes
    startYear.addEventListener('change', function() {
        if (this.value && !endYear.value) {
            endYear.value = parseInt(this.value) + 1;
        }
    });
});
</script>
@endsection 