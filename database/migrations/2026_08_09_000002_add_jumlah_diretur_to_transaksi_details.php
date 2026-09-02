<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_details', function (Blueprint $table) {
            if (!Schema::hasColumn('transaksi_details', 'jumlah_diretur')) {
                $table->integer('jumlah_diretur')->default(0)->after('jumlah');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_details', function (Blueprint $table) {
            $table->dropColumn('jumlah_diretur');
        });
    }
};
