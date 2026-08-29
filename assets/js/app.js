$(function () {
    // Shortcut global: tekan "n" di mana saja (di luar input/textarea) untuk buka Transaksi Baru
    $(document).on('keydown', function (e) {
        var tag = (e.target.tagName || '').toLowerCase();
        var typing = tag === 'input' || tag === 'textarea' || tag === 'select' || e.target.isContentEditable;

        if (!typing && e.key === 'n') {
            var quickAddBtn = document.getElementById('btn-transaksi-baru');
            if (quickAddBtn) {
                e.preventDefault();
                window.location.href = quickAddBtn.getAttribute('href');
            }
        }
    });

    // Auto-focus ke elemen pertama yang punya atribut data-autofocus (dipakai di form input cepat)
    var autoFocusEl = document.querySelector('[data-autofocus]');
    if (autoFocusEl) {
        autoFocusEl.focus();
    }
});
