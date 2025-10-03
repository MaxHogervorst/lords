<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Fixes double-escaped backslashes in polymorphic type columns.
     * This issue occurred during Laravel version upgrade where namespace
     * separators were stored as \\ instead of \ in the database.
     */
    public function up(): void
    {
        // Fix orders.ownerable_type: replace double backslashes with single backslashes
        DB::statement("UPDATE orders SET ownerable_type = REPLACE(ownerable_type, '\\\\', '\\')");
    }

    /**
     * Reverse the migrations.
     *
     * This migration is irreversible as we cannot safely determine which
     * backslashes were originally doubled without data loss.
     */
    public function down(): void
    {
        // This migration cannot be reliably reversed
        // The original double-escaped data is lost after the fix
    }
};
