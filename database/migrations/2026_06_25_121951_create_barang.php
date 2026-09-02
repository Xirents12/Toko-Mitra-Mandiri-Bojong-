<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barangs', function (Blueprint $table) {
            $table->id();
            $table->string('kode_barang')->unique();
            $table->string('nama_barang');
            $table->foreignId('kategori_id')->constrained('kategoris')->onDelete('restrict');
            $table->string('satuan'); // pcs, sak, m2, m3, kg, dll
            $table->integer('stok_saat_ini')->default(0);
            $table->integer('stok_minimum')->default(0);  // reorder point
            $table->integer('stok_maksimum')->default(0); // maximum level
            $table->decimal('harga_beli', 15, 2)->default(0);
            $table->decimal('harga_jual', 15, 2)->default(0);
            $table->string('lokasi_rak')->nullable();
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};