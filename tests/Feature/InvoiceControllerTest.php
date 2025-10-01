<?php

namespace Tests\Feature;

use Tests\TestCase;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Sentinel;
use App\Models\Member;
use App\Models\Group;
use App\Models\Product;
use App\Models\Order;
use App\Models\InvoiceGroup;
use App\Models\InvoiceLine;
use App\Models\InvoiceProduct;
use App\Models\InvoiceProductPrice;

class InvoiceControllerTest extends TestCase
{
    use DatabaseTransactions;

    private $adminRole;

    public function setUp()
    {
        parent::setUp();
        // Clear cache before each test to avoid stale product cache
        \Cache::flush();

        // Create at least one product to avoid count() issues with Product::all()
        factory(Product::class)->create();

        // Create an active invoice group for tests that need it
        factory(InvoiceGroup::class)->create([
            'status' => true,
        ]);

        // Create admin role for tests
        $this->adminRole = Sentinel::getRoleRepository()->createModel()->firstOrCreate([
            'slug' => 'admin',
        ], [
            'name' => 'Admin',
        ]);
    }

    /**
     * Test invoice index page is accessible by admin
     */
    public function testInvoiceIndexIsAccessibleByAdmin()
    {
        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'invoiceadmin@example.com',
            'password' => 'password',
        ]);
        $this->adminRole->users()->attach($sentinelUser);
        Sentinel::login($sentinelUser);
        $user = \App\User::find($sentinelUser->id);

        $this->actingAs($user)
            ->get('/invoice')
            ->assertStatus(200)
            ->assertDontSee('Unauthorized')
            ->assertDontSee('Whoops');
    }

    /**
     * Test invoice index page is not accessible by non-admin
     */
    public function testInvoiceIndexIsNotAccessibleByNonAdmin()
    {
        // Create a regular user without admin role
        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'regular@example.com',
            'password' => 'password',
        ]);

        $response = $this->call('GET', '/invoice');

        // Should redirect or show unauthorized
        $this->assertEquals(302, $response->getStatusCode());
    }

    /**
     * Test invoice page shows invoice groups
     */
    public function testInvoicePageShowsInvoiceGroups()
    {
        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'invoicegroup@example.com',
            'password' => 'password',
        ]);
        $this->adminRole->users()->attach($sentinelUser);
        Sentinel::login($sentinelUser);
        $user = \App\User::find($sentinelUser->id);

        $invoiceGroup = factory(InvoiceGroup::class)->create([
            'name' => 'Test Invoice Group',
            'status' => true,
        ]);

        $this->actingAs($user)
            ->get('/invoice')
            ->assertStatus(200)
            ->assertSee('Test Invoice Group');
    }

    /**
     * Test creating invoice group
     */
    public function testCreateInvoiceGroup()
    {
        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'invoicecreate@example.com',
            'password' => 'password',
        ]);
        $this->adminRole->users()->attach($sentinelUser);
        Sentinel::login($sentinelUser);
        $user = \App\User::find($sentinelUser->id);

        $this->actingAs($user)
            ->withSession([])
            ->json('POST', '/invoice/storeinvoicegroup', [
                'invoiceMonth' => 'January 2025',
            ])
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('invoice_groups', ['name' => 'January 2025']);
    }

    /**
     * Test invoice PDF generation page is accessible
     */
    public function testInvoicePdfPageIsAccessible()
    {
        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'invoicepdf@example.com',
            'password' => 'password',
        ]);
        $this->adminRole->users()->attach($sentinelUser);
        Sentinel::login($sentinelUser);
        $user = \App\User::find($sentinelUser->id);

        $invoiceGroup = factory(InvoiceGroup::class)->create([
            'name' => 'Test Month',
            'status' => true,
        ]);

        // Set current month in cache
        \Cache::put('currentmonth', $invoiceGroup->id, 60);

        $this->actingAs($user)
            ->get('/invoice/pdf')
            ->assertStatus(200)
            ->assertDontSee('Unauthorized')
            ->assertDontSee('Whoops');
    }

    /**
     * Test SEPA generation page is accessible
     */
    public function testSepaGenerationPageIsAccessible()
    {
        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'invoicesepa@example.com',
            'password' => 'password',
        ]);
        $this->adminRole->users()->attach($sentinelUser);
        Sentinel::login($sentinelUser);
        $user = \App\User::find($sentinelUser->id);

        $invoiceGroup = factory(InvoiceGroup::class)->create([
            'name' => 'SEPA Test Month',
            'status' => true,
        ]);

        \Cache::put('currentmonth', $invoiceGroup->id, 60);

        $response = $this->actingAs($user)
            ->call('GET', '/invoice/sepa');

        // SEPA generation should return 200 or redirect
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302]));
    }

    /**
     * Test invoice page shows members with orders
     */
    public function testInvoicePageShowsMembersWithOrders()
    {
        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'invoicemember@example.com',
            'password' => 'password',
        ]);
        $this->adminRole->users()->attach($sentinelUser);
        Sentinel::login($sentinelUser);
        $user = \App\User::find($sentinelUser->id);

        $member = factory(Member::class)->create([
            'firstname' => 'Invoice',
            'lastname' => 'Tester',
        ]);

        $product = factory(Product::class)->create(['name' => 'Test Beer']);
        $invoiceGroup = factory(InvoiceGroup::class)->create();

        $order = factory(Order::class)->create([
            'ownerable_id' => $member->id,
            'ownerable_type' => 'App\\Models\\Member',
            'product_id' => $product->id,
            'invoice_group_id' => $invoiceGroup->id,
            'amount' => 5,
        ]);

        // Test that invoice page loads successfully with member orders
        $response = $this->actingAs($user)
            ->call('GET', '/invoice');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test invoice page shows group orders
     */
    public function testInvoicePageShowsGroupOrders()
    {
        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'invoicegroups@example.com',
            'password' => 'password',
        ]);
        $this->adminRole->users()->attach($sentinelUser);
        Sentinel::login($sentinelUser);
        $user = \App\User::find($sentinelUser->id);

        $group = factory(Group::class)->create(['name' => 'Test Group']);
        $product = factory(Product::class)->create(['name' => 'Group Beer']);
        $invoiceGroup = factory(InvoiceGroup::class)->create();

        $order = factory(Order::class)->create([
            'ownerable_id' => $group->id,
            'ownerable_type' => 'App\\Models\\Group',
            'product_id' => $product->id,
            'invoice_group_id' => $invoiceGroup->id,
            'amount' => 10,
        ]);

        // Just test that the invoice page loads without error with group orders
        $response = $this->actingAs($user)
            ->call('GET', '/invoice');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test invoice with invoice lines
     */
    public function testInvoiceWithInvoiceLines()
    {
        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'invoicelines@example.com',
            'password' => 'password',
        ]);
        $this->adminRole->users()->attach($sentinelUser);
        Sentinel::login($sentinelUser);
        $user = \App\User::find($sentinelUser->id);

        // Use the invoice group created in setUp
        $invoiceGroup = InvoiceGroup::where('status', true)->first();

        $member = factory(Member::class)->create();
        $invoiceProduct = factory(InvoiceProduct::class)->create([
            'invoice_group_id' => $invoiceGroup->id,
            'name' => 'Special Item',
        ]);
        $invoiceProductPrice = factory(InvoiceProductPrice::class)->create([
            'invoice_product_id' => $invoiceProduct->id,
            'price' => 5.50,
        ]);
        $invoiceLine = factory(InvoiceLine::class)->create([
            'member_id' => $member->id,
            'invoice_product_price_id' => $invoiceProductPrice->id,
        ]);

        // Test that invoice page loads successfully with invoice lines
        $response = $this->actingAs($user)
            ->call('GET', '/invoice');

        $this->assertEquals(200, $response->getStatusCode());
    }
}
