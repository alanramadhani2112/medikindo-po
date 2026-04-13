# Table Engine - Quick Reference Card

**Print this and keep it handy!**

---

## 🚀 Basic Setup

```php
// Controller
$table = TableEngine::make($query)
    ->columns([...])
    ->process($request);

// View
{!! $table->render() !!}
```

---

## 📋 Column Types

```php
// Text
['key' => 'name', 'label' => 'Name']

// Badge
['key' => 'status', 'label' => 'Status', 'type' => 'badge', 'variants' => [...]]

// Date
['key' => 'created_at', 'label' => 'Date', 'type' => 'date', 'format' => 'd M Y']

// Currency
['key' => 'amount', 'label' => 'Amount', 'type' => 'currency']

// Boolean
['key' => 'is_active', 'label' => 'Active', 'type' => 'boolean']

// Custom
['key' => 'custom', 'label' => 'Custom', 'render' => fn($row) => view(...)]
```

---

## 🔍 Filter Types

```php
// Search
['type' => 'search', 'name' => 'search', 'placeholder' => '...', 'columns' => [...]]

// Select
['type' => 'select', 'name' => 'status', 'options' => [...]]

// Date
['type' => 'date', 'name' => 'date_from', 'label' => 'From']
```

---

## ⚡ Action Types

```php
// View
['label' => 'View', 'route' => 'module.show', 'icon' => 'eye', 'variant' => 'light-primary']

// Edit
['label' => 'Edit', 'route' => 'module.edit', 'icon' => 'pencil', 'can' => 'update']

// Delete
['label' => 'Delete', 'route' => 'module.destroy', 'icon' => 'trash', 'variant' => 'danger', 'method' => 'DELETE', 'confirm' => true]
```

---

## 🎨 Common Patterns

### Sortable Column
```php
['key' => 'name', 'label' => 'Name', 'sortable' => true]
```

### Searchable Column
```php
['key' => 'name', 'label' => 'Name', 'searchable' => true]
```

### Relationship Column
```php
['key' => 'user.name', 'label' => 'User', 'sortable' => true]
```

### Conditional Action
```php
['label' => 'Approve', 'route' => '...', 'visible' => fn($row) => $row->status === 'pending']
```

### Permission-based Action
```php
['label' => 'Edit', 'route' => '...', 'can' => 'update_module']
```

---

## ⚙️ Configuration

```php
->perPage(25)                              // Items per page
->defaultSort('created_at', 'desc')        // Default sorting
->emptyState([...])                        // Custom empty state
->searchable()                             // Enable global search
->exportable()                             // Enable export
```

---

## 🎯 Complete Example

```php
$table = TableEngine::make(Model::with(['relation']))
    ->columns([
        ['key' => 'name', 'label' => 'Name', 'sortable' => true],
        ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
    ])
    ->filters([
        ['type' => 'search', 'name' => 'search', 'columns' => ['name']],
        ['type' => 'select', 'name' => 'status', 'options' => [...]],
    ])
    ->actions([
        ['label' => 'View', 'route' => 'module.show', 'icon' => 'eye'],
        ['label' => 'Edit', 'route' => 'module.edit', 'icon' => 'pencil', 'can' => 'update'],
        ['label' => 'Delete', 'route' => 'module.destroy', 'icon' => 'trash', 'method' => 'DELETE', 'confirm' => true],
    ])
    ->perPage(25)
    ->defaultSort('created_at', 'desc')
    ->process($request);
```

---

## 📚 Documentation

- **TABLE_ENGINE_SPECIFICATION.md** - Full specification
- **TABLE_ENGINE_USAGE_GUIDE.md** - Examples and usage
- **TABLE_ENGINE_SUMMARY.md** - Overview and benefits

---

**Keep this card handy for quick reference!**
