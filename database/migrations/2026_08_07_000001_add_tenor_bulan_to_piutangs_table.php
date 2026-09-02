<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('piutangs', function (Blueprint $table) {
            // Tenor (dalam bulan) cicilan, maksimal 3 bulan
            $table->unsignedTinyInteger('tenor_bulan')->default(3)->after('max_cicilan');
        });
    }

    public function down(): void
    {
        Schema::table('piutangs', function (Blueprint $table) {
            $table->dropColumn('tenor_bulan');
        });
    }
};
