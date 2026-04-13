@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="mb-7">
        <h1 class="fs-2 fw-bold text-gray-900 mb-2">Tambah Pengguna</h1>
        <p class="text-gray-600 fs-6 mb-0">Form tambah pengguna baru</p>
    </div>

    {{-- Form Card --}}
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-outline ki-user-tick fs-2 me-2"></i>
                        Registrasi Pengguna Baru
                    </h3>
                </div>
                <div class="card-body">
                    {{-- Error Alert --}}
                    @if($errors->any())
                        <div class="alert alert-danger d-flex align-items-start mb-5">
                            <i class="ki-outline ki-information-5 fs-2 me-3"></i>
                            <div>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('web.users.store') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                {{-- Nama Lengkap --}}
                                <div class="mb-5">
                                    <label class="form-label fs-6 fw-semibold required">Nama Lengkap</label>
                                    <input type="text" name="name" value="{{ old('name') }}" required
                                           placeholder="John Doe"
                                           class="form-control form-control-solid @error('name') is-invalid @enderror">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Password --}}
                                <div class="mb-5">
                                    <label class="form-label fs-6 fw-semibold required">Password</label>
                                    <input type="password" name="password" required minlength="8"
                                           placeholder="••••••••"
                                           class="form-control form-control-solid @error('password') is-invalid @enderror">
                                    <div class="form-text">Minimal 8 karakter</div>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                {{-- Email --}}
                                <div class="mb-5">
                                    <label class="form-label fs-6 fw-semibold required">Email</label>
                                    <input type="email" name="email" value="{{ old('email') }}" required
                                           placeholder="john@example.com"
                                           class="form-control form-control-solid @error('email') is-invalid @enderror">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Role --}}
                                <div class="mb-5">
                                    <label class="form-label fs-6 fw-semibold required">Role</label>
                                    <select name="role" required class="form-select form-select-solid @error('role') is-invalid @enderror">
                                        <option value="">— Pilih Role —</option>
                                        <option value="Super Admin" {{ old('role') === 'Super Admin' ? 'selected' : '' }}>Super Admin</option>
                                        <option value="Healthcare User" {{ old('role') === 'Healthcare User' ? 'selected' : '' }}>Healthcare User</option>
                                        <option value="Approver" {{ old('role') === 'Approver' ? 'selected' : '' }}>Approver</option>
                                        <option value="Finance" {{ old('role') === 'Finance' ? 'selected' : '' }}>Finance</option>
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
                                        <option value="{{ $organization->id }}" {{ old('organization_id') == $organization->id ? 'selected' : '' }}>
                                            {{ $organization->name }} ({{ ucfirst($organization->type) }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Biarkan kosong untuk Super Admin dan Approver lintas organisasi</div>
                                @error('organization_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-flex justify-content-end gap-3 mt-7 pt-7 border-top">
                            <a href="{{ route('web.users.index') }}" class="btn btn-light">
                                <i class="ki-outline ki-cross fs-2"></i>
                                Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ki-outline ki-check fs-2"></i>
                                Tambah Pengguna
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
