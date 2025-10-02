<?php

use App\Models\InvoiceGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->sentinelUser = \Sentinel::registerAndActivate([
        'email' => 'invoice@example.com',
        'password' => 'password',
    ]);
    \Sentinel::login($this->sentinelUser);
    $this->user = User::find($this->sentinelUser->id);

    // Create admin role
    $role = \Sentinel::getRoleRepository()->createModel()->create([
        'name' => 'Admin',
        'slug' => 'admin',
    ]);
    $role->users()->attach($this->user);

    $this->invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);
});

test('invoice index page loads', function () {
    $response = $this->get('/invoice');

    $response->assertStatus(200)
        ->assertViewIs('invoice.index');
});

test('invoice page requires admin authentication', function () {
    // Remove admin role
    $role = \Sentinel::findRoleBySlug('admin');
    $role->users()->detach($this->user);

    $response = $this->get('/invoice');

    $response->assertRedirect('/');
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
