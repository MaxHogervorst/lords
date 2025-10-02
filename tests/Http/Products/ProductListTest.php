<?php

use App\Models\InvoiceGroup;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->sentinelUser = \Sentinel::registerAndActivate([
        'email' => 'productlist@example.com',
        'password' => 'password',
    ]);
    \Sentinel::login($this->sentinelUser);
    $this->user = User::find($this->sentinelUser->id);
    $this->invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);
});

test('product index page loads', function () {
    $response = $this->get('/product');

    $response->assertStatus(200)
        ->assertViewIs('product.index');
});

test('product index displays products', function () {
    $products = Product::factory()->count(3)->create();

    $response = $this->get('/product');

    $response->assertStatus(200);

    foreach ($products as $product) {
        $response->assertSee($product->name);
    }
});

test('product index requires authentication', function () {
    \Sentinel::logout();

    $response = $this->get('/product');

    $response->assertRedirect('/auth/login');
});
