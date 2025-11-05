<?php

use App\Models\InvoiceGroup;
use App\Models\InvoiceProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'fiscuslist@example.com',
        'password' => bcrypt('password'),
        'is_admin' => true,
    ]);
    $this->actingAs($this->user);

    $this->invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);
});

test('fiscus index page loads', function () {
    $response = $this->get('/fiscus');

    $response->assertStatus(200)
        ->assertViewIs('fiscus.index');
});

test('fiscus index displays invoice products', function () {
    $invoiceProducts = InvoiceProduct::factory()->count(3)->create([
        'invoice_group_id' => $this->invoiceGroup->id,
    ]);

    $response = $this->get('/fiscus');

    $response->assertStatus(200);

    foreach ($invoiceProducts as $product) {
        $response->assertSee($product->name);
    }
});

test('fiscus index has members data for modal', function () {
    $response = $this->get('/fiscus');

    $response->assertStatus(200)
        ->assertViewIs('fiscus.index')
        ->assertViewHas('members');
});

test('fiscus requires admin authentication', function () {
    // Remove admin privileges
    $this->user->update(['is_admin' => false]);

    $response = $this->get('/fiscus');

    $response->assertForbidden();
});
