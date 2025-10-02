<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Orders table - frequently filtered by invoice_group_id and product_id
        Schema::table('orders', function (Blueprint $table) {
            $table->index('invoice_group_id', 'orders_invoice_group_id_index');
            $table->index('product_id', 'orders_product_id_index');
            // Polymorphic relationship indexes
            $table->index(['ownerable_type', 'ownerable_id'], 'orders_ownerable_index');
        });

        // Groups table - frequently filtered by invoice_group_id
        Schema::table('groups', function (Blueprint $table) {
            $table->index('invoice_group_id', 'groups_invoice_group_id_index');
        });

        // Members table - frequently queried by bank info and collection status
        Schema::table('members', function (Blueprint $table) {
            $table->index('had_collection', 'members_had_collection_index');
            $table->index(['bic', 'iban'], 'members_bank_info_index');
        });

        // Invoice groups table - frequently queried by status
        Schema::table('invoice_groups', function (Blueprint $table) {
            $table->index('status', 'invoice_groups_status_index');
        });

        // Products table - no active column, skipping index

        // Invoice products table - frequently joined with invoice_group_id
        Schema::table('invoice_products', function (Blueprint $table) {
            $table->index('invoice_group_id', 'invoice_products_invoice_group_id_index');
        });

        // Invoice product prices table - frequently joined with invoice_product_id
        Schema::table('invoice_product_prices', function (Blueprint $table) {
            $table->index('invoice_product_id', 'invoice_product_prices_invoice_product_id_index');
        });

        // Invoice lines table - frequently joined with member_id and invoice_product_price_id
        Schema::table('invoice_lines', function (Blueprint $table) {
            $table->index('member_id', 'invoice_lines_member_id_index');
            $table->index('invoice_product_price_id', 'invoice_lines_invoice_product_price_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_invoice_group_id_index');
            $table->dropIndex('orders_product_id_index');
            $table->dropIndex('orders_ownerable_index');
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->dropIndex('groups_invoice_group_id_index');
        });

        Schema::table('members', function (Blueprint $table) {
            $table->dropIndex('members_had_collection_index');
            $table->dropIndex('members_bank_info_index');
        });

        Schema::table('invoice_groups', function (Blueprint $table) {
            $table->dropIndex('invoice_groups_status_index');
        });

        // Products table - no active column index to drop

        Schema::table('invoice_products', function (Blueprint $table) {
            $table->dropIndex('invoice_products_invoice_group_id_index');
        });

        Schema::table('invoice_product_prices', function (Blueprint $table) {
            $table->dropIndex('invoice_product_prices_invoice_product_id_index');
        });

        Schema::table('invoice_lines', function (Blueprint $table) {
            $table->dropIndex('invoice_lines_member_id_index');
            $table->dropIndex('invoice_lines_invoice_product_price_id_index');
        });
    }
};
