<?php

declare(strict_types=1);

use App\Models\Product;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    // Clear cache before each test
    Cache::flush();
});

test('cache is cleared when product is created', function () {
    // Populate cache
    Cache::put('products', ['test' => 'data'], 1);
    expect(Cache::has('products'))->toBeTrue();

    // Create a product
    Product::factory()->create(['name' => 'Test Product', 'price' => 10.00]);

    // Cache should be cleared
    expect(Cache::has('products'))->toBeFalse();
});

test('cache is cleared when product is updated', function () {
    // Create a product first
    $product = Product::factory()->create(['name' => 'Original Name', 'price' => 10.00]);

    // Populate cache
    Cache::put('products', ['test' => 'data'], 1);
    expect(Cache::has('products'))->toBeTrue();

    // Update the product
    $product->update(['name' => 'Updated Name']);

    // Cache should be cleared
    expect(Cache::has('products'))->toBeFalse();
});

test('cache is cleared when product is deleted', function () {
    // Create a product
    $product = Product::factory()->create(['name' => 'Test Product', 'price' => 10.00]);

    // Populate cache
    Cache::put('products', ['test' => 'data'], 1);
    expect(Cache::has('products'))->toBeTrue();

    // Delete the product
    $product->delete();

    // Cache should be cleared
    expect(Cache::has('products'))->toBeFalse();
});

test('cache is cleared when product attribute is changed and saved', function () {
    // Create a product
    $product = Product::factory()->create(['name' => 'Test Product', 'price' => 10.00]);

    // Populate cache
    Cache::put('products', ['test' => 'data'], 1);
    expect(Cache::has('products'))->toBeTrue();

    // Update via attribute assignment
    $product->price = 20.00;
    $product->save();

    // Cache should be cleared
    expect(Cache::has('products'))->toBeFalse();
});

test('cache is not cleared when product is only retrieved', function () {
    // Create a product
    $product = Product::factory()->create(['name' => 'Test Product', 'price' => 10.00]);

    // Populate cache
    Cache::put('products', ['test' => 'data'], 1);
    expect(Cache::has('products'))->toBeTrue();

    // Just retrieve the product
    Product::find($product->id);

    // Cache should still exist
    expect(Cache::has('products'))->toBeTrue();
});

test('multiple product operations clear cache each time', function () {
    // Create first product
    $product1 = Product::factory()->create(['name' => 'Product 1', 'price' => 10.00]);
    expect(Cache::has('products'))->toBeFalse();

    // Populate cache
    Cache::put('products', ['test' => 'data1'], 1);
    expect(Cache::has('products'))->toBeTrue();

    // Create second product
    Product::factory()->create(['name' => 'Product 2', 'price' => 20.00]);
    expect(Cache::has('products'))->toBeFalse();

    // Populate cache again
    Cache::put('products', ['test' => 'data2'], 1);
    expect(Cache::has('products'))->toBeTrue();

    // Update first product
    $product1->update(['price' => 15.00]);
    expect(Cache::has('products'))->toBeFalse();
});
