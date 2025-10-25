@extends('layouts.coreui')

@section('title', 'Kelola Tabungan')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Kelola Tabungan</h4>
        <a href="{{ route('manage.tabungan.create') }}" class="btn btn-primary text-white">
            <i class="fas fa-bank me-2 text-white"></i>Tambah Tabungan
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px;">
                        <i class="fas fa-piggy-bank text-primary fa-2x"></i>
                    </div>
                    <h5 class="mb-1 fw-bold">{{ $totalStudents }}</h5>
                    <small class="text-muted">Total Siswa dengan Tabungan</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px;">
                        <i class="fas fa-coins text-success fa-2x"></i>
                    </div>
                    <h5 class="mb-1 fw-bold">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</h5>
                    <small class="text-muted">Total Saldo Tabungan</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px;">
                        <i class="fas fa-chart-pie text-info fa-2x"></i>
                    </div>
                    <h5 class="mb-1 fw-bold">Rp {{ number_format($averageSaldo, 0, ',', '.') }}</h5>
                    <small class="text-muted">Rata-rata Saldo per Siswa</small>
                </div>
            </div>
        </div>
    </div>

         <!-- Filter Section -->
     <div class="card border-0 shadow-sm mb-4">
         <div class="card-header bg-transparent border-0">
             <h6 class="mb-0 fw-bold">Filter Pencarian</h6>
         </div>
         <div class="card-body">
             <form method="GET" action="{{ route('manage.tabungan.index') }}" class="row g-3">
                 <div class="col-md-4">
                     <label for="search" class="form-label">NIS / Nama Siswa</label>
                     <input type="text" class="form-control" id="search" name="search" 
                            value="{{ request('search') }}" placeholder="Cari berdasarkan NIS atau nama...">
                 </div>
                 <div class="col-md-3">
                     <label for="class_id" class="form-label">Kelas</label>
                     <select class="form-select" id="class_id" name="class_id">
                         <option value="">Semua Kelas</option>
                         @foreach($classes as $class)
                             <option value="{{ $class->class_id }}" {{ request('class_id') == $class->class_id ? 'selected' : '' }}>
                                 {{ $class->class_name }}
                             </option>
                         @endforeach
                     </select>
                 </div>
                 <div class="col-md-3">
                     <label for="status" class="form-label">Status</label>
                     <select class="form-select" id="status" name="status">
                         <option value="">Semua Status</option>
                         <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                         <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                     </select>
                 </div>
                 <div class="col-md-2 d-flex align-items-end">
                     <div class="d-grid gap-2 w-100">
                         <button type="submit" class="btn btn-primary text-white">
                             <i class="fas fa-search me-1 text-white"></i>Cari
                         </button>
                     </div>
                 </div>
             </form>
             @if(request('search') || request('class_id') || request('status'))
                 <div class="mt-3">
                     <a href="{{ route('manage.tabungan.index') }}" class="btn btn-outline-secondary btn-sm">
                         <i class="fas fa-times me-1"></i>Hapus Filter
                     </a>
                 </div>
             @endif
         </div>
     </div>
 
     <!-- Tabungan Table -->
     <div class="card border-0 shadow-sm">
         <div class="card-header bg-transparent border-0">
             <h6 class="mb-0 fw-bold">Daftar Tabungan Siswa</h6>
         </div>
         <div class="card-body">
            @if($tabungan->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                                                 <th>No</th>
                                 <th>NIS</th>
                                 <th>Nama Siswa</th>
                                 <th>Kelas</th>
                                 <th>Saldo</th>
                                 <th>Tanggal Input</th>
                                 <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tabungan as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->student_nis }}</td>
                                    <td>{{ $item->student_full_name }}</td>
                                    <td>{{ $item->class_name }}</td>
                                    <td>
                                        <span class="fw-bold text-success">
                                            Rp {{ number_format($item->saldo, 0, ',', '.') }}
                                        </span>
                                    </td>
                                                                         <td>{{ \Carbon\Carbon::parse($item->tabungan_input_date)->format('d/m/Y H:i') }}</td>
                                     <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('manage.tabungan.setoran', $item->tabungan_id) }}" 
                                               class="btn btn-sm btn-success text-white" title="Setoran">
                                                <i class="fas fa-arrow-up text-white"></i>
                                            </a>
                                            <a href="{{ route('manage.tabungan.penarikan', $item->tabungan_id) }}" 
                                               class="btn btn-sm btn-warning text-white" title="Penarikan">
                                                <i class="fas fa-arrow-down text-white"></i>
                                            </a>
                                            <a href="{{ route('manage.tabungan.riwayat', $item->tabungan_id) }}" 
                                               class="btn btn-sm btn-info text-white" title="Riwayat">
                                                <i class="fas fa-list-alt text-white"></i>
                                            </a>
                                            <a href="{{ route('manage.tabungan.edit', $item->tabungan_id) }}" 
                                               class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>
                                            <form action="{{ route('manage.tabungan.destroy', $item->tabungan_id) }}" 
                                                  method="POST" 
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus tabungan ini?')"
                                                  style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                                 </div>
 
                 <!-- Pagination -->
                 <div class="d-flex justify-content-center mt-4">
                     {{ $tabungan->links('pagination::simple-bootstrap-4') }}
                 </div>
 
             @else
                <div class="text-center py-5">
                    <i class="fas fa-piggy-bank fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">Belum ada data tabungan</h6>
                    <p class="text-muted mb-0">Klik tombol "Tambah Tabungan" untuk menambahkan data</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 