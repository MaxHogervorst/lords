<?php

use App\Models\InvoiceGroup;
use App\Models\InvoiceProduct;
use App\Models\User;
use function Pest\Laravel\{actingAs};

beforeEach(function () {
    $this->user = User::factory()->create([
        'is_admin' => true,
    ]);
    $this->invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);
});

test('can view fiscus page', function () {
    actingAs($this->user);

    $this->visit('/fiscus')
        ->assertSee('Name')
        ->assertSee('Actions');
});

test('has search field on fiscus page', function () {
    actingAs($this->user);

    $page = $this->visit('/fiscus');

    // Verify search field exists
    $html = $page->content();
    expect($html)->toContain('name="name"');
    expect($html)->toContain('Search or Add');
});

test('can navigate to fiscus edit page', function () {
    actingAs($this->user);

    $page = $this->visit('/fiscus/edit');

    // Should see the wizard steps
    $page->assertSee('Select Product')
        ->assertSee('Add/Edit Price')
        ->assertSee('Select Members')
        ->assertSee('Summary');
});

test('can view fiscus create wizard steps', function () {
    actingAs($this->user);

    // Create some members to select
    \App\Models\Member::factory()->create(['firstname' => 'Alice', 'lastname' => 'Test']);
    \App\Models\Member::factory()->create(['firstname' => 'Bob', 'lastname' => 'Test']);

    $page = $this->visit('/fiscus/create');

    // Step 1: Add Product - verify page structure
    $page->assertSee('Add Product')
        ->assertSee('Product Name')
        ->assertSee('Total Price')
        ->assertSee('Price per person')
        ->assertSee('Description');

    // Verify Alpine.js wizard is initialized by checking for step indicators
    $html = $page->content();
    expect($html)->toContain('1. Add Product');
    expect($html)->toContain('2. Select Members');
    expect($html)->toContain('3. Summary');
});

test('can complete full fiscus edit wizard flow', function () {
    actingAs($this->user);

    // Create a product with a price and invoice lines
    $product = InvoiceProduct::factory()->create([
        'name' => 'Test Beer Product',
        'invoice_group_id' => $this->invoiceGroup->id,
    ]);

    $price = \App\Models\InvoiceProductPrice::factory()->create([
        'invoice_product_id' => $product->id,
        'price' => 5.50,
        'description' => 'Test description',
    ]);

    $member1 = \App\Models\Member::factory()->create(['firstname' => 'Alice', 'lastname' => 'Smith']);
    $member2 = \App\Models\Member::factory()->create(['firstname' => 'Bob', 'lastname' => 'Jones']);

    \App\Models\InvoiceLine::factory()->create([
        'invoice_product_price_id' => $price->id,
        'member_id' => $member1->id,
    ]);

    $page = $this->visit('/fiscus/edit');

    // Step 1: Verify we start at step 1 and can select product
    $page->assertSee('1. Select Product');

    // Use JavaScript to interact with Alpine.js directly
    $currentStep = $page->script('Alpine.$data(document.querySelector("[x-data]")).currentStep');
    expect($currentStep)->toBe(1);

    // Select product via JavaScript
    $page->script("Alpine.\$data(document.querySelector('[x-data]')).selectedProduct = {$product->id}");

    // Move to step 2
    $page->script("Alpine.\$data(document.querySelector('[x-data]')).nextStep()");

    // Wait for AJAX to load prices
    sleep(2);

    // Step 2: Verify we're at step 2 and prices loaded
    $currentStep = $page->script('Alpine.$data(document.querySelector("[x-data]")).currentStep');
    expect($currentStep)->toBe(2);

    $page->assertSee('2. Add/Edit Price');

    // Check that availablePrices has data
    $availablePrices = $page->script('Alpine.$data(document.querySelector("[x-data]")).availablePrices.length');
    expect($availablePrices)->toBeGreaterThan(0);

    // Select the price
    $page->script("Alpine.\$data(document.querySelector('[x-data]')).selectedPrice = {$price->id}");
    $page->script("Alpine.\$data(document.querySelector('[x-data]')).onPriceSelect()");

    // Move to step 3
    $page->script("Alpine.\$data(document.querySelector('[x-data]')).nextStep()");

    // Wait for AJAX to load members
    sleep(2);

    // Step 3: Verify we're at step 3 and members loaded
    $currentStep = $page->script('Alpine.$data(document.querySelector("[x-data]")).currentStep');
    expect($currentStep)->toBe(3);

    $page->assertSee('3. Select Members')
        ->assertSee('Alice')
        ->assertSee('Bob');

    // Verify existing member is pre-selected
    $selectedMembers = $page->script('Alpine.$data(document.querySelector("[x-data]")).selectedMembers');
    expect($selectedMembers)->toContain($member1->id);

    // Add second member
    $page->script("Alpine.\$data(document.querySelector('[x-data]')).selectedMembers.push({$member2->id})");

    // Move to step 4
    $page->script("Alpine.\$data(document.querySelector('[x-data]')).nextStep()");

    // Step 4: Verify we're at summary
    $currentStep = $page->script('Alpine.$data(document.querySelector("[x-data]")).currentStep');
    expect($currentStep)->toBe(4);

    $page->assertSee('4. Summary')
        ->assertSee('Test Beer Product');

    // Verify we successfully navigated through all 4 steps
    expect(true)->toBeTrue();
});
