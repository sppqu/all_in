@extends('layouts.coreui')

@section('head')
<title>Tambah Jenis Pembayaran</title>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light border-bottom-0" style="border-top: 4px solid #28a745;">
                    <h4 class="mb-0">Tambah Jenis Pembayaran</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('payment.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="pos_pos_id" class="form-label">Nama Pembayaran *</label>
                            <select name="pos_pos_id" id="pos_pos_id" class="form-select @error('pos_pos_id') is-invalid @enderror" required>
                                <option value="">-Pilih Nama Pembayaran-</option>
                                @foreach($posList as $pos)
                                    <option value="{{ $pos->pos_id }}" {{ old('pos_pos_id')==$pos->pos_id?'selected':'' }}>{{ $pos->pos_name }}</option>
                                @endforeach
                            </select>
                            @error('pos_pos_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="period_period_id" class="form-label">Tahun Pelajaran *</label>
                            <select name="period_period_id" id="period_period_id" class="form-select @error('period_period_id') is-invalid @enderror" required>
                                <option value="">-Pilih Tahun-</option>
                                @foreach($periods as $period)
                                    <option value="{{ $period->period_id }}" {{ old('period_period_id')==$period->period_id?'selected':'' }}>{{ $period->period_start }}/{{ $period->period_end }}</option>
                                @endforeach
                            </select>
                            @error('period_period_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="payment_type" class="form-label">Tipe *</label>
                            <select name="payment_type" id="payment_type" class="form-select @error('payment_type') is-invalid @enderror" required>
                                <option value="">-Pilih Tipe-</option>
                                <option value="BULAN" {{ old('payment_type')=='BULAN'?'selected':'' }}>Bulanan</option>
                                <option value="BEBAS" {{ old('payment_type')=='BEBAS'?'selected':'' }}>Bebas</option>
                            </select>
                            @error('payment_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="text-muted small mb-3">*) Kolom wajib diisi.</div>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('payment.index') }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 