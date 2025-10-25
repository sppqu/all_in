@extends('layouts.coreui')

@section('title', 'Edit Peminjaman - E-Perpustakaan')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="mb-4">
        <a href="{{ route('manage.library.loans.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Peminjaman</h5>
                </div>
                <div class="card-body p-4">
                    <!-- Book & User Info -->
                    <div class="alert alert-light border">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="mb-2"><i class="fas fa-user me-2"></i>Peminjam</h6>
                                <p class="mb-1"><strong>{{ $loan->user->name }}</strong></p>
                                <p class="mb-0 text-muted small">{{ $loan->user->email }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-2"><i class="fas fa-book me-2"></i>Buku</h6>
                                <p class="mb-1"><strong>{{ $loan->book->judul }}</strong></p>
                                <p class="mb-0 text-muted small">{{ $loan->book->pengarang }}</p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('manage.library.loans.update', $loan->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Pinjam</label>
                                <input type="date" name="tanggal_pinjam" class="form-control" 
                                       value="{{ $loan->tanggal_pinjam->format('Y-m-d') }}" disabled>
                                <small class="text-muted">Tidak dapat diubah</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Harus Kembali <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_kembali_rencana" class="form-control @error('tanggal_kembali_rencana') is-invalid @enderror" 
                                       value="{{ old('tanggal_kembali_rencana', $loan->tanggal_kembali_rencana->format('Y-m-d')) }}" required>
                                @error('tanggal_kembali_rencana')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea name="catatan" rows="3" class="form-control @error('catatan') is-invalid @enderror">{{ old('catatan', $loan->catatan) }}</textarea>
                            @error('catatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Current Status -->
                        <div class="alert alert-info">
                            <h6 class="mb-2"><i class="fas fa-info-circle me-2"></i>Status Peminjaman</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Status:</small>
                                    @if($loan->status == 'dipinjam')
                                        @if($loan->isOverdue())
                                        <span class="badge bg-danger">Terlambat</span>
                                        @else
                                        <span class="badge bg-primary">Dipinjam</span>
                                        @endif
                                    @else
                                    <span class="badge bg-success">{{ ucfirst($loan->status) }}</span>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Lama Pinjam:</small>
                                    <strong>{{ $loan->tanggal_pinjam->diffInDays(now()) }} hari</strong>
                                </div>
                                @if($loan->isOverdue())
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Keterlambatan:</small>
                                    <strong class="text-danger">{{ $loan->daysOverdue() }} hari</strong>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="fas fa-save me-2"></i>Update Peminjaman
                            </button>
                            <a href="{{ route('manage.library.loans.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

