@extends('layouts.coreui')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="font-size: 1.5rem;">Kelola Sekolah</h4>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">{{ $foundation->nama_yayasan }}</p>
        </div>
        <a href="{{ route('manage.foundation.schools.create') }}" class="btn btn-primary">
            <i class="fa fa-plus me-2"></i>Tambah Sekolah
        </a>
    </div>

    <!-- Schools Table -->
    <div class="card shadow-sm border-0" style="border-radius: 15px;">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr style="font-size: 0.9rem;">
                            <th style="font-size: 0.9rem;">Logo</th>
                            <th style="font-size: 0.9rem;">Nama Sekolah</th>
                            <th style="font-size: 0.9rem;">Jenjang</th>
                            <th style="font-size: 0.9rem;">Alamat</th>
                            <th style="font-size: 0.9rem;">No. Telp</th>
                            <th style="font-size: 0.9rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schools as $school)
                        <tr>
                            <td>
                                @if($school->logo_sekolah)
                                    <img src="{{ Storage::url($school->logo_sekolah) }}" 
                                         alt="{{ $school->nama_sekolah }}" 
                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                @else
                                    <div class="bg-secondary text-white rounded d-flex align-items-center justify-content-center" 
                                         style="width: 50px; height: 50px;">
                                        <i class="fa fa-school"></i>
                                    </div>
                                @endif
                            </td>
                            <td style="font-size: 0.9rem;">
                                <strong>{{ $school->nama_sekolah }}</strong>
                            </td>
                            <td style="font-size: 0.9rem;">{{ $school->jenjang }}</td>
                            <td style="font-size: 0.9rem;">{{ Str::limit($school->alamat, 50) }}</td>
                            <td style="font-size: 0.9rem;">{{ $school->no_telp }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('manage.foundation.schools.show', $school) }}" 
                                       class="btn btn-sm btn-info" title="Detail">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <a href="{{ route('manage.foundation.schools.edit', $school) }}" 
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <p class="text-muted mb-2">Belum ada sekolah terdaftar</p>
                                <a href="{{ route('manage.foundation.schools.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fa fa-plus me-2"></i>Tambah Sekolah Pertama
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

