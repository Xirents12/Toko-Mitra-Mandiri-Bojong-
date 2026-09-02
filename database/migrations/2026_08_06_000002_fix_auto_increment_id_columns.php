<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Perbaikan: beberapa tabel dibuat tanpa AUTO_INCREMENT pada kolom `id`,
 * sehingga INSERT baru gagal dengan "Field 'id' doesn't have a default value".
 *
 * Kasus khusus: `transaksis` & `transaksi_details` bahkan tidak punya PRIMARY KEY,
 * jadi PRIMARY KEY ditambahkan lebih dulu sebelum AUTO_INCREMENT.
 *
 * Idempotent: setiap langkah dicek dulu ke information_schema sehingga migrasi
 * aman dijalankan ulang (mis. setelah rollback) tanpa error.
 */
return new class extends Migration
{
    /** Tabel normal: sudah punya PRIMARY KEY, tinggal tambah AUTO_INCREMENT. */
    private const TABLES = [
        'cicilans',
        'piutangs',
        'po_details',
        'purchase_orders',
        'transaksis',
        'transaksi_details',
    ];

    /** Tabel yang id-nya belum jadi PRIMARY KEY. */
    private const TABLES_WITHOUT_PK = [
        'transaksis',
        'transaksi_details',
    ];

    private function columnIsAutoIncrement(string $table): bool
    {
        $row = DB::selectOne(
            'SELECT EXTRA FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = "id"',
            [DB::getDatabaseName(), $table]
        );

        return $row !== null && str_contains($row->EXTRA, 'auto_increment');
    }

    private function tableHasPrimaryKey(string $table): bool
    {
        return DB::selectOne(
            'SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_TYPE = "PRIMARY KEY"',
            [DB::getDatabaseName(), $table]
        ) !== null;
    }

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            if (in_array($table, self::TABLES_WITHOUT_PK, true) && ! $this->tableHasPrimaryKey($table)) {
                DB::statement("ALTER TABLE `{$table}` ADD PRIMARY KEY (`id`)");
            }

            if (! $this->columnIsAutoIncrement($table)) {
                DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
            }
        }
    }

    public function down(): void
    {
        // Kebalikan: hapus AUTO_INCREMENT, lalu PK (kembali ke kondisi awal).
        foreach (array_reverse(self::TABLES) as $table) {
            if ($this->columnIsAutoIncrement($table)) {
                DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `id` BIGINT UNSIGNED NOT NULL");
            }

            if (in_array($table, self::TABLES_WITHOUT_PK, true) && $this->tableHasPrimaryKey($table)) {
                DB::statement("ALTER TABLE `{$table}` DROP PRIMARY KEY");
            }
        }
    }
};
