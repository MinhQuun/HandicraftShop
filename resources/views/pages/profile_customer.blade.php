@extends('layouts.main')

@section('title', 'Thông tin cá nhân')

@section('content')
<div class="container mt-5">
    <h2 class="mb-4">Thông tin cá nhân</h2>


    {{-- Thông tin cá nhân --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label">Họ tên</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="name" name="name" value="{{ old('name', $user->name) }}">
                    @error('name') 
                        <div class="invalid-feedback">{{ $message }}</div> 
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                           id="email" name="email" value="{{ old('email', $user->email) }}">
                    @error('email') 
                        <div class="invalid-feedback">{{ $message }}</div> 
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Số điện thoại</label>
                    <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                           id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                    @error('phone') 
                        <div class="invalid-feedback">{{ $message }}</div> 
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Cập nhật thông tin</button>
                <button type="button" class="btn btn-warning ms-2" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                    Đổi mật khẩu
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Modal đổi mật khẩu --}}
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg">
      <div class="modal-header bg-warning text-white">
        <h5 class="modal-title" id="changePasswordModalLabel">Đổi mật khẩu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('profile.changePassword') }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
            <div class="mb-3">
                <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
                <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                       id="current_password" name="current_password">
                @error('current_password') 
                    <div class="invalid-feedback">{{ $message }}</div> 
                @enderror
            </div>

            <div class="mb-3">
                <label for="new_password" class="form-label">Mật khẩu mới</label>
                <input type="password" class="form-control @error('new_password') is-invalid @enderror" 
                       id="new_password" name="new_password">
                @error('new_password') 
                    <div class="invalid-feedback">{{ $message }}</div> 
                @enderror
            </div>

            <div class="mb-3">
                <label for="new_password_confirmation" class="form-label">Xác nhận mật khẩu mới</label>
                <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation">
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
          <button type="submit" class="btn btn-warning">Đổi mật khẩu</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Optional: thêm CSS để nâng cấp giao diện --}}
@push('styles')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endpush
@push('scripts')
<script src="{{ asset('js/profile.js') }}"></script>
@endpush
@endsection
@section('scripts')