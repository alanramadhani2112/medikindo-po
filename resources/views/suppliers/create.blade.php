@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="mb-7">
        <h1 class="fs-2 fw-bold text-gray-900 mb-2">Tambah Supplier</h1>
        <p class="text-gray-600 fs-6 mb-0">Form tambah supplier baru</p>
    </div>

    {{-- Form Card --}}
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Registrasi Supplier Baru
                    </h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('web.suppliers.store') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                {{-- Nama Supplier --}}
                                <div class="mb-5">
                                    <label class="form-label fs-6 fw-semibold required">Nama Supplier</label>
                                    <input type="text" name="name" value="{{ old('name') }}" required
                                           placeholder="PT. Medika Sejahtera"
                                           class="form-control form-control-solid @error('name') is-invalid @enderror">
                                    <div class="form-text">Nama resmi badan hukum pemasok</div>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Email --}}
                                <div class="mb-5">
                                    <label class="form-label fs-6 fw-semibold">Email Kontak</label>
                                    <input type="email" name="email" value="{{ old('email') }}"
                                           placeholder="sales@supplier.com"
                                           class="form-control form-control-solid @error('email') is-invalid @enderror">
                                    <div class="form-text">Alamat surat elektronik untuk PO info</div>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- NPWP --}}
                                <div class="mb-5">
                                    <label class="form-label fs-6 fw-semibold">NPWP (Pajak)</label>
                                    <input type="text" name="npwp" value="{{ old('npwp') }}"
                                           placeholder="xx.xxx.xxx.x-xxx.xxx"
                                           class="form-control form-control-solid @error('npwp') is-invalid @enderror">
                                    <div class="form-text">Nomor Pokok Wajib Pajak untuk finance</div>
                                    @error('npwp')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                {{-- Kode Supplier --}}
                                <div class="mb-5">
                                    <label class="form-label fs-6 fw-semibold required">Kode Supplier</label>
                                    <input type="text" name="code" value="{{ old('code') }}" required maxlength="20"
                                           placeholder="SPL-01"
                                           class="form-control form-control-solid @error('code') is-invalid @enderror">
                                    <div class="form-text">Kode identifikasi sistem (Unique)</div>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Telepon --}}
                                <div class="mb-5">
                                    <label class="form-label fs-6 fw-semibold">Nomor Telepon</label>
                                    <input type="text" name="phone" value="{{ old('phone') }}"
                                           placeholder="021-xxxxxxx"
                                           class="form-control form-control-solid @error('phone') is-invalid @enderror">
                                    <div class="form-text">Kontak sales atau operasional aktif</div>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Nomor Izin PBF --}}
                                <div class="mb-5">
                                    <label class="form-label fs-6 fw-semibold required">Nomor Izin PBF</label>
                                    <input type="text" name="license_number" value="{{ old('license_number') }}" required
                                           placeholder="Nomor izin resmi..."
                                           class="form-control form-control-solid @error('license_number') is-invalid @enderror">
                                    <div class="form-text">Wajib untuk penyalur sediaan farmasi</div>
                                    @error('license_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Tanggal Kadaluarsa Izin --}}
                                <div class="mb-5">
                                    <label class="form-label fs-6 fw-semibold">Tanggal Kadaluarsa Izin</label>
                                    <input type="date" name="license_expiry_date" value="{{ old('license_expiry_date') }}"
                                           class="form-control form-control-solid @error('license_expiry_date') is-invalid @enderror">
                                    <div class="form-text">Tanggal berakhirnya izin distribusi</div>
                                    @error('license_expiry_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                {{-- Alamat --}}
                                <div class="mb-5">
                                    <label class="form-label fs-6 fw-semibold">Alamat Kantor/Gudang</label>
                                    <textarea name="address" rows="3" placeholder="Jl. Industri No. 45..."
                                              class="form-control form-control-solid @error('address') is-invalid @enderror">{{ old('address') }}</textarea>
                                    <div class="form-text">Detail lokasi penjemputan/korespondensi</div>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Izin Narkotika --}}
                                <div class="mb-5">
                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" name="is_authorized_narcotic" 
                                               id="is_authorized_narcotic" value="1" {{ old('is_authorized_narcotic') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold text-gray-700" for="is_authorized_narcotic">
                                            Memiliki Izin Distribusi Narkotika
                                        </label>
                                    </div>
                                    <div class="form-text text-warning mt-2">
                                        <i class="ki-outline ki-information-5 fs-5"></i>
                                        Centang jika supplier memiliki izin resmi untuk mendistribusikan obat narkotika/psikotropika
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-flex justify-content-end gap-3 mt-7 pt-7 border-top">
                            <a href="{{ route('web.suppliers.index') }}" class="btn btn-light">
                                <i class="ki-outline ki-arrow-zigzag fs-2"></i>
                                Batal
                            </a>
                            <button type="submit" class="btn btn-primary create-confirm" data-type="supplier">
                                <i class="ki-outline ki-picture fs-2"></i>
                                Simpan Registrasi Supplier
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
