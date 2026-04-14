<!DOCTYPE html>
<html lang="en">
<head>
    <base href=""/>
    <title><?php echo e($title ?? 'Medikindo PO System'); ?> | Medikindo</title>
    <meta charset="utf-8" />
    <meta name="description" content="Medikindo Procurement & Financial System" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="shortcut icon" href="<?php echo e(asset('assets/metronic8/media/logos/favicon.ico')); ?>" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="<?php echo e(asset('assets/metronic8/plugins/global/plugins.bundle.css')); ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo e(asset('assets/metronic8/css/style.bundle.css')); ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo e(asset('css/custom-layout.css')); ?>" rel="stylesheet" type="text/css" />
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body id="kt_app_body" data-kt-app-header-fixed="true" data-kt-app-header-fixed-mobile="true" data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-push-header="true" data-kt-app-sidebar-push-toolbar="true" data-kt-app-sidebar-push-footer="true" data-kt-app-toolbar-enabled="true" class="app-default">
    <script>
        var defaultThemeMode = "light"; 
        var themeMode; 
        if (document.documentElement) { 
            if (document.documentElement.hasAttribute("data-bs-theme-mode")) { 
                themeMode = document.documentElement.getAttribute("data-bs-theme-mode"); 
            } else { 
                if (localStorage.getItem("data-bs-theme") !== null) { 
                    themeMode = localStorage.getItem("data-bs-theme"); 
                } else { 
                    themeMode = defaultThemeMode; 
                } 
            } 
            if (themeMode === "system") { 
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light"; 
            } 
            document.documentElement.setAttribute("data-bs-theme", themeMode); 
        }
    </script>
    <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
            <?php echo $__env->make('components.partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
                <?php echo $__env->make('components.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                    <div class="d-flex flex-column flex-column-fluid">
                        <div id="kt_app_content" class="app-content flex-column-fluid">
                            <div id="kt_app_content_container" class="app-container container-fluid">
                                <?php if(session('success')): ?>
                                    <div class="alert alert-success d-flex align-items-center mb-5">
                                        <i class="ki-outline ki-check-circle fs-2 text-success me-3"></i>
                                        <span><?php echo e(session('success')); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if(session('error')): ?>
                                    <div class="alert alert-danger d-flex align-items-center mb-5">
                                        <i class="ki-outline ki-cross-circle fs-2 text-danger me-3"></i>
                                        <span><?php echo e(session('error')); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if($errors->any()): ?>
                                    <div class="alert alert-danger d-flex align-items-start mb-5">
                                        <i class="ki-outline ki-information-5 fs-2 text-danger me-3 mt-1"></i>
                                        <div>
                                            <div class="fw-bold mb-1">Terdapat kesalahan validasi:</div>
                                            <ul class="mb-0 ps-4">
                                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <li><?php echo e($error); ?></li>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </ul>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                
                                <?php echo $__env->yieldContent('content'); ?>
                            </div>
                        </div>
                    </div>
                    <div id="kt_app_footer" class="app-footer">
                        <div class="app-container container-fluid d-flex flex-column flex-md-row flex-center flex-md-stack py-3">
                            <div class="text-dark order-2 order-md-1">
                                <span class="text-muted fw-semibold me-1"><?php echo e(date('Y')); ?>&copy;</span>
                                <span class="text-gray-800 fw-semibold">Medikindo Procurement System</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="<?php echo e(asset('assets/metronic8/plugins/global/plugins.bundle.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/metronic8/js/scripts.bundle.js')); ?>"></script>
    <script>
        // Initialize Metronic components when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize KTApp if available
            if (typeof KTApp !== 'undefined' && typeof KTApp.init === 'function') {
                KTApp.init();
            }
            
            // Initialize menu components
            if (typeof KTMenu !== 'undefined') {
                KTMenu.createInstances();
            }
            
            // Initialize drawer components
            if (typeof KTDrawer !== 'undefined') {
                KTDrawer.createInstances();
            }
            
            // Initialize scroll components
            if (typeof KTScroll !== 'undefined') {
                KTScroll.createInstances();
            }
        });
    </script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\laragon\www\medikindo-po\resources\views/layouts/app.blade.php ENDPATH**/ ?>