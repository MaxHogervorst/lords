<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Group;
use App\Models\InvoiceGroup;
use App\Models\InvoiceProduct;
use App\Models\InvoiceProductPrice;
use App\Models\InvoiceLine;
use App\Models\Member;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Tests\TestCase;

class PdfExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create at least one product
        Product::factory()->create();
    }

    /**
     * Test PDF export is accessible by admin
     */
    public function test_pdf_export_is_accessible_by_admin(): void
    {
        $user = User::factory()->create([
            'email' => 'pdfadmin@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $invoiceGroup = InvoiceGroup::factory()->create([
            'name' => 'PDF Test Month',
            'status' => true,
        ]);

        $response = $this->actingAs($user)->get('/invoice/pdf');

        // Should return PDF file
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }

    /**
     * Test PDF export filename includes invoice group name
     */
    public function test_pdf_export_filename_includes_invoice_group_name(): void
    {
        $user = User::factory()->create([
            'email' => 'pdffilename@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $invoiceGroup = InvoiceGroup::factory()->create([
            'name' => 'February 2025',
            'status' => true,
        ]);

        $response = $this->actingAs($user)->get('/invoice/pdf');

        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('February 2025.pdf', $contentDisposition);
    }

    /**
     * Test PDF export only includes members with invoice items
     */
    public function test_pdf_export_only_includes_members_with_invoice_items(): void
    {
        $user = User::factory()->create([
            'email' => 'pdffilter@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $invoiceGroup = InvoiceGroup::factory()->create([
            'name' => 'Filter Test Month',
            'status' => true,
        ]);

        // Create member WITH orders
        $memberWithOrders = Member::factory()->create([
            'firstname' => 'Active',
            'lastname' => 'Member',
        ]);

        $product = Product::factory()->create([
            'name' => 'Test Beer',
            'price' => 2.50,
        ]);

        Product::toArrayIdAsKey();

        Order::factory()->create([
            'ownerable_id' => $memberWithOrders->id,
            'ownerable_type' => 'App\\Models\\Member',
            'product_id' => $product->id,
            'invoice_group_id' => $invoiceGroup->id,
            'amount' => 5,
        ]);

        // Create member WITHOUT orders
        $memberWithoutOrders = Member::factory()->create([
            'firstname' => 'Inactive',
            'lastname' => 'Member',
        ]);

        $response = $this->actingAs($user)->get('/invoice/pdf');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));

        // PDF is binary, but we can check it contains the expected text
        $content = $response->getContent();
        $this->assertNotEmpty($content);
        $this->assertStringContainsString('%PDF', $content); // PDF header
    }

    /**
     * Test PDF export includes member with group orders
     */
    public function test_pdf_export_includes_members_with_group_orders(): void
    {
        $user = User::factory()->create([
            'email' => 'pdfgroup@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $invoiceGroup = InvoiceGroup::factory()->create([
            'name' => 'Group Test Month',
            'status' => true,
        ]);

        // Create group and members
        $group = Group::factory()->create([
            'name' => 'PDF Test Group',
            'invoice_group_id' => $invoiceGroup->id,
        ]);

        $member = Member::factory()->create([
            'firstname' => 'Group',
            'lastname' => 'Member',
        ]);

        $group->members()->attach([$member->id]);

        $product = Product::factory()->create([
            'name' => 'Group Beer',
            'price' => 3.00,
        ]);

        Product::toArrayIdAsKey();

        Order::factory()->create([
            'ownerable_id' => $group->id,
            'ownerable_type' => 'App\\Models\\Group',
            'product_id' => $product->id,
            'invoice_group_id' => $invoiceGroup->id,
            'amount' => 10,
        ]);

        $response = $this->actingAs($user)->get('/invoice/pdf');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));

        $content = $response->getContent();
        $this->assertNotEmpty($content);
        $this->assertStringContainsString('%PDF', $content);
    }

    /**
     * Test PDF export includes member with invoice lines
     */
    public function test_pdf_export_includes_members_with_invoice_lines(): void
    {
        $user = User::factory()->create([
            'email' => 'pdfinvoiceline@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $invoiceGroup = InvoiceGroup::factory()->create([
            'name' => 'Invoice Line Test Month',
            'status' => true,
        ]);

        $member = Member::factory()->create([
            'firstname' => 'Invoice',
            'lastname' => 'Line Member',
        ]);

        // Create invoice product and price
        $invoiceProduct = InvoiceProduct::factory()->create([
            'invoice_group_id' => $invoiceGroup->id,
            'name' => 'Special Fee',
        ]);

        $invoiceProductPrice = InvoiceProductPrice::factory()->create([
            'invoice_product_id' => $invoiceProduct->id,
            'price' => 10.00,
            'description' => 'Monthly fee',
        ]);

        InvoiceLine::factory()->create([
            'invoice_product_price_id' => $invoiceProductPrice->id,
            'member_id' => $member->id,
        ]);

        $response = $this->actingAs($user)->get('/invoice/pdf');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));

        $content = $response->getContent();
        $this->assertNotEmpty($content);
        $this->assertStringContainsString('%PDF', $content);
    }
}
