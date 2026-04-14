<?php if (isset($component)) { $__componentOriginal23a33f287873b564aaf305a1526eada4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal23a33f287873b564aaf305a1526eada4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout','data' => ['title' => 'Tambah Organisasi','pageTitle' => 'Tambah Organisasi','breadcrumb' => 'Form tambah data baru']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Tambah Organisasi','pageTitle' => 'Tambah Organisasi','breadcrumb' => 'Form tambah data baru']); ?>
    
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['title' => 'Registrasi Organisasi Baru']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Registrasi Organisasi Baru']); ?>
                <form method="POST" action="<?php echo e(route('web.organizations.store')); ?>">
                    <?php echo csrf_field(); ?>

                    <div class="row g-5">
                        <div class="col-12">
                            <label class="form-label required fw-semibold fs-6 mb-2">Tipe Organisasi</label>
                            <select name="type" required class="form-select form-select-solid">
                                <option value="clinic" <?php echo e(old('type') === 'clinic' ? 'selected' : ''); ?>>Klinik</option>
                                <option value="hospital" <?php echo e(old('type') === 'hospital' ? 'selected' : ''); ?>>Rumah Sakit</option>
                            </select>
                            <div class="form-text">Klasifikasi entitas untuk manajemen inventory & regulasi</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required fw-semibold fs-6 mb-2">Nama Organisasi</label>
                            <input type="text" name="name" value="<?php echo e(old('name')); ?>" required 
                                   class="form-control form-control-solid" 
                                   placeholder="Contoh: Medikindo Hospital">
                            <div class="form-text">Nama resmi faskes</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold fs-6 mb-2">Kode Internal</label>
                            <input type="text" name="code" value="<?php echo e(old('code')); ?>" required maxlength="20"
                                   class="form-control form-control-solid" 
                                   placeholder="ORG-01">
                            <div class="form-text">Kode identifikasi sistem (Unique)</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-semibold fs-6 mb-2">Email Operasional</label>
                            <input type="email" name="email" value="<?php echo e(old('email')); ?>" 
                                   class="form-control form-control-solid" 
                                   placeholder="email@example.com">
                            <div class="form-text">Alamat surat elektronik resmi</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold fs-6 mb-2">Nomor Telepon</label>
                            <input type="text" name="phone" value="<?php echo e(old('phone')); ?>" 
                                   class="form-control form-control-solid" 
                                   placeholder="021-xxxxxxx">
                            <div class="form-text">Kontak aktif organisasi</div>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-semibold fs-6 mb-2">Alamat Korespondensi</label>
                            <textarea name="address" rows="3" class="form-control form-control-solid" 
                                      placeholder="Jl. Raya Utama No. 123..."><?php echo e(old('address')); ?></textarea>
                            <div class="form-text">Detail lokasi penagihan/pengiriman</div>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-semibold fs-6 mb-2">Izin Operasional (SIA/SIP/SIPA)</label>
                            <input type="text" name="license_number" value="<?php echo e(old('license_number')); ?>" 
                                   class="form-control form-control-solid" 
                                   placeholder="Nomor izin resmi...">
                            <div class="form-text">Wajib untuk faskes narkotika/psikotropika</div>
                        </div>
                    </div>

                    <div class="separator my-7"></div>

                    <div class="d-flex justify-content-end gap-3">
                        <a href="<?php echo e(route('web.organizations.index')); ?>" class="btn btn-light-secondary">
                            <i class="ki-solid ki-cross fs-3"></i>
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ki-solid ki-check fs-3"></i>
                            Simpan Data Organisasi
                        </button>
                    </div>
                </form>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $attributes = $__attributesOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__attributesOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $component = $__componentOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__componentOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal23a33f287873b564aaf305a1526eada4)): ?>
<?php $attributes = $__attributesOriginal23a33f287873b564aaf305a1526eada4; ?>
<?php unset($__attributesOriginal23a33f287873b564aaf305a1526eada4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal23a33f287873b564aaf305a1526eada4)): ?>
<?php $component = $__componentOriginal23a33f287873b564aaf305a1526eada4; ?>
<?php unset($__componentOriginal23a33f287873b564aaf305a1526eada4); ?>
<?php endif; ?>
<?php /**PATH C:\laragon\www\medikindo-po\resources\views/organizations/create.blade.php ENDPATH**/ ?>