@extends('layouts.adminty')

@section('head')
<title>Edit Pos Pembayaran</title>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Edit Pos Pembayaran</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('pos.update', $pos->pos_id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="pos_name" class="form-label">Nama Pos <span class="text-danger">*</span></label>
                            <input type="text" name="pos_name" id="pos_name" class="form-control @error('pos_name') is-invalid @enderror" value="{{ old('pos_name', $pos->pos_name) }}" required>
                            @error('pos_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="pos_description" class="form-label">Deskripsi</label>
                            <input type="text" name="pos_description" id="pos_description" class="form-control @error('pos_description') is-invalid @enderror" value="{{ old('pos_description', $pos->pos_description) }}">
                            @error('pos_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('pos.index') }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 