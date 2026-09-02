<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Kategori;
use App\Models\Barang;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\PoDetail;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Users
        User::create([
            'name'     => 'Administrator',
            'email'    => 'admin@mitramandiri.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);
        User::create([
            'name'     => 'Gudang',
            'email'    => 'gudang@mitramandiri.com',
            'password' => Hash::make('password'),
            'role'     => 'gudang',
        ]);
        User::create([
            'name'     => 'Kasir',
            'email'    => 'kasir@mitramandiri.com',
            'password' => Hash::make('password'),
            'role'     => 'kasir',
        ]);

        // Kategori
        $kategoriData = [
            ['nama_kategori' => 'Material Dasar',     'deskripsi' => 'Semen, pasir, batu bata, dll'],
            ['nama_kategori' => 'Besi & Baja',        'deskripsi' => 'Besi beton, hollow, dll'],
            ['nama_kategori' => 'Kayu & Triplek',     'deskripsi' => 'Kayu balok, multiplek, dll'],
            ['nama_kategori' => 'Atap & Plafon',      'deskripsi' => 'Genteng, spandek, gypsum, dll'],
            ['nama_kategori' => 'Cat & Finishing',    'deskripsi' => 'Cat tembok, pelapis, dll'],
            ['nama_kategori' => 'Keramik & Granit',   'deskripsi' => 'Keramik lantai, dinding, dll'],
            ['nama_kategori' => 'Pipa & Sanitasi',    'deskripsi' => 'Pipa PVC, fitting, kloset, dll'],
            ['nama_kategori' => 'Alat & Aksesoris',   'deskripsi' => 'Paku, sekrup, engsel, dll'],
        ];
        foreach ($kategoriData as $k) {
            Kategori::create($k);
        }

        // Supplier
        $supplierData = [
            ['kode_supplier' => 'SUP001', 'nama_supplier' => 'CV. Sumber Bangunan',    'telepon' => '022-1234567',  'alamat' => 'Bandung'],
            ['kode_supplier' => 'SUP002', 'nama_supplier' => 'PT. Material Jaya',      'telepon' => '022-7654321',  'alamat' => 'Bandung Barat'],
            ['kode_supplier' => 'SUP003', 'nama_supplier' => 'UD. Maju Bersama',       'telepon' => '08123456789', 'alamat' => 'Cimahi'],
        ];
        foreach ($supplierData as $s) {
            Supplier::create($s);
        }

        // Barang
        $barangData = [
            ['nama_barang'=>'Semen Portland 50kg',        'kategori_id'=>1,'satuan'=>'sak',  'stok_saat_ini'=>150,'stok_minimum'=>20,'stok_maksimum'=>300,'harga_beli'=>60000, 'harga_jual'=>68000],
            ['nama_barang'=>'Pasir Beton (per kubik)',     'kategori_id'=>1,'satuan'=>'m3',   'stok_saat_ini'=>30, 'stok_minimum'=>5, 'stok_maksimum'=>80, 'harga_beli'=>250000,'harga_jual'=>300000],
            ['nama_barang'=>'Batu Bata Merah',            'kategori_id'=>1,'satuan'=>'pcs',  'stok_saat_ini'=>5000,'stok_minimum'=>500,'stok_maksimum'=>10000,'harga_beli'=>700,  'harga_jual'=>900],
            ['nama_barang'=>'Besi Beton 10mm (6m)',       'kategori_id'=>2,'satuan'=>'btg',  'stok_saat_ini'=>200,'stok_minimum'=>30,'stok_maksimum'=>500,'harga_beli'=>45000, 'harga_jual'=>52000],
            ['nama_barang'=>'Besi Beton 8mm (6m)',        'kategori_id'=>2,'satuan'=>'btg',  'stok_saat_ini'=>180,'stok_minimum'=>30,'stok_maksimum'=>400,'harga_beli'=>32000, 'harga_jual'=>38000],
            ['nama_barang'=>'Multiplek 9mm 122x244',      'kategori_id'=>3,'satuan'=>'lbr',  'stok_saat_ini'=>60, 'stok_minimum'=>10,'stok_maksimum'=>150,'harga_beli'=>95000, 'harga_jual'=>110000],
            ['nama_barang'=>'Genteng Keramik Flat',       'kategori_id'=>4,'satuan'=>'pcs',  'stok_saat_ini'=>800,'stok_minimum'=>100,'stok_maksimum'=>2000,'harga_beli'=>5500,  'harga_jual'=>7000],
            ['nama_barang'=>'Cat Tembok 25kg Putih',      'kategori_id'=>5,'satuan'=>'galon','stok_saat_ini'=>40, 'stok_minimum'=>5, 'stok_maksimum'=>100,'harga_beli'=>185000,'harga_jual'=>220000],
            ['nama_barang'=>'Keramik Lantai 40x40 Putih','kategori_id'=>6,'satuan'=>'dos',  'stok_saat_ini'=>120,'stok_minimum'=>20,'stok_maksimum'=>300,'harga_beli'=>65000, 'harga_jual'=>80000],
            ['nama_barang'=>'Pipa PVC 4 inch (4m)',       'kategori_id'=>7,'satuan'=>'btg',  'stok_saat_ini'=>15, 'stok_minimum'=>10,'stok_maksimum'=>80, 'harga_beli'=>55000, 'harga_jual'=>68000],
            ['nama_barang'=>'Batu Bata Merah',             'kategori_id'=>1,'satuan'=>'pcs',  'stok_saat_ini'=>5000,'stok_minimum'=>500,'stok_maksimum'=>10000,'harga_beli'=>700,  'harga_jual'=>900],
            ['nama_barang'=>'Batu Split (per kubik)',      'kategori_id'=>1,'satuan'=>'m3',   'stok_saat_ini'=>20, 'stok_minimum'=>5,  'stok_maksimum'=>60,  'harga_beli'=>320000,'harga_jual'=>380000],
            ['nama_barang'=>'Batu Kali (per kubik)',       'kategori_id'=>1,'satuan'=>'m3',   'stok_saat_ini'=>15, 'stok_minimum'=>5,  'stok_maksimum'=>50,  'harga_beli'=>300000,'harga_jual'=>350000],
            ['nama_barang'=>'Semen Putih 40kg',            'kategori_id'=>1,'satuan'=>'sak',  'stok_saat_ini'=>80, 'stok_minimum'=>10, 'stok_maksimum'=>200, 'harga_beli'=>85000, 'harga_jual'=>98000],
            ['nama_barang'=>'Kapur Sirih',                 'kategori_id'=>1,'satuan'=>'karung','stok_saat_ini'=>100,'stok_minimum'=>20, 'stok_maksimum'=>300, 'harga_beli'=>25000, 'harga_jual'=>32000],
            ['nama_barang'=>'Bata Ringan AAC 7.5cm',       'kategori_id'=>1,'satuan'=>'pcs',  'stok_saat_ini'=>3000,'stok_minimum'=>300,'stok_maksimum'=>8000, 'harga_beli'=>6500,  'harga_jual'=>7500],
            ['nama_barang'=>'Mortar Perekat Hebel 40kg',   'kategori_id'=>1,'satuan'=>'sak',  'stok_saat_ini'=>120,'stok_minimum'=>20, 'stok_maksimum'=>250, 'harga_beli'=>52000, 'harga_jual'=>60000],
            ['nama_barang'=>'Besi Beton 12mm (6m)',        'kategori_id'=>2,'satuan'=>'btg',  'stok_saat_ini'=>150,'stok_minimum'=>30, 'stok_maksimum'=>400, 'harga_beli'=>65000, 'harga_jual'=>72000],
            ['nama_barang'=>'Besi Beton 6mm (6m)',         'kategori_id'=>2,'satuan'=>'btg',  'stok_saat_ini'=>200,'stok_minimum'=>40, 'stok_maksimum'=>500, 'harga_beli'=>18000, 'harga_jual'=>22000],
            ['nama_barang'=>'Besi Hollow 4x4 1.2mm',       'kategori_id'=>2,'satuan'=>'btg',  'stok_saat_ini'=>100,'stok_minimum'=>15, 'stok_maksimum'=>250, 'harga_beli'=>45000, 'harga_jual'=>52000],
            ['nama_barang'=>'Besi Siku 3x3 (6m)',          'kategori_id'=>2,'satuan'=>'btg',  'stok_saat_ini'=>60, 'stok_minimum'=>10, 'stok_maksimum'=>150, 'harga_beli'=>55000, 'harga_jual'=>62000],
            ['nama_barang'=>'Wiremesh M8 2.1x5.4m',        'kategori_id'=>2,'satuan'=>'lbr',  'stok_saat_ini'=>30, 'stok_minimum'=>5,  'stok_maksimum'=>80,  'harga_beli'=>420000,'harga_jual'=>480000],
            ['nama_barang'=>'Kawat Bendrat 1kg',           'kategori_id'=>2,'satuan'=>'kg',   'stok_saat_ini'=>300,'stok_minimum'=>30, 'stok_maksimum'=>800, 'harga_beli'=>18000, 'harga_jual'=>22000],
            ['nama_barang'=>'Kayu Balok 5x7 (4m)',         'kategori_id'=>3,'satuan'=>'btg',  'stok_saat_ini'=>200,'stok_minimum'=>40, 'stok_maksimum'=>500, 'harga_beli'=>55000, 'harga_jual'=>65000],
            ['nama_barang'=>'Kayu Kaso 4x6 (4m)',          'kategori_id'=>3,'satuan'=>'btg',  'stok_saat_ini'=>400,'stok_minimum'=>80, 'stok_maksimum'=>1000, 'harga_beli'=>25000, 'harga_jual'=>30000],
            ['nama_barang'=>'Triplek 3mm 122x244',         'kategori_id'=>3,'satuan'=>'lbr',  'stok_saat_ini'=>80, 'stok_minimum'=>15, 'stok_maksimum'=>200, 'harga_beli'=>45000, 'harga_jual'=>55000],
            ['nama_barang'=>'Triplek 12mm 122x244',        'kategori_id'=>3,'satuan'=>'lbr',  'stok_saat_ini'=>50, 'stok_minimum'=>10, 'stok_maksimum'=>150, 'harga_beli'=>120000,'harga_jual'=>140000],
            ['nama_barang'=>'Papan Cor 2x20 (4m)',         'kategori_id'=>3,'satuan'=>'lbr',  'stok_saat_ini'=>100,'stok_minimum'=>20, 'stok_maksimum'=>250, 'harga_beli'=>40000, 'harga_jual'=>48000],
            ['nama_barang'=>'Atap Spandek 0.3mm (6m)',     'kategori_id'=>4,'satuan'=>'lbr',  'stok_saat_ini'=>100,'stok_minimum'=>15, 'stok_maksimum'=>250, 'harga_beli'=>95000, 'harga_jual'=>110000],
            ['nama_barang'=>'Genteng Metal Pasir',         'kategori_id'=>4,'satuan'=>'pcs',  'stok_saat_ini'=>500,'stok_minimum'=>50, 'stok_maksimum'=>1500, 'harga_beli'=>25000, 'harga_jual'=>30000],
            ['nama_barang'=>'Gypsum Aplus 9mm',            'kategori_id'=>4,'satuan'=>'lbr',  'stok_saat_ini'=>150,'stok_minimum'=>20, 'stok_maksimum'=>400, 'harga_beli'=>45000, 'harga_jual'=>55000],
            ['nama_barang'=>'Hollow Gypsum 4x4',           'kategori_id'=>4,'satuan'=>'btg',  'stok_saat_ini'=>200,'stok_minimum'=>30, 'stok_maksimum'=>500, 'harga_beli'=>28000, 'harga_jual'=>34000],
            ['nama_barang'=>'Nok Genteng Keramik',         'kategori_id'=>4,'satuan'=>'pcs',  'stok_saat_ini'=>200,'stok_minimum'=>30, 'stok_maksimum'=>600, 'harga_beli'=>12000, 'harga_jual'=>15000],
            ['nama_barang'=>'Cat Tembok 5kg (Dulux)',      'kategori_id'=>5,'satuan'=>'kaleng','stok_saat_ini'=>60, 'stok_minimum'=>10, 'stok_maksimum'=>150, 'harga_beli'=>95000, 'harga_jual'=>115000],
            ['nama_barang'=>'Cat Kayu & Besi 1kg',         'kategori_id'=>5,'satuan'=>'kaleng','stok_saat_ini'=>80, 'stok_minimum'=>15, 'stok_maksimum'=>200, 'harga_beli'=>35000, 'harga_jual'=>45000],
            ['nama_barang'=>'Plamir Tembok 1kg',           'kategori_id'=>5,'satuan'=>'kg',   'stok_saat_ini'=>150,'stok_minimum'=>20, 'stok_maksimum'=>400, 'harga_beli'=>18000, 'harga_jual'=>23000],
            ['nama_barang'=>'Thinner A 1L',                'kategori_id'=>5,'satuan'=>'liter','stok_saat_ini'=>100,'stok_minimum'=>20, 'stok_maksimum'=>300, 'harga_beli'=>15000, 'harga_jual'=>20000],
            ['nama_barang'=>'Kuas Cat 3 inch',             'kategori_id'=>5,'satuan'=>'pcs',  'stok_saat_ini'=>200,'stok_minimum'=>30, 'stok_maksimum'=>500, 'harga_beli'=>8000,  'harga_jual'=>12000],
            ['nama_barang'=>'Roll Cat 9 inch',             'kategori_id'=>5,'satuan'=>'pcs',  'stok_saat_ini'=>150,'stok_minimum'=>20, 'stok_maksimum'=>400, 'harga_beli'=>15000, 'harga_jual'=>22000],
            ['nama_barang'=>'Keramik Dinding 25x40 Putih', 'kategori_id'=>6,'satuan'=>'dos',  'stok_saat_ini'=>90, 'stok_minimum'=>15, 'stok_maksimum'=>250, 'harga_beli'=>75000, 'harga_jual'=>90000],
            ['nama_barang'=>'Granit 60x60 (per dos)',      'kategori_id'=>6,'satuan'=>'dos',  'stok_saat_ini'=>40, 'stok_minimum'=>5,  'stok_maksimum'=>100, 'harga_beli'=>180000,'harga_jual'=>210000],
            ['nama_barang'=>'Keramik 30x30 Abu',           'kategori_id'=>6,'satuan'=>'dos',  'stok_saat_ini'=>100,'stok_minimum'=>20, 'stok_maksimum'=>250, 'harga_beli'=>65000, 'harga_jual'=>78000],
            ['nama_barang'=>'Granit 80x80 Polos (per dos)','kategori_id'=>6,'satuan'=>'dos',  'stok_saat_ini'=>25, 'stok_minimum'=>5,  'stok_maksimum'=>70,  'harga_beli'=>320000,'harga_jual'=>370000],
            ['nama_barang'=>'Pipa PVC 1/2 inch (4m)',      'kategori_id'=>7,'satuan'=>'btg',  'stok_saat_ini'=>150,'stok_minimum'=>20, 'stok_maksimum'=>400, 'harga_beli'=>18000, 'harga_jual'=>23000],
            ['nama_barang'=>'Pipa PVC 3/4 inch (4m)',      'kategori_id'=>7,'satuan'=>'btg',  'stok_saat_ini'=>120,'stok_minimum'=>20, 'stok_maksimum'=>350, 'harga_beli'=>25000, 'harga_jual'=>30000],
            ['nama_barang'=>'Pipa PVC 2 inch (4m)',        'kategori_id'=>7,'satuan'=>'btg',  'stok_saat_ini'=>80, 'stok_minimum'=>10, 'stok_maksimum'=>200, 'harga_beli'=>45000, 'harga_jual'=>52000],
            ['nama_barang'=>'Kloset Duduk',                'kategori_id'=>7,'satuan'=>'pcs',  'stok_saat_ini'=>15, 'stok_minimum'=>2,  'stok_maksimum'=>30,  'harga_beli'=>850000,'harga_jual'=>950000],
            ['nama_barang'=>'Wastafel Besar',              'kategori_id'=>7,'satuan'=>'pcs',  'stok_saat_ini'=>12, 'stok_minimum'=>2,  'stok_maksimum'=>25,  'harga_beli'=>450000,'harga_jual'=>520000],
            ['nama_barang'=>'Keran Air Tembok',            'kategori_id'=>7,'satuan'=>'pcs',  'stok_saat_ini'=>80, 'stok_minimum'=>10, 'stok_maksimum'=>200, 'harga_beli'=>55000, 'harga_jual'=>68000],
            ['nama_barang'=>'Shower Set',                  'kategori_id'=>7,'satuan'=>'set',  'stok_saat_ini'=>30, 'stok_minimum'=>5,  'stok_maksimum'=>80,  'harga_beli'=>150000,'harga_jual'=>180000],
            ['nama_barang'=>'Paku Kayu 5cm (1kg)',         'kategori_id'=>8,'satuan'=>'kg',   'stok_saat_ini'=>200,'stok_minimum'=>20, 'stok_maksimum'=>500, 'harga_beli'=>15000, 'harga_jual'=>20000],
            ['nama_barang'=>'Sekrup Gypsum 3cm (kotak)',   'kategori_id'=>8,'satuan'=>'kotak','stok_saat_ini'=>150,'stok_minimum'=>20, 'stok_maksimum'=>400, 'harga_beli'=>10000, 'harga_jual'=>15000],
            ['nama_barang'=>'Engsel Pintu 4 inch',         'kategori_id'=>8,'satuan'=>'pcs',  'stok_saat_ini'=>300,'stok_minimum'=>30, 'stok_maksimum'=>800, 'harga_beli'=>8000,  'harga_jual'=>12000],
            ['nama_barang'=>'Grendel Pintu',               'kategori_id'=>8,'satuan'=>'pcs',  'stok_saat_ini'=>150,'stok_minimum'=>20, 'stok_maksimum'=>400, 'harga_beli'=>12000, 'harga_jual'=>16000],
            ['nama_barang'=>'Kunci Pintu Rumah',           'kategori_id'=>8,'satuan'=>'set',  'stok_saat_ini'=>60, 'stok_minimum'=>10, 'stok_maksimum'=>150, 'harga_beli'=>45000, 'harga_jual'=>58000],
            ['nama_barang'=>'Gergaji Kayu',                'kategori_id'=>8,'satuan'=>'pcs',  'stok_saat_ini'=>40, 'stok_minimum'=>5,  'stok_maksimum'=>100, 'harga_beli'=>25000, 'harga_jual'=>35000],
            ['nama_barang'=>'Cangkul',                     'kategori_id'=>8,'satuan'=>'pcs',  'stok_saat_ini'=>50, 'stok_minimum'=>5,  'stok_maksimum'=>120, 'harga_beli'=>55000, 'harga_jual'=>70000],
            ['nama_barang'=>'Ember Cat 10kg',              'kategori_id'=>8,'satuan'=>'pcs',  'stok_saat_ini'=>200,'stok_minimum'=>20, 'stok_maksimum'=>500, 'harga_beli'=>12000, 'harga_jual'=>16000],
            ['nama_barang'=>'Aluminium Foil Insulasi',     'kategori_id'=>4,'satuan'=>'roll', 'stok_saat_ini'=>40, 'stok_minimum'=>5,  'stok_maksimum'=>100, 'harga_beli'=>80000, 'harga_jual'=>100000],
        ];
        foreach ($barangData as $b) {
            // Kode dibuat otomatis dari singkatan nama barang (mis. SP50-001)
            $b['kode_barang'] = Barang::generateKode($b['nama_barang']);
            Barang::create($b);
        }

        // Purchase Order contoh (1 selesai + 1 draft) agar modul langsung terisi
        $po1 = PurchaseOrder::create([
            'no_po'            => PurchaseOrder::generateNoPo(),
            'tanggal_po'       => now()->subDays(3)->toDateString(),
            'estimasi_datang'  => now()->addDays(3)->toDateString(),
            'supplier_id'      => 1,
            'status'           => PurchaseOrder::STATUS_SELESAI,
            'catatan'          => 'Contoh PO selesai (dari seeder).',
            'user_id'          => 2,
        ]);
        PoDetail::create([
            'po_id'       => $po1->id,
            'barang_id'   => 1,
            'jumlah'      => 100,
            'qty_diterima'=> 100,
            'harga_beli'  => 60000,
            'subtotal'    => 6000000,
        ]);

        $po2 = PurchaseOrder::create([
            'no_po'            => PurchaseOrder::generateNoPo(),
            'tanggal_po'       => now()->toDateString(),
            'estimasi_datang'  => now()->addDays(7)->toDateString(),
            'supplier_id'      => 2,
            'status'           => PurchaseOrder::STATUS_DRAFT,
            'catatan'          => 'Contoh PO draft (dari seeder).',
            'user_id'          => 3,
        ]);
        PoDetail::create([
            'po_id'       => $po2->id,
            'barang_id'   => 4,
            'jumlah'      => 50,
            'qty_diterima'=> 0,
            'harga_beli'  => 45000,
            'subtotal'    => 2250000,
        ]);

        // Mapping barang -> supplier preferensi (panduan "beli ke siapa" saat PO)
        $mapKategoriSupplier = [1 => 1, 2 => 1, 3 => 2, 4 => 2, 5 => 2, 6 => 3, 7 => 3, 8 => 3];
        foreach (Barang::all() as $barang) {
            $supId = $mapKategoriSupplier[$barang->kategori_id] ?? null;
            if (!$supId) {
                continue;
            }
            $barang->update(['preferred_supplier_id' => $supId]);
            $barang->suppliers()->syncWithoutDetaching([
                $supId => ['harga_beli_terakhir' => $barang->harga_beli, 'is_preferred' => 1],
            ]);
        }
    }
}