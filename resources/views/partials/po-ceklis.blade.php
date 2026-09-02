{{-- Script bersama fitur ceklis "Pesan Barang Terpilih" untuk PO otomatis dari stok kritis.
     Elemen yang diharapkan ada di halaman (opsional, script aman jika tidak ada):
     - .check-item        : checkbox per barang (value = id barang)
     - #pilihSemua        : checkbox "pilih semua"
     - #jmlTerpilih       : span jumlah terpilih
     - #barangIdsTerpilih : hidden input (name=barang_ids) di form "Pesan Terpilih"
     - #btnPesanTerpilih  : tombol submit form "Pesan Terpilih" --}}
<script>
(function () {
    function kumpulTerpilih() {
        var cbs = document.querySelectorAll('.check-item');
        var ids = [], jml = 0;
        for (var i = 0; i < cbs.length; i++) {
            if (cbs[i].checked) { jml++; ids.push(cbs[i].value); }
        }
        var jmlEl = document.getElementById('jmlTerpilih');
        if (jmlEl) jmlEl.textContent = jml;
        var idsEl = document.getElementById('barangIdsTerpilih');
        if (idsEl) idsEl.value = ids.join(',');
        var btn = document.getElementById('btnPesanTerpilih');
        if (btn) btn.disabled = jml === 0;
        var semua = document.getElementById('pilihSemua');
        if (semua) semua.checked = cbs.length > 0 && document.querySelectorAll('.check-item:checked').length === cbs.length;
    }

    var pilihSemua = document.getElementById('pilihSemua');
    if (pilihSemua) {
        pilihSemua.addEventListener('change', function () {
            var cbs = document.querySelectorAll('.check-item');
            for (var i = 0; i < cbs.length; i++) cbs[i].checked = this.checked;
            kumpulTerpilih();
        });
    }

    document.addEventListener('change', function (e) {
        var t = e.target;
        if (t && t.classList && t.classList.contains('check-item')) kumpulTerpilih();
    });

    window.konfirmasiPesanTerpilih = function (e) {
        kumpulTerpilih(); // pastikan hidden barang_ids selalu sinkron dengan ceklis saat submit
        var ids = [], nama = [];
        var cbs = document.querySelectorAll('.check-item');
        for (var i = 0; i < cbs.length; i++) {
            if (cbs[i].checked) {
                ids.push(cbs[i].value);
                if (nama.length < 5) nama.push(cbs[i].getAttribute('data-nama') || cbs[i].value);
            }
        }
        if (ids.length === 0) {
            e.preventDefault();
            alert('Centang dulu barang yang ingin dipesan.');
            return false;
        }
        var daftar = nama.join(', ');
        if (ids.length > 5) daftar += ' (+' + (ids.length - 5) + ' lainnya)';
        return confirm('Pesan ' + ids.length + ' barang terpilih: ' + daftar + '? Permintaan dikirim ke admin/owner untuk disetujui. Barang yang sudah punya permintaan/PO berjalan atau tanpa supplier akan dilewati.');
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', kumpulTerpilih);
    } else {
        kumpulTerpilih();
    }
})();
</script>
