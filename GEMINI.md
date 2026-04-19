# 🏥 MEDIKINDO PO SYSTEM - GEMINI INSTRUCTIONAL CONTEXT

This document provides essential context and instructions for AI agents working on the Medikindo PO System codebase.

## 🎯 Project Overview
- **Project Type**: Enterprise Resource Planning (ERP) / Purchase Order (PO) Management System for pharmaceutical distribution.
- **Framework**: Laravel 13.x (PHP 8.3+)
- **Frontend**: Blade Templates, Alpine.js, Metronic Theme (v8.x), KeenIcons.
- **Database**: MySQL with strict integrity constraints (Batch, Expiry, Organization-based scoping).
- **Architecture**: Service-oriented with clear business logic separation (found in `app/Services`).
- **Authorization**: RBAC powered by `spatie/laravel-permission` with custom "Super Admin" god-mode logic in `AppServiceProvider`.

## 🏗️ Technical Architecture & Conventions

### 1. Business Logic Placement
- **DO NOT** put complex business logic in Controllers or Models.
- **ALWAYS** use Service classes in `app/Services/` (e.g., `PaymentService`, `InvoiceFromGRService`).
- **Audit Logging**: Use `AuditService` to log all critical business actions (PO submission, approval, GR confirmation, invoice creation, payments).

### 2. UI System Standard (CRITICAL)
- **Component-Based**: Use Blade components for ALL UI elements. Refer to `docs/UI_SYSTEM_STANDARD.md`.
- **Base Components**:
    - `<x-layout>`: Master layout.
    - `<x-page-header>`: Mandatory for every page.
    - `<x-card>`: Standard container for content.
    - `<x-filter-bar>`: For index page filtering.
    - `<x-data-table>`: For tabular data.
    - `<x-button>`: Standard buttons with `ki-outline` icons.
    - `<x-badge>`: Status indicators (must follow color mapping).
- **Icons**: Exclusively use KeenIcons (`ki-outline ki-{icon-name}`). **No SVGs or other icon fonts allowed.**
- **Styling**: Metronic Utility classes (Bootstrap 5 based). Avoid raw CSS.

### 3. Core Business Rules (IMMUTABLE)
- **Payment Lifecycle**: Medikindo only pays suppliers (Payment OUT) **AFTER** receiving payment from the customer (Payment IN). Validated in `PaymentService`.
- **Invoice Source**: Invoices MUST be generated from a **Goods Receipt (GR)**, never directly from a PO.
- **Price Security**: 
    - Supplier Invoice prices are read-only from the PO.
    - Customer Invoice prices are read-only from the Product's `selling_price`.
- **Batch & Expiry**: Tracked from Goods Receipt. Once recorded in GR, they are READ-ONLY in subsequent steps (Invoicing).
- **Status Flow**:
    - PO: `draft` → `submitted` → `approved`/`rejected` → `completed`
    - GR: `completed` (Simplified: no pending status)

### 4. Database & Scoping
- **Organization Scoping**: Data must be scoped by `organization_id` unless the user is a "Super Admin".
- **Observers**: Used for immutability enforcement and calculation updates (see `app/Observers`).

## 🚀 Development Workflow

### Key Commands
- **Setup**: `composer run setup` (installs dependencies, migrates, builds assets).
- **Development**: `composer run dev` (runs parallel server, queue, vite, and logs).
- **Testing**: `php artisan test`.
- **Build**: `npm run build`.

### Coding Standards
- **PSR-12**: Strictly followed.
- **Type Hinting**: Mandatory for all method parameters and return types (PHP 8.3 features).
- **Documentation**: Keep `DOCUMENTATION_INDEX.md` and related docs updated when changing business flows.

## 📁 Key File Locations
- **Business Logic**: `app/Services/`
- **Models & Relationships**: `app/Models/`
- **Web Routes**: `routes/web.php`
- **UI Components**: `resources/views/components/`
- **Public Assets**: `public/assets/`
- **Documentation**: `/` (root) and `docs/`

## 🔒 Security & Compliance
- **RBAC**: Permissions are strictly enforced via Middleware and Policies.
- **Fraud Prevention**: Automated cashflow validation in `PaymentService`.
- **Data Integrity**: Enforced via DB constraints and Observer-level immutability checks.

---
**Note**: When in doubt, refer to `docs/UI_SYSTEM_STANDARD.md` for UI and `README_v2.0.md` for current system state.
