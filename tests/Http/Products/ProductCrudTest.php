<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'productcrud@example.com',
        'password' => bcrypt('password'),
    ]);
    $this->actingAs($this->user);
});

test('create product successfully via JSON', function () {
    $response = $this
        ->json('POST', '/product', [
            'name' => 'Beer',
            'productPrice' => 2.50,
        ]);

    $response->assertJson([
        'success' => true,
        'name' => 'Beer',
        'price' => 2.50,
    ]);

    $this->assertDatabaseHas('products', [
        'name' => 'Beer',
        'price' => 2.50,
    ]);
});

test('create product validates required fields', function () {
    $response = $this
        ->json('POST', '/product', [
            'name' => 'Beer',
        ]);

    $response->assertJsonStructure(['errors']);
});

test('edit product returns JSON with product data', function () {
    $product = Product::factory()->create();

    $response = $this
        ->json('GET', "/product/{$product->id}/edit");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'id',
            'name',
            'price',
        ]);
});

test('update product successfully', function () {
    $product = Product::factory()->create([
        'name' => 'Beer',
        'price' => 2.50,
    ]);

    $response = $this
        ->json('PUT', "/product/{$product->id}", [
            'productName' => 'Wine',
            'productPrice' => 5.00,
        ]);

    $response->assertJson(['success' => true]);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Wine',
        'price' => 5.00,
    ]);
});

test('delete product successfully', function () {
    $product = Product::factory()->create();

    $response = $this
        ->json('DELETE', "/product/{$product->id}");

    $response->assertJson(['success' => true]);

    $this->assertDatabaseMissing('products', [
        'id' => $product->id,
    ]);
});
