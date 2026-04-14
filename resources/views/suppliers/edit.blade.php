@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="mb-7">
        <h1 class="fs-2 fw-bold text-gray-900 mb-2">Ubah Supplier</h1>
        <p class="text-gray-600 fs-6 mb-0">Form ubah data supplier</p>
    </div>

    {{-- Form Card --}}
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-duotone ki-note-2 fs-2 me-2"></i>
                        Ubah Data Supplier
                    </h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('web.suppliers.update', $supplier) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                {{-- Nama Supplier --}}
                                <div class="mb-5">
                                    <label class="form-label fs-6 fw-semibold required">Nama Supplier</label>
                                    <input type="text" name="name" value="{{ old('name', $supplier->name) }}" required
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
                                    <input type="email" name="email" value="{{ old('email', $supplier->email) }}"
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
                                    <input type="text" name="npwp" value="{{ old('npwp', $supplier->npwp) }}"
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
                                    <input type="text" name="code" value="{{ old('code', $supplier->code) }}" required maxlength="20"
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
                                    <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}"
                                           placeholder="021-xxxxxxx"
                                           class="form-control form-control-solid @error('phone') is-invalid @enderror">
                                    <div class="form-text">Kontak sales atau operasional aktif</div>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Nomor Izin PBF --}}
                                <div class="mb-5">
                                    <label class="form-label fs-6 fw-semibold">Nomor Izin PBF</label>
                                    <input type="text" name="license_number" value="{{ old('license_number', $supplier->license_number) }}"
                                           placeholder="Nomor izin resmi..."
                                           class="form-control form-control-solid @error('license_number') is-invalid @enderror">
                                    <div class="form-text">Wajib untuk penyalur sediaan farmasi</div>
                                    @error('license_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                {{-- Alamat --}}
                                <div class="mb-5">
                                    <label class="form-label fs-6 fw-semibold">Alamat Kantor/Gudang</label>
                                    <textarea name="address" rows="3" placeholder="Jl. Industri No. 45..."
                                              class="form-control form-control-solid @error('address') is-invalid @enderror">{{ old('address', $supplier->address) }}</textarea>
                                    <div class="form-text">Detail lokasi penjemputan/korespondensi</div>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-flex justify-content-end gap-3 mt-7 pt-7 border-top">
                            <a href="{{ route('web.suppliers.index') }}" class="btn btn-light">
                                <i class="ki-duotone ki-cross fs-2"></i>
                                Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ki-duotone ki-check fs-2"></i>
                                Perbarui Data Supplier
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
