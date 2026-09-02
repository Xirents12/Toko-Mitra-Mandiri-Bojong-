<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stok_keluars', function (Blueprint $table) {
            if (!Schema::hasColumn('stok_keluars', 'supplier_id')) {
                $table->foreignId('supplier_id')->nullable()->after('nama_pelanggan');
                $table->foreign('supplier_id')
                    ->references('id')->on('suppliers')
                    ->onDelete('set null');
            }
        });

        Schema::table('stok_keluar_details', function (Blueprint $table) {
            if (!Schema::hasColumn('stok_keluar_details', 'harga_beli')) {
                $table->decimal('harga_beli', 15, 2)->default(0)->after('harga_jual');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stok_keluars', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
        });

        Schema::table('stok_keluar_details', function (Blueprint $table) {
            $table->dropColumn('harga_beli');
        });
    }
};
