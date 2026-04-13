// Alpine.js Debug Helper
// Check if Alpine is loaded and working

document.addEventListener('DOMContentLoaded', function() {
    console.log('=== Alpine.js Debug ===');
    console.log('Alpine loaded:', typeof window.Alpine !== 'undefined');
    console.log('poForm function defined:', typeof window.poForm === 'function');
    
    // Wait for Alpine to initialize
    setTimeout(() => {
        console.log('Alpine after init:', window.Alpine);
        
        // Check if x-data is working
        const poFormElement = document.querySelector('[x-data="poForm()"]');
        if (poFormElement) {
            console.log('PO Form element found:', poFormElement);
            console.log('Alpine data:', poFormElement.__x);
        } else {
            console.error('PO Form element NOT found!');
        }
    }, 1000);
});
