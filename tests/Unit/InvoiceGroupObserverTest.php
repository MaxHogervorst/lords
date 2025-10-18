<?php

declare(strict_types=1);

use App\Models\InvoiceGroup;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    // Clear cache before each test
    Cache::flush();
});

test('cache is cleared when invoice group is created', function () {
    // Populate cache
    Cache::put('invoice_group', ['test' => 'data'], 1);
    expect(Cache::has('invoice_group'))->toBeTrue();

    // Create an invoice group
    InvoiceGroup::factory()->create(['name' => 'October 2025', 'status' => false]);

    // Cache should be cleared
    expect(Cache::has('invoice_group'))->toBeFalse();
});

test('cache is cleared when invoice group is updated', function () {
    // Create an invoice group first
    $invoiceGroup = InvoiceGroup::factory()->create(['name' => 'October 2025', 'status' => false]);

    // Populate cache
    Cache::put('invoice_group', ['test' => 'data'], 1);
    expect(Cache::has('invoice_group'))->toBeTrue();

    // Update the invoice group
    $invoiceGroup->update(['status' => true]);

    // Cache should be cleared
    expect(Cache::has('invoice_group'))->toBeFalse();
});

test('cache is cleared when invoice group is deleted', function () {
    // Create an invoice group
    $invoiceGroup = InvoiceGroup::factory()->create(['name' => 'October 2025', 'status' => false]);

    // Populate cache
    Cache::put('invoice_group', ['test' => 'data'], 1);
    expect(Cache::has('invoice_group'))->toBeTrue();

    // Delete the invoice group
    $invoiceGroup->delete();

    // Cache should be cleared
    expect(Cache::has('invoice_group'))->toBeFalse();
});

test('cache is cleared when invoice group status is changed', function () {
    // Create an invoice group
    $invoiceGroup = InvoiceGroup::factory()->create(['name' => 'October 2025', 'status' => false]);

    // Populate cache
    Cache::put('invoice_group', ['test' => 'data'], 1);
    expect(Cache::has('invoice_group'))->toBeTrue();

    // Change status via attribute assignment
    $invoiceGroup->status = true;
    $invoiceGroup->save();

    // Cache should be cleared
    expect(Cache::has('invoice_group'))->toBeFalse();
});

test('cache is not cleared when invoice group is only retrieved', function () {
    // Create an invoice group
    $invoiceGroup = InvoiceGroup::factory()->create(['name' => 'October 2025', 'status' => false]);

    // Populate cache
    Cache::put('invoice_group', ['test' => 'data'], 1);
    expect(Cache::has('invoice_group'))->toBeTrue();

    // Just retrieve the invoice group
    InvoiceGroup::find($invoiceGroup->id);

    // Cache should still exist
    expect(Cache::has('invoice_group'))->toBeTrue();
});

test('cache is cleared when multiple invoice groups are modified', function () {
    // Create first invoice group
    $invoiceGroup1 = InvoiceGroup::factory()->create(['name' => 'October 2025', 'status' => true]);
    expect(Cache::has('invoice_group'))->toBeFalse();

    // Populate cache
    Cache::put('invoice_group', ['test' => 'data1'], 1);
    expect(Cache::has('invoice_group'))->toBeTrue();

    // Create second invoice group
    InvoiceGroup::factory()->create(['name' => 'November 2025', 'status' => false]);
    expect(Cache::has('invoice_group'))->toBeFalse();

    // Populate cache again
    Cache::put('invoice_group', ['test' => 'data2'], 1);
    expect(Cache::has('invoice_group'))->toBeTrue();

    // Update first invoice group
    $invoiceGroup1->update(['status' => false]);
    expect(Cache::has('invoice_group'))->toBeFalse();
});

test('cache is cleared when switching active invoice group', function () {
    // Create two invoice groups
    $invoiceGroup1 = InvoiceGroup::factory()->create(['name' => 'October 2025', 'status' => true]);
    $invoiceGroup2 = InvoiceGroup::factory()->create(['name' => 'November 2025', 'status' => false]);

    // Populate cache
    Cache::put('invoice_group', $invoiceGroup1, 1);
    expect(Cache::has('invoice_group'))->toBeTrue();

    // Switch active invoice group
    $invoiceGroup1->status = false;
    $invoiceGroup1->save();

    // Cache should be cleared after first update
    expect(Cache::has('invoice_group'))->toBeFalse();

    // Populate cache again
    Cache::put('invoice_group', ['test' => 'data'], 1);

    // Activate second invoice group
    $invoiceGroup2->status = true;
    $invoiceGroup2->save();

    // Cache should be cleared again
    expect(Cache::has('invoice_group'))->toBeFalse();
});
