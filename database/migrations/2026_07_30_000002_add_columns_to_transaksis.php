<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            if (!Schema::hasColumn('transaksis', 'no_invoice')) {
                $table->string('no_invoice')->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('transaksis', 'metode_bayar')) {
                $table->enum('metode_bayar', ['tunai', 'kredit'])->default('tunai')->after('total_harga');
            }
            if (!Schema::hasColumn('transaksis', 'status_kredit')) {
                $table->enum('status_kredit', ['lunas', 'belum_lunas', 'cicil'])->nullable()->after('metode_bayar');
            }
            if (!Schema::hasColumn('transaksis', 'nama_pelanggan')) {
                $table->string('nama_pelanggan')->nullable()->after('status_kredit');
            }
            if (!Schema::hasColumn('transaksis', 'bayar')) {
                $table->bigInteger('bayar')->default(0)->after('nama_pelanggan');
            }
            if (!Schema::hasColumn('transaksis', 'kembalian')) {
                $table->bigInteger('kembalian')->default(0)->after('bayar');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropColumn(['no_invoice', 'metode_bayar', 'status_kredit', 'nama_pelanggan', 'bayar', 'kembalian']);
        });
    }
};
