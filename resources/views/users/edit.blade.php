@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="mb-7">
        <h1 class="fs-2 fw-bold text-gray-900 mb-2">Ubah Pengguna</h1>
        <p class="text-gray-600 fs-6 mb-0">Form ubah data pengguna</p>
    </div>

    {{-- Form Card --}}
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-solid ki-notepad-edit fs-2 me-2"></i>
                        Ubah Data Pengguna
                    </h3>
                </div>
                <div class="card-body">
                    {{-- Error Alert --}}
                    @if($errors->any())
                        <div class="alert alert-danger d-flex align-items-start mb-5">
                            <i class="ki-solid ki-information-5 fs-2 me-3"></i>
                            <div>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('web.users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                {{-- Nama Lengkap --}}
                                <div class="mb-5">
                                    <label class="form-label fs-6 fw-semibold required">Nama Lengkap</label>
                                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                           class="form-control form-control-solid @error('name') is-invalid @enderror">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Password --}}
                                <div class="mb-5">
                                    <label class="form-label fs-6 fw-semibold">Password</label>
                                    <input type="password" name="password" minlength="8"
                                           class="form-control form-control-solid @error('password') is-invalid @enderror">
                                    <div class="form-text">Kosongkan jika tidak ingin mengubah password. Minimal 8 karakter jika diisi.</div>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                {{-- Email --}}
                                <div class="mb-5">
                                    <label class="form-label fs-6 fw-semibold required">Email</label>
                                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                           class="form-control form-control-solid @error('email') is-invalid @enderror">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Role --}}
                                <div class="mb-5">
                                    <label class="form-label fs-6 fw-semibold required">Role</label>
                                    @php $userRole = $user->roles->first()?->name; @endphp
                                    <select name="role" required class="form-select form-select-solid @error('role') is-invalid @enderror">
                                        <option value="">— Pilih Role —</option>
                                        <option value="Super Admin" {{ old('role', $userRole) === 'Super Admin' ? 'selected' : '' }}>Super Admin</option>
                                        <option value="Healthcare User" {{ old('role', $userRole) === 'Healthcare User' ? 'selected' : '' }}>Healthcare User</option>
                                        <option value="Approver" {{ old('role', $userRole) === 'Approver' ? 'selected' : '' }}>Approver</option>
                                        <option value="Finance" {{ old('role', $userRole) === 'Finance' ? 'selected' : '' }}>Finance</option>
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Organisasi --}}
                            <div class="col-12 mb-5">
                                <label class="form-label fs-6 fw-semibold">Organisasi</label>
                                <select name="organization_id" class="form-select form-select-solid @error('organization_id') is-invalid @enderror">
                                    <option value="">— System Wide (Super Admin/Approver) —</option>
                                    @foreach($organizations as $organization)
                                        <option value="{{ $organization->id }}" {{ old('organization_id', $user->organization_id) == $organization->id ? 'selected' : '' }}>
                                            {{ $organization->name }} ({{ ucfirst($organization->type) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('organization_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Status Aktif Checkbox --}}
                            <div class="col-12 mb-5">
                                <div class="form-check form-check-custom form-check-solid">
                                    <input type="checkbox" name="is_active" value="1" id="is_active" 
                                           {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                           class="form-check-input">
                                    <label class="form-check-label" for="is_active">
                                        <span class="fw-bold text-primary">Akun Aktif</span>
                                        <span class="d-block text-gray-600 fs-7 mt-1">Nonaktifkan untuk mencabut akses sementara</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-flex justify-content-end gap-3 mt-7 pt-7 border-top">
                            <a href="{{ route('web.users.index') }}" class="btn btn-light">
                                <i class="ki-solid ki-cross fs-2"></i>
                                Batal
                            </a>
                            <button type="submit" class="btn btn-primary update-confirm" data-name="{{ $user->name }}">
                                <i class="ki-solid ki-check fs-2"></i>
                                Perbarui Pengguna
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
