#!/usr/bin/env php
<?php

/**
 * Import MySQL JSON data to PostgreSQL
 *
 * Usage: php scripts/import-to-postgres.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Verify we're using PostgreSQL
$driver = config('database.default');
if ($driver !== 'pgsql') {
    die("ERROR: Database driver must be 'pgsql'. Current: {$driver}\n");
}

$exportDir = __DIR__ . '/../storage/app/mysql-export';

if (!is_dir($exportDir)) {
    die("ERROR: Export directory not found: {$exportDir}\n");
}

// Tables to import (in order to respect foreign keys)
$tables = [
    'users',
    'members',
    'groups',
    'products',
    'invoice_groups',
    'orders',
    'group_member',
    'invoice_products',
    'invoice_product_prices',
    'invoice_lines',
];

echo "Starting PostgreSQL data import...\n\n";

foreach ($tables as $table) {
    $filename = "{$exportDir}/{$table}.json";

    if (!file_exists($filename)) {
        echo "Skipping {$table} (no export file)\n";
        continue;
    }

    echo "Importing {$table}... ";

    try {
        $data = json_decode(file_get_contents($filename), true);

        if (empty($data)) {
            echo "SKIPPED (no data)\n";
            continue;
        }

        // Disable triggers and foreign key checks during import
        DB::statement('SET session_replication_role = replica;');

        // Insert in chunks
        $chunks = array_chunk($data, 100);
        foreach ($chunks as $chunk) {
            DB::table($table)->insert($chunk);
        }

        // Re-enable triggers and foreign key checks
        DB::statement('SET session_replication_role = DEFAULT;');

        // Reset sequence for auto-increment columns
        if (in_array('id', array_keys($data[0]))) {
            $maxId = max(array_column($data, 'id'));
            DB::statement("SELECT setval('{$table}_id_seq', {$maxId}, true);");
        }

        $count = count($data);
        echo "OK ({$count} rows)\n";

    } catch (Exception $e) {
        echo 'ERROR: ' . $e->getMessage() . "\n";
        // Re-enable constraints even on error
        DB::statement('SET session_replication_role = DEFAULT;');
    }
}

echo "\nImport complete!\n";
echo "\nVerify data:\n";
foreach ($tables as $table) {
    try {
        $count = DB::table($table)->count();
        echo "  {$table}: {$count} rows\n";
    } catch (Exception $e) {
        echo "  {$table}: ERROR\n";
    }
}
