# Table Engine - Usage Guide

**Version:** 1.0  
**Date:** 2024  
**Status:** Ready to Use

---

## 🚀 Quick Start

### Step 1: Controller Setup

```php
use App\Services\TableEngine;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        // Start with your Eloquent query
        $query = PurchaseOrder::with(['organization', 'supplier']);
        
        // Build the table
        $table = TableEngine::make($query)
            ->columns([
                ['key' => 'po_number', 'label' => 'PO Number', 'sortable' => true],
                ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
            ])
            ->process($request);
        
        return view('purchase-orders.index', ['table' => $table]);
    }
}
```

### Step 2: View Setup

```blade
<x-layout title="Purchase Orders">
    <x-page-header title="Purchase Orders" />
    
    {{-- Render the table --}}
    {!! $table->render() !!}
</x-layout>
```

**That's it!** You now have a fully functional table with Metronic styling.

---

## 📚 Complete Examples

### Example 1: Basic Table

```php
$table = TableEngine::make(User::query())
    ->columns([
        ['key' => 'name', 'label' => 'Name'],
        ['key' => 'email', 'label' => 'Email'],
        ['key' => 'created_at', 'label' => 'Joined', 'type' => 'date'],
    ])
    ->process($request);
```

### Example 2: Table with Sorting

```php
$table = TableEngine::make(User::query())
    ->columns([
        ['key' => 'name', 'label' => 'Name', 'sortable' => true],
        ['key' => 'email', 'label' => 'Email', 'sortable' => true],
        ['key' => 'created_at', 'label' => 'Joined', 'type' => 'date', 'sortable' => true],
    ])
    ->defaultSort('created_at', 'desc')
    ->process($request);
```

### Example 3: Table with Filters

```php
$table = TableEngine::make(PurchaseOrder::query())
    ->columns([
        ['key' => 'po_number', 'label' => 'PO Number', 'sortable' => true],
        ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
        ['key' => 'total_amount', 'label' => 'Total', 'type' => 'currency'],
    ])
    ->filters([
        [
            'type' => 'search',
            'name' => 'search',
            'placeholder' => 'Search PO number...',
            'columns' => ['po_number'],
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
            ],
        ],
    ])
    ->process($request);
```

### Example 4: Table with Actions

```php
$table = TableEngine::make(PurchaseOrder::query())
    ->columns([
        ['key' => 'po_number', 'label' => 'PO Number'],
        ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
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
    ->process($request);
```

### Example 5: Table with Relationships

```php
$table = TableEngine::make(PurchaseOrder::with(['organization', 'supplier']))
    ->columns([
        ['key' => 'po_number', 'label' => 'PO Number', 'sortable' => true],
        ['key' => 'organization.name', 'label' => 'Organization', 'sortable' => true],
        ['key' => 'supplier.name', 'label' => 'Supplier'],
        ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
    ])
    ->process($request);
```

### Example 6: Table with Badge Variants

```php
$table = TableEngine::make(PurchaseOrder::query())
    ->columns([
        ['key' => 'po_number', 'label' => 'PO Number'],
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
    ])
    ->process($request);
```

### Example 7: Table with Custom Cell Rendering

```php
$table = TableEngine::make(User::query())
    ->columns([
        [
            'key' => 'user',
            'label' => 'User',
            'render' => function($row) {
                return view('partials.user-cell', [
                    'name' => $row->name,
                    'email' => $row->email,
                    'avatar' => $row->avatar,
                ]);
            },
        ],
        ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
    ])
    ->process($request);
```

**partials/user-cell.blade.php:**
```blade
<div class="d-flex align-items-center">
    <div class="symbol symbol-40px me-3">
        <img src="{{ $avatar }}" alt="{{ $name }}" class="rounded-circle" />
    </div>
    <div class="d-flex flex-column">
        <span class="text-gray-900 fw-bold fs-6">{{ $name }}</span>
        <span class="text-gray-600 fs-7">{{ $email }}</span>
    </div>
</div>
```

### Example 8: Table with Conditional Actions

```php
$table = TableEngine::make(PurchaseOrder::query())
    ->columns([
        ['key' => 'po_number', 'label' => 'PO Number'],
        ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
    ])
    ->actions([
        [
            'label' => 'Approve',
            'route' => 'web.po.approve',
            'icon' => 'check',
            'variant' => 'success',
            'visible' => function($row) {
                return $row->status === 'pending';
            },
        ],
        [
            'label' => 'Reject',
            'route' => 'web.po.reject',
            'icon' => 'cross',
            'variant' => 'danger',
            'visible' => function($row) {
                return $row->status === 'pending';
            },
        ],
    ])
    ->process($request);
```

### Example 9: Complete Purchase Orders Table

