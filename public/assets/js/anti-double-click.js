/**
 * Anti Double Click — Global Form Submission Guard
 * Prevents duplicate form submissions by disabling submit buttons
 * after the first click, until the page navigates or reloads.
 *
 * Works with:
 * - Standard HTML forms (type="submit")
 * - Alpine.js forms
 * - Forms with confirmation dialogs (submit-confirm, create-confirm, update-confirm)
 */

(function () {
    'use strict';

    /**
     * Disable a button and show loading state.
     */
    function disableButton(btn) {
        if (btn.dataset.submitting === 'true') return false; // already submitting

        btn.dataset.submitting = 'true';
        btn.disabled = true;

        // Store original content
        btn.dataset.originalHtml = btn.innerHTML;

        // Show spinner
        btn.innerHTML =
            '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' +
            'Memproses...';

        return true;
    }

    /**
     * Re-enable a button (used on validation failure or back navigation).
     */
    function enableButton(btn) {
        btn.dataset.submitting = 'false';
        btn.disabled = false;
        if (btn.dataset.originalHtml) {
            btn.innerHTML = btn.dataset.originalHtml;
        }
    }

    /**
     * Attach anti-double-click to all submit buttons inside a form.
     */
    function attachToForm(form) {
        // Skip filter forms (search/filter — fast, no side effects)
        if (form.classList.contains('filter-form') || form.dataset.noGuard === 'true') {
            return;
        }

        form.addEventListener('submit', function (e) {
            // If form has native validation and it fails, don't disable
            if (!form.checkValidity()) return;

            const submitBtns = form.querySelectorAll('button[type="submit"], input[type="submit"]');

            submitBtns.forEach(function (btn) {
                // Skip buttons that are already disabled by Alpine.js or other logic
                if (btn.hasAttribute('disabled') && !btn.dataset.submitting) return;

                disableButton(btn);
            });
        });
    }

    /**
     * Re-enable all buttons on popstate (browser back button).
     * This handles the case where user navigates back after submission.
     */
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) {
            document.querySelectorAll('button[data-submitting="true"]').forEach(enableButton);
        }
    });

    /**
     * Initialize on DOM ready — attach to all existing forms.
     */
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form').forEach(attachToForm);

        // Also observe dynamically added forms (e.g. Alpine.js rendered forms)
        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (node.nodeType !== 1) return;
                    if (node.tagName === 'FORM') {
                        attachToForm(node);
                    }
                    node.querySelectorAll && node.querySelectorAll('form').forEach(attachToForm);
                });
            });
        });

        observer.observe(document.body, { childList: true, subtree: true });
    });
})();
