<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barangs', function (Blueprint $table) {
            if (!Schema::hasColumn('barangs', 'preferred_supplier_id')) {
                $table->foreignId('preferred_supplier_id')->nullable()
                    ->constrained('suppliers')->nullOnDelete()->after('lokasi_rak');
            }
            if (!Schema::hasColumn('barangs', 'harga_beli_terakhir')) {
                $table->decimal('harga_beli_terakhir', 15, 2)->default(0)->after('preferred_supplier_id');
            }
            if (!Schema::hasColumn('barangs', 'harga_jual_terakhir')) {
                $table->decimal('harga_jual_terakhir', 15, 2)->default(0)->after('harga_beli_terakhir');
            }
        });
    }

    public function down(): void
    {
        Schema::table('barangs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('preferred_supplier_id');
            $table->dropColumn(['harga_beli_terakhir', 'harga_jual_terakhir']);
        });
    }
};
