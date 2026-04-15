// Action Menu Handler - uses fixed positioning to avoid overflow:hidden clipping
document.addEventListener('DOMContentLoaded', function () {

    // Track currently open menu
    let activeMenu = null;

    document.addEventListener('click', function (e) {
        const button = e.target.closest('[data-action-menu]');

        // Clicked outside - close
        if (!button) {
            if (!e.target.closest('.action-dropdown-menu')) {
                closeAll();
            }
            return;
        }

        e.preventDefault();
        e.stopPropagation();

        const wrapper = button.closest('.action-menu-wrapper');
        const menu = wrapper ? wrapper.querySelector('.action-dropdown-menu') : null;
        if (!menu) return;

        const isOpen = menu.classList.contains('show');
        closeAll();

        if (!isOpen) {
            openMenu(button, menu);
        }
    });

    function openMenu(button, menu) {
        const rect = button.getBoundingClientRect();

        // Use fixed positioning so overflow:hidden on table/card doesn't clip it
        menu.style.position = 'fixed';
        menu.style.top = (rect.bottom + 4) + 'px';
        menu.style.left = 'auto';
        menu.style.right = (window.innerWidth - rect.right) + 'px';
        menu.style.zIndex = '9999';

        menu.classList.add('show');
        activeMenu = menu;
    }

    function closeAll() {
        document.querySelectorAll('.action-dropdown-menu.show').forEach(function (m) {
            m.classList.remove('show');
            m.style.display = 'none';
            m.style.position = '';
            m.style.top = '';
            m.style.right = '';
        });
        activeMenu = null;
    }

    // Also close on scroll (since fixed position won't follow scroll)
    window.addEventListener('scroll', closeAll, true);
});
