#!/usr/bin/env php
<?php
/**
 * Export MySQL data to JSON format for PostgreSQL import
 *
 * Usage: php scripts/export-mysql-data.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Tables to export (in order to respect foreign keys)
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

$exportDir = __DIR__ . '/../storage/app/mysql-export';

// Create export directory
if (!is_dir($exportDir)) {
    mkdir($exportDir, 0755, true);
}

echo "Starting MySQL data export...\n\n";

foreach ($tables as $table) {
    echo "Exporting {$table}... ";

    try {
        // Check if table exists
        $exists = DB::select("SHOW TABLES LIKE ?", [$table]);

        if (empty($exists)) {
            echo "SKIPPED (table doesn't exist)\n";
            continue;
        }

        // Get all data from table
        $data = DB::table($table)->get()->toArray();

        // Convert to array of arrays (not objects)
        $data = json_decode(json_encode($data), true);

        // Save to JSON file
        $filename = "{$exportDir}/{$table}.json";
        file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));

        $count = count($data);
        echo "OK ({$count} rows)\n";

    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}

echo "\nExport complete! Files saved to: {$exportDir}\n";
echo "\nNext steps:\n";
echo "1. Update .env to use PostgreSQL\n";
echo "2. Run: php artisan migrate:fresh\n";
echo "3. Run: php scripts/import-to-postgres.php\n";
