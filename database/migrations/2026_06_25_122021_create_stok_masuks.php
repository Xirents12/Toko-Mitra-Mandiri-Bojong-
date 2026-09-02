<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_masuks', function (Blueprint $table) {
            $table->id();
            $table->string('no_transaksi')->unique();
            $table->date('tanggal_masuk');
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('no_nota_supplier')->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });

        Schema::create('stok_masuk_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stok_masuk_id')->constrained('stok_masuks')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('barangs')->onDelete('restrict');
            $table->integer('jumlah');
            $table->decimal('harga_beli', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_masuk_details');
        Schema::dropIfExists('stok_masuks');
    }
};