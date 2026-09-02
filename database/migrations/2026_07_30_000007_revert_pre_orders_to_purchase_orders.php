<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Drop FK from pre_order_details (if exists)
        if (Schema::hasTable('pre_order_details')) {
            try {
                Schema::table('pre_order_details', function (Blueprint $table) {
                    $table->dropForeign(['pre_order_id']);
                });
            } catch (\Exception $e) {
                // FK constraint name might differ — try raw SQL
                try {
                    DB::statement('ALTER TABLE `pre_order_details` DROP FOREIGN KEY `pre_order_details_pre_order_id_foreign`');
                } catch (\Exception $e2) {
                    // FK already dropped or never existed — safe to continue
                }
            }

            // Step 2: Rename pre_order_details -> po_details
            if (Schema::hasColumn('pre_order_details', 'pre_order_id')) {
                Schema::table('pre_order_details', function (Blueprint $table) {
                    $table->renameColumn('pre_order_id', 'po_id');
                });
            }
            Schema::rename('pre_order_details', 'po_details');
        }

        // Step 3: Rename pre_orders -> purchase_orders
        if (Schema::hasTable('pre_orders') && !Schema::hasTable('purchase_orders')) {
            Schema::rename('pre_orders', 'purchase_orders');
        }

        // Step 4: Re-add FK
        if (Schema::hasTable('po_details') && Schema::hasColumn('po_details', 'po_id') && Schema::hasTable('purchase_orders')) {
            try {
                Schema::table('po_details', function (Blueprint $table) {
                    $table->foreign('po_id')->references('id')->on('purchase_orders')->onDelete('cascade');
                });
            } catch (\Exception $e) {
                // FK might already exist
            }
        }
    }

    public function down(): void
    {
        // Reverse: back to pre_orders
        if (Schema::hasTable('po_details')) {
            try {
                Schema::table('po_details', function (Blueprint $table) {
                    $table->dropForeign(['po_id']);
                });
            } catch (\Exception $e) {
                try {
                    DB::statement('ALTER TABLE `po_details` DROP FOREIGN KEY `po_details_po_id_foreign`');
                } catch (\Exception $e2) {}
            }

            if (Schema::hasColumn('po_details', 'po_id')) {
                Schema::table('po_details', function (Blueprint $table) {
                    $table->renameColumn('po_id', 'pre_order_id');
                });
            }
            Schema::rename('po_details', 'pre_order_details');
        }

        if (Schema::hasTable('purchase_orders') && !Schema::hasTable('pre_orders')) {
            Schema::rename('purchase_orders', 'pre_orders');
        }

        if (Schema::hasTable('pre_order_details') && Schema::hasColumn('pre_order_details', 'pre_order_id') && Schema::hasTable('pre_orders')) {
            try {
                Schema::table('pre_order_details', function (Blueprint $table) {
                    $table->foreign('pre_order_id')->references('id')->on('pre_orders')->onDelete('cascade');
                });
            } catch (\Exception $e) {}
        }
    }
};
