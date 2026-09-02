<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\StokMasukDetail;
use App\Models\StokKeluarDetail;

class Barang extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'kategori_id',
        'satuan',
        'harga_beli',
        'harga_jual',
        'stok_saat_ini',
        'stok_minimum',
        'stok_maksimum',
        'lokasi_rak',
        'deskripsi',
        'preferred_supplier_id',
        'harga_beli_terakhir',
        'harga_jual_terakhir',
    ];

    // ✅ Generate kode barang otomatis = singkatan nama barang + nomor unik
    // Contoh: "Semen Portland 50kg" -> "SP50-001", "Besi Beton 10mm" -> "BB10-001"
    public static function generateKode(string $namaBarang): string
    {
        $prefix = self::abbrevNama($namaBarang) . '-';

        $nomor = 1;
        do {
            $kode = $prefix . str_pad((string) $nomor, 3, '0', STR_PAD_LEFT);
            if (! static::where('kode_barang', $kode)->exists()) {
                return $kode;
            }
            $nomor++;
        } while ($nomor < 9999);

        // Sangat jarang terjadi (ribuan barang kembar) — fallback acak
        return $prefix . strtoupper(substr(uniqid(), -4));
    }

    /**
     * Buat singkatan dari nama barang: huruf awal tiap kata + angka ukuran.
     * Contoh: "Semen Portland 50kg" -> "SP50", "Besi Beton 10mm" -> "BB10".
     */
    public static function abbrevNama(string $nama): string
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

    // Relasi ke Kategori
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    // Relasi ke Preferred Supplier
    public function preferredSupplier()
    {
        return $this->belongsTo(Supplier::class, 'preferred_supplier_id');
    }

    // Relasi Many-to-Many ke Supplier via pivot
    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'barang_supplier')
            ->withPivot(['harga_beli_terakhir', 'is_preferred'])
            ->withTimestamps();
    }

    // Relasi ke StokMasuk
    public function stokMasukDetails()
    {
        return $this->hasMany(StokMasukDetail::class, 'barang_id');
    }

    public function stokKeluarDetails()
    {
        return $this->hasMany(StokKeluarDetail::class, 'barang_id');
    }

    // Auto-detect status stok
    public function getStatusStokAttribute(): string
    {
        if ($this->stok_saat_ini <= 0) return 'habis';
        if ($this->stok_saat_ini <= $this->stok_minimum) return 'menipis';
        if ($this->stok_maksimum > 0 && $this->stok_saat_ini >= $this->stok_maksimum) return 'overstock';
        return 'normal';
    }

    // Dapatkan supplier rekomendasi untuk restok
    public function getSupplierRekomendasiAttribute()
    {
        if ($this->preferredSupplier) {
            return $this->preferredSupplier;
        }
        // Ambil supplier yang paling sering supply barang ini
        $topSupplier = \App\Models\StokMasukDetail::where('barang_id', $this->id)
            ->join('stok_masuks', 'stok_masuk_details.stok_masuk_id', '=', 'stok_masuks.id')
            ->selectRaw('stok_masuks.supplier_id, COUNT(*) as total')
            ->whereNotNull('stok_masuks.supplier_id')
            ->groupBy('stok_masuks.supplier_id')
            ->orderByDesc('total')
            ->first();

        if ($topSupplier) {
            return Supplier::find($topSupplier->supplier_id);
        }

        return null;
    }
}
