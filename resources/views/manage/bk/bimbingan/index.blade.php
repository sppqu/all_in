@extends('layouts.coreui')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 fw-bold text-dark">
                <i class="fas fa-comments me-2 text-info"></i>Bimbingan Konseling
            </h2>
            <p class="text-muted mb-0">Kelola data bimbingan konseling siswa</p>
        </div>
        <div>
            <a href="{{ route('manage.bk.bimbingan-konseling') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
            <a href="{{ route('manage.bk.bimbingan.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Tambah Bimbingan
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('manage.bk.bimbingan.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Cari Siswa</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Nama atau NIS siswa..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="dijadwalkan" {{ request('status') == 'dijadwalkan' ? 'selected' : '' }}>Dijadwalkan</option>
                            <option value="berlangsung" {{ request('status') == 'berlangsung' ? 'selected' : '' }}>Berlangsung</option>
                            <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="ditunda" {{ request('status') == 'ditunda' ? 'selected' : '' }}>Ditunda</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Jenis Bimbingan</label>
                        <select name="jenis" class="form-select">
                            <option value="">Semua Jenis</option>
                            <option value="akademik" {{ request('jenis') == 'akademik' ? 'selected' : '' }}>Akademik</option>
                            <option value="pribadi" {{ request('jenis') == 'pribadi' ? 'selected' : '' }}>Pribadi</option>
                            <option value="sosial" {{ request('jenis') == 'sosial' ? 'selected' : '' }}>Sosial</option>
                            <option value="karir" {{ request('jenis') == 'karir' ? 'selected' : '' }}>Karir</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="10%">Tanggal</th>
                            <th width="15%">NIS/Nama Siswa</th>
                            <th width="10%">Kelas</th>
                            <th width="12%">Jenis</th>
                            <th width="10%">Kategori</th>
                            <th width="8%">Sesi</th>
                            <th width="10%">Status</th>
                            <th width="10%">Guru BK</th>
                            <th width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bimbingan as $index => $item)
                        <tr>
                            <td>{{ $bimbingan->firstItem() + $index }}</td>
                            <td>{{ $item->tanggal_bimbingan->format('d M Y') }}</td>
                            <td>
                                <div class="fw-semibold">{{ $item->siswa->student_full_name }}</div>
                                <small class="text-muted">{{ $item->siswa->student_nis }}</small>
                            </td>
                            <td>
                                @if($item->siswa->class)
                                    <span class="badge bg-secondary">{{ $item->siswa->class->class_name }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    {{ ucfirst($item->jenis_bimbingan) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $item->kategori_badge }}">
                                    {{ ucfirst($item->kategori) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary">Sesi #{{ $item->sesi_ke }}</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $item->status_badge }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $item->guruBK->name ?? '-' }}</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('manage.bk.bimbingan.show', $item->id) }}" 
                                       class="btn btn-info" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('manage.bk.bimbingan.edit', $item->id) }}" 
                                       class="btn btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('manage.bk.bimbingan.destroy', $item->id) }}" 
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted">Belum ada data bimbingan konseling</p>
                                <a href="{{ route('manage.bk.bimbingan.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus-circle me-2"></i>Tambah Bimbingan Pertama
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($bimbingan->hasPages())
        <div class="card-footer bg-white">
            {{ $bimbingan->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

