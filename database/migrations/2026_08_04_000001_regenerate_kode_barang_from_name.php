<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ubah seluruh kode_barang lama (mis. BRG001) menjadi singkatan nama barang
     * + nomor unik, contoh: "Semen Portland 50kg" -> "SP50-001".
     */
    public function up(): void
    {
        $rows = DB::table('barangs')->orderBy('id')->get(['id', 'nama_barang']);
        $used = []; // prefix => nomor terakhir terpakai

        foreach ($rows as $row) {
            $prefix = $this->singkatanNama($row->nama_barang) . '-';

            $nomor = ($used[$prefix] ?? 0) + 1;
            do {
                $kode = $prefix . str_pad((string) $nomor, 3, '0', STR_PAD_LEFT);
                $nomor++;
            } while (DB::table('barangs')->where('kode_barang', $kode)->where('id', '!=', $row->id)->exists());

            $used[$prefix] = $nomor - 1;
            DB::table('barangs')->where('id', $row->id)->update(['kode_barang' => $kode]);
        }
    }

    /**
     * Kode lama (BRG...) tidak disimpan, jadi migrasi tidak bisa dibatalkan otomatis.
     */
    public function down(): void
    {
        // Tidak ada operasi kebalikan yang aman.
    }

    /**
     * Buat singkatan dari nama barang: huruf awal tiap kata + angka ukuran.
     * Contoh: "Semen Portland 50kg" -> "SP50", "Besi Beton 10mm" -> "BB10".
     */
    private function singkatanNama(string $nama): string
    {
        $bersih = preg_replace('/\(.*?\)/', ' ', $nama);
        $kata = preg_split('/[^a-zA-Z0-9]+/', $bersih, -1, PREG_SPLIT_NO_EMPTY);
        $sing = '';

        foreach ($kata as $k) {
            if (preg_match('/\d/', $k)) {
                // Kata berisi angka: ambil angkanya + huruf pertama sisanya ("50kg" -> "50K")
                $sing .= preg_replace('/\D/', '', $k)
                      . strtoupper(substr(preg_replace('/\d/', '', $k), 0, 1));
            } else {
                $sing .= strtoupper(substr($k, 0, 1));
            }
            if (strlen($sing) >= 4) break;
        }

        $sing = substr($sing, 0, 4);

        // Nama pendek (1 kata tanpa angka): ambil 4 huruf pertama
        if (strlen($sing) < 2) {
            $sing = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $nama), 0, 4));
        }

        return $sing !== '' ? $sing : 'XXX';
    }
};
