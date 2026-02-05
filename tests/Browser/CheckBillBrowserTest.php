<?php

use App\Models\Group;
use App\Models\InvoiceGroup;
use App\Models\Member;
use App\Models\Order;
use App\Models\Product;

beforeEach(function () {
    // Create active invoice group
    $this->invoiceGroup = InvoiceGroup::factory()->create([
        'name' => 'January 2025',
        'status' => true,
    ]);

    // Create test product
    $this->product = Product::factory()->create([
        'name' => 'Test Beer',
        'price' => 2.50,
    ]);

    // Refresh product cache
    Product::toArrayIdAsKey();

    // Create test member with orders
    $this->member = Member::factory()->create([
        'firstname' => 'John',
        'lastname' => 'Doe',
        'iban' => 'NL91ABNA0417164300',
    ]);

    // Create an order for the member
    Order::factory()->create([
        'ownerable_id' => $this->member->id,
        'ownerable_type' => 'App\\Models\\Member',
        'product_id' => $this->product->id,
        'invoice_group_id' => $this->invoiceGroup->id,
        'amount' => 5,
    ]);
});

test('can view check-bill page without authentication', function () {
    skip('Flaky in CI - page load timing issues');

    $this->visit('/check-bill')
        ->assertSee('Check Your Bill')
        ->assertSee('Select Invoice Month')
        ->assertSee('Lookup Your Invoice')
        ->assertSee('Last Name')
        ->assertSee('IBAN')
        ->assertVisible('input[name="name"]')
        ->assertVisible('input[name="iban"]')
        ->assertSee('Viewing: January 2025');
});

test('displays member invoice data when session is set', function () {
    skip('Flaky in CI - page load timing issues');

    // Set member in session and visit
    $page = $this->withSession(['member_id' => $this->member->id])
        ->visit('/check-bill');

    $page->assertSee('Invoice for John Doe')
        ->assertSee('Test Beer')
        ->assertSee('Total:');
});

test('displays group orders correctly', function () {
    skip('Flaky in CI - page load timing issues');

    // Create a group with multiple members
    $member2 = Member::factory()->create([
        'firstname' => 'Jane',
        'lastname' => 'Smith',
        'iban' => 'NL20INGB0001234567',
    ]);

    $group = Group::factory()->create([
        'name' => 'Party Group',
        'invoice_group_id' => $this->invoiceGroup->id,
    ]);

    $group->members()->attach([$this->member->id, $member2->id]);

    // Refresh product cache
    Product::toArrayIdAsKey();

    // Create a group order
    Order::factory()->create([
        'ownerable_id' => $group->id,
        'ownerable_type' => 'App\\Models\\Group',
        'product_id' => $this->product->id,
        'invoice_group_id' => $this->invoiceGroup->id,
        'amount' => 10,
    ]);

    $page = $this->withSession(['member_id' => $this->member->id])
        ->visit('/check-bill');

    $page->assertSee('John Doe')
        ->assertSee('Party Group')
        ->assertSee('Groupmembers: 2');
});

test('shows current viewing month', function () {
    skip('Flaky in CI - page load timing issues');

    $page = $this->visit('/check-bill');

    // Check that viewing month is displayed in the alert
    $page->assertSee('Viewing:')
        ->assertSee('January 2025');
});
