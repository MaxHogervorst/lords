<?php

use App\Models\InvoiceGroup;
use App\Models\InvoiceProduct;
use App\Models\Member;
use App\Models\User;
use App\Services\FiscusService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'fiscuscrud@example.com',
        'password' => bcrypt('password'),
        'is_admin' => true,
    ]);
    $this->actingAs($this->user);

    $this->invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);
});

test('create invoice product successfully', function () {
    $members = Member::factory()->count(3)->create();

    $response = $this
        ->json('POST', '/fiscus', [
            'finalproductname' => 'Pizza Night',
            'finalpriceperperson' => 8.50,
            'finalproductdescription' => 'Pizza for everyone',
            'member' => $members->pluck('id')->toArray(),
        ]);

    $response->assertJson(['success' => true]);

    $this->assertDatabaseHas('invoice_products', [
        'name' => 'Pizza Night',
        'invoice_group_id' => $this->invoiceGroup->id,
    ]);

    $this->assertDatabaseCount('invoice_lines', 3);
});

test('create invoice product validates required fields', function () {
    $response = $this
        ->json('POST', '/fiscus', [
            'finalproductname' => 'Pizza Night',
        ]);

    $response->assertJsonStructure(['errors']);
});

test('get invoice prices returns product prices', function () {
    $invoiceProduct = InvoiceProduct::factory()->create([
        'invoice_group_id' => $this->invoiceGroup->id,
    ]);

    $response = $this
        ->json('GET', "/fiscus/invoiceprices/{$invoiceProduct->id}");

    $response->assertStatus(200);
});

test('update invoice product successfully', function () {
    $invoiceProduct = InvoiceProduct::factory()->create([
        'invoice_group_id' => $this->invoiceGroup->id,
    ]);
    $members = Member::factory()->count(2)->create();

    $response = $this
        ->json('PUT', "/fiscus/{$invoiceProduct->id}", [
            'finalpriceperperson' => 10.00,
            'finalproductdescription' => 'Updated description',
            'member' => $members->pluck('id')->toArray(),
        ]);

    $response->assertJson(['success' => true]);
});

test('delete invoice product successfully', function () {
    $fiscusService = new FiscusService();
    $members = Member::factory()->count(2)->create();

    $data = [
        'finalproductname' => 'Test Product',
        'finalpriceperperson' => 5.00,
        'finalproductdescription' => 'Test',
        'member' => $members->pluck('id')->toArray(),
    ];

    $fiscusService->createInvoiceProduct($data, $this->invoiceGroup->id);
    $invoiceProduct = InvoiceProduct::where('name', 'Test Product')->first();

    $response = $this
        ->json('DELETE', "/fiscus/{$invoiceProduct->id}");

    $response->assertJson(['success' => true]);

    $this->assertDatabaseMissing('invoice_products', [
        'id' => $invoiceProduct->id,
    ]);

    $this->assertDatabaseCount('invoice_lines', 0);
});
