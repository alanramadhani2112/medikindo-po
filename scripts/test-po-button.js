/**
 * Test Script: PO "Tambah Produk" Button
 * 
 * Run this in browser console to verify the fix
 */

console.log('=== PO Button Test Script ===\n');

// Test 1: Check Alpine.js
console.log('1. Checking Alpine.js...');
if (typeof window.Alpine !== 'undefined') {
    console.log('   ✅ Alpine.js loaded');
} else {
    console.error('   ❌ Alpine.js NOT loaded');
}

// Test 2: Check poForm function
console.log('\n2. Checking poForm function...');
if (typeof window.poForm === 'function') {
    console.log('   ✅ poForm function defined');
} else {
    console.error('   ❌ poForm function NOT defined');
}

// Test 3: Check form element
console.log('\n3. Checking form element...');
const formElement = document.querySelector('[x-data="poForm()"]');
if (formElement) {
    console.log('   ✅ Form element found');
    
    // Check Alpine data
    if (formElement.__x) {
        console.log('   ✅ Alpine data attached');
        console.log('   - Supplier ID:', formElement.__x.$data.supplierId);
        console.log('   - Products:', formElement.__x.$data.products.length);
        console.log('   - Items:', formElement.__x.$data.items.length);
        console.log('   - Total:', formElement.__x.$data.total);
    } else {
        console.error('   ❌ Alpine data NOT attached');
    }
} else {
    console.error('   ❌ Form element NOT found');
}

// Test 4: Check button
console.log('\n4. Checking "Tambah Produk" button...');
const button = document.querySelector('button[\\@click="addItem()"]');
if (button) {
    console.log('   ✅ Button found');
    console.log('   - Disabled:', button.disabled);
    console.log('   - Text:', button.textContent.trim());
} else {
    console.error('   ❌ Button NOT found');
}

// Test 5: Check supplier select
console.log('\n5. Checking supplier select...');
const supplierSelect = document.querySelector('select[name="supplier_id"]');
if (supplierSelect) {
    console.log('   ✅ Supplier select found');
    console.log('   - Options:', supplierSelect.options.length);
    console.log('   - Selected:', supplierSelect.value);
} else {
    console.error('   ❌ Supplier select NOT found');
}

// Test 6: Simulate button click
console.log('\n6. Simulating button click...');
if (button && formElement && formElement.__x) {
    const itemsBefore = formElement.__x.$data.items.length;
    console.log('   - Items before:', itemsBefore);
    
    try {
        // Trigger click
        button.click();
        
        setTimeout(() => {
            const itemsAfter = formElement.__x.$data.items.length;
            console.log('   - Items after:', itemsAfter);
            
            if (itemsAfter > itemsBefore) {
                console.log('   ✅ Button click successful! Item added.');
            } else {
                console.warn('   ⚠️ Button clicked but no item added (check if supplier selected)');
            }
        }, 100);
    } catch (error) {
        console.error('   ❌ Error clicking button:', error);
    }
} else {
    console.warn('   ⚠️ Cannot simulate click (missing elements)');
}

console.log('\n=== Test Complete ===');
console.log('\nTo manually test:');
console.log('1. Select a supplier from dropdown');
console.log('2. Click "Tambah Produk" button');
console.log('3. Check if new row appears in table');
console.log('4. Select a product from the new row');
console.log('5. Enter quantity');
console.log('6. Verify subtotal calculates');
