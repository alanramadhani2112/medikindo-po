# Table Engine Specification - Metronic + Laravel

**Version:** 1.0  
**Date:** 2024  
**Status:** Design Phase

---

## 🎯 Overview

The Table Engine is a powerful, reusable component system that combines:
- **Metronic 8** design patterns
- **Laravel** backend features (Eloquent, pagination, sorting)
- **Zero configuration** - works out of the box
- **Full customization** - when you need it

---

## 🏗️ System Architecture

### Component Stack

```
┌─────────────────────────────────────────┐
│         Blade Component Layer           │
│  <x-table-engine> (View Interface)      │
└─────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────┐
│         PHP Engine Layer                │
│  TableEngine Class (Logic & Processing) │
└─────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────┐
│         Laravel Backend Layer           │
│  Eloquent Query Builder + Pagination    │
└─────────────────────────────────────────┘
```

### Data Flow

```
Controller
    ↓
TableEngine::make($query)
    ↓
->columns([...])
    ↓
->filters([...])
    ↓
->actions([...])
    ↓
->render()
    ↓
Blade Component
    ↓
Metronic UI
```

---

## 📋 Features

### Core Features
- ✅ **Auto-pagination** - Laravel pagination built-in
- ✅ **Auto-sorting** - Click column headers to sort
- ✅ **Search** - Global search across columns
- ✅ **Filters** - Dropdown filters with auto-reset
- ✅ **Actions** - Row actions (view, edit, delete)
- ✅ **Bulk actions** - Select multiple rows
- ✅ **Empty states** - Beautiful empty state display
- ✅ **Responsive** - Mobile-friendly tables
- ✅ **Export** - CSV, Excel, PDF export

### Advanced Features
- ✅ **Custom columns** - Render callbacks
- ✅ **Relationships** - Eager loading support
- ✅ **Badges** - Auto-badge rendering
- ✅ **Icons** - Keenicons integration
- ✅ **Permissions** - @can integration
- ✅ **AJAX** - Optional AJAX loading
- ✅ **State persistence** - Remember filters/sorting

---

## 🧩 Component API

### Basic Usage

```blade
<x-table-engine
    :query="$query"
    :columns="[
        ['key' => 'name', 'label' => 'Name', 'sortable' => true],
        ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
        ['key' => 'created_at', 'label' => 'Date', 'type' => 'date'],
    ]"
    :actions="[
        ['label' => 'View', 'route' => 'module.show', 'icon' => 'eye', 'variant' => 'light-primary'],
        ['label' => 'Edit', 'route' => 'module.edit', 'icon' => 'pencil', 'variant' => 'light-primary', 'can' => 'update'],
        ['label' => 'Delete', 'route' => 'module.destroy', 'icon' => 'trash', 'variant' => 'danger', 'method' => 'DELETE', 'confirm' => true],
    ]"
/>
```

### Advanced Usage

```blade
<x-table-engine
    :query="$query"
    :columns="$columns"
    :filters="[
        ['type' => 'search', 'name' => 'search', 'placeholder' => 'Search...'],
        ['type' => 'select', 'name' => 'status', 'label' => 'Status', 'options' => $statuses],
        ['type' => 'date', 'name' => 'date_from', 'label' => 'From Date'],
    ]"
    :actions="$actions"
    :bulk-actions="[
        ['label' => 'Delete Selected', 'route' => 'module.bulk-delete', 'icon' => 'trash', 'confirm' => true],
        ['label' => 'Export Selected', 'route' => 'module.export', 'icon' => 'cloud-download'],
    ]"
    :per-page="[10, 25, 50, 100]"
    :default-sort="['column' => 'created_at', 'direction' => 'desc']"
    :empty-state="[
        'icon' => 'file-deleted',
        'title' => 'No Data Found',
        'message' => 'Try adjusting your filters',
    ]"
    searchable
    exportable
    ajax
/>
```

---

## 📐 Column Configuration

### Column Types

#### 1. Text Column (Default)
```php
[
    'key' => 'name',
    'label' => 'Name',
    'sortable' => true,
    'searchable' => true,
]
```

#### 2. Badge Column
```php
[
    'key' => 'status',
    'label' => 'Status',
    'type' => 'badge',
    'variants' => [
        'active' => 'success',
        'inactive' => 'secondary',
        'pending' => 'warning',
    ],
]
```

#### 3. Date Column
```php
[
    'key' => 'created_at',
    'label' => 'Created',
    'type' => 'date',
    'format' => 'd M Y',
]
```

#### 4. Currency Column
```php
[
    'key' => 'amount',
    'label' => 'Amount',
    'type' => 'currency',
    'currency' => 'IDR',
]
```

#### 5. Boolean Column
```php
[
    'key' => 'is_active',
    'label' => 'Active',
    'type' => 'boolean',
    'true_label' => 'Yes',
    'false_label' => 'No',
]
```

#### 6. Image Column
```php
[
    'key' => 'avatar',
    'label' => 'Avatar',
    'type' => 'image',
    'width' => 40,
    'height' => 40,
    'rounded' => true,
]
```

