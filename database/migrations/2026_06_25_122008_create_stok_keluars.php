<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_keluars', function (Blueprint $table) {
            $table->id();
            $table->string('no_transaksi')->unique();
            $table->date('tanggal_keluar');
            $table->string('nama_pelanggan')->nullable();
            $table->enum('jenis_keluar', ['penjualan', 'pemakaian', 'retur', 'lainnya'])->default('penjualan');
            $table->text('keterangan')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });

        Schema::create('stok_keluar_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stok_keluar_id')->constrained('stok_keluars')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('barangs')->onDelete('restrict');
            $table->integer('jumlah');
            $table->decimal('harga_jual', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_keluar_details');
        Schema::dropIfExists('stok_keluars');
    }
};