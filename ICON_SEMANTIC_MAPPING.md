# Icon Semantic Mapping - Keenicons System

## APPROVED ICON SYSTEM
**System**: Keenicons (ki-solid)
**Format**: `<i class="ki-solid ki-{name} fs-{size}"></i>`

---

## SEMANTIC ICON MAPPING (MANDATORY)

### 1. ACTIONS (Button Icons)

| Action | Icon | Class | Usage |
|--------|------|-------|-------|
| **Create / Add** | `ki-plus` | `ki-solid ki-plus fs-2` | Tambah data baru |
| **Edit / Update** | `ki-notepad-edit` | `ki-solid ki-notepad-edit fs-4` | Edit data existing |
| **Delete** | `ki-trash` | `ki-solid ki-trash fs-4` | Hapus data |
| **View / Detail** | `ki-eye` | `ki-solid ki-eye fs-4` | Lihat detail |
| **Save / Submit** | `ki-check` | `ki-solid ki-check fs-2` | Simpan/submit form |
| **Cancel** | `ki-cross` | `ki-solid ki-cross fs-2` | Batal/cancel |
| **Search** | `ki-magnifier` | `ki-solid ki-magnifier fs-2` | Cari/filter |
| **Download** | `ki-cloud-download` | `ki-solid ki-cloud-download fs-4` | Download file |
| **Upload** | `ki-cloud-upload` | `ki-solid ki-cloud-upload fs-4` | Upload file |
| **Refresh** | `ki-arrows-circle` | `ki-solid ki-arrows-circle fs-4` | Refresh data |
| **Filter** | `ki-filter` | `ki-solid ki-filter fs-4` | Filter data |

### 2. STATUS ICONS

| Status | Icon | Class | Color |
|--------|------|-------|-------|
| **Success** | `ki-check-circle` | `ki-solid ki-check-circle fs-2` | text-success |
| **Pending** | `ki-time` | `ki-solid ki-time fs-2` | text-warning |
| **Failed** | `ki-cross-circle` | `ki-solid ki-cross-circle fs-2` | text-danger |
| **Warning** | `ki-information-5` | `ki-solid ki-information-5 fs-2` | text-warning |
| **Info** | `ki-information` | `ki-solid ki-information fs-2` | text-info |
| **Active** | `ki-check-circle` | `ki-solid ki-check-circle fs-4` | text-success |
| **Inactive** | `ki-cross-square` | `ki-solid ki-cross-square fs-4` | text-warning |

### 3. BUSINESS CONTEXT ICONS

| Context | Icon | Class | Usage |
|---------|------|-------|-------|
| **Dashboard** | `ki-home` | `ki-solid ki-home fs-2` | Dashboard menu |
| **Purchase Order** | `ki-purchase` | `ki-solid ki-purchase fs-2` | PO menu/card |
| **Approval** | `ki-basket-ok` | `ki-solid ki-basket-ok fs-2` | Approval menu |
| **Goods Receipt** | `ki-questionnaire-tablet` | `ki-solid ki-questionnaire-tablet fs-2` | GR menu |
| **Invoice (AR)** | `ki-bill` | `ki-solid ki-bill fs-2` | Customer invoice |
| **Invoice (AP)** | `ki-arrow-down` | `ki-solid ki-arrow-down fs-2` | Supplier invoice |
| **Payment** | `ki-wallet` | `ki-solid ki-wallet fs-2` | Payment menu |
| **Credit Control** | `ki-chart-line` | `ki-solid ki-chart-line fs-2` | Financial control |
| **Organization** | `ki-people` | `ki-solid ki-people fs-2` | Organization menu |
| **Supplier** | `ki-cube-2` | `ki-solid ki-cube-2 fs-2` | Supplier menu |
| **Product** | `ki-capsule` | `ki-solid ki-capsule fs-2` | Product menu |
| **User** | `ki-user` | `ki-solid ki-user fs-2` | User menu |
| **Notification** | `ki-notification` | `ki-solid ki-notification fs-2` | Notification bell |

### 4. EMPTY STATE ICONS

| State | Icon | Class | Color |
|-------|------|-------|-------|
| **No Data** | `ki-file-deleted` | `ki-solid ki-file-deleted fs-3x` | text-gray-400 |
| **Empty List** | `ki-folder` | `ki-solid ki-folder fs-3x` | text-gray-400 |
| **No Results** | `ki-magnifier` | `ki-solid ki-magnifier fs-3x` | text-gray-400 |

### 5. DROPDOWN/MENU ICONS

| Action | Icon | Class |
|--------|------|-------|
| **More Actions** | `ki-dots-vertical` | `ki-solid ki-dots-vertical fs-3` |
| **Dropdown** | `ki-down` | `ki-solid ki-down fs-5` |

---

## SIZE GUIDELINES

| Context | Size Class | Pixel Size |
|---------|-----------|------------|
| **Button Primary** | `fs-2` | 18px |
| **Button Small** | `fs-4` | 14px |
| **Inline Text** | `fs-5` | 12px |
| **Card Header** | `fs-2` | 18px |
| **Empty State** | `fs-3x` | 48px |
| **Alert** | `fs-2` | 18px |

---

## SPACING RULES

### Button Icons
```html
<!-- LEFT placement with spacing -->
<button class="btn btn-primary">
    <i class="ki-solid ki-plus fs-2"></i>
    Tambah Data
</button>
```

### Inline Icons
```html
<!-- With me-2 or me-3 spacing -->
<i class="ki-solid ki-check-circle fs-2 me-3"></i>
<span>Success message</span>
```

---

## COLOR RULES

| Semantic | Color Class | Usage |
|----------|-------------|-------|
| **Primary** | `text-primary` | Main actions |
| **Success** | `text-success` | Success states |
| **Danger** | `text-danger` | Delete, errors |
| **Warning** | `text-warning` | Warnings, inactive |
| **Info** | `text-info` | Information |
| **Muted** | `text-gray-400` | Empty states |

---

## FORBIDDEN PATTERNS

❌ **DO NOT**:
- Mix icon libraries (ki-solid + bi)
- Use multiple icons in one button
- Use oversized icons in buttons
- Use emoji instead of icons
- Use inline SVG if system uses Keenicons
- Use decorative icons without meaning
- Use wrong semantic icons

✅ **DO**:
- Use consistent icon for same action
- Match icon to semantic meaning
- Follow size guidelines
- Use proper spacing
- Apply semantic colors
- Keep icons left-aligned in buttons

---

## VALIDATION CHECKLIST

- [ ] All CREATE buttons use `ki-plus`
- [ ] All EDIT buttons use `ki-notepad-edit`
- [ ] All DELETE buttons use `ki-trash`
- [ ] All SAVE buttons use `ki-check`
- [ ] All CANCEL buttons use `ki-cross`
- [ ] All SEARCH buttons use `ki-magnifier`
- [ ] All status icons match semantic meaning
- [ ] All empty states use `ki-file-deleted`
- [ ] All icon sizes are consistent
- [ ] All icons have proper spacing

---

**Status**: REFERENCE DOCUMENT
**System**: Keenicons (ki-solid)
**Version**: 1.0
**Date**: 2026-04-14
