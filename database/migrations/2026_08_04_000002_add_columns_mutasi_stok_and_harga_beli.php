<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 1) MutasiStok: tabel mutasi_stoks sebelumnya hanya berisi id+timestamps.
     *    Tambahkan kolom agar bisa dipakai sebagai log mutasi stok otomatis.
     * 2) transaksi_details: simpan snapshot harga_beli saat transaksi untuk
     *    perhitungan laba (HPP) yang akurat di laporan penjualan.
     */
    public function up(): void
    {
        Schema::table('mutasi_stoks', function (Blueprint $table) {
            $table->foreignId('barang_id')->nullable()->after('id')
                ->constrained('barangs')->nullOnDelete();
            $table->string('tipe', 10)->default('masuk')->after('barang_id'); // masuk | keluar
            $table->integer('jumlah')->default(0)->after('tipe');
            $table->string('keterangan')->nullable()->after('jumlah');
            $table->foreignId('user_id')->nullable()->after('keterangan')
                ->constrained('users')->nullOnDelete();
        });

        Schema::table('transaksi_details', function (Blueprint $table) {
            $table->integer('harga_beli')->default(0)->after('harga_satuan');
        });
    }

    public function down(): void
    {
        Schema::table('mutasi_stoks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('barang_id');
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['tipe', 'jumlah', 'keterangan']);
        });

        Schema::table('transaksi_details', function (Blueprint $table) {
            $table->dropColumn('harga_beli');
        });
    }
};