```php
public function index(Request $request)
{
    $query = PurchaseOrder::with(['organization', 'supplier', 'user']);
    
    $table = TableEngine::make($query)
        ->columns([
            [
                'key' => 'po_number',
                'label' => 'Nomor PO',
                'sortable' => true,
                'searchable' => true,
                'width' => 'min-w-150px',
            ],
            [
                'key' => 'organization.name',
                'label' => 'Organisasi / Klinik',
                'sortable' => true,
                'width' => 'min-w-150px',
            ],
            [
                'key' => 'total_amount',
                'label' => 'Total Amount',
                'type' => 'currency',
                'sortable' => true,
                'width' => 'min-w-120px',
            ],
            [
                'key' => 'status',
                'label' => 'Status',
                'type' => 'badge',
                'variants' => [
                    'draft' => 'secondary',
                    'pending' => 'warning',
                    'submitted' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'danger',
                ],
                'width' => 'min-w-100px',
            ],
            [
                'key' => 'created_at',
                'label' => 'Tanggal / Pembuat',
                'type' => 'date',
                'format' => 'd M Y',
                'sortable' => true,
                'width' => 'min-w-150px',
                'render' => function($row) {
                    return '
                        <div class="d-flex flex-column">
                            <span class="text-gray-800 fw-semibold fs-7">' . $row->created_at->format('d M Y') . '</span>
                            <span class="text-gray-600 fs-8">' . ($row->user->name ?? '-') . '</span>
                        </div>
                    ';
                },
            ],
        ])
        ->filters([
            [
                'type' => 'search',
                'name' => 'search',
                'placeholder' => 'Cari nomor PO atau organisasi...',
                'columns' => ['po_number', 'organization.name'],
            ],
            [
                'type' => 'select',
                'name' => 'status',
                'label' => 'Status',
                'options' => [
                    '' => 'Semua Status',
                    'draft' => 'Draft',
                    'pending' => 'Menunggu',
                    'approved' => 'Disetujui',
                    'rejected' => 'Ditolak',
                ],
            ],
        ])
        ->actions([
            [
                'label' => 'Detail',
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
                'visible' => function($row) {
                    return $row->status === 'draft';
                },
            ],
            [
                'label' => 'Delete',
                'route' => 'web.po.destroy',
                'icon' => 'trash',
                'variant' => 'danger',
                'method' => 'DELETE',
                'confirm' => true,
                'confirm_message' => 'Apakah Anda yakin ingin menghapus PO ini?',
                'can' => 'delete_po',
            ],
        ])
        ->perPage(25)
        ->defaultSort('created_at', 'desc')
        ->emptyState([
            'icon' => 'file-deleted',
            'title' => 'Tidak Ada Purchase Order',
            'message' => 'Tidak ada purchase order ditemukan. Coba sesuaikan filter Anda.',
        ])
        ->process($request);
    
    return view('purchase-orders.index', [
        'table' => $table,
    ]);
}
```

---

## 🎨 Column Types Reference

### Text Column
```php
['key' => 'name', 'label' => 'Name']
```

### Badge Column
```php
[
    'key' => 'status',
    'label' => 'Status',
    'type' => 'badge',
    'variants' => [
        'active' => 'success',
        'inactive' => 'secondary',
    ],
]
```

### Date Column
```php
[
    'key' => 'created_at',
    'label' => 'Date',
    'type' => 'date',
    'format' => 'd M Y',
]
```

### Currency Column
```php
[
    'key' => 'amount',
    'label' => 'Amount',
    'type' => 'currency',
    'currency' => 'IDR',
]
```

### Boolean Column
```php
[
    'key' => 'is_active',
    'label' => 'Active',
    'type' => 'boolean',
    'true_label' => 'Yes',
    'false_label' => 'No',
]
```

### Image Column
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

### Custom Column
```php
[
    'key' => 'custom',
    'label' => 'Custom',
    'render' => function($row) {
        return view('partials.custom-cell', ['row' => $row]);
    },
]
```

---

## 🔍 Filter Types Reference

### Search Filter
```php
[
    'type' => 'search',
    'name' => 'search',
    'placeholder' => 'Search...',
    'columns' => ['name', 'email'],
]
```

### Select Filter
```php
[
    'type' => 'select',
    'name' => 'status',
    'label' => 'Status',
    'options' => [
        '' => 'All',
        'active' => 'Active',
        'inactive' => 'Inactive',
    ],
]
```

### Date Filter
```php
[
    'type' => 'date',
    'name' => 'date_from',
    'label' => 'From Date',
]
```

---

## ⚡ Action Types Reference

### View Action
```php
[
    'label' => 'View',
    'route' => 'module.show',
    'icon' => 'eye',
    'variant' => 'light-primary',
]
```

### Edit Action
```php
[
    'label' => 'Edit',
    'route' => 'module.edit',
    'icon' => 'pencil',
    'variant' => 'light-primary',
    'can' => 'update',
]
```

### Delete Action
```php
[
    'label' => 'Delete',
    'route' => 'module.destroy',
    'icon' => 'trash',
    'variant' => 'danger',
    'method' => 'DELETE',
    'confirm' => true,
    'can' => 'delete',
]
```

---

## 🚀 Performance Tips

### 1. Eager Load Relationships
```php
$query = PurchaseOrder::with(['organization', 'supplier', 'user']);
```

### 2. Select Only Needed Columns
```php
$query = PurchaseOrder::select(['id', 'po_number', 'status', 'total_amount']);
```

### 3. Use Indexes
```sql
CREATE INDEX idx_status ON purchase_orders(status);
CREATE INDEX idx_created_at ON purchase_orders(created_at);
```

### 4. Limit Per Page
```php
->perPage(25) // Don't use too high numbers
```

---

## ✅ Migration Checklist

To migrate existing tables to Table Engine:

- [ ] Replace raw HTML table with `TableEngine::make()`
- [ ] Define columns array
- [ ] Define filters array (if applicable)
- [ ] Define actions array
- [ ] Add eager loading for relationships
- [ ] Set default sorting
- [ ] Configure empty state
- [ ] Test sorting functionality
- [ ] Test filtering functionality
- [ ] Test actions (view, edit, delete)
- [ ] Test responsive design
- [ ] Verify permissions work correctly

---

## 🎯 Next Steps

1. **Migrate Purchase Orders** - Use Example 9 as template
2. **Migrate other modules** - Follow same pattern
3. **Create custom cell partials** - For complex displays
4. **Add bulk actions** - If needed
5. **Optimize queries** - Add indexes, eager loading

---

**Ready to use!** Start with a simple table and add features as needed.
