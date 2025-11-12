@extends('layouts.coreui')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card my-3">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Managemen Pengguna</h4>
                    <a href="{{ route('manage.users.create') }}" class="btn btn-primary"><i class="fa fa-plus me-1"></i> Tambah Pengguna</a>
                </div>
                <div class="card-body">
                    @if(auth()->user()->role === 'superadmin' || auth()->user()->role === 'admin_yayasan')
                    <div class="mb-4">
                        <form method="GET" action="{{ route('manage.users.index') }}" class="d-flex align-items-end gap-2">
                            <div class="flex-grow-1">
                                <label for="school_id" class="form-label text-dark fw-semibold">Sekolah</label>
                                <select name="school_id" id="school_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">Semua Sekolah</option>
                                    @foreach($schools as $school)
                                        <option value="{{ $school->id }}" {{ $selectedSchoolId == $school->id ? 'selected' : '' }}>
                                            {{ $school->nama_sekolah }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @if($selectedSchoolId)
                            <a href="{{ route('manage.users.index') }}" class="btn btn-outline-secondary">
                                <i class="fa fa-times me-1"></i> Reset
                            </a>
                            @endif
                        </form>
                    </div>
                    @endif
                    <table class="table table-bordered">
                        <thead>
                            <tr class="table-primary">
                                <th>#</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>No. WhatsApp</th>
                                <th>Role</th>
                                @if(auth()->user()->role === 'superadmin' || auth()->user()->role === 'admin_yayasan')
                                <th>Sekolah</th>
                                @endif
                                <th>Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $i => $user)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->nomor_wa ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-info">{{ ucfirst($user->role) }}</span>
                                </td>
                                @if(auth()->user()->role === 'superadmin' || auth()->user()->role === 'admin_yayasan')
                                <td>
                                    @if(in_array($user->role, ['superadmin', 'admin_yayasan']))
                                        <span class="badge bg-secondary">Semua Sekolah</span>
                                    @else
                                        @php
                                            $userSchools = $user->schools()->get();
                                        @endphp
                                        @if($userSchools->count() > 0)
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($userSchools as $school)
                                                    <span class="badge bg-success">{{ $school->nama_sekolah }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted">Belum di-assign</span>
                                        @endif
                                    @endif
                                </td>
                                @endif
                                <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('manage.users.edit', $user->id) }}" class="btn btn-sm btn-outline-primary"><i class="fa fa-edit"></i></a>
                                    <form action="{{ route('manage.users.destroy', $user->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Yakin hapus pengguna ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ (auth()->user()->role === 'superadmin' || auth()->user()->role === 'admin_yayasan') ? '8' : '7' }}" class="text-center py-4">
                                    <p class="text-muted mb-0">Tidak ada data pengguna</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
.card-header.bg-primary, .card-header {
    background-color: #2e7d32 !important;
    color: #fff !important;
}
.btn-outline-primary {
    border-color: #2e7d32;
    color: #2e7d32;
}
.btn-outline-primary.active, .btn-outline-primary:active, .btn-outline-primary:focus, .btn-outline-primary:hover {
    background-color: #2e7d32 !important;
    color: #fff !important;
    border-color: #2e7d32 !important;
}
.btn-primary {
    background-color: #2e7d32 !important;
    border-color: #2e7d32 !important;
    color: #fff !important;
}
.btn-primary:active, .btn-primary:focus, .btn-primary:hover {
    background-color: #256026 !important;
    border-color: #256026 !important;
    color: #fff !important;
}
.table-primary {
    background-color: #e8f5e9 !important;
    color: #2e7d32 !important;
}
</style>
@endsection 