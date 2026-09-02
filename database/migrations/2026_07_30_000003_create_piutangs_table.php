<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('piutangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_id')->constrained('transaksis')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->string('nama_pelanggan');
            $table->string('no_telepon')->nullable();
            $table->string('alamat')->nullable();
            $table->decimal('total_piutang', 15, 2)->default(0);
            $table->decimal('sisa_piutang', 15, 2)->default(0);
            $table->integer('max_cicilan')->default(5);
            $table->integer('jml_cicilan_terbayar')->default(0);
            $table->decimal('besar_cicilan', 15, 2)->default(0);
            $table->date('tanggal_jatuh_tempo');
            $table->enum('status', ['aktif', 'lunas', 'macet'])->default('aktif');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('cicilans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('piutang_id')->constrained('piutangs')->onDelete('cascade');
            $table->decimal('jumlah', 15, 2)->default(0);
            $table->date('tanggal_bayar');
            $table->enum('metode_bayar', ['tunai', 'transfer'])->default('tunai');
            $table->text('keterangan')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cicilans');
        Schema::dropIfExists('piutangs');
    }
};