#### 7. Relationship Column
```php
[
    'key' => 'user.name',
    'label' => 'User',
    'sortable' => true,
    'relation' => 'user',
]
```

#### 8. Custom Column
```php
[
    'key' => 'custom',
    'label' => 'Custom',
    'render' => function($row) {
        return view('partials.custom-cell', ['row' => $row]);
    },
]
```

### Column Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `key` | string | required | Database column name |
| `label` | string | required | Column header label |
| `type` | string | 'text' | Column type (text, badge, date, currency, etc.) |
| `sortable` | boolean | false | Enable sorting |
| `searchable` | boolean | false | Include in search |
| `class` | string | '' | CSS classes for column |
| `align` | string | 'left' | Text alignment (left, center, right) |
| `width` | string | null | Column width (e.g., 'min-w-150px') |
| `render` | callable | null | Custom render function |
| `format` | string | null | Format string (for dates, numbers) |
| `variants` | array | [] | Badge variants mapping |
| `relation` | string | null | Relationship name for eager loading |

---

## 🔍 Filter Configuration

### Filter Types

#### 1. Search Filter
```php
[
    'type' => 'search',
    'name' => 'search',
    'placeholder' => 'Search...',
    'columns' => ['name', 'email', 'phone'], // Columns to search
]
```

#### 2. Select Filter
```php
[
    'type' => 'select',
    'name' => 'status',
    'label' => 'Status',
    'options' => [
        '' => 'All Status',
        'active' => 'Active',
        'inactive' => 'Inactive',
    ],
]
```

#### 3. Date Filter
```php
[
    'type' => 'date',
    'name' => 'date_from',
    'label' => 'From Date',
]
```

#### 4. Date Range Filter
```php
[
    'type' => 'daterange',
    'name' => 'date_range',
    'label' => 'Date Range',
    'from' => 'date_from',
    'to' => 'date_to',
]
```

#### 5. Number Range Filter
```php
[
    'type' => 'numberrange',
    'name' => 'amount_range',
    'label' => 'Amount Range',
    'from' => 'amount_from',
    'to' => 'amount_to',
]
```

#### 6. Checkbox Filter
```php
[
    'type' => 'checkbox',
    'name' => 'is_active',
    'label' => 'Active Only',
]
```

---

## ⚡ Action Configuration

### Row Actions

```php
[
    'label' => 'View',
    'route' => 'module.show',
    'icon' => 'eye',
    'variant' => 'light-primary',
    'size' => 'sm',
    'can' => 'view', // Permission check
]

[
    'label' => 'Edit',
    'route' => 'module.edit',
    'icon' => 'pencil',
    'variant' => 'light-primary',
    'can' => 'update',
]

[
    'label' => 'Delete',
    'route' => 'module.destroy',
    'icon' => 'trash',
    'variant' => 'danger',
    'method' => 'DELETE',
    'confirm' => true,
    'confirm_message' => 'Are you sure you want to delete this item?',
    'can' => 'delete',
]
```

### Bulk Actions

```php
[
    'label' => 'Delete Selected',
    'route' => 'module.bulk-delete',
    'icon' => 'trash',
    'variant' => 'danger',
    'confirm' => true,
    'confirm_message' => 'Are you sure you want to delete {count} items?',
]

[
    'label' => 'Export Selected',
    'route' => 'module.export',
    'icon' => 'cloud-download',
    'variant' => 'primary',
]
```

### Action Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `label` | string | required | Action button label |
| `route` | string | required | Route name |
| `icon` | string | null | Keenicon name |
| `variant` | string | 'light-primary' | Button variant |
| `size` | string | 'sm' | Button size |
| `method` | string | 'GET' | HTTP method |
| `confirm` | boolean | false | Show confirmation dialog |
| `confirm_message` | string | null | Confirmation message |
| `can` | string | null | Permission check |
| `class` | string | '' | Additional CSS classes |

---

## 🎨 Styling Configuration

### Table Styling

```php
[
    'table_class' => 'table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4',
    'header_class' => 'fw-bold text-muted',
    'row_class' => '',
    'cell_class' => '',
    'striped' => false,
    'hover' => true,
    'bordered' => false,
]
```

### Pagination Styling

```php
[
    'pagination_class' => 'pagination',
    'per_page_options' => [10, 25, 50, 100],
    'show_per_page' => true,
    'show_total' => true,
    'show_range' => true,
]
```

---

## 🔧 Backend Implementation

### Controller Example

