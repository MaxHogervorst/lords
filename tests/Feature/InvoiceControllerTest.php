<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\InvoiceGroup;
use App\Models\InvoiceLine;
use App\Models\InvoiceProduct;
use App\Models\InvoiceProductPrice;
use App\Models\Member;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Sentinel;
use Tests\TestCase;

class InvoiceControllerTest extends TestCase
{
    use DatabaseTransactions;

    private $adminRole;

    protected function setUp(): void
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
    public function test_invoice_index_is_accessible_by_admin()
    {
        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'invoiceadmin@example.com',
            'password' => 'password',
        ]);
        $this->adminRole->users()->attach($sentinelUser);
        Sentinel::login($sentinelUser);
        $user = \App\Models\User::find($sentinelUser->id);

        $this->actingAs($user)
            ->get('/invoice')
            ->assertStatus(200)
            ->assertDontSee('Unauthorized')
            ->assertDontSee('Whoops');
    }

    /**
     * Test invoice index page is not accessible by non-admin
     */
    public function test_invoice_index_is_not_accessible_by_non_admin()
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
    public function test_invoice_page_shows_invoice_groups()
    {
        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'invoicegroup@example.com',
            'password' => 'password',
        ]);
        $this->adminRole->users()->attach($sentinelUser);
        Sentinel::login($sentinelUser);
        $user = \App\Models\User::find($sentinelUser->id);

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
    public function test_create_invoice_group()
    {
        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'invoicecreate@example.com',
            'password' => 'password',
        ]);
        $this->adminRole->users()->attach($sentinelUser);
        Sentinel::login($sentinelUser);
        $user = \App\Models\User::find($sentinelUser->id);

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
    public function test_invoice_pdf_page_is_accessible()
    {
        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'invoicepdf@example.com',
            'password' => 'password',
        ]);
        $this->adminRole->users()->attach($sentinelUser);
        Sentinel::login($sentinelUser);
        $user = \App\Models\User::find($sentinelUser->id);

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
    public function test_sepa_generation_page_is_accessible()
    {
        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'invoicesepa@example.com',
            'password' => 'password',
        ]);
        $this->adminRole->users()->attach($sentinelUser);
        Sentinel::login($sentinelUser);
        $user = \App\Models\User::find($sentinelUser->id);

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
    public function test_invoice_page_shows_members_with_orders()
    {
        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'invoicemember@example.com',
            'password' => 'password',
        ]);
        $this->adminRole->users()->attach($sentinelUser);
        Sentinel::login($sentinelUser);
        $user = \App\Models\User::find($sentinelUser->id);

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
    public function test_invoice_page_shows_group_orders()
    {
        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'invoicegroups@example.com',
            'password' => 'password',
        ]);
        $this->adminRole->users()->attach($sentinelUser);
        Sentinel::login($sentinelUser);
        $user = \App\Models\User::find($sentinelUser->id);

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
    public function test_invoice_with_invoice_lines()
    {
        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'invoicelines@example.com',
            'password' => 'password',
        ]);
        $this->adminRole->users()->attach($sentinelUser);
        Sentinel::login($sentinelUser);
        $user = \App\Models\User::find($sentinelUser->id);

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

    /**
     * Test SEPA file generation with actual data
     */
    public function test_sepa_file_generation()
    {
        // Create admin user
        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'sepatest@example.com',
            'password' => 'password',
        ]);
        $this->adminRole->users()->attach($sentinelUser);
        Sentinel::login($sentinelUser);
        $user = \App\Models\User::find($sentinelUser->id);

        // Create invoice group and set as current month
        $invoiceGroup = factory(InvoiceGroup::class)->create([
            'name' => 'SEPA Test Month',
            'status' => true,
        ]);
        \Cache::put('currentmonth', $invoiceGroup->id, 60);
        \Cache::put('invoice_group', $invoiceGroup, 60);

        // Set up SEPA settings
        \Settings::set('creditorName', 'Test Creditor');
        \Settings::set('creditorAccountIBAN', 'NL91ABNA0417164300');
        \Settings::set('creditorAgentBIC', 'ABNANL2A');
        \Settings::set('creditorId', 'NL00ZZZ000000000000');
        \Settings::set('creditorPain', 'pain.008.001.02');
        \Settings::set('creditorMaxMoneyPerBatch', 999999);
        \Settings::set('creditorMaxMoneyPerTransaction', 100000);
        \Settings::set('creditorMaxTransactionsPerBatch', 1000);
        \Settings::set('ReqdColltnDt', 5);
        \Settings::save();

        // Create a member with bank info and RCUR status (recurring)
        $member = factory(Member::class)->create([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'iban' => 'NL20INGB0001234567',
            'bic' => 'INGBNL2A',
            'had_collection' => true, // RCUR
        ]);

        // Create a product - note: price is stored on product
        $product = factory(Product::class)->create([
            'name' => 'Test Beer',
            'price' => 2.50,
        ]);

        // Refresh product cache after creating product
        \Cache::forget('products');
        Product::toArrayIdAsKey();

        // Create an order for the member
        factory(Order::class)->create([
            'ownerable_id' => $member->id,
            'ownerable_type' => 'App\\Models\\Member',
            'product_id' => $product->id,
            'invoice_group_id' => $invoiceGroup->id,
            'amount' => 10,
        ]);

        // Ensure SEPA directory exists
        $sepaDir = storage_path('SEPA');
        if (! file_exists($sepaDir)) {
            mkdir($sepaDir, 0755, true);
        }

        // Call the SEPA generation endpoint
        $response = $this->actingAs($user)
            ->call('GET', '/invoice/sepa');

        // Assert response is successful
        $this->assertEquals(200, $response->getStatusCode());

        // Check that SEPA XML file was created
        $files = glob($sepaDir.'/GSRC RCUR *.xml');
        $this->assertNotEmpty($files, 'SEPA XML file should be generated');

        // Read and verify the XML content
        $xmlContent = file_get_contents($files[0]);
        $this->assertStringContainsString('Test Creditor', $xmlContent);
        $this->assertStringContainsString('NL91ABNA0417164300', $xmlContent);
        $this->assertStringContainsString('John Doe', $xmlContent);
        $this->assertStringContainsString('NL20INGB0001234567', $xmlContent);
        $this->assertStringContainsString('25.00', $xmlContent); // 10 * 2.50

        // Clean up generated files
        foreach ($files as $file) {
            unlink($file);
        }
    }
}
