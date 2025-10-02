<?php

use App\Models\InvoiceGroup;
use App\Models\InvoiceProduct;
use App\Models\Member;
use App\Services\FiscusService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->fiscusService = new FiscusService;
});

test('create invoice product with members', function () {
    $invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);
    $members = Member::factory()->count(3)->create();

    $data = [
        'finalproductname' => 'Test Product',
        'finalpriceperperson' => 10.50,
        'finalproductdescription' => 'Test Description',
        'member' => $members->pluck('id')->toArray(),
    ];

    $result = $this->fiscusService->createInvoiceProduct($data, $invoiceGroup->id);

    expect($result['product_name'])->toBe('Test Product')
        ->and($result['price'])->toBe(10.50)
        ->and($result['member_count'])->toBe(3);

    $this->assertDatabaseHas('invoice_products', [
        'name' => 'Test Product',
        'invoice_group_id' => $invoiceGroup->id,
    ]);

    $this->assertDatabaseCount('invoice_lines', 3);
});

test('update invoice product creates new price', function () {
    $invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);
    $invoiceProduct = InvoiceProduct::factory()->create([
        'invoice_group_id' => $invoiceGroup->id,
    ]);
    $members = Member::factory()->count(2)->create();

    $data = [
        'finalpriceperperson' => 15.00,
        'finalproductdescription' => 'Updated Description',
        'member' => $members->pluck('id')->toArray(),
    ];

    $result = $this->fiscusService->updateInvoiceProduct($invoiceProduct, $data, null);

    expect($result['update_type'])->toBe('added new price')
        ->and($result['price'])->toBe(15.00)
        ->and($result['member_count'])->toBe(2);
});

test('delete invoice product cascades', function () {
    $invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);
    $members = Member::factory()->count(2)->create();

    $data = [
        'finalproductname' => 'Product to Delete',
        'finalpriceperperson' => 20.00,
        'finalproductdescription' => 'Will be deleted',
        'member' => $members->pluck('id')->toArray(),
    ];

    $result = $this->fiscusService->createInvoiceProduct($data, $invoiceGroup->id);
    $invoiceProduct = InvoiceProduct::where('name', 'Product to Delete')->first();

    $this->assertDatabaseHas('invoice_products', ['name' => 'Product to Delete']);

    $deletedName = $this->fiscusService->deleteInvoiceProduct($invoiceProduct);

    expect($deletedName)->toBe('Product to Delete');

    $this->assertDatabaseMissing('invoice_products', ['name' => 'Product to Delete']);
    $this->assertDatabaseCount('invoice_lines', 0);
});
