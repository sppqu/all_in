@extends('layouts.coreui')

@section('title', 'Detail Kode Akun')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-eye me-2"></i>
                            Detail Kode Akun: {{ $accountCode->kode }}
                        </h4>
                        <div>
                            <a href="{{ route('manage.account-codes.edit', $accountCode) }}" class="btn btn-warning">
                                <i class="fas fa-edit me-1"></i>
                                Edit
                            </a>
                            <a href="{{ route('manage.account-codes.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                Kembali
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Informasi Utama -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h5 class="text-primary mb-3">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Informasi Akun
                                    </h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="35%"><strong>Kode Akun:</strong></td>
                                            <td>
                                                <span class="badge bg-primary fs-6">{{ $accountCode->kode }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Nama Akun:</strong></td>
                                            <td>{{ $accountCode->nama }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tipe:</strong></td>
                                            <td>
                                                <span class="{{ $accountCode->tipe_badge_class }}">
                                                    {{ $accountCode->tipe_label }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Kategori:</strong></td>
                                            <td>
                                                @if($accountCode->kategori)
                                                    <span class="badge bg-info">{{ $accountCode->kategori_label }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                <span class="{{ $accountCode->status_badge_class }}">
                                                    {{ $accountCode->status_label }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="text-primary mb-3">
                                        <i class="fas fa-clock me-2"></i>
                                        Informasi Sistem
                                    </h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="35%"><strong>ID:</strong></td>
                                            <td>{{ $accountCode->id }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Dibuat:</strong></td>
                                            <td>{{ $accountCode->created_at->format('d/m/Y H:i:s') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Terakhir Update:</strong></td>
                                            <td>{{ $accountCode->updated_at->format('d/m/Y H:i:s') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Durasi:</strong></td>
                                            <td>{{ $accountCode->created_at->diffForHumans() }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <!-- Deskripsi -->
                            @if($accountCode->deskripsi)
                            <div class="mb-4">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-align-left me-2"></i>
                                    Deskripsi
                                </h5>
                                <div class="card">
                                    <div class="card-body">
                                        <p class="mb-0">{{ $accountCode->deskripsi }}</p>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Aksi -->
                            <div class="mb-4">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-cogs me-2"></i>
                                    Aksi
                                </h5>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('manage.account-codes.edit', $accountCode) }}" class="btn btn-warning">
                                        <i class="fas fa-edit me-1"></i>
                                        Edit Akun
                                    </a>
                                    <form action="{{ route('manage.account-codes.toggle-status', $accountCode) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-secondary"
                                                onclick="return confirm('Yakin ingin mengubah status kode akun ini?')">
                                            <i class="fas fa-toggle-on me-1"></i>
                                            {{ $accountCode->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('manage.account-codes.destroy', $accountCode) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger"
                                                onclick="return confirm('Yakin ingin menghapus kode akun ini?')">
                                            <i class="fas fa-trash me-1"></i>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <!-- Statistik -->
                            <div class="card bg-primary text-white mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title">Tipe Akun</h6>
                                            <h4>{{ $accountCode->tipe_label }}</h4>
                                        </div>
                                        <div>
                                            <i class="fas fa-chart-line fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($accountCode->kategori)
                            <div class="card bg-info text-white mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title">Kategori</h6>
                                            <h4>{{ $accountCode->kategori_label }}</h4>
                                        </div>
                                        <div>
                                            <i class="fas fa-tags fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div class="card {{ $accountCode->is_active ? 'bg-success' : 'bg-danger' }} text-white mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title">Status</h6>
                                            <h4>{{ $accountCode->status_label }}</h4>
                                        </div>
                                        <div>
                                            <i class="fas {{ $accountCode->is_active ? 'fa-check-circle' : 'fa-times-circle' }} fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Panduan Tipe -->
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Panduan Tipe Akun
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <small class="text-muted">
                                        <strong>{{ $accountCode->tipe_label }}:</strong><br>
                                        @switch($accountCode->tipe)
                                            @case('aktiva')
                                                Aset yang dimiliki sekolah yang dapat memberikan manfaat ekonomi di masa depan.
                                                @break
                                            @case('pasiva')
                                                Kewajiban sekolah yang harus dibayar kepada pihak lain.
                                                @break
                                            @case('modal')
                                                Investasi pemilik atau laba yang ditahan untuk operasional sekolah.
                                                @break
                                            @case('pendapatan')
                                                Penghasilan yang diperoleh sekolah dari berbagai sumber.
                                                @break
                                            @case('beban')
                                                Biaya yang dikeluarkan sekolah untuk operasional dan administrasi.
                                                @break
                                        @endswitch
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 