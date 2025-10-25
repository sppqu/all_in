@extends('layouts.coreui')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card my-3">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Data Tahun Pelajaran</h4>
                    <button class="btn btn-primary" style="min-width:200px;"><i class="fa fa-plus me-1"></i> Tambah Tahun Pelajaran</button>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">Halaman Tahun Pelajaran.</div>
                    <div class="mb-3">
                        <button class="btn btn-outline-primary">Tambah Tahun Pelajaran</button>
                    </div>
                    <table class="table table-bordered">
                        <thead>
                            <tr class="table-primary">
                                <th>#</th>
                                <th>Tahun Pelajaran</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>2023/2024</td>
                                <td><span class="badge bg-success">Aktif</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary">Edit</button>
                                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                </td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>2022/2023</td>
                                <td><span class="badge bg-secondary">Nonaktif</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary">Edit</button>
                                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                </td>
                            </tr>
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
.table-dark th, .table-dark td, .table-dark {
    background-color: #2e7d32 !important;
    color: #fff !important;
}
.alert-success {
    background-color: #e8f5e9;
    color: #2e7d32;
    border-color: #2e7d32;
}
.badge.bg-success {
    background-color: #2e7d32 !important;
}
</style>
@endsection 