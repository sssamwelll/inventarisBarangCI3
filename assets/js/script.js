
            // ========== SCRIPT MODUL TRANSAKSI ==========
$(function () {
    var TABEL_DETAIL = document.getElementById('tabel-detail-transaksi');

    function inisialisasiPencarianBarang(row) {
        var searchInput = row.querySelector('.barang-search-input');
        var selectAsli = row.querySelector('.barang-select');

        if (!searchInput || !selectAsli || searchInput.dataset.acInit) {
            return; // elemen tidak ada, atau sudah pernah di-init sebelumnya
        }
        searchInput.dataset.acInit = '1';

        $(searchInput).autocomplete({
            source: function (request, response) {
                var kata = request.term.toLowerCase();
                var hasil = DAFTAR_BARANG.filter(function (b) {
                    return b.nama.toLowerCase().indexOf(kata) !== -1;
                });
                response(hasil.slice(0, 15));
            },
            minLength: 1,
            select: function (event, ui) {
                selectAsli.value = ui.item.id;
                // Trigger event 'change' PERSIS seperti kalau admin pilih manual dari <select>,
                // supaya semua logika lama (isi harga, hitung subtotal, cek harga langganan)
                // tetap jalan tanpa perlu ditulis ulang di sini.
                selectAsli.dispatchEvent(new Event('change', { bubbles: true }));
                searchInput.value = ui.item.label;
                return false;
            }
        });

        // Kalau baris ini sudah ada isinya (misal form gagal validasi & dirender ulang),
        // sinkronkan teks pencarian dengan barang yang sudah kepilih sebelumnya.
        if (selectAsli.value) {
            var terpilih = DAFTAR_BARANG.find(function (b) {
                return String(b.id) === String(selectAsli.value);
            });
            if (terpilih) {
                searchInput.value = terpilih.label;
            }
        }
    }

    function inisialisasiSemuaBaris() {
        TABEL_DETAIL.querySelectorAll('.detail-row').forEach(inisialisasiPencarianBarang);
    }

    inisialisasiSemuaBaris();

    // Baris baru dari tombol "Tambah baris" otomatis ikut dapat fitur pencarian,
    // tanpa perlu tahu/ubah kode yang bikin baris barunya.
    new MutationObserver(inisialisasiSemuaBaris).observe(TABEL_DETAIL, {
        childList: true,
        subtree: true
    });
});

// <!-- Daftar Barang --- -->
(function () {
    var tableBody = document.querySelector('#tabel-detail-transaksi tbody');
    var addRowButton = document.getElementById('btn-tambah-baris');

    function getTipeValue() {
        var checked = document.querySelector('input[name="tipe"]:checked');
        return checked ? checked.value : 'beli';
    }
    
    var template = document.getElementById('template-baris-detail');

    function formatRupiah(number) {
        var value = Number(number || 0);
        return 'Rp ' + value.toLocaleString('id-ID', { maximumFractionDigits: 2 });
    }

    function getSelectedPrice(row) {
        var select = row.querySelector('.barang-select');
        var selected = select.options[select.selectedIndex];
        if (!selected) {
            return 0;
        }

        var hargaBeli = parseFloat(selected.dataset.hargaBeli || '0') || 0;
        var hargaJual = parseFloat(selected.dataset.hargaJual || '0') || 0;
        return getTipeValue() === 'jual' ? hargaJual : hargaBeli;
    }

    function recalculateRow(row) {
        var qtyInput = row.querySelector('.qty-input');
        var hargaInput = row.querySelector('.harga-input');
        var subtotalOutput = row.querySelector('.subtotal-output');
        var qty = parseFloat(qtyInput.value || '0') || 0;
        var harga = parseFloat(hargaInput.value || '0') || 0;
        var subtotal = qty * harga;

        subtotalOutput.value = formatRupiah(subtotal);
        return subtotal;
    }

    function recalculateAll() {
        var total = 0;
        tableBody.querySelectorAll('.detail-row').forEach(function (row) {
            total += recalculateRow(row);
        });

        document.getElementById('grand-total').textContent = formatRupiah(total);
        document.getElementById('tfoot-total').textContent = formatRupiah(total);
    }

    function bindRow(row) {
        var select = row.querySelector('.barang-select');
        var qtyInput = row.querySelector('.qty-input');
        var hargaInput = row.querySelector('.harga-input');
        var removeButton = row.querySelector('.btn-hapus-baris');

        select.addEventListener('change', function () {
            hargaInput.value = getSelectedPrice(row) || '';
            recalculateAll();
        });

        qtyInput.addEventListener('input', recalculateAll);
        hargaInput.addEventListener('input', recalculateAll);

        removeButton.addEventListener('click', function () {
            if (tableBody.querySelectorAll('.detail-row').length === 1) {
                select.value = '';
                qtyInput.value = '';
                hargaInput.value = '';
                recalculateAll();
                return;
            }

            row.remove();
            recalculateAll();
        });
    }

    function createRow() {
        var row = template.content.firstElementChild.cloneNode(true);
        bindRow(row);
        return row;
    }

    addRowButton.addEventListener('click', function () {
        tableBody.appendChild(createRow());
    });

    tableBody.querySelectorAll('.detail-row').forEach(function (row) {
        bindRow(row);
    });

    document.querySelectorAll('input[name="tipe"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            tableBody.querySelectorAll('.detail-row').forEach(function (row) {
                var select = row.querySelector('.barang-select');
                var hargaInput = row.querySelector('.harga-input');
                if (select.value) {
                    hargaInput.value = getSelectedPrice(row) || hargaInput.value || '';
                }
            });
            recalculateAll();
        });
    });

    recalculateAll();
})();

