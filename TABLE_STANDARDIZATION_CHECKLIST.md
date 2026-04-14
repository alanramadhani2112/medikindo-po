# ✅ TABLE STANDARDIZATION CHECKLIST

Use this checklist when creating or updating table modules to ensure consistency.

---

## 📋 STRUCTURE CHECKLIST

### 1. Layout & Header
- [ ] Uses `@extends('layouts.app')`
- [ ] Has page header with title (fs-2 fw-bold)
- [ ] Has description (fs-6 text-gray-600)
- [ ] Has action button (btn-primary) if applicable
- [ ] Section spacing is mb-7

### 2. KPI Cards (If Applicable)
- [ ] Uses row with col-md-4 (or appropriate grid)
- [ ] Cards have bg-{color} classes
- [ ] Text is white (text-white)
- [ ] Label is fs-7 fw-bold
- [ ] Value is fs-2x fw-bold mt-2
- [ ] Section spacing is mb-7

### 3. Filter Bar
- [ ] Wrapped in card with mb-5
- [ ] Form uses `d-flex flex-wrap gap-3`
- [ ] Search input has `form-control form-control-solid`
- [ ] Search input has max-width: 400px
- [ ] Filter button is btn-dark with icon
- [ ] Reset button appears when filters active
- [ ] Reset button is btn-light with icon
- [ ] Hidden inputs preserve state (status, etc.)

### 4. Tabs (If Applicable)
- [ ] Uses nav-tabs nav-line-tabs nav-line-tabs-2x
- [ ] Each tab has icon (ki-solid)
- [ ] Each tab shows count
- [ ] Active tab has 'active' class
- [ ] Badge colors match tab purpose
- [ ] Links preserve other query params

### 5. Table Structure
- [ ] Wrapped in `table-responsive`
- [ ] Uses class: `table table-row-dashed table-row-gray-300 align-middle gs-7 gy-4`
- [ ] Header row: `fw-bold text-muted bg-light`
- [ ] First column: `ps-4 rounded-start`
- [ ] Last column: `text-end pe-4 rounded-end`
- [ ] All columns have min-w-{size}px

### 6. Table Content
- [ ] First column has ID/Name with link
- [ ] Links use: `text-gray-900 text-hover-primary fw-bold fs-6`
- [ ] Secondary info uses: `text-gray-500 fs-7 mt-1`
- [ ] Status badges use correct colors
- [ ] Amounts are right-aligned
- [ ] Dates show formatted + relative time
- [ ] Actions are right-aligned with gap-2

### 7. Empty State
- [ ] Has icon (ki-solid fs-3x text-gray-400)
- [ ] Has title (text-gray-700 fs-5 fw-semibold)
- [ ] Has description (text-gray-500 fs-6)
- [ ] Centered with py-10
- [ ] Uses flex-column align-items-center

### 8. Pagination
- [ ] Wrapped in `d-flex flex-stack flex-wrap pt-7`
- [ ] Shows count: "Menampilkan X - Y dari Z items"
- [ ] Count uses: `fs-6 fw-semibold text-gray-700`
- [ ] Links use: `{{ $items->links() }}`
- [ ] Only shows if `$items->hasPages()`

---

## 🎨 DESIGN TOKENS CHECKLIST

### Buttons
- [ ] Primary: `btn-primary` (create/main action)
- [ ] Success: `btn-success` (approve/confirm)
- [ ] Danger: `btn-danger` (delete/reject)
- [ ] Light: `btn-light` (view/secondary)
- [ ] Dark: `btn-dark` (filter)

### Badges
- [ ] Success: `badge-success` (completed/paid/approved)
- [ ] Warning: `badge-warning` (pending/unpaid)
- [ ] Danger: `badge-danger` (rejected/overdue)
- [ ] Secondary: `badge-secondary` (draft/inactive)
- [ ] Primary: `badge-primary` (submitted/active)

### Icons
- [ ] All use Keenicons: `ki-solid ki-{name}`
- [ ] Button icons: fs-2
- [ ] Inline icons: fs-4
- [ ] Large icons: fs-3x

### Typography
- [ ] Page title: `fs-2 fw-bold text-gray-900`
- [ ] Section title: `fs-3 fw-bold`
- [ ] Body text: `fs-6`
- [ ] Labels/meta: `fs-7`
- [ ] Large numbers: `fs-2x fw-bold`

### Colors
- [ ] Primary text: `text-gray-900`
- [ ] Secondary text: `text-gray-600`
- [ ] Meta text: `text-gray-500`
- [ ] Muted text: `text-muted`
- [ ] Hover: `text-hover-primary`

