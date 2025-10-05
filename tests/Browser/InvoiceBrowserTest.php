<?php

use App\Models\Group;
use App\Models\InvoiceGroup;
use App\Models\InvoiceProduct;
use App\Models\InvoiceProductPrice;
use App\Models\Member;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use function Pest\Laravel\{actingAs};

beforeEach(function () {
    $this->user = User::factory()->create([
        'is_admin' => true,
    ]);
    $this->invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);
});

test('can view invoice page', function () {
    actingAs($this->user);

    $this->visit('/invoice')
        ->assertSee('Select Month')
        ->assertSee('Export to Excel')
        ->assertSee('Export to PDF')
        ->assertSee('Export to SEPA');
});

test('can see member invoices with personal orders', function () {
    actingAs($this->user);

    // Create member with order
    $member = Member::factory()->create([
        'firstname' => 'John',
        'lastname' => 'Doe',
    ]);
    $product = Product::factory()->create([
        'name' => 'Test Beer',
        'price' => 3.50,
    ]);
    Order::factory()->create([
        'ownerable_id' => $member->id,
        'ownerable_type' => 'App\\Models\\Member',
        'product_id' => $product->id,
        'amount' => 2,
        'invoice_group_id' => $this->invoiceGroup->id,
    ]);

    $this->visit('/invoice')
        ->assertSee('John Doe')
        ->assertSee('Test Beer')
        ->assertSee('2') // Amount
        ->assertSee('Total:');
});

test('can see member invoices with group orders', function () {
    actingAs($this->user);

    // Create members and group
    $member1 = Member::factory()->create(['firstname' => 'Alice', 'lastname' => 'Smith']);
    $member2 = Member::factory()->create(['firstname' => 'Bob', 'lastname' => 'Jones']);

    $group = Group::factory()->create([
        'name' => 'Test Group',
        'invoice_group_id' => $this->invoiceGroup->id,
    ]);

    // Add members to group
    $group->members()->attach([$member1->id, $member2->id]);

    // Create product and group order
    $product = Product::factory()->create([
        'name' => 'Pizza',
        'price' => 20.00,
    ]);
    Order::factory()->create([
        'ownerable_id' => $group->id,
        'ownerable_type' => 'App\\Models\\Group',
        'product_id' => $product->id,
        'amount' => 1,
        'invoice_group_id' => $this->invoiceGroup->id,
    ]);

    $this->visit('/invoice')
        ->assertSee('Alice Smith')
        ->assertSee('Test Group')
        ->assertSee('Groupmembers: 2');
});

test('can see member invoices with fiscus invoice lines', function () {
    actingAs($this->user);

    // Create member
    $member = Member::factory()->create([
        'firstname' => 'Charlie',
        'lastname' => 'Brown',
    ]);

    // Create invoice product with price
    $invoiceProduct = InvoiceProduct::factory()->create([
        'name' => 'Monthly Fee',
        'invoice_group_id' => $this->invoiceGroup->id,
    ]);
    $productPrice = InvoiceProductPrice::factory()->create([
        'invoice_product_id' => $invoiceProduct->id,
        'price' => 5.00,
        'description' => 'Standard monthly fee',
    ]);

    // Create invoice line linking member to product price
    $member->invoice_lines()->create([
        'invoice_product_price_id' => $productPrice->id,
    ]);

    $this->visit('/invoice')
        ->assertSee('Charlie Brown')
        ->assertSee('Monthly Fee')
        ->assertSee('Standard monthly fee');
});

test('pagination displays correctly with many members', function () {
    actingAs($this->user);

    // Create 15 members with orders (more than default 10 per page)
    // Use padded numbers to ensure alphabetical sorting works as expected
    $product = Product::factory()->create([
        'name' => 'Test Product',
        'price' => 5.00,
    ]);

    for ($i = 1; $i <= 15; $i++) {
        $member = Member::factory()->create([
            'firstname' => "Member",
            'lastname' => sprintf("Test%02d", $i), // Test01, Test02, etc.
        ]);
        Order::factory()->create([
            'ownerable_id' => $member->id,
            'ownerable_type' => 'App\\Models\\Member',
            'product_id' => $product->id,
            'amount' => 1,
            'invoice_group_id' => $this->invoiceGroup->id,
        ]);
    }

    // Page 1 - should show first 10 members alphabetically
    $this->visit('/invoice')
        ->assertSee('Test01')
        ->assertSee('Test10')
        ->assertDontSee('Test11') // 11th member should be on page 2
        ->assertSee('Show:')
        ->assertSee('Next »');

    // Navigate to Page 2
    $this->visit('/invoice?page=2')
        ->assertSee('Test11')
        ->assertSee('Test15')
        ->assertDontSee('Test01'); // First member should not be on page 2
});

test('can change items per page', function () {
    actingAs($this->user);

    // Create 30 members
    $product = Product::factory()->create([
        'name' => 'Test Product',
        'price' => 5.00,
    ]);

    for ($i = 1; $i <= 30; $i++) {
        $member = Member::factory()->create([
            'firstname' => "Member",
            'lastname' => sprintf("Test%02d", $i), // Test01, Test02, etc.
        ]);
        Order::factory()->create([
            'ownerable_id' => $member->id,
            'ownerable_type' => 'App\\Models\\Member',
            'product_id' => $product->id,
            'amount' => 1,
            'invoice_group_id' => $this->invoiceGroup->id,
        ]);
    }

    $this->visit('/invoice')
        ->assertSee('Test01')
        ->assertDontSee('Test11')
        ->select('per_page', '25') // Change to 25 per page
        ->assertSee('Test01')
        ->assertSee('Test25')
        ->assertDontSee('Test26'); // 26th should be on page 2
});

test('pagination preserves per_page parameter across pages', function () {
    actingAs($this->user);

    // Create 30 members
    $product = Product::factory()->create([
        'name' => 'Test Product',
        'price' => 5.00,
    ]);

    for ($i = 1; $i <= 30; $i++) {
        $member = Member::factory()->create([
            'firstname' => "Member",
            'lastname' => sprintf("Test%02d", $i), // Test01, Test02, etc.
        ]);
        Order::factory()->create([
            'ownerable_id' => $member->id,
            'ownerable_type' => 'App\\Models\\Member',
            'product_id' => $product->id,
            'amount' => 1,
            'invoice_group_id' => $this->invoiceGroup->id,
        ]);
    }

    $this->visit('/invoice?per_page=25')
        ->assertSee('Test25');

    // Navigate to page 2 and verify per_page is preserved
    $this->visit('/invoice?per_page=25&page=2')
        ->assertQueryStringHas('per_page', '25')
        ->assertSee('Test26')
        ->assertSee('Test30');
});

test('does not show pagination with few members', function () {
    actingAs($this->user);

    // Create only 5 members (less than default 10 per page)
    $product = Product::factory()->create([
        'name' => 'Test Product',
        'price' => 5.00,
    ]);

    for ($i = 1; $i <= 5; $i++) {
        $member = Member::factory()->create([
            'firstname' => "Member",
            'lastname' => sprintf("Test%02d", $i), // Test01, Test02, etc.
        ]);
        Order::factory()->create([
            'ownerable_id' => $member->id,
            'ownerable_type' => 'App\\Models\\Member',
            'product_id' => $product->id,
            'amount' => 1,
            'invoice_group_id' => $this->invoiceGroup->id,
        ]);
    }

    $this->visit('/invoice')
        ->assertSee('Test01')
        ->assertSee('Test05')
        ->assertDontSee('Show:'); // Pagination controls should not appear
});
