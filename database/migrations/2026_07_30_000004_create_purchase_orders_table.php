<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('no_po')->unique();
            $table->date('tanggal_po');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->enum('status', ['draft', 'dikonfirmasi', 'selesai', 'dibatalkan'])->default('draft');
            $table->text('catatan')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });

        Schema::create('po_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_id')->constrained('purchase_orders')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('barangs')->onDelete('restrict');
            $table->integer('jumlah');
            $table->decimal('harga_beli', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('po_details');
        Schema::dropIfExists('purchase_orders');
    }
};
