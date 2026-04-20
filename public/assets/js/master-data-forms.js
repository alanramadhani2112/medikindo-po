/**
 * Master Data Forms - Conditional Logic Handler
 * Handles dynamic form fields for Products and Users
 */

// Products Form: Toggle Narcotic Group Field
function toggleNarcoticFields() {
    const isNarcoticCheckbox = document.getElementById('is_narcotic');
    const narcoticGroupField = document.getElementById('narcotic_group_field');
    const narcoticGroupSelect = document.getElementById('narcotic_group_select');
    
    if (!isNarcoticCheckbox || !narcoticGroupField || !narcoticGroupSelect) {
        return; // Elements not found, skip
    }
    
    const isNarcotic = isNarcoticCheckbox.checked;
    
    if (isNarcotic) {
        narcoticGroupField.style.display = '';
        narcoticGroupSelect.required = true;
    } else {
        narcoticGroupField.style.display = 'none';
        narcoticGroupSelect.required = false;
        narcoticGroupSelect.value = '';
    }
}

// Users Form: Toggle Pharmacist Field
function togglePharmacistField() {
    const roleSelect = document.getElementById('role_select');
    const pharmacistField = document.getElementById('pharmacist_field');
    
    if (!roleSelect || !pharmacistField) {
        return; // Elements not found, skip
    }
    
    const role = roleSelect.value;
    
    if (role === 'Healthcare') {
        pharmacistField.style.display = '';
    } else {
        pharmacistField.style.display = 'none';
        const pharmacistCheckbox = document.getElementById('is_pharmacist');
        if (pharmacistCheckbox) {
            pharmacistCheckbox.checked = false;
        }
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    // Products Form: Narcotic checkbox handler
    const isNarcoticCheckbox = document.getElementById('is_narcotic');
    if (isNarcoticCheckbox) {
        isNarcoticCheckbox.addEventListener('change', toggleNarcoticFields);
        toggleNarcoticFields(); // Initialize on page load
    }
    
    // Users Form: Role select handler
    const roleSelect = document.getElementById('role_select');
    if (roleSelect) {
        roleSelect.addEventListener('change', togglePharmacistField);
        togglePharmacistField(); // Initialize on page load
    }
});
