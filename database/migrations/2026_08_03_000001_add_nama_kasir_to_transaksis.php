<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            if (!Schema::hasColumn('transaksis', 'nama_kasir')) {
                $table->string('nama_kasir')->nullable()->after('user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            if (Schema::hasColumn('transaksis', 'nama_kasir')) {
                $table->dropColumn('nama_kasir');
            }
        });
    }
};
