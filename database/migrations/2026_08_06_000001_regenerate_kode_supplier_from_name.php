<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ubah seluruh kode_supplier lama (mis. SUP001) menjadi singkatan nama market
     * + nomor unik, contoh: "CV. Sumber Bangunan" -> "SB-001".
     */
    public function up(): void
    {
        $rows = DB::table('suppliers')->orderBy('id')->get(['id', 'nama_supplier']);
        $used = []; // prefix => nomor terakhir terpakai

        foreach ($rows as $row) {
            $prefix = $this->singkatanNama($row->nama_supplier) . '-';

            $nomor = ($used[$prefix] ?? 0) + 1;
            do {
                $kode = $prefix . str_pad((string) $nomor, 3, '0', STR_PAD_LEFT);
                $nomor++;
            } while (DB::table('suppliers')->where('kode_supplier', $kode)->where('id', '!=', $row->id)->exists());

            $used[$prefix] = $nomor - 1;
            DB::table('suppliers')->where('id', $row->id)->update(['kode_supplier' => $kode]);
        }
    }

    /**
     * Kode lama (SUP...) tidak disimpan, jadi migrasi tidak bisa dibatalkan otomatis.
     */
    public function down(): void
    {
        // Tidak ada operasi kebalikan yang aman.
    }

    /**
     * Buat singkatan dari nama market: huruf awal tiap kata,
     * lewati awalan badan usaha umum (CV/PT/UD/dst).
     * Contoh: "CV. Sumber Bangunan" -> "SB", "Toko Sumber Jaya" -> "TSJ".
     */
    private function singkatanNama(string $nama): string
    {
        $awalan = ['cv', 'pt', 'ud', 'pd', 'pn', 'fa', 'firma', 'perseroan', 'perusahaan'];

        $bersih = preg_replace('/\(.*?\)/', ' ', $nama);
        $kata = preg_split('/[^a-zA-Z0-9]+/', $bersih, -1, PREG_SPLIT_NO_EMPTY);
        $sing = '';

        foreach ($kata as $k) {
            $kecil = strtolower($k);
            if (in_array($kecil, $awalan, true) || !preg_match('/[a-zA-Z0-9]/', $k)) {
                continue;
            }
            $sing .= strtoupper(substr($k, 0, 1));
            if (strlen($sing) >= 3) break;
        }

        $sing = substr($sing, 0, 3);

        if (strlen($sing) < 2) {
            $sing = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $nama), 0, 3));
        }

        return $sing !== '' ? $sing : 'SUP';
    }
};
