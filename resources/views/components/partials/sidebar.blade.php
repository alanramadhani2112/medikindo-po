<!--begin::Sidebar-->
<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar"
    data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="225px"
    data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
    <!--begin::Logo-->
    <div class="app-sidebar-logo flex-shrink-0 d-none d-md-flex align-items-center px-8" id="kt_app_sidebar_logo">
        <a href="{{ route('web.dashboard') }}" class="d-flex align-items-center">
            <img alt="Medikindo Logo" src="{{ asset('logo-medikindo.png') }}" class="app-sidebar-logo-default" />
        </a>
    </div>
    <!--end::Logo-->

    <!--begin::sidebar menu-->
    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
        <!--begin::Menu wrapper-->
        <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper hover-scroll-overlay-y my-5"
            data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-height="auto"
            data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
            data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px">
            <!--begin::Menu-->
            <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold px-3" id="kt_app_sidebar_menu"
                data-kt-menu="true" data-kt-menu-expand="false">

                {{-- Dashboard --}}
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('web.dashboard') ? 'active' : '' }}"
                        href="{{ route('web.dashboard') }}">
                        <span class="menu-icon">
                            <i class="ki-outline ki-abstract-26 fs-2"></i>
                        </span>
                        <span class="menu-title">Dashboard</span>
                    </a>
                </div>

                {{-- Analytics Section --}}
                @can('view_reports')
                    <div class="menu-item pt-5">
                        <div class="menu-content">
                            <span class="menu-heading fw-bold text-uppercase fs-7">Analytics</span>
                        </div>
                    </div>
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('web.analytics.*') ? 'active' : '' }}"
                            href="{{ route('web.analytics.products') }}">
                            <span class="menu-icon">
                                <i class="ki-outline ki-chart-line-star fs-2"></i>
                            </span>
                            <span class="menu-title">Product Analytics</span>
                        </a>
                    </div>
                @endcan

                {{-- PROCUREMENT SECTION --}}
                @canany(['view_purchase_orders', 'view_approvals', 'view_goods_receipt'])
                    <div class="menu-item pt-5">
                        <div class="menu-content">
                            <span class="menu-heading fw-bold text-uppercase fs-7">Procurement</span>
                        </div>
                    </div>

                    @can('view_purchase_orders')
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('web.po.*') ? 'active' : '' }}"
                                href="{{ route('web.po.index') }}">
                                <span class="menu-icon">
                                    <i class="ki-outline ki-purchase fs-2"></i>
                                </span>
                                <span class="menu-title">Purchase Orders</span>
                            </a>
                        </div>
                    @endcan

                    @can('view_approvals')
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('web.approvals.*') ? 'active' : '' }}"
                                href="{{ route('web.approvals.index') }}"
                                @if(isset($pendingApprovalCount) && $pendingApprovalCount > 0)
                                    data-bs-toggle="tooltip" 
                                    data-bs-placement="right" 
                                    title="{{ $pendingApprovalCount }} PO menunggu approval"
                                @endif>
                                <span class="menu-icon">
                                    <i class="ki-outline ki-briefcase fs-2"></i>
                                </span>
                                <span class="menu-title">Approvals</span>
                                @if (isset($pendingApprovalCount) && $pendingApprovalCount > 0)
                                    <span class="badge badge-sm badge-circle badge-danger ms-auto">{{ $pendingApprovalCount }}</span>
                                @endif
                            </a>
                        </div>
                    @endcan

                    @can('view_goods_receipt')
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('web.goods-receipts.*') ? 'active' : '' }}"
                                href="{{ route('web.goods-receipts.index') }}"
                                @if(isset($partialGRCount) && $partialGRCount > 0)
                                    data-bs-toggle="tooltip" 
                                    data-bs-placement="right" 
                                    title="{{ $partialGRCount }} PO menunggu pengiriman sisa"
                                @endif>
                                <span class="menu-icon">
                                    <i class="ki-outline ki-courier-express fs-2"></i>
                                </span>
                                <span class="menu-title">Goods Receipt</span>
                                @if(isset($partialGRCount) && $partialGRCount > 0)
                                    <span class="badge badge-sm badge-circle badge-warning ms-auto">{{ $partialGRCount }}</span>
                                @endif
                            </a>
                        </div>
                    @endcan
                @endcanany

                {{-- HUTANG (AP - ACCOUNTS PAYABLE) SECTION --}}
                @can('create_invoices')
                    <div class="menu-item pt-5">
                        <div class="menu-content">
                            <span class="menu-heading fw-bold text-uppercase fs-7">Hutang (AP)</span>
                        </div>
                    </div>

                    {{-- Supplier Invoice --}}
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('web.invoices.supplier.*') ? 'active' : '' }}"
                            href="{{ route('web.invoices.supplier.index') }}"
                            @if(isset($grReadyToInvoiceCount) && $grReadyToInvoiceCount > 0)
                                data-bs-toggle="tooltip" 
                                data-bs-placement="right" 
                                title="{{ $grReadyToInvoiceCount }} GR siap dibuatkan invoice"
                            @endif>
                            <span class="menu-icon">
                                <i class="ki-outline ki-bill fs-2"></i>
                            </span>
                            <span class="menu-title">Supplier Invoices</span>
                            @if(isset($grReadyToInvoiceCount) && $grReadyToInvoiceCount > 0)
                                <span class="badge badge-sm badge-circle badge-danger ms-auto">{{ $grReadyToInvoiceCount }}</span>
                            @endif
                        </a>
                    </div>

                    {{-- Payment Out - Coming Soon --}}
                    @can('process_payments')
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('web.payments.outgoing.soon') ? 'active' : '' }}" 
                                href="{{ route('web.payments.outgoing.soon') }}"
                                @if(isset($supplierInvoicesDueCount) && $supplierInvoicesDueCount > 0)
                                    data-bs-toggle="tooltip" 
                                    data-bs-placement="right" 
                                    title="{{ $supplierInvoicesDueCount }} invoice supplier jatuh tempo perlu dibayar"
                                @endif>
                                <span class="menu-icon">
                                    <i class="ki-outline ki-exit-right fs-2"></i>
                                </span>
                                <span class="menu-title">Payment Out</span>
                                @if(isset($supplierInvoicesDueCount) && $supplierInvoicesDueCount > 0)
                                    <span class="badge badge-sm badge-circle badge-danger ms-auto">{{ $supplierInvoicesDueCount }}</span>
                                @else
                                    <span class="menu-badge">
                                        <span class="badge badge-light-primary fw-bold fs-9 px-2 py-1">Soon</span>
                                    </span>
                                @endif
                            </a>
                        </div>
                    @endcan
                @endcan

                {{-- PIUTANG (AR - ACCOUNTS RECEIVABLE) SECTION --}}
                @can('view_invoices')
                    <div class="menu-item pt-5">
                        <div class="menu-content">
                            <span class="menu-heading fw-bold text-uppercase fs-7">Piutang (AR)</span>
                        </div>
                    </div>

                    {{-- Customer Invoice --}}
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('web.invoices.customer.*') ? 'active' : '' }}"
                            href="{{ route('web.invoices.customer.index') }}"
                            @if(isset($customerInvoicesOverdueCount) && $customerInvoicesOverdueCount > 0)
                                data-bs-toggle="tooltip" 
                                data-bs-placement="right" 
                                title="{{ $customerInvoicesOverdueCount }} invoice customer sudah jatuh tempo"
                            @endif>
                            <span class="menu-icon">
                                <i class="ki-outline ki-document fs-2"></i>
                            </span>
                            <span class="menu-title">Customer Invoices</span>
                            @if(isset($customerInvoicesOverdueCount) && $customerInvoicesOverdueCount > 0)
                                <span class="badge badge-sm badge-circle badge-danger ms-auto">{{ $customerInvoicesOverdueCount }}</span>
                            @endif
                        </a>
                    </div>

                    {{-- Payment Proofs --}}
                    @can('view_payment_status')
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('web.payment-proofs.*') ? 'active' : '' }}"
                                href="{{ route('web.payment-proofs.index') }}"
                                @if(isset($paymentProofsPendingCount) && $paymentProofsPendingCount > 0)
                                    data-bs-toggle="tooltip" 
                                    data-bs-placement="right" 
                                    title="{{ $paymentProofsPendingCount }} bukti pembayaran perlu diverifikasi/approve"
                                @endif>
                                <span class="menu-icon">
                                    <i class="ki-outline ki-shield-tick fs-2"></i>
                                </span>
                                <span class="menu-title">Payment Proofs</span>
                                @if(isset($paymentProofsPendingCount) && $paymentProofsPendingCount > 0)
                                    <span class="badge badge-sm badge-circle badge-warning ms-auto">{{ $paymentProofsPendingCount }}</span>
                                @endif
                            </a>
                        </div>
                    @endcan

                    {{-- Manual Payment Entry --}}
                    @can('process_payments')
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('web.payments.create.incoming') || request()->routeIs('web.payments.store.incoming') ? 'active' : '' }}"
                                href="{{ route('web.payments.create.incoming') }}">
                                <span class="menu-icon">
                                    <i class="ki-outline ki-entrance-right fs-2"></i>
                                </span>
                                <span class="menu-title">Manual Payment Entry</span>
                                <span class="menu-badge">
                                    <span class="badge badge-light-warning badge-circle fw-bold fs-9">!</span>
                                </span>
                            </a>
                        </div>
                    @endcan

                    {{-- AR Aging --}}
                    @can('view_reports')
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('web.ar-aging.*') ? 'active' : '' }}"
                                href="{{ route('web.ar-aging.index') }}"
                                @if(isset($arAgingCriticalCount) && $arAgingCriticalCount > 0)
                                    data-bs-toggle="tooltip" 
                                    data-bs-placement="right" 
                                    title="{{ $arAgingCriticalCount }} invoice overdue >30 hari perlu tindakan"
                                @endif>
                                <span class="menu-icon">
                                    <i class="ki-outline ki-calendar-tick fs-2"></i>
                                </span>
                                <span class="menu-title">AR Aging</span>
                                @if(isset($arAgingCriticalCount) && $arAgingCriticalCount > 0)
                                    <span class="badge badge-sm badge-circle badge-danger ms-auto">{{ $arAgingCriticalCount }}</span>
                                @endif
                            </a>
                        </div>
                    @endcan

                    {{-- Credit Control --}}
                    @can('view_credit_control')
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('web.financial-controls.*') ? 'active' : '' }}"
                                href="{{ route('web.financial-controls.index') }}"
                                @if(isset($creditLimitExceededCount) && $creditLimitExceededCount > 0)
                                    data-bs-toggle="tooltip" 
                                    data-bs-placement="right" 
                                    title="{{ $creditLimitExceededCount }} customer melebihi credit limit"
                                @endif>
                                <span class="menu-icon">
                                    <i class="ki-outline ki-shield-search fs-2"></i>
                                </span>
                                <span class="menu-title">Credit Control</span>
                                @if(isset($creditLimitExceededCount) && $creditLimitExceededCount > 0)
                                    <span class="badge badge-sm badge-circle badge-danger ms-auto">{{ $creditLimitExceededCount }}</span>
                                @endif
                            </a>
                        </div>
                    @endcan
                @endcan

                {{-- KAS & BANK SECTION --}}
                @canany(['view_payments', 'manage_bank_accounts'])
                    <div class="menu-item pt-5">
                        <div class="menu-content">
                            <span class="menu-heading fw-bold text-uppercase fs-7">Kas & Bank</span>
                        </div>
                    </div>

                    {{-- Payment Ledger (Buku Kas) --}}
                    @can('view_payments')
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('web.payments.index') ? 'active' : '' }}"
                                href="{{ route('web.payments.index') }}">
                                <span class="menu-icon">
                                    <i class="ki-outline ki-book fs-2"></i>
                                </span>
                                <span class="menu-title">Payment Ledger</span>
                            </a>
                        </div>
                    @endcan

                    {{-- Bank Accounts --}}
                    @can('manage_bank_accounts')
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('web.bank-accounts.*') ? 'active' : '' }}"
                                href="{{ route('web.bank-accounts.index') }}">
                                <span class="menu-icon">
                                    <i class="ki-outline ki-bank fs-2"></i>
                                </span>
                                <span class="menu-title">Bank Accounts</span>
                            </a>
                        </div>
                    @endcan

                    {{-- Cash Flow - Coming Soon --}}
                    @can('view_payments')
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('web.cash-flow.*') ? 'active' : '' }}" href="{{ route('web.cash-flow.index') }}">
                                <span class="menu-icon">
                                    <i class="ki-outline ki-chart-line-up fs-2"></i>
                                </span>
                                <span class="menu-title">Cash Flow</span>
                                <span class="menu-badge">
                                    <span class="badge badge-light-primary fw-bold fs-9 px-2 py-1">Soon</span>
                                </span>
                            </a>
                        </div>
                    @endcan
                @endcanany

                {{-- INVENTORY SECTION --}}
                @can('view_inventory')
                    <div class="menu-item pt-5">
                        <div class="menu-content">
                            <span class="menu-heading fw-bold text-uppercase fs-7">Inventory</span>
                        </div>
                    </div>
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('web.inventory.*') ? 'active' : '' }}"
                            href="{{ route('web.inventory.index') }}">
                            <span class="menu-icon">
                                <i class="ki-outline ki-package fs-2"></i>
                            </span>
                            <span class="menu-title">Inventory</span>
                            <span class="menu-badge">
                                <span class="badge badge-light-warning fw-bold fs-9 px-2 py-1">Soon</span>
                            </span>
                        </a>
                    </div>
                @endcan

                {{-- MASTER DATA SECTION --}}
                @canany(['manage_organizations', 'manage_suppliers', 'manage_products', 'manage_users'])
                    <div class="menu-item pt-5">
                        <div class="menu-content">
                            <span class="menu-heading fw-bold text-uppercase fs-7">Master Data</span>
                        </div>
                    </div>

                    @can('manage_organizations')
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('web.organizations.*') ? 'active' : '' }}"
                                href="{{ route('web.organizations.index') }}">
                                <span class="menu-icon">
                                    <i class="ki-outline ki-bank fs-2"></i>
                                </span>
                                <span class="menu-title">Organizations</span>
                            </a>
                        </div>
                    @endcan

                    @can('manage_suppliers')
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('web.suppliers.*') ? 'active' : '' }}"
                                href="{{ route('web.suppliers.index') }}">
                                <span class="menu-icon">
                                    <i class="ki-outline ki-cube-2 fs-2"></i>
                                </span>
                                <span class="menu-title">Suppliers</span>
                            </a>
                        </div>
                    @endcan

                    @can('manage_products')
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('web.products.*') ? 'active' : '' }}"
                                href="{{ route('web.products.index') }}">
                                <span class="menu-icon">
                                    <i class="ki-outline ki-pill fs-2"></i>
                                </span>
                                <span class="menu-title">Products</span>
                            </a>
                        </div>
                    @endcan

                    @can('manage_users')
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('web.users.*') ? 'active' : '' }}"
                                href="{{ route('web.users.index') }}">
                                <span class="menu-icon">
                                    <i class="ki-outline ki-profile-user fs-2"></i>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize Bootstrap tooltips for sidebar badges
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl, {
        trigger: 'hover',
        placement: 'right',
        container: 'body'
    }));
});
</script>
@endpush
