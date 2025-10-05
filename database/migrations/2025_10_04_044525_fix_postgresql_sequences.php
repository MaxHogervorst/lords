<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only run for PostgreSQL
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        // List of tables with auto-incrementing IDs
        $tables = [
            'orders',
            'members',
            'groups',
            'products',
            'group_member',
            'invoice_groups',
            'invoice_lines',
            'invoice_products',
            'invoice_product_prices',
            'users',
            'roles',
            'activations',
            'persistences',
            'reminders',
            'throttle',
        ];

        foreach ($tables as $table) {
            // Reset sequence to match the current MAX(id)
            DB::statement("SELECT setval('{$table}_id_seq', (SELECT COALESCE(MAX(id), 1) FROM {$table}));");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this
    }
};
