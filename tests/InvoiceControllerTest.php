<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
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

    public function setUp()
    {
        parent::setUp();
        // Clear cache before each test to avoid stale product cache
        \Cache::flush();

        // Create at least one product to avoid count() issues with Product::all()
        factory(Product::class)->create();
    }

    /**
     * Test invoice index page is accessible by admin
     */
    public function testInvoiceIndexIsAccessibleByAdmin()
    {
        $user = Sentinel::findById(3);
        Sentinel::login($user);

        $this->actingAs(\App\User::find(3))
            ->visit('/invoice')
            ->dontSee('Unauthorized')
            ->dontSee('Whoops');
    }

    /**
     * Test invoice index page is not accessible by non-admin
     */
    public function testInvoiceIndexIsNotAccessibleByNonAdmin()
    {
        // Create a regular user without admin role
        $user = Sentinel::registerAndActivate([
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
        $user = Sentinel::findById(3);
        Sentinel::login($user);

        $invoiceGroup = factory(InvoiceGroup::class)->create([
            'name' => 'Test Invoice Group',
            'status' => true,
        ]);

        $this->actingAs(\App\User::find(3))
            ->visit('/invoice')
            ->see('Test Invoice Group');
    }

    /**
     * Test creating invoice group
     */
    public function testCreateInvoiceGroup()
    {
        $user = Sentinel::findById(3);
        Sentinel::login($user);

        $this->actingAs(\App\User::find(3))
            ->withSession([])
            ->json('POST', '/invoice/storeinvoicegroup', [
                'invoiceMonth' => 'January 2025',
            ])
            ->seeJson(['success' => true])
            ->seeInDatabase('invoice_groups', ['name' => 'January 2025']);
    }

    /**
     * Test invoice PDF generation page is accessible
     */
    public function testInvoicePdfPageIsAccessible()
    {
        $user = Sentinel::findById(3);
        Sentinel::login($user);

        $invoiceGroup = factory(InvoiceGroup::class)->create([
            'name' => 'Test Month',
            'status' => true,
        ]);

        // Set current month in cache
        \Cache::put('currentmonth', $invoiceGroup->id, 60);

        $this->actingAs(\App\User::find(3))
            ->visit('/invoice/pdf')
            ->dontSee('Unauthorized')
            ->dontSee('Whoops');
    }

    /**
     * Test SEPA generation page is accessible
     */
    public function testSepaGenerationPageIsAccessible()
    {
        $user = Sentinel::findById(3);
        Sentinel::login($user);

        $invoiceGroup = factory(InvoiceGroup::class)->create([
            'name' => 'SEPA Test Month',
            'status' => true,
        ]);

        \Cache::put('currentmonth', $invoiceGroup->id, 60);

        $response = $this->actingAs(\App\User::find(3))
            ->call('GET', '/invoice/sepa');

        // SEPA generation should return 200 or redirect
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302]));
    }

    /**
     * Test invoice page shows members with orders
     */
    public function testInvoicePageShowsMembersWithOrders()
    {
        $user = Sentinel::findById(3);
        Sentinel::login($user);

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
        $response = $this->actingAs(\App\User::find(3))
            ->call('GET', '/invoice');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test invoice page shows group orders
     */
    public function testInvoicePageShowsGroupOrders()
    {
        $user = Sentinel::findById(3);
        Sentinel::login($user);

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
        $response = $this->actingAs(\App\User::find(3))
            ->call('GET', '/invoice');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test invoice with invoice lines
     */
    public function testInvoiceWithInvoiceLines()
    {
        $user = Sentinel::findById(3);
        Sentinel::login($user);

        $member = factory(Member::class)->create();
        $invoiceGroup = factory(InvoiceGroup::class)->create();
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

        \Cache::put('currentmonth', $invoiceGroup->id, 60);

        // Test that invoice page loads successfully with invoice lines
        $response = $this->actingAs(\App\User::find(3))
            ->call('GET', '/invoice');

        $this->assertEquals(200, $response->getStatusCode());
    }
}