// Potongan
(function () {
    var inputPotongan = document.getElementById('input-potongan');
    var elGrandTotal = document.getElementById('grand-total');
    var elTampilPotongan = document.getElementById('tampil-potongan');
    var elBarisPotongan = document.getElementById('baris-potongan');
    var elTotalBersih = document.getElementById('total-bersih');

    function formatRupiahSimple(n) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n));
    }

    // "Rp 12.345" -> 12345 (buang semua karakter selain digit)
    function parseRupiahText(text) {
        var digits = (text || '').replace(/[^0-9]/g, '');
        return digits ? parseInt(digits, 10) : 0;
    }

    function perbaruiTotalBersih() {
        var totalBarang = parseRupiahText(elGrandTotal.textContent);
        var potongan = parseFloat(inputPotongan.value) || 0;

        if (potongan > totalBarang) {
            potongan = totalBarang; // tidak boleh sampai minus
        }

        var bersih = totalBarang - potongan;

        elTampilPotongan.textContent = '- ' + formatRupiahSimple(potongan);
        elBarisPotongan.style.display = potongan > 0 ? 'flex' : 'none';
        elTotalBersih.textContent = formatRupiahSimple(bersih);
    }

    inputPotongan.addEventListener('input', perbaruiTotalBersih);

    // "Total barang" (grand-total) diisi oleh script lama Anda (recalculateAll()) setiap
    // baris barang berubah. Daripada mengubah kode lama, kita "pantau" perubahan teksnya
    // pakai MutationObserver, lalu hitung ulang total bersih otomatis.
    new MutationObserver(perbaruiTotalBersih).observe(elGrandTotal, {
        childList: true,
        characterData: true,
        subtree: true
    });

    perbaruiTotalBersih();
})();

// <!-- Script Cegat DOM enrter -->
(function () {
    var form = document.getElementById('form-transaksi');

    form.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') {
            return;
        }

        // Kalau Enter ini sudah ditangani duluan sama widget lain (misal jQuery UI
        // autocomplete lagi milih salah satu saran dari dropdown), biarkan -- jangan diganggu.
        if (e.defaultPrevented) {
            return;
        }

        // Enter di tombol/link tetap boleh jalan normal (misal tombol "Tambah baris").
        if (e.target.tagName === 'BUTTON' || e.target.tagName === 'A') {
            return;
        }

        e.preventDefault();

        var modalNota = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalNota'));
        modalNota.show();
    });
})();

// Sciprt Modal Button
(function () {
    var form = document.getElementById('form-transaksi');
    var modalNotaEl = document.getElementById('modalNota');
    var modalNota = bootstrap.Modal.getOrCreateInstance(modalNotaEl);
    var submitBtn = modalNotaEl.querySelector('button[type="submit"]');
    var teksAsliTombol = submitBtn.innerHTML;

    function tampilkanNotifikasi(pesan, tipe) {
        var area = document.getElementById('area-notifikasi');
        if (!area) return;

        area.innerHTML =
            '<div class="alert alert-' + tipe + ' border-0 alert-dismissible fade show" role="alert">' +
                pesan +
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
            '</div>';

        area.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function aturLoading(loading) {
        submitBtn.disabled = loading;
        submitBtn.innerHTML = loading
            ? '<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...'
            : teksAsliTombol;
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        aturLoading(true);

        $.ajax({
            url: URL_TRANSAKSI_SIMPAN,
            method: 'POST',
            data: $(form).serialize(),
            dataType: 'json'
        })
        .done(function (res) {
            modalNota.hide();
            window.open(res.cetak_url, '_blank');
            // Reload dari server -- cara paling aman untuk mengosongkan form + semua
            // baris dinamis + status autocomplete, tanpa perlu bongkar satu-satu
            // logika createRow()/bindRow() yang sudah ada.
            window.location.href = URL_SETELAH_SUKSES;
        })
        .fail(function (xhr) {
            modalNota.hide();
            var pesan = 'Terjadi kesalahan saat menyimpan transaksi. Silakan coba lagi.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                pesan = xhr.responseJSON.message;
            }
            tampilkanNotifikasi(pesan, 'danger');
            aturLoading(false);
        });
    });
})();


            // ========== SCRIPT MODUL BARANG ==========
