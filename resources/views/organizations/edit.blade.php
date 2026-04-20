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

                        <div class="col-md-6">
                            <label class="form-label fw-semibold fs-6 mb-2">Kota</label>
                            <input type="text" name="city" value="{{ old('city', $organization->city) }}" maxlength="100"
                                   class="form-control form-control-solid" 
                                   placeholder="Jakarta">
                            <div class="form-text">Kota lokasi organisasi</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold fs-6 mb-2">Provinsi</label>
                            <input type="text" name="province" value="{{ old('province', $organization->province) }}" maxlength="100"
                                   class="form-control form-control-solid" 
                                   placeholder="DKI Jakarta">
                            <div class="form-text">Provinsi lokasi organisasi</div>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-semibold fs-6 mb-2">Izin Operasional (SIA/SIP/SIPA)</label>
                            <input type="text" name="license_number" value="{{ old('license_number', $organization->license_number) }}" 
                                   class="form-control form-control-solid" 
                                   placeholder="Nomor izin resmi...">
                            <div class="form-text">Wajib untuk faskes narkotika/psikotropika</div>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="is_authorized_narcotic" 
                                       id="is_authorized_narcotic" value="1" 
                                       {{ old('is_authorized_narcotic', $organization->is_authorized_narcotic) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold text-gray-700" for="is_authorized_narcotic">
                                    Memiliki Izin Pengelolaan Narkotika
                                </label>
                            </div>
                            <div class="form-text text-warning mt-2">
                                <i class="ki-outline ki-information-5 fs-5"></i>
                                Centang jika organisasi memiliki izin resmi untuk mengelola obat narkotika/psikotropika
                            </div>
                        </div>
                    </div>

                    <div class="separator my-7"></div>

                    <!-- Fiscal Data Section -->
                    <h3 class="fw-bold mb-5">Data Fiscal & Perpajakan</h3>
                    <div class="row g-5">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold fs-6 mb-2">NPWP</label>
                            <input type="text" name="npwp" value="{{ old('npwp', $organization->npwp) }}" maxlength="20"
                                   class="form-control form-control-solid" 
                                   placeholder="00.000.000.0-000.000">
                            <div class="form-text">Nomor Pokok Wajib Pajak</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold fs-6 mb-2">NIK</label>
                            <input type="text" name="nik" value="{{ old('nik', $organization->nik) }}" maxlength="16"
                                   class="form-control form-control-solid" 
                                   placeholder="16 digit NIK">
                            <div class="form-text">Untuk customer perorangan</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold fs-6 mb-2">Kode Customer</label>
                            <input type="text" name="customer_code" value="{{ old('customer_code', $organization->customer_code) }}" maxlength="50"
                                   class="form-control form-control-solid" 
                                   placeholder="CUST-001">
                            <div class="form-text">Kode customer internal (unique)</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold fs-6 mb-2">Tarif Pajak Default (%)</label>
                            <input type="number" name="default_tax_rate" value="{{ old('default_tax_rate', $organization->default_tax_rate) }}" 
                                   step="0.01" min="0" max="100"
                                   class="form-control form-control-solid" 
                                   placeholder="11.00">
                            <div class="form-text">PPN default (contoh: 11% untuk Indonesia)</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold fs-6 mb-2">Diskon Default (%)</label>
                            <input type="number" name="default_discount_percentage" value="{{ old('default_discount_percentage', $organization->default_discount_percentage) }}" 
                                   step="0.01" min="0" max="100"
                                   class="form-control form-control-solid" 
                                   placeholder="5.00">
                            <div class="form-text">Diskon default untuk organisasi ini</div>
                        </div>
                    </div>

                    <div class="separator my-7"></div>

                    <div class="d-flex justify-content-end gap-3">
                        <a href="{{ route('web.organizations.index') }}" class="btn btn-light-secondary">
                            <i class="ki-outline ki-arrow-zigzag fs-3"></i>
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary update-confirm" data-name="{{ $organization->name }}">
                            <i class="ki-outline ki-check fs-3"></i>
                            Perbarui Data
                        </button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-layout>
