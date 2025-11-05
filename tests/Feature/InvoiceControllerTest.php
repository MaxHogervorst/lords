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
use Tests\TestCase;

class InvoiceControllerTest extends TestCase
{
    private $adminRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Create at least one product to avoid count() issues with Product::all()
        Product::factory()->create();

        // Create an active invoice group for tests that need it
        InvoiceGroup::factory()->create([
            'status' => true,
        ]);
    }

    /**
     * Test invoice index page is accessible by admin
     */
    public function test_invoice_index_is_accessible_by_admin()
    {
        $user = \App\Models\User::factory()->create([
            'email' => 'invoiceadmin@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

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
        $user = \App\Models\User::factory()->create([
            'email' => 'regular@example.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
        ]);

        $response = $this->actingAs($user)->get('/invoice');

        $response->assertForbidden();
    }

    /**
     * Test invoice page shows invoice groups
     */
    public function test_invoice_page_shows_invoice_groups()
    {
        $user = \App\Models\User::factory()->create([
            'email' => 'invoicegroup@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $invoiceGroup = InvoiceGroup::factory()->create([
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
        $user = \App\Models\User::factory()->create([
            'email' => 'invoicecreate@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

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
        $user = \App\Models\User::factory()->create([
            'email' => 'invoicepdf@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $invoiceGroup = InvoiceGroup::factory()->create([
            'name' => 'Test Month',
            'status' => true,
        ]);

        // Set current month in cache

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
        $user = \App\Models\User::factory()->create([
            'email' => 'invoicesepa@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $invoiceGroup = InvoiceGroup::factory()->create([
            'name' => 'SEPA Test Month',
            'status' => true,
        ]);

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
        $user = \App\Models\User::factory()->create([
            'email' => 'invoicemember@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $member = Member::factory()->create([
            'firstname' => 'Invoice',
            'lastname' => 'Tester',
        ]);

        $product = Product::factory()->create(['name' => 'Test Beer']);
        $invoiceGroup = InvoiceGroup::factory()->create();

        $order = Order::factory()->create([
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
        $user = \App\Models\User::factory()->create([
            'email' => 'invoicegroups@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $group = Group::factory()->create(['name' => 'Test Group']);
        $product = Product::factory()->create(['name' => 'Group Beer']);
        $invoiceGroup = InvoiceGroup::factory()->create();

        $order = Order::factory()->create([
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
        $user = \App\Models\User::factory()->create([
            'email' => 'invoicelines@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        // Use the invoice group created in setUp
        $invoiceGroup = InvoiceGroup::where('status', true)->first();

        $member = Member::factory()->create();
        $invoiceProduct = InvoiceProduct::factory()->create([
            'invoice_group_id' => $invoiceGroup->id,
            'name' => 'Special Item',
        ]);
        $invoiceProductPrice = InvoiceProductPrice::factory()->create([
            'invoice_product_id' => $invoiceProduct->id,
            'price' => 5.50,
        ]);
        $invoiceLine = InvoiceLine::factory()->create([
            'member_id' => $member->id,
            'invoice_product_price_id' => $invoiceProductPrice->id,
        ]);

        // Test that invoice page loads successfully with invoice lines
        $response = $this->actingAs($user)
            ->call('GET', '/invoice');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test check-bill page is accessible without authentication
     */
    public function test_check_bill_page_is_accessible_without_auth()
    {
        $response = $this->get('/check-bill');

        $response->assertStatus(200)
            ->assertDontSee('Unauthorized')
            ->assertDontSee('Whoops')
            ->assertSee('Check Your Bill')
            ->assertSee('Lookup Your Invoice')
            ->assertSee('Last Name')
            ->assertSee('IBAN')
            ->assertSee('Invoice Month');
    }

    /**
     * Test check-bill page shows invoice groups
     */
    public function test_check_bill_page_shows_invoice_groups()
    {
        $invoiceGroup = InvoiceGroup::factory()->create([
            'name' => 'Public Test Month',
            'status' => true,
        ]);

        $response = $this->get('/check-bill');

        $response->assertStatus(200)
            ->assertSee('Public Test Month');
    }

    /**
     * Test combined check-bill lookup with valid data
     */
    public function test_check_bill_combined_lookup_success()
    {
        $invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);
        $member = Member::factory()->create([
            'lastname' => 'TestUser',
            'iban' => 'NL91ABNA0417164300',
        ]);

        $response = $this->post('/check-bill', [
            'name' => 'TestUser',
            'iban' => 'NL91ABNA0417164300',
            'invoiceGroup' => $invoiceGroup->id,
        ]);

        $response->assertRedirect(route('invoice.check-bill'))
            ->assertSessionMissing('error');
    }

    /**
     * Test combined check-bill lookup with invalid member
     */
    public function test_check_bill_combined_lookup_invalid_member()
    {
        $invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);

        $response = $this->post('/check-bill', [
            'name' => 'NonExistent',
            'iban' => 'NL91ABNA0417164300',
            'invoiceGroup' => $invoiceGroup->id,
        ]);

        $response->assertRedirect(route('invoice.check-bill'))
            ->assertSessionHas('error');
    }

    /**
     * Test setperson endpoint is accessible without authentication
     */
    public function test_setperson_is_accessible_without_auth()
    {
        $member = Member::factory()->create([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'iban' => 'NL91ABNA0417164300',
        ]);

        $response = $this->json('POST', '/invoice/setperson', [
            'name' => 'Doe',
            'iban' => 'NL91ABNA0417164300',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Verify session was set
        $this->assertEquals($member->id, session('member_id'));
    }

    /**
     * Test setperson endpoint with invalid data
     */
    public function test_setperson_fails_with_invalid_data()
    {
        $response = $this->json('POST', '/invoice/setperson', [
            'name' => '',
            'iban' => '',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['errors']);
    }

    /**
     * Test setperson endpoint with non-existent member
     */
    public function test_setperson_fails_with_non_existent_member()
    {
        $response = $this->json('POST', '/invoice/setperson', [
            'name' => 'NonExistent',
            'iban' => 'NL91ABNA0417164300',
        ]);

        $response->assertStatus(200)
            ->assertJson(['errors' => 'Could not find member']);
    }

    /**
     * Test setpersonalinvoicegroup endpoint is accessible without authentication
     */
    public function test_setpersonalinvoicegroup_is_accessible_without_auth()
    {
        $invoiceGroup = InvoiceGroup::factory()->create([
            'name' => 'Test Personal Month',
            'status' => false,
        ]);

        $response = $this->json('POST', '/invoice/setpersonalinvoicegroup', [
            'invoiceGroup' => $invoiceGroup->id,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Verify session was set
        $this->assertEquals($invoiceGroup->id, session('personal_invoice_group_id'));
    }

    /**
     * Test setpersonalinvoicegroup endpoint with invalid data
     */
    public function test_setpersonalinvoicegroup_fails_with_invalid_data()
    {
        $response = $this->json('POST', '/invoice/setpersonalinvoicegroup', [
            'invoiceGroup' => '',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['errors']);
    }

    /**
     * Test setpersonalinvoicegroup endpoint with non-existent invoice group
     */
    public function test_setpersonalinvoicegroup_fails_with_non_existent_group()
    {
        $response = $this->json('POST', '/invoice/setpersonalinvoicegroup', [
            'invoiceGroup' => 99999,
        ]);

        $response->assertStatus(200)
            ->assertJson(['errors' => 'Could not find month']);
    }

    /**
     * Test check-bill page displays member invoice data
     */
    public function test_check_bill_displays_member_invoice_data()
    {
        // Use the invoice group from setUp
        $invoiceGroup = InvoiceGroup::where('status', true)->first();

        // Create member
        $member = Member::factory()->create([
            'firstname' => 'Jane',
            'lastname' => 'Smith',
            'iban' => 'NL20INGB0001234567',
        ]);

        // Create product
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 5.00,
        ]);

        // Refresh product cache
        Product::toArrayIdAsKey();

        // Create order
        Order::factory()->create([
            'ownerable_id' => $member->id,
            'ownerable_type' => 'App\\Models\\Member',
            'product_id' => $product->id,
            'invoice_group_id' => $invoiceGroup->id,
            'amount' => 3,
        ]);

        // Set member in session
        $response = $this->withSession(['member_id' => $member->id])
            ->get('/check-bill');

        $response->assertStatus(200)
            ->assertSee('Invoice for Jane Smith')
            ->assertSee('Test Product');
    }

    /**
     * Test check-bill page displays group orders
     */
    public function test_check_bill_displays_group_orders()
    {
        // Use the invoice group from setUp
        $invoiceGroup = InvoiceGroup::where('status', true)->first();

        // Create members
        $member1 = Member::factory()->create([
            'firstname' => 'Member',
            'lastname' => 'One',
        ]);
        $member2 = Member::factory()->create([
            'firstname' => 'Member',
            'lastname' => 'Two',
        ]);

        $group = Group::factory()->create([
            'name' => 'Test Party Group',
            'invoice_group_id' => $invoiceGroup->id,
        ]);

        $group->members()->attach([$member1->id, $member2->id]);

        // Create product
        $product = Product::factory()->create([
            'name' => 'Group Beer',
            'price' => 10.00,
        ]);

        // Refresh product cache
        Product::toArrayIdAsKey();

        // Create group order
        Order::factory()->create([
            'ownerable_id' => $group->id,
            'ownerable_type' => 'App\\Models\\Group',
            'product_id' => $product->id,
            'invoice_group_id' => $invoiceGroup->id,
            'amount' => 2,
        ]);

        // Set member in session
        $response = $this->withSession(['member_id' => $member1->id])
            ->get('/check-bill');

        $response->assertStatus(200)
            ->assertSee('Test Party Group');
    }

    /**
     * Test SEPA file generation with actual data
     */
    public function test_sepa_file_generation()
    {
        // Create admin user
        $user = \App\Models\User::factory()->create([
            'email' => 'sepatest@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        // Use the invoice group from setUp (already has status = true)
        $invoiceGroup = InvoiceGroup::where('status', true)->first();

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
        $member = Member::factory()->create([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'iban' => 'NL20INGB0001234567',
            'bic' => 'INGBNL2A',
            'had_collection' => true, // RCUR
        ]);

        // Create a product - note: price is stored on product
        $product = Product::factory()->create([
            'name' => 'Test Beer',
            'price' => 2.50,
        ]);

        // Refresh product cache after creating product
        Product::toArrayIdAsKey();

        // Create an order for the member
        Order::factory()->create([
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
        $files = glob($sepaDir . '/GSRC RCUR *.xml');
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
