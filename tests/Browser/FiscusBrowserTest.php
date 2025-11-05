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
        ->assertSee('Actions')
        ->assertSee('Create New Product');
});

test('has search field on fiscus page', function () {
    actingAs($this->user);

    $page = $this->visit('/fiscus');

    // Verify search field exists
    $html = $page->content();
    expect($html)->toContain('Search products');
});

test('can open create modal', function () {
    actingAs($this->user);

    // Create some members to select
    \App\Models\Member::factory()->create(['firstname' => 'Alice', 'lastname' => 'Test']);
    \App\Models\Member::factory()->create(['firstname' => 'Bob', 'lastname' => 'Test']);

    $page = $this->visit('/fiscus');

    // Click create button via JavaScript
    $page->script("Alpine.\$data(document.querySelector('[x-data]')).openCreate()");

    // Wait for modal to open
    sleep(1);

    // Verify modal is open
    $isOpen = $page->script('Alpine.$data(document.querySelector("[x-data]")).isOpen');
    expect($isOpen)->toBeTrue();

    // Verify form elements are present
    $page->assertSee('Product Details')
        ->assertSee('Product Name')
        ->assertSee('Pricing')
        ->assertSee('Price Per Person')
        ->assertSee('Select Members');
});

test('can open edit modal and load product data', function () {
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

    $page = $this->visit('/fiscus');

    // Open edit modal via JavaScript
    $page->script("Alpine.\$data(document.querySelector('[x-data]')).openEdit({$product->id})");

    // Wait for AJAX to load data
    sleep(2);

    // Verify modal is open in edit mode
    $isOpen = $page->script('Alpine.$data(document.querySelector("[x-data]")).isOpen');
    expect($isOpen)->toBeTrue();

    $mode = $page->script('Alpine.$data(document.querySelector("[x-data]")).mode');
    expect($mode)->toBe('edit');

    // Verify product data loaded
    $productName = $page->script('Alpine.$data(document.querySelector("[x-data]")).form.productName');
    expect($productName)->toBe('Test Beer Product');

    $pricePerPerson = $page->script('Alpine.$data(document.querySelector("[x-data]")).form.productPricePerPerson');
    expect($pricePerPerson)->toBe(5.50);

    // Verify member is pre-selected
    $selectedMembers = $page->script('Alpine.$data(document.querySelector("[x-data]")).form.selectedMembers');
    expect($selectedMembers)->toContain($member1->id);
});

test('modal form shows summary with calculated values', function () {
    actingAs($this->user);

    // Create some members
    $member1 = \App\Models\Member::factory()->create(['firstname' => 'Alice', 'lastname' => 'Test']);
    $member2 = \App\Models\Member::factory()->create(['firstname' => 'Bob', 'lastname' => 'Test']);

    $page = $this->visit('/fiscus');

    // Open create modal
    $page->script("Alpine.\$data(document.querySelector('[x-data]')).openCreate()");
    sleep(1);

    // Set form values
    $page->script("Alpine.\$data(document.querySelector('[x-data]')).form.productName = 'Test Product'");
    $page->script("Alpine.\$data(document.querySelector('[x-data]')).form.productPricePerPerson = 10.50");
    $page->script("Alpine.\$data(document.querySelector('[x-data]')).form.selectedMembers = [{$member1->id}, {$member2->id}]");

    // Verify calculated total price
    $calculatedTotal = $page->script('Alpine.$data(document.querySelector("[x-data]")).calculatedTotalPrice');
    expect($calculatedTotal)->toBe(21.0); // 2 members * 10.50

    // Verify member count
    $memberCount = $page->script('Alpine.$data(document.querySelector("[x-data]")).selectedMemberCount');
    expect($memberCount)->toBe(2);
});
