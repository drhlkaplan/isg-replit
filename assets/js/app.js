'use strict';

document.addEventListener('DOMContentLoaded', function () {
    /* Auto-dismiss flash alerts after 5 seconds */
    document.querySelectorAll('.alert-dismissible').forEach(function (el) {
        setTimeout(function () {
            var bsAlert = bootstrap.Alert.getOrCreateInstance(el);
            if (bsAlert) bsAlert.close();
        }, 5000);
    });

    /* Confirm on delete buttons */
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!confirm(el.dataset.confirm || 'Emin misiniz?')) e.preventDefault();
        });
    });

    /* TC No mask — only digits, max 11 */
    document.querySelectorAll('input[name="tc_identity_no"]').forEach(function (el) {
        el.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').substring(0, 11);
        });
    });

    /* Phone mask */
    document.querySelectorAll('input[name="phone"]').forEach(function (el) {
        el.addEventListener('input', function () {
            this.value = this.value.replace(/[^\d\s\-\+\(\)]/g, '');
        });
    });

    /* Progress bar fill animation on load */
    document.querySelectorAll('.progress-bar[data-pct]').forEach(function (bar) {
        setTimeout(function () {
            bar.style.width = bar.dataset.pct + '%';
        }, 100);
    });
});
