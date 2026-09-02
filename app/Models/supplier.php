<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = ['kode_supplier','nama_supplier','alamat','telepon','email','keterangan','is_active'];

    public function stokMasuks()
    {
        return $this->hasMany(StokMasuk::class);
    }

    // ✅ Generate kode supplier otomatis = singkatan nama market + nomor unik
    // Contoh: "CV. Sumber Bangunan" -> "SB-001", "Toko Sumber Jaya" -> "TSJ-001"
    public static function generateKode(string $nama): string
    {
        $prefix = self::abbrevNama($nama) . '-';

        $nomor = 1;
        do {
            $kode = $prefix . str_pad((string) $nomor, 3, '0', STR_PAD_LEFT);
            if (! static::where('kode_supplier', $kode)->exists()) {
                return $kode;
            }
            $nomor++;
        } while ($nomor < 9999);

        // Sangat jarang terjadi (ribuan supplier kembar) — fallback acak
        return $prefix . strtoupper(substr(uniqid(), -4));
    }

    /**
     * Buat singkatan dari nama market: huruf awal tiap kata,
     * lewati awalan badan usaha umum (CV/PT/UD/PD/Toko, dst).
     * Contoh: "CV. Sumber Bangunan" -> "SB", "Toko Sumber Jaya" -> "TSJ".
     */
    public static function abbrevNama(string $nama): string
    {
        // Awalan badan usaha (legal entity) yang tidak dijadikan singkatan,
        // mis. "CV. Sumber Bangunan" -> "SB". Kata umum seperti "Toko" tetap disertakan.
        $awalan = ['cv', 'pt', 'ud', 'pd', 'pn', 'fa', 'firma', 'perseroan', 'perusahaan'];

        $bersih = preg_replace('/\(.*?\)/', ' ', $nama);
        $kata = preg_split('/[^a-zA-Z0-9]+/', $bersih, -1, PREG_SPLIT_NO_EMPTY);
        $sing = '';

        foreach ($kata as $k) {
            $kecil = strtolower($k);
            // Lewati awalan badan usaha (mis. "CV", "Toko", "PT") dan kata pendek sisa tanda baca
            if (in_array($kecil, $awalan, true) || !preg_match('/[a-zA-Z0-9]/', $k)) {
                continue;
            }
            $sing .= strtoupper(substr($k, 0, 1));
            if (strlen($sing) >= 3) break;
        }

        $sing = substr($sing, 0, 3);

        // Nama pendek / hanya awalan badan usaha: ambil huruf awal nama
        if (strlen($sing) < 2) {
            $sing = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $nama), 0, 3));
        }

        return $sing !== '' ? $sing : 'SUP';
    }
}