```php
use App\Services\TableEngine;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['organization', 'supplier', 'user']);
        
        $table = TableEngine::make($query)
            ->columns([
                [
                    'key' => 'po_number',
                    'label' => 'PO Number',
                    'sortable' => true,
                    'searchable' => true,
                    'width' => 'min-w-150px',
                ],
                [
                    'key' => 'organization.name',
                    'label' => 'Organization',
                    'sortable' => true,
                    'relation' => 'organization',
                ],
                [
                    'key' => 'total_amount',
                    'label' => 'Total',
                    'type' => 'currency',
                    'sortable' => true,
                ],
                [
                    'key' => 'status',
                    'label' => 'Status',
                    'type' => 'badge',
                    'variants' => [
                        'draft' => 'secondary',
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    ],
                ],
                [
                    'key' => 'created_at',
                    'label' => 'Date',
                    'type' => 'date',
                    'format' => 'd M Y',
                    'sortable' => true,
                ],
            ])
            ->filters([
                [
                    'type' => 'search',
                    'name' => 'search',
                    'placeholder' => 'Search PO number or organization...',
                    'columns' => ['po_number', 'organization.name'],
                ],
                [
                    'type' => 'select',
                    'name' => 'status',
                    'label' => 'Status',
                    'options' => [
                        '' => 'All Status',
                        'draft' => 'Draft',
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ],
                ],
            ])
            ->actions([
                [
                    'label' => 'View',
                    'route' => 'web.po.show',
                    'icon' => 'eye',
                    'variant' => 'light-primary',
                ],
                [
                    'label' => 'Edit',
                    'route' => 'web.po.edit',
                    'icon' => 'pencil',
                    'variant' => 'light-primary',
                    'can' => 'update_po',
                ],
                [
                    'label' => 'Delete',
                    'route' => 'web.po.destroy',
                    'icon' => 'trash',
                    'variant' => 'danger',
                    'method' => 'DELETE',
                    'confirm' => true,
                    'can' => 'delete_po',
                ],
            ])
            ->perPage(25)
            ->defaultSort('created_at', 'desc')
            ->process($request);
        
        return view('purchase-orders.index', [
            'table' => $table,
        ]);
    }
}
```

### View Example

```blade
<x-layout title="Purchase Orders">

    <x-page-header 
        title="Purchase Orders"
        description="Manage all purchase orders"
    >
        <x-slot name="actions">
            @can('create_po')
            <x-button variant="primary" icon="plus" href="{{ route('web.po.create') }}">
                Create PO
            </x-button>
            @endcan
        </x-slot>
    </x-page-header>

    {{-- Table Engine renders everything --}}
    {!! $table->render() !!}

</x-layout>
```

---

## 🚀 Advanced Features

### 1. Custom Cell Rendering

```php
[
    'key' => 'user',
    'label' => 'User',
    'render' => function($row) {
        return view('partials.user-cell', [
            'user' => $row->user,
            'avatar' => $row->user->avatar,
            'name' => $row->user->name,
            'email' => $row->user->email,
        ]);
    },
]
```

### 2. Conditional Actions

```php
[
    'label' => 'Approve',
    'route' => 'web.po.approve',
    'icon' => 'check',
    'variant' => 'success',
    'visible' => function($row) {
        return $row->status === 'pending' && auth()->user()->can('approve_po');
    },
]
```

### 3. Row Styling

```php
->rowClass(function($row) {
    return $row->is_urgent ? 'bg-light-danger' : '';
})
```

### 4. Export Configuration

```php
->export([
    'csv' => true,
    'excel' => true,
    'pdf' => true,
    'filename' => 'purchase-orders',
    'columns' => ['po_number', 'organization.name', 'total_amount', 'status'],
])
```

### 5. AJAX Loading

```php
->ajax([
    'enabled' => true,
    'url' => route('web.po.data'),
    'method' => 'GET',
    'debounce' => 300,
])
```

---

## 📊 Performance Optimization

### 1. Eager Loading
```php
$query = PurchaseOrder::with(['organization', 'supplier', 'user', 'items']);
```

### 2. Select Only Needed Columns
```php
$query = PurchaseOrder::select(['id', 'po_number', 'status', 'total_amount', 'created_at']);
```

### 3. Index Database Columns
```sql
CREATE INDEX idx_po_status ON purchase_orders(status);
CREATE INDEX idx_po_created_at ON purchase_orders(created_at);
```

### 4. Cache Results
```php
->cache([
    'enabled' => true,
    'ttl' => 300, // 5 minutes
    'key' => 'po_table',
])
```

---

## 🎯 Implementation Phases

### Phase 1: Core Engine (Week 1)
- [ ] TableEngine PHP class
- [ ] Basic column rendering
- [ ] Pagination support
- [ ] Sorting support
- [ ] Search support

### Phase 2: Blade Component (Week 1)
- [ ] table-engine.blade.php component
- [ ] Metronic styling integration
- [ ] Empty state rendering
- [ ] Action buttons rendering

### Phase 3: Advanced Features (Week 2)
- [ ] Filter system
- [ ] Bulk actions
- [ ] Custom cell rendering
- [ ] Badge auto-rendering
- [ ] Export functionality

### Phase 4: Optimization (Week 2)
- [ ] AJAX loading
- [ ] State persistence
- [ ] Performance optimization
- [ ] Caching layer

### Phase 5: Documentation (Week 3)
- [ ] API documentation
- [ ] Usage examples
- [ ] Migration guide
- [ ] Best practices

---

## ✅ Success Criteria

- [ ] Zero configuration for basic tables
- [ ] Full customization when needed
- [ ] Perfect Metronic 8 styling
- [ ] Laravel native (Eloquent, pagination)
- [ ] Performance optimized
- [ ] Comprehensive documentation
- [ ] Easy to maintain
- [ ] Reusable across all modules

---

**Next Step:** Implement Phase 1 - Core Engine
