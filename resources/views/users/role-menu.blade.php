@extends('layouts.adminty')

@section('title','Hak Akses Menu')

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h6 class="mb-0">Pengaturan Hak Akses Menu per Role</h6>
    <a href="{{ url()->previous() }}" class="btn btn-sm btn-secondary">Kembali</a>
  </div>
  <div class="card-body">
    <div class="alert alert-info mb-3">
      <i class="fa fa-info-circle me-2"></i>
      <strong>Admin</strong> dan <strong>Superadmin</strong> memiliki akses penuh ke semua menu secara default.
    </div>
  </div>
  <form method="post" action="{{ route('manage.users.role-menu.save') }}" id="roleMenuForm">
    @csrf
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead>
            <tr>
              <th>Menu</th>
                              @foreach($roles as $role)
                  @if(!in_array($role, ['admin', 'superadmin']))
                    <th class="text-center text-capitalize">
                      @if($role === 'kasir')
                        Kasir
                      @elseif($role === 'bendahara')
                        Bendahara
                      @elseif($role === 'spmb_admin')
                        Admin SPMB
                      @elseif($role === 'admin_perpustakaan')
                        Admin Perpustakaan
                      @elseif($role === 'admin_bk')
                        Admin BK
                      @elseif($role === 'admin_jurnal')
                        Admin Jurnal
                      @else
                        {{ ucfirst(str_replace('_', ' ', $role)) }}
                      @endif
                    </th>
                  @endif
                @endforeach
            </tr>
          </thead>
          <tbody>
            @php $labels = config('menus'); @endphp
            @foreach($menuKeys as $key)
              <tr>
                <td>{{ $labels[$key] ?? $key }}</td>
                @foreach($roles as $role)
                  @if(!in_array($role, ['admin', 'superadmin']))
                    @php
                      $allowed = optional($permissions[$role] ?? collect())->firstWhere('menu_key',$key)->allowed ?? null;
                    @endphp
                    <td class="text-center">
                      <input type="checkbox" name="perm[{{ $role }}][{{ $key }}]" {{ $allowed ? 'checked' : '' }}>
                    </td>
                  @endif
                @endforeach
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="mt-3">
        <button type="submit" class="btn btn-primary">
          <i class="fa fa-save me-2"></i>Simpan
        </button>
      </div>
    </div>
  </form>
</div>


@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form submission with loading state
    const form = document.getElementById('roleMenuForm');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    form.addEventListener('submit', function() {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Menyimpan...';
    });
});
</script>
@endsection