### Spacing
- [ ] Section spacing: `mb-7`
- [ ] Card spacing: `mb-5`
- [ ] Button gaps: `gap-3`
- [ ] Table padding: `gs-7 gy-4`
- [ ] Top padding: `pt-7` (pagination)
- [ ] Vertical padding: `py-10` (empty state)

---

## 🔍 CODE QUALITY CHECKLIST

### Blade Syntax
- [ ] No custom components (`<x-*>`)
- [ ] Uses `@extends` not `<x-layout>`
- [ ] Proper indentation (4 spaces)
- [ ] Comments for major sections
- [ ] Consistent quote style (double quotes)

### PHP Code
- [ ] Uses `@php` blocks for complex logic
- [ ] Match expressions for status colors
- [ ] Proper null coalescing (`??`)
- [ ] Type casting where needed
- [ ] Clean variable names

### HTML Structure
- [ ] Semantic HTML elements
- [ ] Proper nesting
- [ ] No unnecessary divs
- [ ] Accessible markup
- [ ] Responsive classes

### Performance
- [ ] Minimal DOM elements
- [ ] No inline styles (use classes)
- [ ] Efficient queries (done in controller)
- [ ] Proper pagination
- [ ] Lazy loading where applicable

---

## 🧪 TESTING CHECKLIST

### Visual Testing
- [ ] Page loads without errors
- [ ] Layout looks correct
- [ ] Spacing is consistent
- [ ] Colors are correct
- [ ] Icons display properly
- [ ] Responsive on mobile
- [ ] Responsive on tablet
- [ ] Responsive on desktop

### Functional Testing
- [ ] Search works
- [ ] Filters work
- [ ] Reset button works
- [ ] Tabs work (if applicable)
- [ ] Pagination works
- [ ] Links work
- [ ] Buttons work
- [ ] Forms submit correctly

### Edge Cases
- [ ] Empty state displays correctly
- [ ] Single item displays correctly
- [ ] Many items display correctly
- [ ] Long text truncates properly
- [ ] Missing data shows "—"
- [ ] Null values handled
- [ ] Error states handled

### Browser Testing
- [ ] Chrome
- [ ] Firefox
- [ ] Safari
- [ ] Edge

---

## 📝 DOCUMENTATION CHECKLIST

### Code Comments
- [ ] Section headers for major parts
- [ ] Inline comments for complex logic
- [ ] TODO comments for future work
- [ ] Reference to design system

### Commit Message
- [ ] Clear description of changes
- [ ] References issue/ticket if applicable
- [ ] Lists affected files
- [ ] Notes any breaking changes

### Documentation
- [ ] Update README if needed
- [ ] Update CHANGELOG if needed
- [ ] Update API docs if needed
- [ ] Update user guide if needed

---

## 🚀 DEPLOYMENT CHECKLIST

### Pre-Deployment
- [ ] All tests pass
- [ ] No console errors
- [ ] No PHP errors
- [ ] No diagnostics warnings
- [ ] Code reviewed
- [ ] Changes approved

### Deployment
- [ ] Database migrations run
- [ ] Cache cleared
- [ ] Assets compiled
- [ ] Config cached
- [ ] Routes cached

### Post-Deployment
- [ ] Smoke test on production
- [ ] Check error logs
- [ ] Monitor performance
- [ ] User feedback collected

---

## 📚 REFERENCE FILES

### Templates
- `TABLE_PATTERN_TEMPLATE.blade.php` - Complete template

### Documentation
- `TABLE_STANDARDIZATION_SPEC.md` - Full specification
- `TABLE_STANDARDIZATION_SUMMARY.md` - Quick reference
- `TABLE_STANDARDIZATION_COMPLETE.md` - Completion report

### Examples
- `resources/views/purchase-orders/index.blade.php` - With tabs + KPI
- `resources/views/payments/index.blade.php` - With tabs + KPI
- `resources/views/suppliers/index.blade.php` - Simple table
- `resources/views/notifications/index.blade.php` - Card-based layout

---

## ✅ QUICK VALIDATION

Run through this quick checklist before committing:

1. **Structure**: Does it follow the 6-part structure?
2. **Classes**: Are all classes from Bootstrap/Metronic?
3. **Icons**: Are all icons Keenicons?
4. **Spacing**: Is spacing consistent (mb-7, mb-5, gap-3)?
5. **Typography**: Is typography consistent (fs-2, fs-6, fs-7)?
6. **Buttons**: Do buttons use correct variants?
7. **Badges**: Do badges use correct colors?
8. **Pagination**: Does pagination show count?
9. **Filter**: Does filter have reset button?
10. **Empty State**: Is empty state professional?

If all 10 are ✅, you're good to go! 🚀

---

**Last Updated**: April 13, 2026
**Version**: 1.0
**Status**: ✅ Complete
