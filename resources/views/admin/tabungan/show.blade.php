@extends('layouts.coreui')

@section('title', 'Detail Tabungan')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Detail Tabungan</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="150"><strong>NIS:</strong></td>
                                    <td>{{ $tabungan->student_nis }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Nama Siswa:</strong></td>
                                    <td>{{ $tabungan->student_full_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Kelas:</strong></td>
                                    <td>{{ $tabungan->class_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Saldo:</strong></td>
                                    <td>
                                        <span class="{{ $tabungan->saldo >= 0 ? 'text-success' : 'text-danger' }}">
                                            Rp {{ number_format($tabungan->saldo, 0, ',', '.') }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal Input:</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($tabungan->tabungan_input_date)->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <a href="{{ route('manage.tabungan.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                        <a href="{{ route('manage.tabungan.edit', $tabungan->tabungan_id) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Edit
                        </a>
                        <a href="{{ route('manage.tabungan.setoran', $tabungan->tabungan_id) }}" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i>Setoran
                        </a>
                        <a href="{{ route('manage.tabungan.penarikan', $tabungan->tabungan_id) }}" class="btn btn-warning">
                            <i class="fas fa-minus me-2"></i>Penarikan
                        </a>
                        <a href="{{ route('manage.tabungan.riwayat', $tabungan->tabungan_id) }}" class="btn btn-info">
                            <i class="fas fa-history me-2"></i>Riwayat
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 