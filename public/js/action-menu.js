/**
 * Action Dropdown Menu Handler
 * Uses fixed positioning to avoid overflow:hidden clipping inside tables/cards.
 */
(function () {
    'use strict';

    var activeMenu = null;

    // ── Open / close helpers ──────────────────────────────────────────────

    function openMenu(button, menu) {
        closeAll();

        var rect = button.getBoundingClientRect();
        menu.style.position = 'fixed';
        menu.style.top      = (rect.bottom + 4) + 'px';
        menu.style.left     = 'auto';
        menu.style.right    = (window.innerWidth - rect.right) + 'px';
        menu.style.zIndex   = '9999';
        menu.classList.add('show');
        activeMenu = menu;
    }

    function closeAll() {
        document.querySelectorAll('.action-dropdown-menu.show').forEach(function (m) {
            m.classList.remove('show');
            m.style.position = '';
            m.style.top      = '';
            m.style.right    = '';
        });
        activeMenu = null;
    }

    // ── Main click handler ────────────────────────────────────────────────

    document.addEventListener('click', function (e) {

        // 1. Toggle button clicked → open/close menu
        var toggleBtn = e.target.closest('[data-action-toggle]');
        if (toggleBtn) {
            e.preventDefault();
            var menuId = toggleBtn.dataset.actionToggle;
            var menu   = document.getElementById(menuId);
            if (!menu) return;

            if (menu.classList.contains('show')) {
                closeAll();
            } else {
                openMenu(toggleBtn, menu);
            }
            return; // stop here, don't fall through
        }

        // 2. Confirm button inside dropdown → SweetAlert then submit
        var confirmBtn = e.target.closest('.action-confirm');
        if (confirmBtn) {
            var form    = confirmBtn.closest('form');
            var message = confirmBtn.dataset.confirm || 'Apakah Anda yakin?';
            if (!form) return;

            e.preventDefault();
            closeAll();

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Konfirmasi',
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, lanjutkan',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#f1416c',
                    cancelButtonColor: '#e4e6ef',
                }).then(function (result) {
                    if (result.isConfirmed) form.submit();
                });
            } else {
                if (window.confirm(message)) form.submit();
            }
            return;
        }

        // 3. Click on a link/button inside dropdown → let it work naturally, then close
        var insideMenu = e.target.closest('.action-dropdown-menu');
        if (insideMenu) {
            // Don't close immediately — let the browser follow the link/submit the form.
            // Close after a tiny delay so the action fires first.
            setTimeout(closeAll, 50);
            return;
        }

        // 4. Click outside → close
        closeAll();
    });

    // ── Close on scroll & Escape ──────────────────────────────────────────

    window.addEventListener('scroll', closeAll, true);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeAll();
    });

}());
