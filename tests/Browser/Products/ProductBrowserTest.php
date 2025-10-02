<?php

use App\Models\InvoiceGroup;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->sentinelUser = \Sentinel::registerAndActivate([
        'email' => 'productbrowser@example.com',
        'password' => 'password',
    ]);
    \Sentinel::login($this->sentinelUser);
    $this->user = User::find($this->sentinelUser->id);
    $this->invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);
});

test('can view product list page', function () {
    Product::factory()->count(3)->create();

    visit('/product')
        ->assertSee('Products')
        ->screenshot('product-list');
});

test('can create new product via UI', function () {
    visit('/product')
        ->click('Add Product')
        ->type('name', 'Test Product')
        ->type('price', '19.99')
        ->submit()
        ->assertSee('Product created')
        ->screenshot('product-created');

    expect(Product::where('name', 'Test Product')->exists())->toBeTrue();
});

test('can edit existing product', function () {
    $product = Product::factory()->create([
        'name' => 'Original Product',
    ]);

    visit("/product/{$product->id}/edit")
        ->type('name', 'Updated Product')
        ->submit()
        ->assertSee('Product updated')
        ->screenshot('product-updated');

    expect(Product::find($product->id)->name)->toBe('Updated Product');
});

test('can view product details', function () {
    $product = Product::factory()->create([
        'name' => 'Sample Product',
    ]);

    visit("/product/{$product->id}")
        ->assertSee('Sample Product')
        ->screenshot('product-details');
});

test('can delete product', function () {
    $product = Product::factory()->create();

    visit('/product')
        ->click('Delete')
        ->assertSee('Product deleted')
        ->screenshot('product-deleted');

    expect(Product::find($product->id))->toBeNull();
});

test('requires authentication to access products', function () {
    \Sentinel::logout();

    visit('/product')
        ->assertUrlIs(url('/auth/login'))
        ->screenshot('product-requires-auth');
});
