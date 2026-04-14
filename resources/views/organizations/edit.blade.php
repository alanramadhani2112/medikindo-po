<x-layout title="Ubah Organisasi" pageTitle="Ubah Organisasi" breadcrumb="Form ubah data">
    
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <x-card title="Ubah Data Organisasi">
                <form method="POST" action="{{ route('web.organizations.update', $organization) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-5">
                        <div class="col-12">
                            <label class="form-label required fw-semibold fs-6 mb-2">Tipe Organisasi</label>
                            <select name="type" required class="form-select form-select-solid">
                                <option value="clinic" {{ old('type', $organization->type) === 'clinic' ? 'selected' : '' }}>Klinik</option>
                                <option value="hospital" {{ old('type', $organization->type) === 'hospital' ? 'selected' : '' }}>Rumah Sakit</option>
                            </select>
                            <div class="form-text">Klasifikasi entitas untuk manajemen inventory & regulasi</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required fw-semibold fs-6 mb-2">Nama Organisasi</label>
                            <input type="text" name="name" value="{{ old('name', $organization->name) }}" required 
                                   class="form-control form-control-solid" 
                                   placeholder="Contoh: Medikindo Hospital">
                            <div class="form-text">Nama resmi faskes</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold fs-6 mb-2">Kode Internal</label>
                            <input type="text" name="code" value="{{ old('code', $organization->code) }}" required maxlength="20"
                                   class="form-control form-control-solid" 
                                   placeholder="KMU-01">
                            <div class="form-text">Kode identifikasi sistem (Unique)</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-semibold fs-6 mb-2">Email Operasional</label>
                            <input type="email" name="email" value="{{ old('email', $organization->email) }}" 
                                   class="form-control form-control-solid" 
                                   placeholder="email@example.com">
                            <div class="form-text">Alamat surat elektronik resmi</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold fs-6 mb-2">Nomor Telepon</label>
                            <input type="text" name="phone" value="{{ old('phone', $organization->phone) }}" 
                                   class="form-control form-control-solid" 
                                   placeholder="021-xxxxxxx">
                            <div class="form-text">Kontak aktif organisasi</div>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-semibold fs-6 mb-2">Alamat Korespondensi</label>
                            <textarea name="address" rows="3" class="form-control form-control-solid" 
                                      placeholder="Jl. Raya Utama No. 123...">{{ old('address', $organization->address) }}</textarea>
                            <div class="form-text">Detail lokasi penagihan/pengiriman</div>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-semibold fs-6 mb-2">Izin Operasional (SIA/SIP/SIPA)</label>
                            <input type="text" name="license_number" value="{{ old('license_number', $organization->license_number) }}" 
                                   class="form-control form-control-solid" 
                                   placeholder="Nomor izin resmi...">
                            <div class="form-text">Wajib untuk faskes narkotika/psikotropika</div>
                        </div>
                    </div>

                    <div class="separator my-7"></div>

                    <div class="d-flex justify-content-end gap-3">
                        <a href="{{ route('web.organizations.index') }}" class="btn btn-light-secondary">
                            <i class="ki-solid ki-cross fs-3"></i>
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ki-solid ki-check fs-3"></i>
                            Perbarui Data
                        </button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-layout>
