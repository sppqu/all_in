@extends('layouts.coreui')

@section('head')
<title>Edit Jenis Pembayaran</title>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Edit Jenis Pembayaran</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('payment.update', $payment->payment_id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="payment_type" class="form-label">Tipe Pembayaran <span class="text-danger">*</span></label>
                            <select name="payment_type" id="payment_type" class="form-select @error('payment_type') is-invalid @enderror" required>
                                <option value="">Pilih Tipe</option>
                                <option value="BEBAS" {{ old('payment_type', $payment->payment_type)=='BEBAS'?'selected':'' }}>BEBAS</option>
                                <option value="BULAN" {{ old('payment_type', $payment->payment_type)=='BULAN'?'selected':'' }}>BULAN</option>
                            </select>
                            @error('payment_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="period_period_id" class="form-label">Periode <span class="text-danger">*</span></label>
                            <select name="period_period_id" id="period_period_id" class="form-select @error('period_period_id') is-invalid @enderror" required>
                                <option value="">Pilih Periode</option>
                                @foreach($periods as $period)
                                    <option value="{{ $period->period_id }}" {{ old('period_period_id', $payment->period_period_id)==$period->period_id?'selected':'' }}>{{ $period->period_name }}</option>
                                @endforeach
                            </select>
                            @error('period_period_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="pos_pos_id" class="form-label">Pos Pembayaran <span class="text-danger">*</span></label>
                            <select name="pos_pos_id" id="pos_pos_id" class="form-select @error('pos_pos_id') is-invalid @enderror" required>
                                <option value="">Pilih Pos</option>
                                @foreach($posList as $pos)
                                    <option value="{{ $pos->pos_id }}" {{ old('pos_pos_id', $payment->pos_pos_id)==$pos->pos_id?'selected':'' }}>{{ $pos->pos_name }}</option>
                                @endforeach
                            </select>
                            @error('pos_pos_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_for_spmb" name="is_for_spmb" value="1" {{ old('is_for_spmb', $payment->is_for_spmb) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_for_spmb">
                                    <strong>Aktifkan untuk SPMB</strong>
                                </label>
                            </div>
                            <small class="form-text text-muted d-block mt-1">
                                <i class="fas fa-info-circle me-1"></i>
                                Jika dicentang, payment ini akan digunakan untuk membuat tagihan biaya SPMB saat transfer siswa yang diterima. Bisa digunakan untuk jenis Bulanan atau Bebas.
                            </small>
                        </div>
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