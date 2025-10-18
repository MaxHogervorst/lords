<?php

use App\Models\InvoiceGroup;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'productlist@example.com',
        'password' => bcrypt('password'),
    ]);
    $this->actingAs($this->user);
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
    auth()->logout();

    $response = $this->get('/product');

    $response->assertRedirect('/auth/login');
});
