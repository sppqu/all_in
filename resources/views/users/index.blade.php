@extends('layouts.adminty')

@section('content')
@if(session('success') || session('error'))
    <div id="session-messages" style="display: none;">
        @if(session('success'))
            <div data-type="success" data-message="{{ session('success') }}"></div>
        @endif
        @if(session('error'))
            <div data-type="error" data-message="{{ session('error') }}"></div>
        @endif
    </div>
@endif

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
                                <select name="school_id" id="school_id" class="form-control select-primary" onchange="this.form.submit()">
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
                                    <div class="d-flex" style="gap: 8px;">
                                        <a href="{{ route('manage.users.edit', $user->id) }}" class="btn btn-sm btn-action-edit" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-action-delete" onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')" title="Hapus">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
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
    background-color: #01a9ac !important;
    color: #fff !important;
}
.btn-outline-primary {
    border-color: #01a9ac;
    color: #01a9ac;
}
.btn-outline-primary.active, .btn-outline-primary:active, .btn-outline-primary:focus, .btn-outline-primary:hover {
    background-color: #01a9ac !important;
    color: #fff !important;
    border-color: #01a9ac !important;
}
.btn-primary {
    background-color: #01a9ac !important;
    border-color: #01a9ac !important;
    color: #fff !important;
}
.btn-primary:active, .btn-primary:focus, .btn-primary:hover {
    background-color: #018a8c !important;
    border-color: #018a8c !important;
    color: #fff !important;
}
.table-primary {
    background-color: #ffffff !important;
    color: #212529 !important;
}
.table-primary th {
    background-color: #ffffff !important;
    color: #212529 !important;
    font-weight: 600;
    border-bottom: 2px solid #dee2e6 !important;
}

/* Action Buttons Styling */
.btn-action-edit,
.btn-action-delete {
    width: 36px;
    height: 36px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid;
    border-radius: 4px;
    background: white;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-action-edit {
    border-color: #01a9ac;
    color: #01a9ac;
}

.btn-action-edit:hover {
    background-color: #01a9ac;
    color: white;
}

.btn-action-delete {
    border-color: #dc3545;
    color: #dc3545;
}

.btn-action-delete:hover {
    background-color: #dc3545;
    color: white;
}
</style>

@push('scripts')
<script>
$(document).ready(function() {
    // Handle session messages with global toast
    const sessionMessages = $('#session-messages');
    if (sessionMessages.length) {
        sessionMessages.find('div').each(function() {
            const type = $(this).data('type');
            const message = $(this).data('message');
            
            if (typeof showToast === 'function') {
                showToast(type === 'success' ? 'success' : 'error', type === 'success' ? 'Berhasil' : 'Error', message);
            }
        });
    }
});

function deleteUser(userId, userName) {
    // Show confirmation dialog
    if (!confirm('Yakin hapus pengguna "' + userName + '"?')) {
        return;
    }
    
    // Create form dynamically and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("manage.users.destroy", ":id") }}'.replace(':id', userId);
    
    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    form.appendChild(csrfInput);
    
    // Add method spoofing
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';
    form.appendChild(methodInput);
    
    // Append to body and submit
    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush
@endsection 