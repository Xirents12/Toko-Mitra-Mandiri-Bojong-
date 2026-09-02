<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) purchase_orders: estimasi kedatangan barang + soft delete
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->date('estimasi_datang')->nullable()->after('tanggal_po');
            $table->softDeletes()->after('updated_at');
        });

        // 2) Status workflow baru (8 status)
        DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN status ENUM('draft','menunggu_persetujuan','disetujui','dikirim_supplier','diterima_sebagian','selesai','ditolak','dibatalkan') NOT NULL DEFAULT 'draft'");

        // 3) Data lama: dikonfirmasi -> disetujui (status lama tidak dipakai lagi)
        DB::table('purchase_orders')->where('status', 'dikonfirmasi')->update(['status' => 'disetujui']);

        // 4) po_details: qty yang benar-benar diterima
        Schema::table('po_details', function (Blueprint $table) {
            $table->unsignedInteger('qty_diterima')->default(0)->after('jumlah');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['estimasi_datang', 'deleted_at']);
        });

        DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN status ENUM('draft','dikonfirmasi','selesai','dibatalkan') NOT NULL DEFAULT 'draft'");

        Schema::table('po_details', function (Blueprint $table) {
            $table->dropColumn('qty_diterima');
        });
    }
};
