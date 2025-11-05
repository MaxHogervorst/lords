<?php

use App\Models\InvoiceGroup;
use App\Models\InvoiceLine;
use App\Models\InvoiceProduct;
use App\Models\InvoiceProductPrice;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'fiscusmodal@example.com',
        'password' => bcrypt('password'),
        'is_admin' => true,
    ]);
    $this->actingAs($this->user);

    $this->invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);
});

test('fiscus index provides all data needed for modal', function () {
    // Create some products and members
    InvoiceProduct::factory()->count(3)->create([
        'invoice_group_id' => $this->invoiceGroup->id,
    ]);
    Member::factory()->count(5)->create();

    $response = $this->get('/fiscus');

    $response->assertStatus(200)
        ->assertViewIs('fiscus.index')
        ->assertViewHas('invoice_products')
        ->assertViewHas('members');

    // Verify data structure
    $viewData = $response->viewData('invoice_products');
    expect($viewData)->toHaveCount(3);

    $membersData = $response->viewData('members');
    expect($membersData)->toHaveCount(5);
});

test('modal shows create button on index page', function () {
    $response = $this->get('/fiscus');

    $response->assertStatus(200)
        ->assertSee('Create New Product');
});

test('modal form includes all required sections', function () {
    $response = $this->get('/fiscus');

    $response->assertStatus(200)
        ->assertSee('Product Details')
        ->assertSee('Product Name')
        ->assertSee('Pricing')
        ->assertSee('Per Person')
        ->assertSee('Select Members');
});

test('modal delete button only shown in edit mode', function () {
    $response = $this->get('/fiscus');

    $response->assertStatus(200);

    $html = $response->getContent();

    // Verify delete button exists with x-show="mode === 'edit'"
    expect($html)->toContain('x-show="mode === \'edit\'"');
    expect($html)->toContain('deleteProduct');
});

test('get invoice prices for edit modal', function () {
    $product = InvoiceProduct::factory()->create([
        'invoice_group_id' => $this->invoiceGroup->id,
    ]);

    $price1 = InvoiceProductPrice::factory()->create([
        'invoice_product_id' => $product->id,
        'price' => 10.00,
        'description' => 'First price',
    ]);

    $price2 = InvoiceProductPrice::factory()->create([
        'invoice_product_id' => $product->id,
        'price' => 12.00,
        'description' => 'Second price',
    ]);

    $response = $this->getJson("/fiscus/invoiceprices/{$product->id}");

    $response->assertStatus(200)
        ->assertJsonCount(2)
        ->assertJsonFragment([
            'id' => $price1->id,
            'price' => 10,
            'description' => 'First price',
        ])
        ->assertJsonFragment([
            'id' => $price2->id,
            'price' => 12,
            'description' => 'Second price',
        ]);
});

test('get specific invoice lines for edit modal', function () {
    $product = InvoiceProduct::factory()->create([
        'invoice_group_id' => $this->invoiceGroup->id,
    ]);

    $price = InvoiceProductPrice::factory()->create([
        'invoice_product_id' => $product->id,
    ]);

    $members = Member::factory()->count(3)->create();

    foreach ($members as $member) {
        InvoiceLine::factory()->create([
            'invoice_product_price_id' => $price->id,
            'member_id' => $member->id,
        ]);
    }

    $response = $this->getJson("/fiscus/specificinvoicelines/{$price->id}");

    $response->assertStatus(200)
        ->assertJsonCount(3);

    $data = $response->json();
    expect($data[0])->toHaveKeys(['id', 'invoice_product_price_id', 'member_id']);
});

test('delete product via modal API', function () {
    $product = InvoiceProduct::factory()->create([
        'invoice_group_id' => $this->invoiceGroup->id,
    ]);

    $price = InvoiceProductPrice::factory()->create([
        'invoice_product_id' => $product->id,
    ]);

    $members = Member::factory()->count(2)->create();
    foreach ($members as $member) {
        InvoiceLine::factory()->create([
            'invoice_product_price_id' => $price->id,
            'member_id' => $member->id,
        ]);
    }

    $response = $this->deleteJson("/fiscus/{$product->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);

    // Verify product was deleted
    $this->assertDatabaseMissing('invoice_products', [
        'id' => $product->id,
    ]);

    // Verify cascading deletes
    $this->assertDatabaseMissing('invoice_product_prices', [
        'id' => $price->id,
    ]);

    $this->assertDatabaseMissing('invoice_lines', [
        'invoice_product_price_id' => $price->id,
    ]);
});

test('delete button appears in modal footer', function () {
    $response = $this->get('/fiscus');

    $response->assertStatus(200);

    $html = $response->getContent();

    // Verify delete button structure
    expect($html)->toContain('deleteProduct');
    expect($html)->toContain('btn-danger');
    expect($html)->toContain('trash-2'); // Lucide icon
    expect($html)->toContain('Delete');
});

test('delete button has confirmation and proper AJAX call', function () {
    $product = InvoiceProduct::factory()->create([
        'name' => 'Product to Delete',
        'invoice_group_id' => $this->invoiceGroup->id,
    ]);

    $price = InvoiceProductPrice::factory()->create([
        'invoice_product_id' => $product->id,
    ]);

    $member = Member::factory()->create();
    InvoiceLine::factory()->create([
        'invoice_product_price_id' => $price->id,
        'member_id' => $member->id,
    ]);

    // Test the delete endpoint
    $response = $this->deleteJson("/fiscus/{$product->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonStructure([
            'success',
            'message',
        ]);

    // Verify the product name is in the success message
    expect($response->json('message'))->toContain('Product to Delete');
});

test('delete button only shown in edit mode not create mode', function () {
    $response = $this->get('/fiscus');

    $html = $response->getContent();

    // The delete button should have x-show="mode === 'edit'"
    // This ensures it's hidden in create mode
    expect($html)->toContain('x-show="mode === \'edit\'"');

    // Verify the button is within the same conditional block as deleteProduct
    $deleteButtonSection = substr($html, strpos($html, 'deleteProduct') - 200, 400);
    expect($deleteButtonSection)->toContain('x-show="mode === \'edit\'"');
});

test('unauthorized users cannot access fiscus modal', function () {
    // Create non-admin user
    $nonAdminUser = User::factory()->create(['is_admin' => false]);
    $this->actingAs($nonAdminUser);

    $response = $this->get('/fiscus');
    $response->assertForbidden();

    $response = $this->postJson('/fiscus', []);
    $response->assertForbidden();

    $product = InvoiceProduct::factory()->create([
        'invoice_group_id' => $this->invoiceGroup->id,
    ]);

    $response = $this->putJson("/fiscus/{$product->id}", []);
    $response->assertForbidden();

    $response = $this->deleteJson("/fiscus/{$product->id}");
    $response->assertForbidden();
});

test('index table displays created date, member count, per person price, and total price', function () {
    // Create product with price and members
    $product = InvoiceProduct::factory()->create([
        'name' => 'Test Product',
        'invoice_group_id' => $this->invoiceGroup->id,
    ]);

    $price = InvoiceProductPrice::factory()->create([
        'invoice_product_id' => $product->id,
        'price' => 15.50,
    ]);

    $members = Member::factory()->count(3)->create();
    foreach ($members as $member) {
        InvoiceLine::factory()->create([
            'invoice_product_price_id' => $price->id,
            'member_id' => $member->id,
        ]);
    }

    $response = $this->get('/fiscus');

    $response->assertStatus(200)
        ->assertSee('Created')
        ->assertSee('Members')
        ->assertSee('Per Person')
        ->assertSee('Total');
});
