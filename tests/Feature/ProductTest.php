<?php

use App\Models\InvoiceGroup;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    // Clear cache and logout any existing session
    \Cache::flush();
    if (\Sentinel::check()) {
        \Sentinel::logout();
    }

    // Create required data for tests
    Product::factory()->create();
    InvoiceGroup::factory()->create(['status' => true]);
});

afterEach(function () {
    // Logout after each test
    if (\Sentinel::check()) {
        \Sentinel::logout();
    }
});

test('create product requires authentication', function () {
    $this->json('POST', '/product', ['name' => 'Sally'])
        ->assertDontSee('Whoops')
        ->assertSee('Unauthorized.');
});

test('create product validates required fields', function () {
    $sentinelUser = \Sentinel::registerAndActivate([
        'email' => 'producttest@example.com',
        'password' => 'password',
    ]);
    \Sentinel::login($sentinelUser);
    $user = User::find($sentinelUser->id);

    $this->actingAs($user)
        ->withSession([])
        ->json('POST', '/product', ['name' => 'Sally'])
        ->assertDontSee('Whoops')
        ->assertJsonMissing(['success' => true])
        ->assertJsonStructure(['errors']);
});

test('create product successfully', function () {
    $sentinelUser = \Sentinel::registerAndActivate([
        'email' => 'producttest@example.com',
        'password' => 'password',
    ]);
    \Sentinel::login($sentinelUser);
    $user = User::find($sentinelUser->id);

    $this->actingAs($user)
        ->withSession([])
        ->json('POST', '/product', ['name' => 'Sally', 'productPrice' => '3.56'])
        ->assertDontSee('Whoops')
        ->assertJson([
            'success' => true,
            'name' => 'Sally',
            'price' => '3.56',
        ]);

    $this->assertDatabaseHas('products', ['name' => 'Sally', 'price' => '3.56']);
});

test('edit product requires authentication', function () {
    $product = Product::factory()->create([
        'name' => 'Sally',
        'price' => 3.56,
    ]);

    $this->json('PUT', '/product/'.$product->id, ['name' => 'Max'])
        ->assertDontSee('Whoops')
        ->assertSee('Unauthorized.');
});

test('edit product validates required fields', function () {
    $product = Product::factory()->create([
        'name' => 'Sally',
        'price' => 3.56,
    ]);

    $sentinelUser = \Sentinel::registerAndActivate([
        'email' => 'productedit@example.com',
        'password' => 'password',
    ]);
    \Sentinel::login($sentinelUser);
    $user = User::find($sentinelUser->id);

    $this->actingAs($user)
        ->withSession([])
        ->json('PUT', '/product/'.$product->id, ['productName' => 'Max', 'productPrice' => null])
        ->assertDontSee('Whoops')
        ->assertJsonMissing(['success' => true])
        ->assertJsonStructure(['errors']);
});

test('edit product successfully', function () {
    $product = Product::factory()->create([
        'name' => 'Sally',
        'price' => 3.56,
    ]);

    $sentinelUser = \Sentinel::registerAndActivate([
        'email' => 'productedit@example.com',
        'password' => 'password',
    ]);
    \Sentinel::login($sentinelUser);
    $user = User::find($sentinelUser->id);

    $this->actingAs($user)
        ->withSession([])
        ->json('PUT', '/product/'.$product->id, ['productName' => 'Max', 'productPrice' => 3.56])
        ->assertDontSee('Whoops')
        ->assertJson([
            'success' => true,
        ]);

    $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Max', 'price' => 3.56]);
});

test('delete product requires authentication', function () {
    $product = Product::factory()->create([
        'name' => 'Sally',
        'price' => 3.56,
    ]);

    $this->json('DELETE', '/product/'.$product->id)
        ->assertDontSee('Whoops')
        ->assertSee('Unauthorized.');
});

test('delete product successfully', function () {
    $product = Product::factory()->create([
        'name' => 'Sally',
        'price' => 3.56,
    ]);

    \Sentinel::logout();
    $sentinelUser = \Sentinel::registerAndActivate([
        'email' => 'productdelete@example.com',
        'password' => 'password',
    ]);
    \Sentinel::login($sentinelUser);
    $user = User::find($sentinelUser->id);

    $this->actingAs($user)
        ->withSession([])
        ->json('DELETE', '/product/'.$product->id)
        ->assertDontSee('Whoops')
        ->assertJson([
            'success' => true,
        ]);

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});
