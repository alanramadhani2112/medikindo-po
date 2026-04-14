<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Diagnostic Page</title>
    
    <link href="{{ asset('assets/metronic8/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/metronic8/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    
    <style>
        body { padding: 20px; font-family: Inter, sans-serif; }
        .test-card { 
            background: white; 
            border: 1px solid #e1e5e9; 
            border-radius: 8px; 
            padding: 20px; 
            margin: 20px 0; 
        }
        .success { color: #50cd89; }
        .error { color: #f1416c; }
    </style>
</head>
<body>
    <h1>Layout Diagnostic Page</h1>
    
    <div class="test-card">
        <h3>Asset Loading Test</h3>
        <p>CSS Bundle: <span id="css-status" class="error">Not Loaded</span></p>
        <p>JS Bundle: <span id="js-status" class="error">Not Loaded</span></p>
        <p>Metronic Components: <span id="metronic-status" class="error">Not Available</span></p>
    </div>
    
    <div class="test-card">
        <h3>Bootstrap Components Test</h3>
        <div class="alert alert-primary" role="alert">
            <i class="ki-duotone ki-information-5 fs-2 me-3"></i>
            This is a primary alert with Keenicons
        </div>
        <button class="btn btn-primary me-2">Primary Button</button>
        <button class="btn btn-success me-2">Success Button</button>
        <button class="btn btn-danger">Danger Button</button>
    </div>
    
    <div class="test-card">
        <h3>Keenicons Test</h3>
        <p>
            <i class="ki-duotone ki-home fs-1 me-2"></i>
            <i class="ki-duotone ki-user fs-1 me-2"></i>
            <i class="ki-duotone ki-document fs-1 me-2"></i>
            <i class="ki-duotone ki-check-circle fs-1 me-2"></i>
        </p>
    </div>
    
    <script src="{{ asset('assets/metronic8/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/metronic8/js/scripts.bundle.js') }}"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check CSS loading
            const testEl = document.createElement('div');
            testEl.className = 'btn btn-primary';
            testEl.style.display = 'none';
            document.body.appendChild(testEl);
            
            const styles = window.getComputedStyle(testEl);
            if (styles.backgroundColor !== 'rgba(0, 0, 0, 0)') {
                document.getElementById('css-status').textContent = 'Loaded';
                document.getElementById('css-status').className = 'success';
            }
            
            // Check JS loading
            if (typeof $ !== 'undefined') {
                document.getElementById('js-status').textContent = 'Loaded';
                document.getElementById('js-status').className = 'success';
            }
            
            // Check Metronic components
            if (typeof KTApp !== 'undefined') {
                document.getElementById('metronic-status').textContent = 'Available';
                document.getElementById('metronic-status').className = 'success';
                
                // Initialize Metronic
                if (typeof KTApp.init === 'function') {
                    KTApp.init();
                }
            }
            
            document.body.removeChild(testEl);
        });
    </script>
</body>
</html>