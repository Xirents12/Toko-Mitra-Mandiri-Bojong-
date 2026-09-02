<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void

{
    Schema::create('transaksis', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->date('tanggal');
        $table->bigInteger('total_harga')->default(0);
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};