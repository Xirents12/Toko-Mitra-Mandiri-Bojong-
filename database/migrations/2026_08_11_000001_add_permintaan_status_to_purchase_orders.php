<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tambah status "permintaan" (permintaan barang dari gudang,
     * admin/owner yang membuat PO ke supplier).
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN status ENUM('draft','permintaan','menunggu_persetujuan','disetujui','dikirim_supplier','diterima_sebagian','selesai','ditolak','dibatalkan') NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN status ENUM('draft','menunggu_persetujuan','disetujui','dikirim_supplier','diterima_sebagian','selesai','ditolak','dibatalkan') NOT NULL DEFAULT 'draft'");
    }
};
