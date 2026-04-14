<!--begin::Sidebar-->
<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="225px" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
    <!--begin::Logo-->
    <div class="app-sidebar-logo flex-shrink-0 d-none d-md-flex align-items-center px-8" id="kt_app_sidebar_logo">
        <a href="{{ route('web.dashboard') }}">
            <img alt="Logo" src="{{ asset('assets/metronic8/media/logos/default-dark.svg') }}" class="h-25px app-sidebar-logo-default" />
        </a>
    </div>
    <!--end::Logo-->
    
    <!--begin::sidebar menu-->
    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
        <!--begin::Menu wrapper-->
        <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper hover-scroll-overlay-y my-5" data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer" data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px">
            <!--begin::Menu-->
            <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold px-3" id="kt_app_sidebar_menu" data-kt-menu="true" data-kt-menu-expand="false">

                {{-- Dashboard --}}
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('web.dashboard') ? 'active' : '' }}" href="{{ route('web.dashboard') }}">
                        <span class="menu-icon">
                            <i class="ki-solid ki-home-2 fs-2"></i>
                        </span>
                        <span class="menu-title">Dashboard</span>
                    </a>
                </div>

                {{-- PROCUREMENT SECTION --}}
                @canany(['view_purchase_orders', 'view_approvals', 'view_goods_receipt'])
                <div class="menu-item pt-5">
                    <div class="menu-content">
                        <span class="menu-heading fw-bold text-uppercase fs-7">Procurement</span>
                    </div>
                </div>

                @can('view_purchase_orders')
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('web.po.*') ? 'active' : '' }}" href="{{ route('web.po.index') }}">
                        <span class="menu-icon">
                            <i class="ki-solid ki-wallet fs-2"></i>
                        </span>
                        <span class="menu-title">Purchase Orders</span>
                    </a>
                </div>
                @endcan

                @can('view_approvals')
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('web.approvals.*') ? 'active' : '' }}" href="{{ route('web.approvals.index') }}">
                        <span class="menu-icon">
                            <i class="ki-duotone ki-check-square fs-2"></i>
                        </span>
                        <span class="menu-title">Approvals</span>
                        @if(isset($pendingApprovalCount) && $pendingApprovalCount > 0)
                            <span class="badge badge-sm badge-circle badge-danger ms-auto">{{ $pendingApprovalCount }}</span>
                        @endif
                    </a>
                </div>
                @endcan

                @can('view_goods_receipt')
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('web.goods-receipts.*') ? 'active' : '' }}" href="{{ route('web.goods-receipts.index') }}">
                        <span class="menu-icon">
                            <i class="ki-duotone ki-package fs-2"></i>
                        </span>
                        <span class="menu-title">Goods Receipt</span>
                    </a>
                </div>
                @endcan
                @endcanany

                {{-- INVOICING SECTION --}}
                @can('view_invoices')
                <div class="menu-item pt-5">
                    <div class="menu-content">
                        <span class="menu-heading fw-bold text-uppercase fs-7">Invoicing</span>
                    </div>
                </div>

                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('web.invoices.customer.*') ? 'active' : '' }}" href="{{ route('web.invoices.customer.index') }}">
                        <span class="menu-icon">
                            <i class="ki-duotone ki-arrow-up fs-2 text-success"></i>
                        </span>
                        <span class="menu-title">Tagihan ke RS/Klinik</span>
                        <span class="menu-badge">
                            <span class="badge badge-light-success badge-circle fw-bold fs-8">AR</span>
                        </span>
                    </a>
                </div>
                @endcan
                
                @canany(['manage_invoices', 'process_payments', 'view_credit_control'])
                @if(!isset($invoicingSectionShown))
                <div class="menu-item pt-5">
                    <div class="menu-content">
                        <span class="menu-heading fw-bold text-uppercase fs-7">Invoicing</span>
                    </div>
                </div>
                @php $invoicingSectionShown = true; @endphp
                @endif
                
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('web.invoices.supplier.*') ? 'active' : '' }}" href="{{ route('web.invoices.supplier.index') }}">
                        <span class="menu-icon">
                            <i class="ki-duotone ki-arrow-down fs-2 text-danger"></i>
                        </span>
                        <span class="menu-title">Hutang ke Supplier</span>
                        <span class="menu-badge">
                            <span class="badge badge-light-danger badge-circle fw-bold fs-8">AP</span>
                        </span>
                    </a>
                </div>
                @endcanany

                {{-- PAYMENT SECTION --}}
                @canany(['view_payments', 'view_credit_control'])
                <div class="menu-item pt-5">
                    <div class="menu-content">
                        <span class="menu-heading fw-bold text-uppercase fs-7">Payment</span>
                    </div>
                </div>

                @can('view_payments')
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('web.payments.*') ? 'active' : '' }}" href="{{ route('web.payments.index') }}">
                        <span class="menu-icon">
                            <i class="ki-duotone ki-wallet fs-2"></i>
                        </span>
                        <span class="menu-title">Payments</span>
                    </a>
                </div>
                @endcan

                @can('view_credit_control')
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('web.financial-controls.*') ? 'active' : '' }}" href="{{ route('web.financial-controls.index') }}">
                        <span class="menu-icon">
                            <i class="ki-duotone ki-chart-simple fs-2"></i>
                        </span>
                        <span class="menu-title">Credit Control</span>
                    </a>
                </div>
                @endcan
                @endcanany

                {{-- MASTER DATA SECTION --}}
                @canany(['manage_organizations', 'manage_suppliers', 'manage_products', 'manage_users'])
                <div class="menu-item pt-5">
                    <div class="menu-content">
                        <span class="menu-heading fw-bold text-uppercase fs-7">Master Data</span>
                    </div>
                </div>

                @can('manage_organizations')
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('web.organizations.*') ? 'active' : '' }}" href="{{ route('web.organizations.index') }}">
                        <span class="menu-icon">
                            <i class="ki-duotone ki-bank fs-2"></i>
                        </span>
                        <span class="menu-title">Organizations</span>
                    </a>
                </div>
                @endcan

                @can('manage_suppliers')
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('web.suppliers.*') ? 'active' : '' }}" href="{{ route('web.suppliers.index') }}">
                        <span class="menu-icon">
                            <i class="ki-duotone ki-delivery-3 fs-2"></i>
                        </span>
                        <span class="menu-title">Suppliers</span>
                    </a>
                </div>
                @endcan

                @can('manage_products')
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('web.products.*') ? 'active' : '' }}" href="{{ route('web.products.index') }}">
                        <span class="menu-icon">
                            <i class="ki-duotone ki-capsule fs-2"></i>
                        </span>
                        <span class="menu-title">Products</span>
                    </a>
                </div>
                @endcan

                @can('manage_users')
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('web.users.*') ? 'active' : '' }}" href="{{ route('web.users.index') }}">
                        <span class="menu-icon">
                            <i class="ki-duotone ki-profile-user fs-2"></i>
                        </span>
                        <span class="menu-title">Users</span>
                    </a>
                </div>
                @endcan
                @endcanany

            </div>
            <!--end::Menu-->
        </div>
        <!--end::Menu wrapper-->
    </div>
    <!--end::sidebar menu-->
</div>
<!--end::Sidebar-->
