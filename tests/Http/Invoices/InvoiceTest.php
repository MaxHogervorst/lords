<?php

use App\Models\InvoiceGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'invoice@example.com',
        'password' => bcrypt('password'),
        'is_admin' => true,
    ]);
    $this->actingAs($this->user);

    $this->invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);
});

test('invoice index page loads', function () {
    $response = $this->get('/invoice');

    $response->assertStatus(200)
        ->assertViewIs('invoice.index');
});

test('invoice page requires admin authentication', function () {
    // Remove admin privileges
    $this->user->update(['is_admin' => false]);

    $response = $this->get('/invoice');

    $response->assertForbidden();
});

test('select invoice group successfully', function () {
    $response = $this->json('POST', '/invoice/selectinvoicegroup', [
        'invoiceGroup' => $this->invoiceGroup->id,
    ]);

    $response->assertStatus(200);
});

test('create new invoice group successfully', function () {
    $response = $this->json('POST', '/invoice/storeinvoicegroup', [
        'invoiceMonth' => 'October 2025',
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('invoice_groups', [
        'name' => 'October 2025',
    ]);
});
