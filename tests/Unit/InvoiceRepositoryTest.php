<?php

declare(strict_types=1);

use App\Models\InvoiceGroup;
use App\Repositories\InvoiceRepository;
use Illuminate\Support\Facades\Cache;

uses()->group('unit', 'invoice');

beforeEach(function () {
    $this->repository = new InvoiceRepository();
});

test('creates invoice group with month name format', function () {
    // Create invoice group with date format
    $invoiceGroup = $this->repository->createAndSetActive('10-25');

    expect($invoiceGroup)->toBeInstanceOf(InvoiceGroup::class)
        ->and($invoiceGroup->name)->toBe('October 2025')
        ->and($invoiceGroup->status)->toBeTrue();

    $this->assertDatabaseHas('invoice_groups', [
        'name' => 'October 2025',
        'status' => true,
    ]);
});

test('sets new invoice group as active', function () {
    $invoiceGroup = $this->repository->createAndSetActive('10-25');

    expect($invoiceGroup->status)->toBeTrue()
        ->and(Cache::get('invoice_group')->id)->toBe($invoiceGroup->id);
});

test('deactivates previous invoice groups when creating new one', function () {
    // Create first invoice group
    $first = $this->repository->createAndSetActive('10-25');
    expect($first->status)->toBeTrue();

    // Create second invoice group
    $second = $this->repository->createAndSetActive('11-25');

    // First should be deactivated (status is stored as 0/1 in database)
    expect((bool) $first->fresh()->status)->toBeFalse()
        // Second should be active
        ->and($second->status)->toBeTrue();
});

test('formats various date inputs correctly', function () {
    $testCases = [
        '10-25' => 'October 2025',
        '2025-10' => 'October 2025',
        '10/25' => 'October 2025',
        '12-25' => 'December 2025',
        '01-26' => 'January 2026',
    ];

    foreach ($testCases as $input => $expected) {
        // Clean up before each test
        InvoiceGroup::query()->delete();
        Cache::forget('invoice_group');

        $invoiceGroup = $this->repository->createAndSetActive($input);

        expect($invoiceGroup->name)->toBe(
            $expected,
            "Failed to format '{$input}' to '{$expected}', got '{$invoiceGroup->name}'"
        );
    }
});

test('preserves already formatted month names', function () {
    $invoiceGroup = $this->repository->createAndSetActive('October 2025');

    expect($invoiceGroup->name)->toBe('October 2025');
});

test('updates cache after creating invoice group', function () {
    Cache::forget('invoice_group');

    $invoiceGroup = $this->repository->createAndSetActive('10-25');

    expect(Cache::has('invoice_group'))->toBeTrue()
        ->and(Cache::get('invoice_group')->id)->toBe($invoiceGroup->id);
});

