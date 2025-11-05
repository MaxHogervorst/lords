<?php

use App\Models\InvoiceGroup;
use App\Models\Product;
use App\Models\User;

use function Pest\Laravel\{actingAs};

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);
});

test('can view product page', function () {
    actingAs($this->user);

    $this->visit('/product')
        ->assertSee('Add Product')
        ->assertSee('Name')
        ->assertSee('Price')
        ->assertSee('Actions');
});

test('can create a new product via the UI', function () {
    actingAs($this->user);

    $page = $this->visit('/product');

    // Type into the name and price fields (use form selectors to avoid modal ambiguity)
    $page->type('input[placeholder="Search or Add"]', 'Test Beer')
        ->type('input#productprice', '3.50');

    // Submit the form and wait for the product to appear
    $page->press('Add Product')
        ->waitForText('Test Beer');

    expect(Product::where('name', 'Test Beer')->exists())->toBeTrue();
});

test('can view product list', function () {
    actingAs($this->user);

    $product = Product::factory()->create([
        'name' => 'Sample Product',
        'price' => 5.99,
    ]);

    $this->visit('/product')
        ->assertSee('Sample Product')
        ->assertSee('5.99');
});

test('can search for products', function () {
    actingAs($this->user);

    Product::factory()->create([
        'name' => 'Alpha Product',
        'price' => 3.00,
    ]);
    Product::factory()->create([
        'name' => 'Beta Product',
        'price' => 4.00,
    ]);

    $page = $this->visit('/product')
        ->assertSee('Alpha Product')
        ->assertSee('Beta Product');

    // Type in search field
    $page->type('input[placeholder="Search or Add"]', 'Alpha')
        ->assertSee('Alpha Product')
        ->assertDontSee('Beta Product');
});

test('can open product edit modal', function () {
    actingAs($this->user);

    $product = Product::factory()->create([
        'name' => 'Edit Test Product',
        'price' => 7.50,
    ]);

    $page = $this->visit('/product')
        ->assertSee('Edit Test Product');

    // Click edit button
    $page->click('button[data-id="' . $product->id . '"]')
        ->waitForText('Product Name')
        ->assertSee('Edit Test Product')
        ->assertSee('7.5')
        ->assertVisible('#product-edit')
        ->assertVisible('[data-testid="product-name-input"]')
        ->assertVisible('[data-testid="product-price-input"]');
});
