<!--begin::Sidebar-->
<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="225px" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
    <!--begin::Logo-->
    <div class="app-sidebar-logo" id="kt_app_sidebar_logo">
        <a href="<?php echo e(route('web.dashboard')); ?>" class="d-flex align-items-center text-decoration-none">
            <div class="symbol symbol-40px bg-primary rounded">
                <i class="ki-outline ki-hospital text-white fs-2"></i>
            </div>
            <span class="text-gray-900 fw-bold fs-5 ms-3">Medikindo</span>
        </a>
    </div>
    <!--end::Logo-->
    
    <!--begin::sidebar menu-->
    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
        <!--begin::Menu wrapper-->
        <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper hover-scroll-overlay-y" data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_app_sidebar_logo" data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px">
            <!--begin::Menu-->
            <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold" id="kt_app_sidebar_menu" data-kt-menu="true" data-kt-menu-expand="false">

                
                <div class="menu-item">
                    <a class="menu-link <?php echo e(request()->routeIs('web.dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('web.dashboard')); ?>">
                        <span class="menu-icon">
                            <i class="ki-outline ki-element-11 fs-2"></i>
                        </span>
                        <span class="menu-title">Dashboard</span>
                    </a>
                </div>

                
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['view_purchase_orders', 'view_approvals', 'view_goods_receipt'])): ?>
                <div class="menu-item pt-5">
                    <div class="menu-content">
                        <span class="menu-heading fw-bold text-uppercase fs-7">Procurement</span>
                    </div>
                </div>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_purchase_orders')): ?>
                <div class="menu-item">
                    <a class="menu-link <?php echo e(request()->routeIs('web.po.*') ? 'active' : ''); ?>" href="<?php echo e(route('web.po.index')); ?>">
                        <span class="menu-icon">
                            <i class="ki-outline ki-purchase fs-2"></i>
                        </span>
                        <span class="menu-title">Purchase Orders</span>
                    </a>
                </div>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_approvals')): ?>
                <div class="menu-item">
                    <a class="menu-link <?php echo e(request()->routeIs('web.approvals.*') ? 'active' : ''); ?>" href="<?php echo e(route('web.approvals.index')); ?>">
                        <span class="menu-icon">
                            <i class="ki-outline ki-check-square fs-2"></i>
                        </span>
                        <span class="menu-title">Approvals</span>
                        <?php if(isset($pendingApprovalCount) && $pendingApprovalCount > 0): ?>
                            <span class="badge badge-sm badge-circle badge-danger ms-auto"><?php echo e($pendingApprovalCount); ?></span>
                        <?php endif; ?>
                    </a>
                </div>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_goods_receipt')): ?>
                <div class="menu-item">
                    <a class="menu-link <?php echo e(request()->routeIs('web.goods-receipts.*') ? 'active' : ''); ?>" href="<?php echo e(route('web.goods-receipts.index')); ?>">
                        <span class="menu-icon">
                            <i class="ki-outline ki-package fs-2"></i>
                        </span>
                        <span class="menu-title">Goods Receipt</span>
                    </a>
                </div>
                <?php endif; ?>
                <?php endif; ?>

                
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['view_invoices', 'view_payments', 'view_credit_control'])): ?>
                <div class="menu-item pt-5">
                    <div class="menu-content">
                        <span class="menu-heading fw-bold text-uppercase fs-7">Finance</span>
                    </div>
                </div>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_invoices')): ?>
                <div class="menu-item">
                    <a class="menu-link <?php echo e(request()->routeIs('web.invoices.*') && request('tab') === 'supplier' ? 'active' : ''); ?>" href="<?php echo e(route('web.invoices.index', ['tab' => 'supplier'])); ?>">
                        <span class="menu-icon">
                            <i class="ki-outline ki-arrow-down fs-2 text-danger"></i>
                        </span>
                        <span class="menu-title">Hutang Pemasok</span>
                    </a>
                </div>
                
                <div class="menu-item">
                    <a class="menu-link <?php echo e(request()->routeIs('web.invoices.*') && request('tab') === 'customer' ? 'active' : ''); ?>" href="<?php echo e(route('web.invoices.index', ['tab' => 'customer'])); ?>">
                        <span class="menu-icon">
                            <i class="ki-outline ki-arrow-up fs-2 text-success"></i>
                        </span>
                        <span class="menu-title">Tagihan ke RS/Klinik</span>
                    </a>
                </div>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_payments')): ?>
                <div class="menu-item">
                    <a class="menu-link <?php echo e(request()->routeIs('web.payments.*') ? 'active' : ''); ?>" href="<?php echo e(route('web.payments.index')); ?>">
                        <span class="menu-icon">
                            <i class="ki-outline ki-wallet fs-2"></i>
                        </span>
                        <span class="menu-title">Payments</span>
                    </a>
                </div>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_credit_control')): ?>
                <div class="menu-item">
                    <a class="menu-link <?php echo e(request()->routeIs('web.financial-controls.*') ? 'active' : ''); ?>" href="<?php echo e(route('web.financial-controls.index')); ?>">
                        <span class="menu-icon">
                            <i class="ki-outline ki-chart-simple fs-2"></i>
                        </span>
                        <span class="menu-title">Credit Control</span>
                    </a>
                </div>
                <?php endif; ?>
                <?php endif; ?>

                
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['manage_organizations', 'manage_suppliers', 'manage_products', 'manage_users'])): ?>
                <div class="menu-item pt-5">
                    <div class="menu-content">
                        <span class="menu-heading fw-bold text-uppercase fs-7">Master Data</span>
                    </div>
                </div>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_organizations')): ?>
                <div class="menu-item">
                    <a class="menu-link <?php echo e(request()->routeIs('web.organizations.*') ? 'active' : ''); ?>" href="<?php echo e(route('web.organizations.index')); ?>">
                        <span class="menu-icon">
                            <i class="ki-outline ki-bank fs-2"></i>
                        </span>
                        <span class="menu-title">Organizations</span>
                    </a>
                </div>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_suppliers')): ?>
                <div class="menu-item">
                    <a class="menu-link <?php echo e(request()->routeIs('web.suppliers.*') ? 'active' : ''); ?>" href="<?php echo e(route('web.suppliers.index')); ?>">
                        <span class="menu-icon">
                            <i class="ki-outline ki-delivery-3 fs-2"></i>
                        </span>
                        <span class="menu-title">Suppliers</span>
                    </a>
                </div>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_products')): ?>
                <div class="menu-item">
                    <a class="menu-link <?php echo e(request()->routeIs('web.products.*') ? 'active' : ''); ?>" href="<?php echo e(route('web.products.index')); ?>">
                        <span class="menu-icon">
                            <i class="ki-outline ki-capsule fs-2"></i>
                        </span>
                        <span class="menu-title">Products</span>
                    </a>
                </div>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_users')): ?>
                <div class="menu-item">
                    <a class="menu-link <?php echo e(request()->routeIs('web.users.*') ? 'active' : ''); ?>" href="<?php echo e(route('web.users.index')); ?>">
                        <span class="menu-icon">
                            <i class="ki-outline ki-profile-user fs-2"></i>
                        </span>
                        <span class="menu-title">Users</span>
                    </a>
                </div>
                <?php endif; ?>
                <?php endif; ?>

            </div>
            <!--end::Menu-->
        </div>
        <!--end::Menu wrapper-->
    </div>
    <!--end::sidebar menu-->
</div>
<!--end::Sidebar-->
<?php /**PATH C:\laragon\www\medikindo-po\resources\views/components/partials/sidebar.blade.php ENDPATH**/ ?>