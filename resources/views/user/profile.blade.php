@extends('layouts.adminty')

@section('title', 'Profil Pengguna')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header fw-bold">Profil Pengguna</div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <form method="POST" action="{{ route('manage.profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">Nama</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="name" value="{{ old('name', $user->name) }}" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">Email</label>
                            <div class="col-sm-9">
                                <input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">No. WA</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="nomor_wa" value="{{ old('nomor_wa', $user->nomor_wa) }}">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">Foto</label>
                            <div class="col-sm-9 d-flex align-items-center" style="gap:12px;">
                                <input type="file" class="form-control" name="avatar" accept="image/*">
                                @if($user->avatar_path)
                                    <img src="{{ asset('storage/'.$user->avatar_path) }}" alt="avatar" style="width:48px;height:48px;border-radius:50%;object-fit:cover;">
                                @endif
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


